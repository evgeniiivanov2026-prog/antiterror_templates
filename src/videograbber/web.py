"""Локальная страница: вставить ссылку и забрать MP4."""

from __future__ import annotations

import json
import logging
import re
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from importlib.resources import files
from pathlib import Path
from urllib.parse import parse_qs, quote, urlsplit

from videograbber.errors import FileNotReady, TooManyJobs, UserInputError
from videograbber.jobs import JobManager

log = logging.getLogger("videograbber")

_MAX_BODY = 1_000_000
_JOB_ID = re.compile(r"^[0-9a-f]{32}$")


def serve(host: str, port: int, manager: JobManager) -> ThreadingHTTPServer:
    app = _App(manager)

    class Handler(BaseHTTPRequestHandler):
        protocol_version = "HTTP/1.1"
        server_version = "videograbber"

        def log_message(self, fmt: str, *args) -> None:
            log.info("%s %s", self.address_string(), fmt % args)

        def do_GET(self) -> None:  # noqa: N802
            self._dispatch("GET")

        def do_POST(self) -> None:  # noqa: N802
            self._dispatch("POST")

        def _dispatch(self, method: str) -> None:
            try:
                route(self, method, app)
            except Exception:
                log.exception("ошибка запроса")
                try:
                    send_json(self, 500, {"error": "Внутренняя ошибка сервера."})
                except Exception:
                    pass

    httpd = ThreadingHTTPServer((host, port), Handler)
    httpd.daemon_threads = True
    return httpd


class _App:
    def __init__(self, manager: JobManager) -> None:
        self.manager = manager
        self.index = load_index()


def load_index() -> bytes:
    resource = files("videograbber").joinpath("web_index.html")
    if resource.is_file():
        return resource.read_bytes()
    fallback = Path(__file__).with_name("web_index.html")
    if fallback.is_file():
        return fallback.read_bytes()
    raise FileNotFoundError("Не найден файл страницы web_index.html")


def route(handler: BaseHTTPRequestHandler, method: str, app: _App) -> None:
    parsed = urlsplit(handler.path)
    path = parsed.path or "/"
    if path != "/" and path.endswith("/"):
        path = path[:-1]
    if path == "/favicon.ico":
        send_empty(handler, 204)
        return
    if method == "GET" and path == "/":
        send_bytes(
            handler,
            200,
            app.index,
            "text/html; charset=utf-8",
        )
        return
    if method == "GET" and path == "/api/health":
        send_json(handler, 200, app.manager.health())
        return
    if method == "GET" and path == "/api/jobs":
        send_json(handler, 200, {"jobs": app.manager.list_jobs()})
        return
    if method == "POST" and path == "/api/jobs":
        _create_job(handler, app)
        return
    parts = [part for part in path.split("/") if part]
    if len(parts) >= 3 and parts[0] == "api" and parts[1] == "jobs":
        job_id = parts[2]
        if not _JOB_ID.fullmatch(job_id):
            send_json(handler, 404, {"error": "Такой загрузки нет."})
            return
        if method == "GET" and len(parts) == 3:
            payload = app.manager.snapshot(job_id)
            if payload is None:
                send_json(handler, 404, {"error": "Такой загрузки нет."})
                return
            send_json(handler, 200, payload)
            return
        if method == "GET" and len(parts) == 4 and parts[3] == "file":
            _send_file(handler, app, job_id, parse_qs(parsed.query))
            return
    if path.startswith("/api/"):
        send_json(handler, 404, {"error": "Такого адреса нет."})
        return
    send_text(handler, 404, "Страница не найдена")


def parse_byte_range(header: str | None, size: int) -> tuple[int, int] | None:
    """Диапазон bytes= из заголовка. None — заголовка нет."""
    if not header:
        return None
    if not header.startswith("bytes="):
        raise ValueError(header)
    spec = header[6:].split(",", 1)[0].strip()
    if "-" not in spec:
        raise ValueError(header)
    start_text, end_text = spec.split("-", 1)
    if start_text == "":
        if not end_text.isdigit() or size <= 0:
            raise ValueError(header)
        length = int(end_text)
        if length <= 0:
            raise ValueError(header)
        return max(0, size - length), size - 1
    if not start_text.isdigit():
        raise ValueError(header)
    start = int(start_text)
    if end_text == "":
        end = size - 1
    elif end_text.isdigit():
        end = int(end_text)
    else:
        raise ValueError(header)
    if size <= 0 or start < 0 or start >= size or end < start:
        raise ValueError(header)
    return start, min(end, size - 1)


def content_disposition(filename: str, *, attachment: bool) -> str:
    kind = "attachment" if attachment else "inline"
    ascii_name = "".join(char if char.isascii() and char not in {'"', "\\"} else " " for char in filename)
    ascii_name = " ".join(ascii_name.split()).strip(" .") or "video"
    if not ascii_name.lower().endswith(".mp4"):
        ascii_name = f"{ascii_name}.mp4"
    encoded = quote(filename, safe="")
    return f"{kind}; filename=\"{ascii_name}\"; filename*=UTF-8''{encoded}"


