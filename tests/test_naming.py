from pathlib import Path

import pytest

from videograbber.errors import UserInputError
from videograbber.naming import (
    allocate_output,
    redact_url,
    sanitize_filename,
    validate_browser,
    validate_quality,
    validate_url,
)
from videograbber.present import format_bytes, format_duration, mode_label


def test_validate_url_accepts_http_and_strips_space() -> None:
    assert validate_url("  https://example.com/watch?v=1  ") == "https://example.com/watch?v=1"


@pytest.mark.parametrize(
    "url",
    ["", "notaurl", "ftp://example.com/a", "file:///tmp/a.mp4", "javascript:alert(1)", "https://"],
)
def test_validate_url_rejects_non_http(url: str) -> None:
    with pytest.raises(UserInputError):
        validate_url(url)


def test_redact_url_hides_password() -> None:
    hidden = redact_url("https://user:secret@example.com:8443/a")
    assert "secret" not in hidden
    assert hidden == "https://***@example.com:8443/a"
    ipv6 = redact_url("https://user:secret@[::1]:8080/a")
    assert "secret" not in ipv6
    assert ipv6 == "https://***@[::1]:8080/a"


def test_quality_accepts_suffix_p() -> None:
    assert validate_quality("1080p") == "1080"
    assert validate_quality(" BEST ") == "best"
    with pytest.raises(UserInputError):
        validate_quality("999")


def test_browser_name() -> None:
    assert validate_browser("Firefox") == "firefox"
    with pytest.raises(UserInputError):
        validate_browser("lynx")


def test_sanitize_filename_keeps_cyrillic_and_drops_path() -> None:
    assert sanitize_filename("  Ёлка / весна? ") == "Ёлка весна"
    assert "/" not in sanitize_filename("../../etc/passwd")
    assert sanitize_filename("clip.mp4") == "clip"
    assert sanitize_filename("CON") == "_CON"
    assert sanitize_filename("   ...  ") == "video"
    assert len(sanitize_filename("я" * 200)) == 80


def test_allocate_output_adds_suffix_and_force_reuses_name(tmp_path: Path) -> None:
    first = allocate_output(tmp_path, "Ролик")
    first.write_bytes(b"x")
    second = allocate_output(tmp_path, "Ролик")
    assert second.name == "Ролик (2).mp4"
    assert allocate_output(tmp_path, "Ролик", force=True) == first


def test_present_helpers() -> None:
    assert format_duration(65) == "1:05"
    assert format_duration(3661) == "1:01:01"
    assert format_duration(None) == "—"
    assert format_bytes(500) == "500 Б"
    assert format_bytes(1536) == "1.5 КБ"
    assert mode_label("transcode") == "перекодирован в H.264/AAC"
