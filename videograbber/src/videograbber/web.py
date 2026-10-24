"""Локальная страница: ссылка, прогресс и скачивание готового MP4."""

from __future__ import annotations

import argparse
import json
import re
import secrets
import shutil
import sys
import tempfile
import threading
import time
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from importlib.resources import files
from pathlib import Path
from urllib.parse import quote, unquote, urlsplit

from videograbber.errors import DownloadError, UserError, VideograbberError
from videograbber.grab import GrabResult, grab, grab_audio
from videograbber.media import ProgressEvent, format_bytes, format_duration, parse_quality
from videograbber.youtube import canonical_youtube_url, fetch_thumbnail, inspect_youtube

_PAGE = files("videograbber").joinpath("web_page.html").read_bytes()
_MAX_BODY = 65536
_JOB_TTL_SECONDS = 20 * 60
_PAGE_QUALITIES = {"best", "1080", "720", "480", "360", "240", "144", "mp3"}
_HOST = re.compile(r"^[A-Za-z0-9.\[\]:-]{1,200}$")
_VIDEO_ID = re.compile(r"^[A-Za-z0-9_-]{11}$")


class Busy(Exception):
    """Уже качается столько роликов, сколько сервер готов собирать одновременно."""


class Job:
    def __init__(self, job_id: str) -> None:
        self.id = job_id
        self.status = "running"
        self.message = "Начинаю"
        self.percent: int | None = None
        self.filename = "video.mp4"
        self.detail = ""
        self.path: Path | None = None
        self._condition = threading.Condition()

    def update(self, event: ProgressEvent) -> None:
        with self._condition:
            self.message = event.message
            if event.percent is not None:
                self.percent = event.percent
            self._condition.notify_all()

    def succeed(self, result: GrabResult) -> None:
        with self._condition:
            self.status = "done"
            self.path = result.path
            self.filename = result.path.name
            self.percent = 100
            self.message = "Файл готов"
            self.detail = _describe(result)
            self._condition.notify_all()

    def fail(self, message: str) -> None:
        with self._condition:
            self.status = "error"
            self.message = message
            self.percent = None
            self._condition.notify_all()

    def snapshot(self) -> dict:
        with self._condition:
            payload = {
                "id": self.id,
                "status": self.status,
                "message": self.message,
                "percent": self.percent,
                "filename": self.filename,
                "detail": self.detail,
            }
            done = self.status == "done"
        if done:
            payload["download"] = f"/api/jobs/{self.id}/file"
        return payload

    def wait(self, timeout: float) -> None:
        with self._condition:
            self._condition.wait(timeout)


