from pathlib import Path

import pytest

from tests.ffmpeg_util import assert_decodes, make_avi, make_mp4, serve_directory
from videograbber.errors import DownloadFailed, UserInputError
from videograbber.grab import coerce_targets, format_inspection, format_selector, grab, inspect
from videograbber.media import probe_media


def test_format_selector_caps_height_and_falls_back() -> None:
    chosen = format_selector("720")
    assert "height<=720" in chosen
    assert chosen.endswith("/best")
    assert format_selector("best").startswith("bestvideo[ext=mp4]")


def test_coerce_targets_prefers_direct_url_and_limits_playlist() -> None:
    single = coerce_targets({"_type": "video", "title": "Один"}, "https://example.com/v", limit=10)
    assert single == [single[0]]
    assert single[0].url == "https://example.com/v"
    assert single[0].title == "Один"
    playlist = {
        "_type": "playlist",
        "entries": [
            {"title": "Первый", "webpage_url": "https://example.com/1"},
            {"title": "Второй", "url": "abc", "playlist_index": 4},
            None,
            {"title": "Третий", "webpage_url": "https://example.com/3"},
        ],
    }
    targets = coerce_targets(playlist, "https://example.com/list", limit=2)
    assert [item.title for item in targets] == ["Первый", "Второй"]
    assert targets[0].url == "https://example.com/1"
    assert targets[0].playlist_items is None
    assert targets[1].url == "https://example.com/list"
    assert targets[1].playlist_items == "4"
    with pytest.raises(DownloadFailed):
        coerce_targets({"_type": "playlist", "entries": []}, "https://example.com/list", limit=5)


def test_format_inspection_for_video_and_playlist() -> None:
    text = format_inspection(
        {
            "title": "Лекция",
            "duration": 65,
            "extractor_key": "generic",
            "formats": [
                {"vcodec": "h264", "height": 720},
                {"vcodec": "none", "height": 720},
                {"vcodec": "h264", "height": 360},
            ],
        }
    )
    assert "Лекция" in text
    assert "1:05" in text
    assert "720p" in text
    playlist = format_inspection(
        {
            "_type": "playlist",
            "title": "Курс",
            "playlist_count": 2,
            "entries": [{"title": "Урок 1"}, {"title": "Урок 2"}],
        }
    )
    assert "Курс" in playlist
    assert "Урок 2" in playlist


@pytest.fixture
def hosted_media(tmp_path: Path):
    library = tmp_path / "library"
    make_mp4(library / "clip.mp4", faststart=False)
    make_avi(library / "old.avi")
    httpd = serve_directory(library)
    base = f"http://127.0.0.1:{httpd.server_address[1]}"
    yield base
    httpd.shutdown()
    httpd.server_close()


def test_grab_turns_direct_links_into_playable_mp4(hosted_media: str, tmp_path: Path) -> None:
    output = tmp_path / "out"
    mp4 = grab(f"{hosted_media}/clip.mp4", output, quality="1080")
    assert len(mp4) == 1
    assert mp4[0].path.suffix == ".mp4"
    assert mp4[0].path.parent == output
    assert mp4[0].video_codec == "h264"
    assert mp4[0].audio_codec == "aac"
    assert mp4[0].mode in {"ready", "remux"}
    assert mp4[0].width == 320
    assert probe_media(mp4[0].path).faststart is True
    assert_decodes(mp4[0].path)

    avi = grab(f"{hosted_media}/old.avi", output, quality="best")
    assert avi[0].mode == "transcode"
    assert avi[0].video_codec == "h264"
    assert avi[0].audio_codec == "aac"
    assert probe_media(avi[0].path).faststart is True
    assert_decodes(avi[0].path)
    assert avi[0].path != mp4[0].path


def test_inspect_reads_direct_file_without_writing_output(hosted_media: str, tmp_path: Path) -> None:
    before = {path.resolve() for path in tmp_path.rglob("*") if path.is_file()}
    text = inspect(f"{hosted_media}/clip.mp4")
    after = {path.resolve() for path in tmp_path.rglob("*") if path.is_file()}
    assert "Название" in text
    assert "Длительность" in text
    assert after == before


def test_grab_rejects_bad_input_before_download(tmp_path: Path) -> None:
    with pytest.raises(UserInputError):
        grab("notaurl", tmp_path)
    with pytest.raises(UserInputError):
        grab("https://example.com/a", tmp_path, playlist_limit=0)