def _create_job(handler: BaseHTTPRequestHandler, app: _App) -> None:
    try:
        length = int(handler.headers.get("Content-Length", ""))
    except ValueError:
        send_json(handler, 400, {"error": "Нужно передать JSON."})
        return
    if length < 2 or length > _MAX_BODY:
        send_json(handler, 400, {"error": "Непонятное или слишком большое тело запроса."})
        return
    raw = handler.rfile.read(length)
    try:
        payload = json.loads(raw.decode("utf-8"))
    except (UnicodeDecodeError, json.JSONDecodeError):
        send_json(handler, 400, {"error": "Нужен JSON со ссылкой."})
        return
    if not isinstance(payload, dict):
        send_json(handler, 400, {"error": "Нужен JSON-объект со ссылкой."})
        return
    url = payload.get("url")
    quality = payload.get("quality") or "1080"
    if not isinstance(url, str) or not isinstance(quality, str):
        send_json(handler, 400, {"error": "Ссылка и качество должны быть текстом."})
        return
    try:
        job = app.manager.submit(url, quality)
    except TooManyJobs as exc:
        send_json(handler, 429, {"error": str(exc)})
        return
    except UserInputError as exc:
        send_json(handler, 400, {"error": str(exc)})
        return
    send_json(handler, 202, job)


def _send_file(handler: BaseHTTPRequestHandler, app: _App, job_id: str, query: dict) -> None:
    try:
        path, filename = app.manager.output_file(job_id)
    except KeyError:
        send_json(handler, 404, {"error": "Такой загрузки нет."})
        return
    except FileNotReady as exc:
        send_json(handler, 409, {"error": str(exc)})
        return
    size = path.stat().st_size
    try:
        byte_range = parse_byte_range(handler.headers.get("Range"), size)
    except ValueError:
        send_range_error(handler, size)
        return
    start = 0 if byte_range is None else byte_range[0]
    end = size - 1 if byte_range is None else byte_range[1]
    status = 200 if byte_range is None else 206
    length = 0 if size == 0 else end - start + 1
    attachment = query.get("download", ["0"])[0] == "1"
    handler.send_response(status)
    _common_headers(handler)
    handler.send_header("Content-Type", "video/mp4")
    handler.send_header("Accept-Ranges", "bytes")
    handler.send_header("Content-Length", str(length))
    handler.send_header("Content-Disposition", content_disposition(filename, attachment=attachment))
    if status == 206:
        handler.send_header("Content-Range", f"bytes {start}-{end}/{size}")
    handler.end_headers()
    if length == 0:
        return
    remaining = length
    with path.open("rb") as handle:
        handle.seek(start)
        while remaining:
            chunk = handle.read(min(64 * 1024, remaining))
            if not chunk:
                break
            handler.wfile.write(chunk)
            remaining -= len(chunk)


def send_json(handler: BaseHTTPRequestHandler, status: int, payload: dict) -> None:
    body = json.dumps(payload, ensure_ascii=False).encode("utf-8")
    send_bytes(handler, status, body, "application/json; charset=utf-8")


def send_text(handler: BaseHTTPRequestHandler, status: int, text: str) -> None:
    send_bytes(handler, status, text.encode("utf-8"), "text/plain; charset=utf-8")


def send_empty(handler: BaseHTTPRequestHandler, status: int) -> None:
    handler.send_response(status)
    _common_headers(handler)
    handler.send_header("Content-Length", "0")
    handler.end_headers()


def send_bytes(handler: BaseHTTPRequestHandler, status: int, body: bytes, content_type: str) -> None:
    handler.send_response(status)
    _common_headers(handler)
    handler.send_header("Content-Type", content_type)
    handler.send_header("Content-Length", str(len(body)))
    handler.end_headers()
    handler.wfile.write(body)


def send_range_error(handler: BaseHTTPRequestHandler, size: int) -> None:
    handler.send_response(416)
    _common_headers(handler)
    handler.send_header("Content-Range", f"bytes */{size}")
    handler.send_header("Content-Length", "0")
    handler.end_headers()


def _common_headers(handler: BaseHTTPRequestHandler) -> None:
    handler.send_header("X-Content-Type-Options", "nosniff")
    handler.send_header("Referrer-Policy", "no-referrer")
    handler.send_header("Cache-Control", "no-store")
    handler.send_header(
        "Content-Security-Policy",
        "default-src 'self'; "
        "connect-src 'self'; "
        "media-src 'self'; "
        "style-src 'unsafe-inline'; "
        "script-src 'unsafe-inline'; "
        "img-src 'self' data:; "
        "object-src 'none'; "
        "base-uri 'none'; "
        "form-action 'self'; "
        "frame-ancestors 'none'",
    )
