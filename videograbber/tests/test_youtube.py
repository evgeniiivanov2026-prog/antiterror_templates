import pytest

from videograbber.errors import DownloadError, UserError
from videograbber.youtube import (
    build_options,
    fetch_remote_thumbnail,
    normalize_watch_url,
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


_RUTUBE = "3eac3b4561676c17df9132a9a1e62e3e"


def test_rutube_links_become_one_video_url():
    assert normalize_watch_url(f" https://www.rutube.ru/video/{_RUTUBE}/?pl_id=4252 ") == (
        f"https://rutube.ru/video/{_RUTUBE}/"
    )
    assert normalize_watch_url(f"https://rutube.ru/shorts/{_RUTUBE}/") == (
        f"https://rutube.ru/video/{_RUTUBE}/"
    )
    assert normalize_watch_url(f"https://rutube.ru/play/embed/{_RUTUBE}") == (
        f"https://rutube.ru/video/{_RUTUBE}/"
    )
    assert normalize_watch_url(
        "https://rutube.ru/video/private/884fb55f07a97ab673c7d654553e0f48/?p=x2QojCumHTS3rsKHWXN8Lg"
    ) == "https://rutube.ru/video/private/884fb55f07a97ab673c7d654553e0f48/?p=x2QojCumHTS3rsKHWXN8Lg"
    assert normalize_watch_url("https://m.rutube.ru/play/embed/6722881?p=IbAigKqWd1do4mjaM5XLIQ") == (
        "https://rutube.ru/play/embed/6722881?p=IbAigKqWd1do4mjaM5XLIQ"
    )
    assert normalize_watch_url("https://youtu.be/abcdefghijk") == (
        "https://www.youtube.com/watch?v=abcdefghijk"
    )


def test_rutube_channel_and_other_sites_are_rejected():
    with pytest.raises(UserError, match="роли"):
        normalize_watch_url("https://rutube.ru/channel/639184/videos/")
    with pytest.raises(UserError, match="роли"):
        normalize_watch_url("https://rutube.ru/plst/308547/")
    with pytest.raises(UserError, match="YouTube или Rutube"):
        normalize_watch_url("https://videos.example/watch?v=abcdefghijk")
    assert fetch_remote_thumbnail("https://example.com/thumb.jpg") is None
    assert fetch_remote_thumbnail("https://pic.rutubelist.ru.evil.test/video/a.jpg") is None


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