class App:
    def __init__(self, grabber, storage: Path, max_jobs: int, inspector=None, thumb_fetcher=None) -> None:
        self.grabber = grabber
        self.storage = storage
        self.max_jobs = max_jobs
        self.inspector = inspector or inspect_youtube
        self.thumb_fetcher = thumb_fetcher or fetch_thumbnail
        self.jobs: dict[str, Job] = {}
        self.thumbs: dict[str, bytes] = {}
        self._previews = 0
        self._lock = threading.Lock()

    def create(self, url: str, quality: str) -> Job:
        cleaned = canonical_youtube_url(url)
        if quality not in _PAGE_QUALITIES:
            raise UserError("Выберите качество от 144p до 1080p или только звук в MP3.")
        if quality != "mp3":
            parse_quality(quality)
        with self._lock:
            running = sum(job.status == "running" for job in self.jobs.values())
            if running >= self.max_jobs:
                raise Busy("Уже идёт сборка. Дождитесь её окончания и повторите.")
            job = Job(secrets.token_urlsafe(18))
            self.jobs[job.id] = job
        threading.Thread(
            target=self._run,
            args=(job, cleaned, quality),
            name=f"videograbber-{job.id[:6]}",
            daemon=True,
        ).start()
        return job

    def job(self, job_id: str) -> Job | None:
        with self._lock:
            return self.jobs.get(job_id)

    def _run(self, job: Job, url: str, quality: str) -> None:
        folder = self.storage / job.id
        folder.mkdir(parents=True, exist_ok=True)
        try:
            result = self.grabber(url, output=folder, quality=quality, on_progress=job.update)
        except VideograbberError as exc:
            job.fail(_web_error(str(exc)))
            shutil.rmtree(folder, ignore_errors=True)
            return
        except Exception as exc:
            job.fail(_web_error(str(exc) or "Не удалось собрать видео."))
            shutil.rmtree(folder, ignore_errors=True)
            return
        job.succeed(result)
        _delete_later(folder)

    def preview(self, url: str) -> dict:
        cleaned = canonical_youtube_url(url)
        with self._lock:
            if self._previews >= self.max_jobs:
                raise Busy("Уже идёт проверка ссылки. Подождите немного и повторите.")
            self._previews += 1
        try:
            catalog = self.inspector(cleaned)
        finally:
            with self._lock:
                self._previews -= 1
        if not isinstance(catalog, dict):
            raise DownloadError("Не удалось прочитать ролик.")
        return catalog

    def thumbnail(self, video_id: str) -> bytes | None:
        if not _VIDEO_ID.fullmatch(video_id):
            return None
        with self._lock:
            cached = self.thumbs.get(video_id)
        if cached is not None:
            return cached
        data = self.thumb_fetcher(video_id)
        if not data:
            return None
        with self._lock:
            if len(self.thumbs) > 32:
                self.thumbs.clear()
            self.thumbs[video_id] = data
        return data

    def build_handler(self):
        app = self

        class Handler(BaseHTTPRequestHandler):
            protocol_version = "HTTP/1.1"

            def do_GET(self) -> None:
                self._route()

            def do_POST(self) -> None:
                self._route()

            def log_message(self, fmt: str, *args) -> None:
                return None

            def _route(self) -> None:
                path = unquote(urlsplit(self.path).path)
                if self.command == "GET" and path == "/":
                    self._page()
                    return
                if self.command == "GET" and path == "/favicon.ico":
                    self._empty()
                    return
                if self.command == "POST" and path == "/api/jobs":
                    self._create()
                    return
                if self.command == "POST" and path == "/api/preview":
                    self._preview()
                    return
                parts = [part for part in path.split("/") if part]
                if len(parts) == 3 and parts[0] == "api" and parts[1] == "thumb" and self.command == "GET":
                    self._thumb(parts[2])
                    return
                if len(parts) >= 3 and parts[0] == "api" and parts[1] == "jobs":
                    job = app.job(parts[2])
                    if job is None:
                        self._json(404, {"error": "Задача не найдена."})
                        return
                    if len(parts) == 3 and self.command == "GET":
                        self._json(200, job.snapshot())
                        return
                    if len(parts) == 4 and parts[3] == "events" and self.command == "GET":
                        self._events(job)
                        return
                    if len(parts) == 4 and parts[3] == "file" and self.command == "GET":
                        self._file(job)
                        return
                self._json(404, {"error": "Не найдено."})

            def _page(self) -> None:
                host = self.headers.get("Host") or "127.0.0.1:8765"
                page = _PAGE.replace(b"__ORIGIN__", _origin(host).encode())
                self.send_response(200)
                self.send_header("Content-Type", "text/html; charset=utf-8")
                self.send_header("Content-Length", str(len(page)))
                self.send_header("Cache-Control", "no-cache")
                self.send_header(
                    "Content-Security-Policy",
                    "default-src 'none'; style-src 'unsafe-inline'; "
                    "script-src 'unsafe-inline'; connect-src 'self'; "
                    "img-src 'self' data:; base-uri 'none'; form-action 'self'",
                )
                self.end_headers()
                self.wfile.write(page)

            def _empty(self) -> None:
                self.send_response(204)
                self.send_header("Content-Length", "0")
                self.end_headers()

            def _body(self) -> dict | None:
                if self.headers.get("X-Videograbber") != "1":
                    self._json(403, {"error": "Запрос отклонён."})
                    return None
                try:
                    length = int(self.headers.get("Content-Length") or "0")
                except ValueError:
                    self._json(400, {"error": "Некорректный запрос."})
                    return None
                if length <= 0 or length > _MAX_BODY:
                    self._json(400, {"error": "Некорректный запрос."})
                    return None
                try:
                    payload = json.loads(self.rfile.read(length).decode("utf-8"))
                except (UnicodeError, json.JSONDecodeError):
                    self._json(400, {"error": "Нужен JSON со ссылкой."})
                    return None
                if not isinstance(payload, dict):
                    self._json(400, {"error": "Нужен JSON со ссылкой."})
                    return None
                return payload

            def _create(self) -> None:
                payload = self._body()
                if payload is None:
                    return
                try:
                    job = app.create(str(payload.get("url") or ""), str(payload.get("quality") or "best"))
                except UserError as exc:
                    self._json(400, {"error": str(exc)})
                    return
                except Busy as exc:
                    self._json(429, {"error": str(exc)})
                    return
                body = job.snapshot()
                body["events"] = f"/api/jobs/{job.id}/events"
                self._json(202, body)

            def _preview(self) -> None:
                payload = self._body()
                if payload is None:
                    return
                try:
                    catalog = app.preview(str(payload.get("url") or ""))
                except UserError as exc:
                    self._json(400, {"error": str(exc)})
                    return
                except Busy as exc:
                    self._json(429, {"error": str(exc)})
                    return
                except VideograbberError as exc:
                    self._json(502, {"error": _web_error(str(exc))})
                    return
                self._json(200, catalog)

            def _thumb(self, video_id: str) -> None:
                data = app.thumbnail(video_id)
                if not data:
                    self._json(404, {"error": "Превью не найдено."})
                    return
                self.send_response(200)
                self.send_header("Content-Type", "image/jpeg")
                self.send_header("Content-Length", str(len(data)))
                self.send_header("Cache-Control", "private, max-age=86400")
                self.end_headers()
                self.wfile.write(data)

            def _events(self, job: Job) -> None:
                self.send_response(200)
                self.send_header("Content-Type", "text/event-stream; charset=utf-8")
                self.send_header("Cache-Control", "no-cache")
                self.send_header("Transfer-Encoding", "chunked")
                self.end_headers()
                started = time.monotonic()
                previous = None
                quiet_rounds = 0
                try:
                    while time.monotonic() - started < 60 * 60:
                        encoded = json.dumps(job.snapshot(), ensure_ascii=False)
                        if encoded != previous:
                            self._chunk(f"data: {encoded}\n\n".encode())
                            previous = encoded
                            quiet_rounds = 0
                        else:
                            quiet_rounds += 1
                            if quiet_rounds >= 40:
                                self._chunk(b": ping\n\n")
                                quiet_rounds = 0
                        if json.loads(encoded)["status"] in {"done", "error"}:
                            break
                        job.wait(0.3)
                    self._chunk(b"")
                except (BrokenPipeError, ConnectionResetError):
                    return

            def _chunk(self, data: bytes) -> None:
                self.wfile.write(f"{len(data):X}\r\n".encode())
                self.wfile.write(data)
                self.wfile.write(b"\r\n")
                self.wfile.flush()

            def _file(self, job: Job) -> None:
                path = job.path
                if job.status != "done" or path is None or not path.is_file():
                    self._json(409, {"error": "Файл ещё не готов."})
                    return
                size = path.stat().st_size
                start, end = 0, max(0, size - 1)
                status = 200
                if size and self.headers.get("Range"):
                    parsed = _parse_range(self.headers.get("Range") or "", size)
                    if parsed is None:
                        self.send_response(416)
                        self.send_header("Content-Range", f"bytes */{size}")
                        self.send_header("Accept-Ranges", "bytes")
                        self.send_header("Content-Length", "0")
                        self.end_headers()
                        return
                    start, end = parsed
                    status = 206
                length = 0 if size == 0 else end - start + 1
                media = "audio/mpeg" if path.suffix.lower() == ".mp3" else "video/mp4"
                self.send_response(status)
                self.send_header("Content-Type", media)
                self.send_header("Content-Length", str(length))
                self.send_header("Accept-Ranges", "bytes")
                if status == 206:
                    self.send_header("Content-Range", f"bytes {start}-{end}/{size}")
                self.send_header("Content-Disposition", _content_disposition(job.filename))
                self.send_header("Cache-Control", "no-store")
                self.end_headers()
                with path.open("rb") as handle:
                    handle.seek(start)
                    remaining = length
                    while remaining:
                        chunk = handle.read(min(256 * 1024, remaining))
                        if not chunk:
                            break
                        self.wfile.write(chunk)
                        remaining -= len(chunk)

            def _json(self, status: int, payload: dict) -> None:
                body = json.dumps(payload, ensure_ascii=False).encode()
                self.send_response(status)
                self.send_header("Content-Type", "application/json; charset=utf-8")
                self.send_header("Content-Length", str(len(body)))
                self.send_header("Cache-Control", "no-store")
                self.end_headers()
                self.wfile.write(body)

        return Handler


