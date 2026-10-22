"""Сквозной прогон: локальный файл по http-ссылке становится целым MP4."""

from __future__ import annotations

import functools
import http.server
import subprocess
import threading
from pathlib import Path

import pytest

from videograbber.cli import run
from videograbber.errors import DownloadError
from videograbber.grab import grab


class _QuietHandler(http.server.SimpleHTTPRequestHandler):
    def log_message(self, format, *args):
        return None


def _ffmpeg(*args: str) -> None:
    subprocess.run(("ffmpeg", "-y", "-hide_banner", "-loglevel", "error", *args), check=True)


def _moov_before_mdat(path: Path) -> bool:
    data = path.read_bytes()
    position = 0
    seen_moov = False
    while position + 8 <= len(data):
        size = int.from_bytes(data[position : position + 4], "big")
        kind = data[position + 4 : position + 8]
        header = 8
        if size == 1:
            if position + 16 > len(data):
                return False
            size = int.from_bytes(data[position + 8 : position + 16], "big")
            header = 16
        if size == 0:
            size = len(data) - position
        if size < header:
            return False
        if kind == b"moov":
            seen_moov = True
        if kind == b"mdat":
            return seen_moov
        position += size
    return False


@pytest.fixture(scope="module")
def sample_server(tmp_path_factory):
    root = tmp_path_factory.mktemp("clips")
    _ffmpeg(
        "-f",
        "lavfi",
        "-i",
        "testsrc=size=160x120:rate=10:duration=1",
        "-f",
        "lavfi",
        "-i",
        "sine=frequency=440:sample_rate=44100:duration=1",
        "-shortest",
        "-c:v",
        "libx264",
        "-pix_fmt",
        "yuv420p",
        "-c:a",
        "aac",
        str(root / "clip.mp4"),
    )
    _ffmpeg(
        "-f",
        "lavfi",
        "-i",
        "testsrc=size=160x120:rate=10:duration=1",
        "-f",
        "lavfi",
        "-i",
        "sine=frequency=440:sample_rate=44100:duration=1",
        "-shortest",
        "-c:v",
        "libvpx-vp9",
        "-b:v",
        "200k",
        "-c:a",
        "libopus",
        str(root / "clip.webm"),
    )
    handler = functools.partial(_QuietHandler, directory=str(root))
    server = http.server.ThreadingHTTPServer(("127.0.0.1", 0), handler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    port = server.server_address[1]
    base = f"http://127.0.0.1:{port}"
    yield {
        "mp4": f"{base}/clip.mp4",
        "webm": f"{base}/clip.webm",
        "missing": f"{base}/missing.mp4",
    }
    server.shutdown()


def _probe(path: Path) -> dict:
    result = subprocess.run(
        (
            "ffprobe",
            "-v",
            "error",
            "-print_format",
            "json",
            "-show_streams",
            "-show_format",
            str(path),
        ),
        check=True,
        capture_output=True,
        text=True,
    )
    import json

    return json.loads(result.stdout)


def test_direct_mp4_link_becomes_a_faststart_file(sample_server, tmp_path):
    destination = tmp_path / "saved.mp4"
    result = grab(sample_server["mp4"], output=destination)
    assert result.remuxed is True
    assert destination.is_file() and destination.stat().st_size > 0
    assert _moov_before_mdat(destination)
    probed = _probe(destination)
    codecs = {stream["codec_type"]: stream["codec_name"] for stream in probed["streams"]}
    assert codecs["video"] == "h264"
    assert codecs["audio"] == "aac"
    assert "mp4" in probed["format"]["format_name"]
    assert 0.5 <= float(probed["format"]["duration"]) <= 2


def test_webm_link_is_transcoded_to_h264_aac(sample_server, tmp_path):
    destination = tmp_path / "from-webm.mp4"
    result = grab(sample_server["webm"], output=destination, quality="720")
    assert result.remuxed is False
    assert _moov_before_mdat(destination)
    probed = _probe(destination)
    codecs = {stream["codec_type"]: stream["codec_name"] for stream in probed["streams"]}
    assert codecs == {"video": "h264", "audio": "aac"}
    assert result.width == 160 and result.height == 120


def test_cli_saves_the_linked_video(sample_server, tmp_path):
    destination = tmp_path / "via-cli.mp4"
    code = run([sample_server["mp4"], "-o", str(destination), "--quiet"])
    assert code == 0
    assert destination.is_file()
    assert _probe(destination)["streams"][0]["codec_name"] == "h264"


def test_missing_link_fails(sample_server, tmp_path):
    with pytest.raises(DownloadError):
        grab(sample_server["missing"], output=tmp_path / "no.mp4")
