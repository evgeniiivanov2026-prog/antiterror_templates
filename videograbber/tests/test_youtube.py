import pytest

from videograbber.errors import DownloadError, UserError
from videograbber.youtube import (
    build_options,
    normalize_youtube_url,
    preview_from_info,
)


def test_normalize_accepts_watch_short_and_short_links():
    assert normalize_youtube_url(" https://youtu.be/abcdefghijk?t=3 ") == (
        "https://www.youtube.com/watch?v=abcdefghijk"
    )
    assert normalize_youtube_url("https://www.youtube.com/shorts/abcdefghijk") == (
        "https://www.youtube.com/watch?v=abcdefghijk"
    )
    assert normalize_youtube_url(
        "https://m.youtube.com/watch?v=abcdefghijk&list=PLxxxxxxxxxxxxxxxxxxxxxx"
    ) == "https://www.youtube.com/watch?v=abcdefghijk"
    assert normalize_youtube_url("https://music.youtube.com/watch?v=abcdefghijk") == (
        "https://www.youtube.com/watch?v=abcdefghijk"
    )
    assert normalize_youtube_url("вот ролик: https://www.youtube.com/embed/abcdefghijk.") == (
        "https://www.youtube.com/watch?v=abcdefghijk"
    )


def test_normalize_rejects_playlist_channel_and_other_sites():
    with pytest.raises(UserError, match="роли"):
        normalize_youtube_url("https://www.youtube.com/playlist?list=PLxxxxxxxxxxxxxxxxxxxxxx")
    with pytest.raises(UserError, match="роли"):
        normalize_youtube_url("https://www.youtube.com/@channel")
    with pytest.raises(UserError, match="YouTube"):
        normalize_youtube_url("https://videos.example/watch?v=abcdefghijk")
    with pytest.raises(UserError, match="http"):
        normalize_youtube_url("not a link")


def _video(width, height, note, **extra):
    item = {
        "vcodec": "avc1",
        "acodec": "none",
        "width": width,
        "height": height,
        "format_note": note,
        "ext": "mp4",
    }
    item.update(extra)
    return item


def test_options_follow_the_formats_that_exist():
    info = {
        "formats": [
            _video(1920, 1080, "1080p", filesize=80_000_000, tbr=2500),
            _video(1280, 720, "720p", filesize=40_000_000, tbr=1200),
            _video(640, 360, "360p", acodec="mp4a.40.2", filesize=12_000_000, tbr=400),
            _video(256, 144, "144p", filesize=3_000_000),
            {"vcodec": "none", "acodec": "mp4a.40.2", "ext": "m4a", "abr": 128, "tbr": 129},
            {"vcodec": "none", "ext": "mhtml", "format_note": "storyboard", "width": 320, "height": 180},
        ]
    }
    assert [item["id"] for item in build_options(info)] == ["1080", "720", "360", "144", "audio"]


def test_vertical_video_counts_by_the_shorter_side():
    info = {"formats": [_video(1080, 1920, "", filesize=9_000_000), {"vcodec": "none", "acodec": "opus"}]}
    options = build_options(info)
    assert options[0]["id"] == "1080"
    assert options[0]["label"] == "1080p Full HD"
    assert options[-1]["id"] == "audio"


def test_four_k_is_offered_as_1080p_and_long_videos_drop_heavy_buttons():
    only_four_k = {
        "duration": 60,
        "formats": [
            _video(3840, 2160, "2160p60", tbr=20000),
            {"vcodec": "none", "acodec": "mp4a.40.2", "abr": 128},
        ],
    }
    assert [item["id"] for item in build_options(only_four_k)] == ["1080", "audio"]

    long = {
        "duration": 4 * 3600,
        "formats": [
            _video(1920, 1080, "1080p", tbr=4500),
            _video(640, 360, "360p", acodec="mp4a.40.2", filesize=200_000_000),
            {"vcodec": "none", "acodec": "mp4a.40.2", "abr": 128, "tbr": 128},
        ],
    }
    assert [item["id"] for item in build_options(long)] == ["360", "audio"]


def test_audio_without_a_soundtrack_is_hidden_and_live_is_rejected():
    silent = {"formats": [_video(640, 360, "360p", filesize=1000)]}
    assert [item["id"] for item in build_options(silent)] == ["360"]
    with pytest.raises(DownloadError, match="эфир"):
        preview_from_info({"is_live": True, "title": "Эфир", "formats": []})


def test_preview_names_the_author_and_duration():
    card = preview_from_info(
        {
            "id": "abcdefghijk",
            "title": "Лекция",
            "uploader": "Автор",
            "duration": 90,
            "formats": [_video(1280, 720, "720p", filesize=1000, acodec="mp4a.40.2")],
        },
        url="https://www.youtube.com/watch?v=abcdefghijk",
    )
    assert card["author"] == "Автор"
    assert card["durationLabel"] == "1:30"
    assert card["options"][0]["label"] == "720p HD"
    assert "sizeLabel" in card["options"][0]