class WebServer(ThreadingHTTPServer):
    allow_reuse_address = True
    daemon_threads = True


def grab_for_web(url, output=None, quality="best", on_progress=None, cookies=None, **_kwargs):
    if quality == "mp3":
        return grab_audio(url, output=output, on_progress=on_progress, cookies=cookies)
    chosen = "1080" if quality == "best" else quality
    return grab(
        url,
        output=output,
        quality=chosen,
        on_progress=on_progress,
        strict=True,
        cookies=cookies,
    )


def make_server(
    host: str = "127.0.0.1",
    port: int = 8765,
    grabber=None,
    storage: Path | None = None,
    max_jobs: int = 2,
    inspector=None,
    thumb_fetcher=None,
    cookies: Path | None = None,
) -> WebServer:
    root = storage or Path(tempfile.mkdtemp(prefix="videograbber-web-"))
    root.mkdir(parents=True, exist_ok=True)

    def default_grabber(url, output=None, quality="best", on_progress=None, **kwargs):
        return grab_for_web(
            url,
            output=output,
            quality=quality,
            on_progress=on_progress,
            cookies=cookies,
        )

    def default_inspector(url):
        return inspect_youtube(url, cookies=cookies)

    app = App(
        grabber or default_grabber,
        root,
        max_jobs,
        inspector=inspector or default_inspector,
        thumb_fetcher=thumb_fetcher,
    )
    server = WebServer((host, port), app.build_handler())
    server.app = app
    return server


