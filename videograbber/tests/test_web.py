import json
import threading
from pathlib import Path
from urllib.error import HTTPError
from urllib.parse import unquote
from urllib.request import Request, urlopen

import pytest

from videograbber.cli import run
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


def _start(grabber, tmp_path, max_jobs=2, inspector=None, audio_grabber=None):
    server = make_server(
        "127.0.0.1",
        0,
        grabber=grabber,
        storage=tmp_path / "jobs",
        max_jobs=max_jobs,
        inspector=inspector,
        audio_grabber=audio_grabber,
    )
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    return server


def _request(port, path, method="GET", body=None, header=True):
    data = None if body is None else json.dumps(body).encode()
    request = Request(f"http://127.0.0.1:{port}{path}", data=data, method=method)
    if data is not None:
        request.add_header("Content-Type", "application/json")
        if header:
            request.add_header("X-Videograbber", "1")
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
    assert "Скачать видео с YouTube и Rutube" in page
    assert "rutube.ru/video" in page
    assert "Найти варианты для скачивания" in page
    assert "MP3" in page
    assert "src=\"http" not in page
    assert "cdn." not in page
    assert len(page) < 40_000


def test_job_builds_an_mp4_and_serves_it(tmp_path):
    def grabber(url, output=None, quality="best", on_progress=None, **kwargs):
        assert url == "https://www.youtube.com/watch?v=abcdefghijk"
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
            body={"url": " https://youtu.be/abcdefghijk ", "quality": "720"},
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
            body={"url": "https://www.youtube.com/shorts/abcdefghijk", "quality": "360"},
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
        other, other_body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://videos.example/a", "quality": "720"},
        )
        icon, _empty, _headers = _request(port, "/favicon.ico")
    finally:
        server.shutdown()
    assert status == 403
    assert bad == 400
    assert "http" in json.loads(payload)["error"]
    assert other == 400
    assert "YouTube" in json.loads(other_body)["error"]
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
            body={"url": "https://www.youtube.com/watch?v=aaaaaaaaaaa", "quality": "360"},
        )
        assert first == 202
        assert started.wait(5)
        busy, payload, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://www.youtube.com/watch?v=bbbbbbbbbbb", "quality": "360"},
        )
    finally:
        release.set()
        server.shutdown()
    assert busy == 429
    assert "сборк" in json.loads(payload)["error"]


def test_lookup_lists_formats_and_audio_job_returns_mp3(tmp_path):
    def inspector(url):
        assert url == "https://www.youtube.com/watch?v=abcdefghijk"
        return {
            "id": "abcdefghijk",
            "title": "Лекция",
            "author": "Автор",
            "duration": 90,
            "durationLabel": "1:30",
            "options": [
                {"id": "720", "label": "720p HD", "kind": "video"},
                {"id": "audio", "label": "Только аудио (MP3)", "kind": "audio"},
            ],
        }

    def audio_grabber(url, output=None, on_progress=None, **kwargs):
        assert url == "https://www.youtube.com/watch?v=abcdefghijk"
        path = Path(output) / "Лекция.mp3"
        path.write_bytes(b"mp3-bytes")
        return GrabResult(
            path=path,
            title="Лекция",
            size=path.stat().st_size,
            duration=90,
            width=None,
            height=None,
            video_codec="",
            audio_codec="mp3",
            remuxed=False,
            kind="audio",
        )

    server = _start(lambda *args, **kwargs: None, tmp_path, inspector=inspector, audio_grabber=audio_grabber)
    port = server.server_address[1]
    try:
        found, body, _headers = _request(
            port,
            "/api/lookup",
            method="POST",
            body={"url": "смотри https://youtu.be/abcdefghijk"},
        )
        status, created_body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={"url": "https://www.youtube.com/watch?v=abcdefghijk", "quality": "audio"},
        )
        created = json.loads(created_body)
        deadline = 0
        snapshot = {}
        while deadline < 20:
            _code, payload, _headers = _request(port, f"/api/jobs/{created['id']}")
            snapshot = json.loads(payload)
            if snapshot["status"] == "done":
                break
            deadline += 1
        else:
            raise AssertionError(snapshot)
        file_status, file_body, file_headers = _request(port, snapshot["download"])
    finally:
        server.shutdown()
    card = json.loads(body)
    assert found == 200
    assert card["title"] == "Лекция"
    assert card["url"] == "https://www.youtube.com/watch?v=abcdefghijk"
    assert [item["id"] for item in card["options"]] == ["720", "audio"]
    assert status == 202
    assert snapshot["filename"] == "Лекция.mp3"
    assert "MP3" in snapshot["detail"]
    assert file_status == 200
    assert file_body == b"mp3-bytes"
    assert "audio/mpeg" in file_headers["Content-Type"]


def test_rutube_link_is_downloaded_as_one_video(tmp_path):
    seen = {}

    def grabber(url, output=None, quality="best", on_progress=None, **kwargs):
        seen["url"] = url
        seen["quality"] = quality
        return _result(Path(output) / "Ролик.mp4")

    server = _start(grabber, tmp_path)
    port = server.server_address[1]
    try:
        status, body, _headers = _request(
            port,
            "/api/jobs",
            method="POST",
            body={
                "url": "https://www.rutube.ru/video/3eac3b4561676c17df9132a9a1e62e3e/?pl_id=4252",
                "quality": "720",
            },
        )
        job_id = json.loads(body)["id"]
        deadline = 0
        snapshot = {}
        while deadline < 20:
            _code, payload, _headers = _request(port, f"/api/jobs/{job_id}")
            snapshot = json.loads(payload)
            if snapshot["status"] == "done":
                break
            deadline += 1
        else:
            raise AssertionError(snapshot)
    finally:
        server.shutdown()
    assert status == 202
    assert seen == {
        "url": "https://rutube.ru/video/3eac3b4561676c17df9132a9a1e62e3e/",
        "quality": "720",
    }
    assert snapshot["filename"] == "Ролик.mp4"


def test_web_help_lists_the_port(capsys):
    with pytest.raises(SystemExit) as caught:
        run(["web", "--help"])
    assert caught.value.code == 0
    assert "8765" in capsys.readouterr().out
