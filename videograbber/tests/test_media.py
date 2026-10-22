from pathlib import Path
from subprocess import CompletedProcess

import pytest

from videograbber.errors import MediaError, UserError
from videograbber.media import (
    MediaInfo,
    assemble_mp4,
    build_ffmpeg_command,
    format_bytes,
    format_duration,
    format_selector,
    moov_before_mdat,
    parse_ffmpeg_time,
    parse_ffprobe,
    parse_quality,
    resolve_output_path,
    sanitize_filename,
    stream_copy_plan,
)


def test_format_selector_prefers_compatible_streams_at_requested_height():
    selected = format_selector(720)
    assert selected.startswith("bestvideo[height<=720][vcodec^=avc1]+bestaudio[acodec^=mp4a]/")
    assert selected.endswith("bestvideo[height<=720]+bestaudio/best[height<=720]/best")


def test_format_selector_without_limit_still_has_fallback():
    assert format_selector(None).endswith("bestvideo+bestaudio/best/best")


def _box(kind: bytes, payload: bytes = b"") -> bytes:
    return (8 + len(payload)).to_bytes(4, "big") + kind + payload


def test_moov_before_mdat_reads_only_the_header_order(tmp_path):
    ready = tmp_path / "ready.mp4"
    ready.write_bytes(_box(b"ftyp", b"isom") + _box(b"moov", b"head") + _box(b"mdat", b"data"))
    late = tmp_path / "late.mp4"
    late.write_bytes(_box(b"ftyp", b"isom") + _box(b"mdat", b"data") + _box(b"moov", b"head"))
    assert moov_before_mdat(ready) is True
    assert moov_before_mdat(late) is False
    assert moov_before_mdat(tmp_path / "missing.mp4") is False


def test_parse_quality():
    assert parse_quality("best") is None
    assert parse_quality("1080") == 1080
    with pytest.raises(UserError):
        parse_quality("999")


@pytest.mark.parametrize(
    ("raw", "expected"),
    [
        ("Интервью", "Интервью"),
        ("a/b:c*d", "a b c d"),
        ("   ", "video"),
        ("end...", "end"),
        ("CON", "_CON"),
        ("a" * 200, "a" * 120),
    ],
)
def test_sanitize_filename(raw, expected):
    assert sanitize_filename(raw) == expected


def test_resolve_output_path(tmp_path):
    assert resolve_output_path(None, "Ролик", tmp_path) == tmp_path / "Ролик.mp4"
    folder = tmp_path / "out"
    folder.mkdir()
    assert resolve_output_path(folder, "Ролик", tmp_path) == folder / "Ролик.mp4"
    assert resolve_output_path(tmp_path / "new", "Ролик", tmp_path) == tmp_path / "new" / "Ролик.mp4"
    target = tmp_path / "clip.MP4"
    assert resolve_output_path(target, "Ролик", tmp_path) == target
    with pytest.raises(UserError):
        resolve_output_path(tmp_path / "clip.mkv", "Ролик", tmp_path)


def test_format_bytes_and_duration():
    assert format_bytes(0) == "0 Б"
    assert format_bytes(1536) == "1.5 КБ"
    assert format_duration(None) == "длительность неизвестна"
    assert format_duration(65) == "1:05"
    assert format_duration(3661) == "1:01:01"


def test_parse_ffmpeg_time():
    assert parse_ffmpeg_time("00:01:02.50") == 62.5
    assert parse_ffmpeg_time("N/A") is None
    assert parse_ffmpeg_time("bad") is None


def test_parse_ffprobe_skips_cover_art_and_reads_audio():
    info = parse_ffprobe(
        {
            "format": {"duration": "12.5"},
            "streams": [
                {
                    "codec_type": "video",
                    "codec_name": "mjpeg",
                    "disposition": {"attached_pic": 1},
                    "width": 100,
                    "height": 100,
                },
                {
                    "codec_type": "video",
                    "codec_name": "h264",
                    "width": 1280,
                    "height": 720,
                },
                {"codec_type": "audio", "codec_name": "aac"},
                {"codec_type": "audio", "codec_name": "opus"},
            ],
        }
    )
    assert info.video_codec == "h264"
    assert info.audio_codecs == ("aac", "opus")
    assert (info.width, info.height, info.duration) == (1280, 720, 12.5)
    assert stream_copy_plan(info) == (True, False)


def test_parse_ffprobe_rejects_audio_only():
    with pytest.raises(MediaError):
        parse_ffprobe({"streams": [{"codec_type": "audio", "codec_name": "aac"}]})


def test_ffmpeg_command_copies_h264_aac_and_transcodes_the_rest(tmp_path):
    source = tmp_path / "in.mkv"
    dest = tmp_path / "out.mp4"
    compatible = MediaInfo(4, 160, 120, "h264", ("aac",))
    command = build_ffmpeg_command(source, dest, compatible)
    assert command[command.index("-c:v") + 1] == "copy"
    assert command[command.index("-c:a") + 1] == "copy"
    assert "+faststart" in command
    assert command[-3:-1] == ["-f", "mp4"]
    assert "0:V:0" in command

    foreign = MediaInfo(4, 160, 120, "vp9", ("opus",))
    transcode = build_ffmpeg_command(source, dest, foreign)
    assert transcode[transcode.index("-c:v") + 1] == "libx264"
    assert transcode[transcode.index("-c:a") + 1] == "aac"
    assert transcode[transcode.index("-threads") + 1] == "0"
    assert "yuv420p" in transcode


def test_ffmpeg_command_omits_audio_codec_when_there_is_no_audio(tmp_path):
    info = MediaInfo(2, 160, 120, "h264", ())
    command = build_ffmpeg_command(tmp_path / "in.mp4", tmp_path / "out.mp4", info)
    assert "-c:a" not in command


def test_assemble_removes_partial_file_when_ffmpeg_fails(tmp_path):
    source = tmp_path / "in.mp4"
    source.write_bytes(b"source")
    dest = tmp_path / "nested" / "out.mp4"

    def runner(command):
        Path(command[-1]).write_bytes(b"broken")
        return CompletedProcess(command, 1, stdout="", stderr="Encoder failed\n")

    with pytest.raises(MediaError, match="Encoder failed"):
        assemble_mp4(source, dest, MediaInfo(1, 16, 16, "vp9", ()), None, runner=runner)
    assert not dest.with_suffix(".part.mp4").exists()
    assert not dest.exists()


def test_parse_ffprobe_reports_unreadable_json(tmp_path):
    from videograbber.media import probe_media

    def runner(command):
        return CompletedProcess(command, 0, stdout="{", stderr="")

    with pytest.raises(MediaError, match="непонятный"):
        probe_media(tmp_path / "a.mp4", runner)


def test_probe_error_includes_stderr(tmp_path):
    from videograbber.media import probe_media

    def runner(command):
        return CompletedProcess(command, 1, stdout="", stderr="no such file")

    with pytest.raises(MediaError, match="no such file"):
        probe_media(tmp_path / "a.mp4", runner)