def serve(host: str = "127.0.0.1", port: int = 8765, cookies: Path | None = None) -> None:
    server = make_server(host, port, cookies=cookies)
    bound_port = server.server_address[1]
    shown = "127.0.0.1" if host in {"0.0.0.0", "::"} else host
    print(f"Веб-версия videograbber: http://{shown}:{bound_port}", flush=True)
    try:
        server.serve_forever()
    finally:
        server.server_close()


def run_web(argv: list[str] | None = None) -> int:
    parser = argparse.ArgumentParser(
        prog="videograbber web",
        description="Локальная страница: ссылка на ролик YouTube превращается в MP4 или MP3.",
    )
    parser.add_argument("--host", default="127.0.0.1", help="Адрес, по умолчанию только этот компьютер")
    parser.add_argument("--port", type=int, default=8765, help="Порт, по умолчанию 8765")
    parser.add_argument(
        "--cookies",
        type=Path,
        help="Файл cookies Netscape, если YouTube просит подтвердить, что вы не робот",
    )
    args = parser.parse_args(argv)
    if args.cookies is not None and not args.cookies.is_file():
        print(f"Файл cookies не найден: {args.cookies}", file=sys.stderr)
        return 2
    try:
        serve(args.host, args.port, cookies=args.cookies)
    except KeyboardInterrupt:
        print("\nОстановлено.", file=sys.stderr)
        return 130
    except OSError as exc:
        print(f"Не удалось запустить сервер: {exc}", file=sys.stderr)
        return 1
    return 0


def _describe(result: GrabResult) -> str:
    if result.audio_only:
        return f"{format_bytes(result.size)}, {format_duration(result.duration)}, MP3"
    if result.width and result.height:
        resolution = f"{result.width}×{result.height}"
    else:
        resolution = "размер кадра неизвестен"
    audio = result.audio_codec or "без звука"
    mode = "дорожки скопированы" if result.remuxed else "H.264 + AAC"
    return (
        f"{format_bytes(result.size)}, {format_duration(result.duration)}, "
        f"{resolution}, видео {result.video_codec}, звук {audio}, {mode}"
    )


def _content_disposition(filename: str) -> str:
    ascii_name = "".join(
        char if 32 <= ord(char) < 127 and char not in {'"', "\\"} else "_" for char in filename
    )
    if not any(char.isalnum() for char in ascii_name):
        ascii_name = "video.mp4"
    return f"attachment; filename=\"{ascii_name}\"; filename*=UTF-8''{quote(filename)}"


def _origin(host: str) -> str:
    if not _HOST.fullmatch(host) or "/" in host or "\\" in host:
        host = "127.0.0.1:8765"
    return f"http://{host}"


def _parse_range(header: str, size: int) -> tuple[int, int] | None:
    if size <= 0 or not header.startswith("bytes="):
        return None
    spec = header[6:].split(",", 1)[0].strip()
    if "-" not in spec:
        return None
    start_text, end_text = spec.split("-", 1)
    try:
        if start_text == "":
            count = int(end_text)
            if count <= 0:
                return None
            start = max(0, size - count)
            end = size - 1
        else:
            start = int(start_text)
            end = size - 1 if end_text == "" else int(end_text)
    except ValueError:
        return None
    if start < 0 or start >= size or end < start:
        return None
    return start, min(end, size - 1)


def _web_error(message: str) -> str:
    lowered = message.lower()
    if (
        "cookie" in lowered
        or "sign in" in lowered
        or "--cookies" in lowered
        or "not a bot" in lowered
        or "робот" in lowered
    ):
        return (
            "YouTube не отдал ролик: площадка просит подтвердить, что запрос не от робота. "
            "Повторите позже или запустите страницу с файлом cookies браузера: "
            "videograbber web --cookies cookies.txt"
        )
    return message


def _delete_later(folder: Path) -> None:
    def cleanup() -> None:
        time.sleep(_JOB_TTL_SECONDS)
        shutil.rmtree(folder, ignore_errors=True)

    threading.Thread(target=cleanup, name="videograbber-cleanup", daemon=True).start()
