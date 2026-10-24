import json
import threading
from pathlib import Path
from urllib.error import HTTPError
from urllib.parse import unquote
from urllib.request import Request, urlopen

import pytest

from videograbber.cli import run
from videograbber.errors import DownloadError
from videograbber.grab import GrabResult
from videograbber.media import ProgressEvent
from videograbber.web import make_server


def _result(path: Path) -> GrabResult:
    path.write_bytes(b"full-mp4")
    return GrabResult(
        path=path,
        title="Ролик",
        size=path.stat().st_size,
        duration=4,
        width=640,
        height=360,
        video_codec="h264",
        audio_codec="aac",
        remuxed=True,
    )


def _start(grabber, tmp_path, max_jobs=2):
    server = make_server("127.0.0.1", 0, grabber=grabber, storage=tmp_path / "jobs", max_jobs=max_jobs)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    return server


def _request(port, path, method="GET", body=None, header=True, headers=None):
    data = None if body is None else json.dumps(body).encode()
    request = Request(f"http://127.0.0.1:{port}{path}", data=data, method=method)
    if data is not None:
        request.add_header("Content-Type", "application/json")
        if header:
            request.add_header("X-Videograbber", "1")
    for key, value in (headers or {}).items():
        request.add_header(key, value)
    try:
        with urlopen(request, timeout=5) as response:
            return response.status, response.read(), response.headers
    except HTTPError as exc:
        return exc.code, exc.read(), exc.headers


def test_page_is_one_document_without_remote_assets(tmp_path):
    server = _start(lambda *args, **kwargs: None, tmp_path)
    try:
        status, body, headers = _request(server.server_address[1], "/")
    finally:
        server.shutdown()
    page = body.decode()
    assert status == 200
    assert "text/html" in headers["Content-Type"]
    assert "videograbber" in page
    assert "Найти варианты для скачивания" in page
    assert "youtube.com/shorts" in page
    assert "src=\"http" not in page
    assert "cdn." not in page
    assert "__ORIGIN__" not in page
    assert "http://127.0.0.1:" in page
    assert len(page) < 32_000


def test_job_builds_an_mp4_and_serves_it(tmp_path):
    def grabber(url, output=None, quality="best", on_progress=None, **kwargs):
        assert url == "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
        assert quality == "720"
        on_progress(ProgressEvent("Скачивание, видео: 40%", transient=True, percent=40))
        return _result(Path(output) / "Ролик.mp4")

    server = _start(grabber, tmp_path)
    port = server.server_address[1]
    try:
        status, body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": " https://youtu.be/dQw4w9WgXcQ ", "quality": "720"},
        )
        assert status == 202
        created = json.loads(body)
        deadline = 0
        snapshot = {}
        while deadline < 20:
            code, payload, _headers = _request(port, f"/api/jobs/{created['id']}")
            assert code == 200
            snapshot = json.loads(payload)
            if snapshot["status"] == "done":
                break
            deadline += 1
        else:
            raise AssertionError(snapshot)
        file_status, file_body, file_headers = _request(port, snapshot["download"])
    finally:
        server.shutdown()
    assert snapshot["filename"] == "Ролик.mp4"
    assert "640×360" in snapshot["detail"]
    assert file_status == 200
    assert file_body == b"full-mp4"
    assert "video/mp4" in file_headers["Content-Type"]
    disposition = file_headers["Content-Disposition"]
    assert "filename*=UTF-8''" in disposition
    assert unquote(disposition.split("filename*=UTF-8''", 1)[1]) == "Ролик.mp4"


def test_events_report_the_finished_job(tmp_path):
    def grabber(url, output=None, **kwargs):
        return _result(Path(output) / "clip.mp4")

    server = _start(grabber, tmp_path)
    port = server.server_address[1]
    try:
        _status, body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"},
        )
        job_id = json.loads(body)["id"]
        _status, stream, _headers = _request(port, f"/api/jobs/{job_id}/events")
    finally:
        server.shutdown()
    assert b"\"status\": \"done\"" in stream or b'"status": "done"' in stream


def test_rejects_a_page_from_another_site_and_a_bad_link(tmp_path):
    server = _start(lambda *args, **kwargs: None, tmp_path, max_jobs=1)
    port = server.server_address[1]
    try:
        status, _body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://videos.example/a"},
            header=False,
        )
        bad, payload, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "not a link"},
        )
        icon, _empty, _headers = _request(port, "/favicon.ico")
    finally:
        server.shutdown()
    assert status == 403
    assert bad == 400
    assert "http" in json.loads(payload)["error"]
    assert icon == 204


def test_second_job_waits_when_the_server_is_busy(tmp_path):
    started = threading.Event()
    release = threading.Event()

    def grabber(url, output=None, **kwargs):
        started.set()
        assert release.wait(5)
        return _result(Path(output) / "clip.mp4")

    server = _start(grabber, tmp_path, max_jobs=1)
    port = server.server_address[1]
    try:
        first, _body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"},
        )
        assert first == 202
        assert started.wait(5)
        busy, payload, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://www.youtube.com/shorts/dQw4w9WgXcQ"},
        )
    finally:
        release.set()
        server.shutdown()
    assert busy == 429
    assert "сборк" in json.loads(payload)["error"]


