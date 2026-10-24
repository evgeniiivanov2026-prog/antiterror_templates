from pathlib import Path

import pytest

from videograbber.download import (
    DownloadProgress,
    _clean_message,
    _locate_download,
    _single_video,
    build_ydl_options,
    reject_unsupported,
)
from videograbber.errors import DownloadError
from videograbber.media import ProgressEvent


def test_reject_live_stream():
    assert reject_unsupported({"is_live": True}) == (
        "Прямой эфир нельзя сохранить как один конечный MP4."
    )
    assert reject_unsupported({"is_live": False}, incomplete=True) is None


def test_single_video_rejects_a_playlist():
    with pytest.raises(DownloadError, match="плейлист"):
        _single_video({"_type": "playlist", "entries": [{"id": "a"}, {"id": "b"}]})
    assert _single_video({"_type": "playlist", "entries": [{"id": "only"}]})["id"] == "only"
    assert _single_video({"id": "clip", "title": "Clip"})["title"] == "Clip"


def test_clean_message_strips_ansi_and_error_prefix():
    assert _clean_message("\x1b[31mERROR: \x1b[0mVideo unavailable") == "Video unavailable"
    assert _clean_message("   ") == "Не удалось скачать видео."
    bot = _clean_message("ERROR: Sign in to confirm you're not a bot")
    assert "--cookies" in bot
    assert "не от бота" in bot


def test_locate_download_uses_reported_path_then_largest_file(tmp_path):
    reported = tmp_path / "media.mkv"
    reported.write_bytes(b"12345")
    assert _locate_download(tmp_path, {"requested_downloads": [{"filepath": str(reported)}]}) == reported

    small = tmp_path / "media.f1.mp4"
    large = tmp_path / "media.webm"
    small.write_bytes(b"12")
    large.write_bytes(b"123456")
    assert _locate_download(tmp_path, {}) == large


def test_locate_download_swaps_merged_extension(tmp_path):
    merged = tmp_path / "media.mkv"
    merged.write_bytes(b"merged")
    found = _locate_download(tmp_path, {"_filename": str(tmp_path / "media.webm")})
    assert found == merged


def test_locate_download_fails_when_directory_is_empty(tmp_path):
    with pytest.raises(DownloadError, match="не найден"):
        _locate_download(tmp_path, {})


def test_progress_announces_title_and_throttles_repeat_percent():
    events: list[ProgressEvent] = []
    progress = DownloadProgress(events.append)
    info = {"title": "Демо", "vcodec": "vp9", "acodec": "none"}
    progress({"status": "downloading", "downloaded_bytes": 50, "total_bytes": 100, "info_dict": info})
    progress({"status": "downloading", "downloaded_bytes": 50, "total_bytes": 100, "info_dict": info})
    progress({"status": "finished", "info_dict": info})
    assert events[0].message == "Найдено: Демо"
    assert "видео: 50%" in events[1].message
    assert events[1].transient is True
    assert events[-1].message == "Видео загружено"
    assert len(events) == 3


def test_cookie_file_is_passed_to_yt_dlp(tmp_path):
    options = build_ydl_options(tmp_path, 720, None, tmp_path / "cookies.txt")
    assert options["cookiefile"] == str(tmp_path / "cookies.txt")
    assert options["noplaylist"] is True
    assert options["concurrent_fragment_downloads"] >= 8
    assert options["buffersize"] >= 1024 * 1024
    assert "cookiefile" not in build_ydl_options(tmp_path, None, None)


def test_progress_without_callback_is_silent():
    DownloadProgress(None)(
        {"status": "downloading", "downloaded_bytes": 1, "info_dict": {"title": "X"}}
    )


