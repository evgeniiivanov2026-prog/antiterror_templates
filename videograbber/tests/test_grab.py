import json
from pathlib import Path
from subprocess import CompletedProcess

import pytest

from videograbber.download import Source
from videograbber.errors import UserError
from videograbber.grab import grab, grab_audio, validate_url


def _payload(codec: str, audio: str | None, width: int = 160, height: int = 120) -> str:
    streams = [
        {"codec_type": "video", "codec_name": codec, "width": width, "height": height, "duration": "2"}
    ]
    if audio is not None:
        streams.append({"codec_type": "audio", "codec_name": audio})
    return json.dumps({"format": {"duration": "2"}, "streams": streams})


def _runner(source_codec: str, source_audio: str | None, commands: list[list[str]]):
    state = {"probes": 0}

    def runner(command):
        commands.append(command)
        if command[0] == "ffprobe":
            state["probes"] += 1
            codec = source_codec if state["probes"] == 1 else "h264"
            audio = source_audio if state["probes"] == 1 else ("aac" if source_audio else None)
            return CompletedProcess(command, 0, stdout=_payload(codec, audio), stderr="")
        Path(command[-1]).write_bytes(b"mp4-bytes")
        return CompletedProcess(command, 0, stdout="", stderr="")

    return runner


def _downloader(title: str, height_box: dict):
    def download(url, directory, max_height, on_progress):
        height_box["height"] = max_height
        height_box["url"] = url
        path = directory / "media.webm"
        path.write_bytes(b"source-bytes")
        if on_progress is not None:
            on_progress_message = getattr(on_progress, "message", None)
            if on_progress_message:
                pass
        return Source(path=path, title=title, duration=2)

    return download


def test_grab_transcodes_incompatible_streams_and_names_the_file(tmp_path):
    commands: list[list[str]] = []
    seen: dict = {}
    result = grab(
        "  https://videos.example/watch?v=1  ",
        output=tmp_path,
        quality="480",
        downloader=_downloader("A/B: урок", seen),
        runner=_runner("vp9", "opus", commands),
    )
    assert seen == {"height": 480, "url": "https://videos.example/watch?v=1"}
    assert result.path == (tmp_path / "A B урок.mp4").resolve()
    assert result.path.read_bytes() == b"mp4-bytes"
    assert result.remuxed is False
    assert result.video_codec == "h264"
    assert result.audio_codec == "aac"
    ffmpeg = next(command for command in commands if command[0] == "ffmpeg")
    assert ffmpeg[ffmpeg.index("-c:v") + 1] == "libx264"


def _faststart_mp4() -> bytes:
    def box(kind: bytes, payload: bytes = b"") -> bytes:
        return (8 + len(payload)).to_bytes(4, "big") + kind + payload

    return box(b"ftyp", b"isom") + box(b"moov", b"head") + box(b"mdat", b"media")


def test_grab_copies_a_ready_mp4_without_ffmpeg(tmp_path):
    commands: list[list[str]] = []
    payload = _faststart_mp4()

    def download(url, directory, max_height, on_progress, cookiefile=None):
        path = directory / "media.mp4"
        path.write_bytes(payload)
        return Source(path=path, title="Готовый", duration=2)

    result = grab(
        "https://videos.example/ready.mp4",
        output=tmp_path / "saved.mp4",
        downloader=download,
        runner=_runner("h264", "aac", commands),
    )
    assert result.remuxed is True
    assert result.path.read_bytes() == payload
    assert [command[0] for command in commands] == ["ffprobe"]


def test_grab_remuxes_h264_aac_without_reencoding(tmp_path):
    commands: list[list[str]] = []
    result = grab(
        "https://videos.example/file.mp4",
        output=tmp_path / "ready.mp4",
        downloader=_downloader("Ignored", {}),
        runner=_runner("h264", "aac", commands),
    )
    assert result.remuxed is True
    assert result.path.name == "ready.mp4"
    ffmpeg = next(command for command in commands if command[0] == "ffmpeg")
    assert ffmpeg[ffmpeg.index("-c:v") + 1] == "copy"
    assert ffmpeg[ffmpeg.index("-c:a") + 1] == "copy"


def test_grab_refuses_to_overwrite_without_force(tmp_path):
    destination = tmp_path / "ready.mp4"
    destination.write_bytes(b"old")
    with pytest.raises(UserError, match="--force"):
        grab(
            "https://videos.example/file.mp4",
            output=destination,
            downloader=_downloader("Clip", {}),
            runner=_runner("h264", "aac", []),
        )
    assert destination.read_bytes() == b"old"


def test_grab_force_replaces_existing_file(tmp_path):
    destination = tmp_path / "ready.mp4"
    destination.write_bytes(b"old")
    result = grab(
        "https://videos.example/file.mp4",
        output=destination,
        force=True,
        downloader=_downloader("Clip", {}),
        runner=_runner("h264", None, []),
    )
    assert result.path.read_bytes() == b"mp4-bytes"
    assert result.audio_codec is None


def test_cookies_are_forwarded_to_the_downloader(tmp_path):
    cookie_file = tmp_path / "cookies.txt"
    cookie_file.write_text("# Netscape\n", encoding="utf-8")
    seen: dict = {}

    def download(url, directory, max_height, on_progress, cookiefile=None):
        seen["cookiefile"] = cookiefile
        path = directory / "media.mp4"
        path.write_bytes(b"source")
        return Source(path=path, title="Clip", duration=1)

    grab(
        "https://videos.example/file.mp4",
        output=tmp_path / "ready.mp4",
        cookies=cookie_file,
        downloader=download,
        runner=_runner("h264", "aac", []),
    )
    assert seen["cookiefile"] == cookie_file


def test_grab_audio_writes_an_mp3(tmp_path):
    def download(url, directory, on_progress, cookiefile=None):
        assert url == "https://example.com/a"
        path = directory / "audio.m4a"
        path.write_bytes(b"aac")
        return Source(path=path, title="Лекция/1", duration=3)

    def runner(command):
        assert "libmp3lame" in command
        assert "-vn" in command
        Path(command[-1]).write_bytes(b"id3")
        return CompletedProcess(command, 0, stdout="", stderr="")

    result = grab_audio(
        "https://example.com/a",
        output=tmp_path,
        downloader=download,
        runner=runner,
    )
    assert result.audio_only is True
    assert result.path == (tmp_path / "Лекция 1.mp3").resolve()
    assert result.path.read_bytes() == b"id3"
    assert result.audio_codec == "mp3"


def test_validate_url():
    assert validate_url(" https://example.com/a ") == "https://example.com/a"
    with pytest.raises(UserError):
        validate_url("ftp://example.com/a")
    with pytest.raises(UserError):
        validate_url("example.com/a")