def test_web_help_lists_the_port(capsys):
    with pytest.raises(SystemExit) as caught:
        run(["web", "--help"])
    assert caught.value.code == 0
    help_text = capsys.readouterr().out
    assert "8765" in help_text
    assert "cookies" in help_text


def test_web_rejects_a_missing_cookie_file(tmp_path):
    assert run(["web", "--cookies", str(tmp_path / "missing.txt")]) == 2


def test_youtube_bot_check_is_explained(tmp_path):
    def inspector(_url):
        raise DownloadError("Sign in to confirm you're not a bot")

    server = make_server(
        "127.0.0.1",
        0,
        grabber=lambda *args, **kwargs: None,
        storage=tmp_path / "jobs",
        inspector=inspector,
    )
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    try:
        status, body, _headers = _request(
            server.server_address[1],
            "/api/preview",
            method="POST",
            body={"url": "https://youtu.be/dQw4w9WgXcQ"},
        )
    finally:
        server.shutdown()
    assert status == 502
    message = json.loads(body)["error"]
    assert "робота" in message
    assert "--cookies" in message


def test_preview_lists_the_formats_of_a_youtube_link(tmp_path):
    seen = {}

    def inspector(url):
        seen["url"] = url
        return {
            "id": "dQw4w9WgXcQ",
            "title": "Лекция",
            "author": "Канал",
            "duration": "3:10",
            "thumbnail": "/api/thumb/dQw4w9WgXcQ",
            "options": [
                {"id": "720", "label": "720p HD", "kind": "mp4", "hint": "40 МБ"},
                {"id": "mp3", "label": "Только аудио (MP3)", "kind": "mp3", "hint": "5 МБ"},
            ],
        }

    server = make_server(
        "127.0.0.1",
        0,
        grabber=lambda *args, **kwargs: None,
        storage=tmp_path / "jobs",
        inspector=inspector,
    )
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    port = server.server_address[1]
    try:
        status, body, _headers = _request(
            port,
            "/api/preview",
            method="POST",
            body={"url": "https://youtu.be/dQw4w9WgXcQ?list=PLxxxx"},
        )
        foreign, payload, _headers = _request(
            port,
            "/api/preview",
            method="POST",
            body={"url": "https://vimeo.com/123456"},
        )
        playlist, _playlist_body, _headers = _request(
            port,
            "/api/preview",
            method="POST",
            body={"url": "https://www.youtube.com/playlist?list=PL123"},
        )
    finally:
        server.shutdown()
    catalog = json.loads(body)
    assert status == 200
    assert seen["url"] == "https://www.youtube.com/watch?v=dQw4w9WgXcQ"
    assert catalog["title"] == "Лекция"
    assert [item["id"] for item in catalog["options"]] == ["720", "mp3"]
    assert foreign == 400
    assert "YouTube" in json.loads(payload)["error"]
    assert playlist == 400


def test_mp3_job_is_served_as_audio(tmp_path):
    def grabber(url, output=None, quality="best", **kwargs):
        assert quality == "mp3"
        path = Path(output) / "Лекция.mp3"
        path.write_bytes(b"mp3-bytes")
        return GrabResult(
            path=path,
            title="Лекция",
            size=path.stat().st_size,
            duration=12,
            width=None,
            height=None,
            video_codec="",
            audio_codec="mp3",
            remuxed=False,
            audio_only=True,
        )

    server = _start(grabber, tmp_path)
    port = server.server_address[1]
    try:
        status, body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://music.youtube.com/watch?v=dQw4w9WgXcQ", "quality": "mp3"},
        )
        created = json.loads(body)
        snapshot = {}
        for _attempt in range(20):
            _code, payload, _headers = _request(port, f"/api/jobs/{created['id']}")
            snapshot = json.loads(payload)
            if snapshot["status"] == "done":
                break
        file_status, file_body, file_headers = _request(port, snapshot["download"])
        ranged, ranged_body, ranged_headers = _request(
            port,
            snapshot["download"],
            headers={"Range": "bytes=0-2"},
        )
        too_high, _high_body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ", "quality": "2160"},
        )
    finally:
        server.shutdown()
    assert status == 202
    assert snapshot["filename"] == "Лекция.mp3"
    assert "MP3" in snapshot["detail"]
    assert file_status == 200
    assert file_body == b"mp3-bytes"
    assert "audio/mpeg" in file_headers["Content-Type"]
    assert ranged == 206
    assert ranged_body == b"mp3"
    assert ranged_headers["Content-Range"] == "bytes 0-2/9"
    assert too_high == 400


def test_thumbnail_uses_the_fetcher_and_rejects_a_bad_id(tmp_path):
    server = make_server(
        "127.0.0.1",
        0,
        grabber=lambda *args, **kwargs: None,
        storage=tmp_path / "jobs",
        thumb_fetcher=lambda video_id: b"\xff\xd8" + video_id.encode(),
    )
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    port = server.server_address[1]
    try:
        status, body, headers = _request(port, "/api/thumb/dQw4w9WgXcQ")
        missing, _body, _headers = _request(port, "/api/thumb/short")
    finally:
        server.shutdown()
    assert status == 200
    assert headers["Content-Type"] == "image/jpeg"
    assert body.startswith(b"\xff\xd8")
    assert missing == 404
