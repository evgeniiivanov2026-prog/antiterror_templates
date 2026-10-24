import pytest

from videograbber.errors import UserError
from videograbber.youtube import (
    build_catalog,
    canonical_youtube_url,
    format_spec_for_edge,
    video_id_from_url,
)


def _video(format_id, width, height, tbr=1000, acodec="none", vcodec="avc1.640028", ext="mp4", filesize=None):
    item = {
        "format_id": format_id,
        "width": width,
        "height": height,
        "vcodec": vcodec,
        "acodec": acodec,
        "ext": ext,
        "tbr": tbr,
    }
    if filesize is not None:
        item["filesize"] = filesize
    return item


def _audio(format_id="140", tbr=128, filesize=None):
    item = {
        "format_id": format_id,
        "vcodec": "none",
        "acodec": "mp4a.40.2",
        "ext": "m4a",
        "tbr": tbr,
        "abr": tbr,
    }
    if filesize is not None:
        item["filesize"] = filesize
    return item


def test_youtube_urls_keep_the_video_and_drop_the_playlist():
    video_id = "dQw4w9WgXcQ"
    assert video_id_from_url(f"https://youtu.be/{video_id}") == video_id
    assert video_id_from_url(f"https://www.youtube.com/shorts/{video_id}?feature=share") == video_id
    assert video_id_from_url(f"https://m.youtube.com/watch?v={video_id}&t=12") == video_id
    assert video_id_from_url(f"https://www.youtube.com/embed/{video_id}") == video_id
    assert video_id_from_url(f"https://www.youtube.com/live/{video_id}") == video_id
    assert video_id_from_url(f"https://music.youtube.com/watch?v={video_id}") == video_id
    assert video_id_from_url("https://www.youtube.com/playlist?list=PL123") is None
    assert video_id_from_url("https://example.com/watch?v=dQw4w9WgXcQ") is None
    assert video_id_from_url("https://www.youtube.com/watch?v=short") is None
    assert (
        canonical_youtube_url(f" https://youtu.be/{video_id}?list=PLxxxx ")
        == f"https://www.youtube.com/watch?v={video_id}"
    )


def test_catalog_offers_existing_heights_and_keeps_shorts_vertical():
    formats = [
        _video("399", 1080, 1920, tbr=2000, filesize=5_000_000),
        _video("398", 720, 1280, tbr=800, vcodec="vp9", ext="webm"),
        _video("18", 640, 360, tbr=500, acodec="mp4a.40.2", filesize=800_000),
        _video("401", 3840, 2160, tbr=20000, vcodec="av01"),
        _audio(),
        {
            "format_id": "sb0",
            "ext": "mhtml",
            "vcodec": "none",
            "acodec": "none",
            "format_note": "storyboard",
            "height": 90,
            "width": 160,
        },
    ]
    catalog = build_catalog(
        {"title": "Short", "uploader": "Автор", "duration": 20, "formats": formats},
        "dQw4w9WgXcQ",
    )
    assert catalog["title"] == "Short"
    assert catalog["author"] == "Автор"
    assert catalog["duration"] == "0:20"
    assert catalog["thumbnail"] == "/api/thumb/dQw4w9WgXcQ"
    assert [item["id"] for item in catalog["options"]] == ["1080", "720", "360", "mp3"]
    assert catalog["options"][-1]["label"] == "Только аудио (MP3)"
    assert format_spec_for_edge(formats, 1080) == "399+140"
    assert format_spec_for_edge(formats, 360) == "18"


def test_very_long_video_hides_qualities_above_the_size_cap():
    formats = [
        _video("137", 1920, 1080, tbr=8000),
        _video("134", 640, 360, tbr=400),
        _audio(tbr=128),
    ]
    catalog = build_catalog(
        {"title": "Лекция", "channel": "Курс", "duration": 3 * 60 * 60, "formats": formats},
        "abcdefghijk",
    )
    ids = [item["id"] for item in catalog["options"]]
    assert "1080" not in ids
    assert "360" in ids
    assert ids[-1] == "mp3"
    assert format_spec_for_edge(formats, 1080) == "137+140"


def test_live_and_playlist_and_empty_video_are_rejected():
    with pytest.raises(UserError, match="эфир"):
        build_catalog({"is_live": True, "formats": []}, "abcdefghijk")
    with pytest.raises(UserError, match="плейлист"):
        build_catalog({"_type": "playlist", "entries": [{}, {}]}, "abcdefghijk")
    with pytest.raises(UserError, match="нет файла"):
        build_catalog({"title": "Пусто", "formats": []}, "abcdefghijk")


def test_four_k_only_video_still_offers_audio():
    formats = [
        _video("401", 3840, 2160, tbr=15000, vcodec="av01"),
        _audio(),
    ]
    catalog = build_catalog({"title": "4K", "duration": 30, "formats": formats}, "abcdefghijk")
    assert [item["id"] for item in catalog["options"]] == ["mp3"]
    assert format_spec_for_edge(formats, 1080) is None
