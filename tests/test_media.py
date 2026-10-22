from pathlib import Path

import pytest

from tests.ffmpeg_util import assert_decodes, make_avi, make_m4a, make_mp4, make_wav, read_title
from videograbber.errors import MediaError
from videograbber.media import (
    MediaInfo,
    _consume_progress,
    assemble_mp4,
    mp4_has_faststart,
    plan_encode,
    probe_media,
    read_mp4_brand,
)


def _info(**overrides: object) -> MediaInfo:
    payload: dict[str, object] = {
        "format_name": "mov,mp4,m4a,3gp,3g2,mj2",
        "duration": 2.0,
        "size": 10_000,
        "has_video": True,
        "has_audio": True,
        "video_codec": "h264",
        "audio_codec": "aac",
        "width": 320,
        "height": 240,
        "pix_fmt": "yuv420p",
        "audio_channels": 2,
        "faststart": True,
        "brand": "isom",
    }
    payload.update(overrides)
    return MediaInfo(**payload)  # type: ignore[arg-type]


def test_plan_keeps_compatible_mp4_and_rebuilds_the_rest() -> None:
    assert plan_encode(_info()).mode == "ready"
    assert plan_encode(_info(faststart=False)).mode == "remux"
    assert plan_encode(_info(format_name="matroska,webm", faststart=False, brand=None)).mode == "remux"
    assert plan_encode(_info(video_codec="vp9", pix_fmt="yuv420p")).mode == "transcode"
    assert plan_encode(_info(audio_codec="opus")).mode == "transcode"
    assert plan_encode(_info(audio_channels=6)).mode == "transcode"
    assert plan_encode(_info(pix_fmt="yuv422p")).mode == "transcode"
    assert plan_encode(_info(), reencode=True).mode == "transcode"
    separate_audio = _info(has_video=False, has_audio=True, video_codec=None, width=None, height=None, pix_fmt=None)
    assert plan_encode(_info(has_audio=False, audio_codec=None, audio_channels=None), separate_audio).mode == "remux"


def test_faststart_scanner_reads_atom_order(tmp_path: Path) -> None:
    path = tmp_path / "atoms.bin"
    ftyp = (16).to_bytes(4, "big") + b"ftyp" + b"isom" + b"\x00\x00\x00\x00"
    mdat = (8).to_bytes(4, "big") + b"mdat"
    moov = (8).to_bytes(4, "big") + b"moov"
    path.write_bytes(ftyp + mdat + moov)
    assert mp4_has_faststart(path) is False
    assert read_mp4_brand(path) == "isom"
    path.write_bytes(ftyp + moov + mdat)
    assert mp4_has_faststart(path) is True


def test_progress_clock_treats_out_time_us_as_microseconds() -> None:
    seen: list[float] = []
    _consume_progress("out_time_us=1000000\n", 2, seen.append)
    _consume_progress("out_time_ms=1000000\n", 2, seen.append)
    assert seen == [50.0]


def test_ready_file_is_copied_and_late_moov_is_moved(tmp_path: Path) -> None:
    ready = tmp_path / "ready.mp4"
    late = tmp_path / "late.mp4"
    make_mp4(ready, faststart=True)
    make_mp4(late, faststart=False)
    ready_out = tmp_path / "out-ready.mp4"
    late_out = tmp_path / "out-late.mp4"
    ready_result = assemble_mp4(ready, ready_out, title="Готово")
    late_result = assemble_mp4(late, late_out, title="Позже")
    assert ready_result.mode == "ready"
    assert ready_out.stat().st_size == ready.stat().st_size
    assert late_result.mode == "remux"
    assert probe_media(late_out).faststart is True
    assert probe_media(late_out).video_codec == "h264"
    assert probe_media(late_out).audio_codec == "aac"
    assert read_title(late_out) == "Позже"
    assert_decodes(late_out)


def test_incompatible_avi_becomes_h264_aac_mp4(tmp_path: Path) -> None:
    source = tmp_path / "old.avi"
    make_avi(source)
    dest = tmp_path / "new.mp4"
    seen: list[float] = []
    result = assemble_mp4(source, dest, title="Проверка", on_progress=seen.append)
    info = probe_media(dest)
    assert result.mode == "transcode"
    assert info.video_codec == "h264"
    assert info.audio_codec == "aac"
    assert info.pix_fmt == "yuv420p"
    assert info.faststart is True
    assert info.width == 320
    assert info.height == 240
    assert info.duration == pytest.approx(2, abs=0.4)
    assert read_title(dest) == "Проверка"
    assert seen[-1] == 100
    assert_decodes(dest)


def test_separate_tracks_are_muxed(tmp_path: Path) -> None:
    video = tmp_path / "picture.mp4"
    audio = tmp_path / "sound.m4a"
    make_mp4(video, faststart=True, audio=False)
    make_m4a(audio)
    dest = tmp_path / "both.mp4"
    result = assemble_mp4(video, dest, audio)
    info = probe_media(dest)
    assert result.mode == "remux"
    assert info.has_audio is True
    assert info.audio_codec == "aac"
    assert info.video_codec == "h264"
    assert info.faststart is True
    assert_decodes(dest)


def test_wav_audio_is_encoded_beside_h264(tmp_path: Path) -> None:
    video = tmp_path / "picture.mp4"
    audio = tmp_path / "sound.wav"
    make_mp4(video, faststart=True, audio=False)
    make_wav(audio)
    dest = tmp_path / "mixed.mp4"
    result = assemble_mp4(video, dest, audio)
    info = probe_media(dest)
    assert result.mode == "transcode"
    assert info.video_codec == "h264"
    assert info.audio_codec == "aac"
    assert_decodes(dest)


def test_reencode_flag_and_audio_only_and_garbage(tmp_path: Path) -> None:
    source = tmp_path / "ready.mp4"
    make_mp4(source, faststart=True)
    forced = assemble_mp4(source, tmp_path / "forced.mp4", reencode=True)
    assert forced.mode == "transcode"
    assert_decodes(forced.path)
    silent = tmp_path / "silent.mp4"
    make_mp4(silent, faststart=True, audio=False)
    only_audio = tmp_path / "tone.m4a"
    make_m4a(only_audio)
    with pytest.raises(MediaError):
        assemble_mp4(only_audio, tmp_path / "nope.mp4")
    garbage = tmp_path / "notes.txt"
    garbage.write_text("не видео", encoding="utf-8")
    with pytest.raises(MediaError):
        probe_media(garbage)
    video_only = assemble_mp4(silent, tmp_path / "silent-out.mp4")
    assert probe_media(video_only.path).has_audio is False
