import json
import threading
import time
import urllib.error
import urllib.request
from pathlib import Path

import pytest

from tests.ffmpeg_util import make_mp4, serve_directory
from videograbber.errors import DownloadFailed, TooManyJobs, UserInputError
from videograbber.grab import GrabResult, Progress
from videograbber.jobs import JobManager
from videograbber.web import content_disposition, parse_byte_range, serve


def test_parse_byte_range() -> None:
    assert parse_byte_range(None, 100) is None
    assert parse_byte_range("bytes=0-7", 100) == (0, 7)
    assert parse_byte_range("bytes=5-", 100) == (5, 99)
    assert parse_byte_range("bytes=-4", 100) == (96, 99)
    assert parse_byte_range("bytes=0-500", 100) == (0, 99)
    with pytest.raises(ValueError):
        parse_byte_range("bytes=100-120", 100)
    with pytest.raises(ValueError):
        parse_byte_range("items=0-1", 100)


def test_content_disposition_keeps_unicode_filename() -> None:
    header = content_disposition("Ёлка.mp4", attachment=True)
    assert header.startswith("attachment;")
    assert "filename*=UTF-8''" in header
    assert "%D0%81%D0%BB%D0%BA%D0%B0.mp4" in header


def test_manager_hides_password_limits_queue_and_reports_errors(tmp_path: Path) -> None:
    seen: list[str] = []
    started = threading.Event()
    release = threading.Event()

    def blocking(url: str, output_dir: Path, **kwargs) -> list[GrabResult]:
        seen.append(url)
        started.set()
        if "secret" in url:
            raise DownloadFailed("не скачалось")
        release.wait(5)
        raise DownloadFailed("остановлено")

    manager = JobManager(tmp_path, grabber=blocking)
    try:
        hidden = manager.submit("https://user:secret@example.com/video")
        assert "secret" not in json.dumps(hidden)
        assert hidden["url"].startswith("https://***@example.com/")
        deadline = time.time() + 5
        while not seen and time.time() < deadline:
            time.sleep(0.05)
        assert seen[0].startswith("https://user:secret@example.com/")
        deadline = time.time() + 5
        snapshot = None
        while time.time() < deadline:
            snapshot = manager.snapshot(hidden["id"])
            if snapshot and snapshot["status"] == "error":
                break
            time.sleep(0.05)
        assert snapshot is not None
        assert snapshot["status"] == "error"
        assert snapshot["error"] == "не скачалось"

        for _ in range(8):
            manager.submit("https://example.com/a.mp4")
        with pytest.raises(TooManyJobs):
            manager.submit("https://example.com/a.mp4")
        with pytest.raises(UserInputError):
            manager.submit("ftp://example.com/a.mp4")
        release.set()
    finally:
        manager.close()


def test_fake_grab_finishes_job(tmp_path: Path) -> None:
    def fake(url: str, output_dir: Path, **kwargs) -> list[GrabResult]:
        on_progress = kwargs["on_progress"]
        on_progress(Progress(phase="download", percent=40, title="Лекция", speed=1200, eta=3))
        path = output_dir / "Лекция.mp4"
        path.write_bytes(b"not-a-real-mp4-but-present")
        return [
            GrabResult(
                path=path,
                title="Лекция",
                bytes=path.stat().st_size,
                width=320,
                height=240,
                duration=2,
                video_codec="h264",
                audio_codec="aac",
                mode="remux",
            )
        ]

    manager = JobManager(tmp_path, grabber=fake)
    try:
        job = manager.submit("https://example.com/watch", "720p")
        deadline = time.time() + 5
        snapshot = None
        while time.time() < deadline:
            snapshot = manager.snapshot(job["id"])
            if snapshot and snapshot["status"] in {"done", "error"}:
                break
            time.sleep(0.05)
        assert snapshot is not None
        assert snapshot["status"] == "done", snapshot
        assert snapshot["title"] == "Лекция"
        assert snapshot["result"]["filename"] == "Лекция.mp4"
        path, filename = manager.output_file(job["id"])
        assert path.is_file()
        assert filename == "Лекция.mp4"
    finally:
        manager.close()


def _request(url: str, payload: dict | None = None, headers: dict | None = None):
    data = None if payload is None else json.dumps(payload).encode()
    request = urllib.request.Request(url, data=data, headers=headers or {})
    try:
        with urllib.request.urlopen(request) as response:
            body = response.read()
            return response.status, dict(response.headers), body
    except urllib.error.HTTPError as exc:
        return exc.code, dict(exc.headers), exc.read()


def test_web_page_downloads_real_mp4(tmp_path: Path) -> None:
    library = tmp_path / "library"
    make_mp4(library / "clip.mp4", faststart=False, duration=1)
    media = serve_directory(library)
    output = tmp_path / "ready"
    manager = JobManager(output)
    page = serve("127.0.0.1", 0, manager)
    thread = threading.Thread(target=page.serve_forever, daemon=True)
    thread.start()
    base = f"http://127.0.0.1:{page.server_address[1]}"
    try:
        status, headers, body = _request(base + "/")
        assert status == 200
        text = body.decode()
        assert "Собрать MP4" in text
        assert "Content-Security-Policy" in headers
        health_status, _, health_body = _request(base + "/api/health")
        assert health_status == 200
        assert json.loads(health_body)["ok"] is True

        bad_status, _, bad_body = _request(
            base + "/api/jobs",
            {"url": "file:///etc/passwd"},
            {"Content-Type": "application/json"},
        )
        assert bad_status == 400
        assert "http" in json.loads(bad_body)["error"]

        created, _, created_body = _request(
            base + "/api/jobs",
            {"url": f"http://127.0.0.1:{media.server_address[1]}/clip.mp4", "quality": "360"},
            {"Content-Type": "application/json"},
        )
        assert created == 202
        job = json.loads(created_body)
        deadline = time.time() + 90
        snapshot = job
        while time.time() < deadline and snapshot["status"] not in {"done", "error"}:
            time.sleep(0.2)
            view_status, _, view_body = _request(base + f"/api/jobs/{job['id']}")
            assert view_status == 200
            snapshot = json.loads(view_body)
        assert snapshot["status"] == "done", snapshot
        assert snapshot["result"]["video_codec"] == "h264"
        assert snapshot["result"]["audio_codec"] == "aac"

        file_status, file_headers, file_body = _request(
            base + f"/api/jobs/{job['id']}/file?download=1",
            headers={"Range": "bytes=0-7"},
        )
        assert file_status == 206
        assert file_body[4:8] == b"ftyp"
        assert "attachment" in file_headers.get("Content-Disposition", "")
        assert file_headers.get("Content-Range", "").startswith("bytes 0-7/")
        saved = list(output.glob("*.mp4"))
        assert len(saved) == 1
        assert saved[0].read_bytes()[4:8] == b"ftyp"
    finally:
        page.shutdown()
        page.server_close()
        manager.close()
        media.shutdown()
        media.server_close()
