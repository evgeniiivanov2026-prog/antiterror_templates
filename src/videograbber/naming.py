"""Ссылки, имена файлов и проверка параметров."""

from __future__ import annotations

import re
import unicodedata
from pathlib import Path
from urllib.parse import urlsplit, urlunsplit

from videograbber.errors import UserInputError

QUALITIES = ("best", "2160", "1440", "1080", "720", "480", "360")
BROWSERS = (
    "brave",
    "chrome",
    "chromium",
    "edge",
    "firefox",
    "opera",
    "safari",
    "vivaldi",
)
_RESERVED_WINDOWS = {
    "AUX",
    "COM1",
    "COM2",
    "COM3",
    "COM4",
    "CON",
    "LPT1",
    "LPT2",
    "LPT3",
    "NUL",
    "PRN",
}
_FORBIDDEN_CHARS = re.compile(r'[<>:"/\\|?*]')


def validate_url(url: str) -> str:
    if not isinstance(url, str):
        raise UserInputError("Ссылка должна быть текстом.")
    cleaned = url.strip()
    if not cleaned:
        raise UserInputError("Вставьте ссылку на видео.")
    if len(cleaned) > 4096:
        raise UserInputError("Слишком длинная ссылка.")
    parsed = urlsplit(cleaned)
    if parsed.scheme not in {"http", "https"}:
        raise UserInputError("Нужна ссылка, которая начинается с http:// или https://.")
    if not parsed.hostname or any(char.isspace() for char in parsed.netloc):
        raise UserInputError("В ссылке не указан сайт.")
    return cleaned


def redact_url(url: str) -> str:
    """Убирает логин и пароль из ссылки перед показом и записью в журнал."""
    parsed = urlsplit(url)
    if parsed.username is None and parsed.password is None:
        return url
    host = parsed.hostname or ""
    if ":" in host and not host.startswith("["):
        host = f"[{host}]"
    if parsed.port:
        host = f"{host}:{parsed.port}"
    return urlunsplit((parsed.scheme, f"***@{host}", parsed.path, parsed.query, parsed.fragment))


def validate_quality(quality: str) -> str:
    value = str(quality).strip().lower().rstrip("p")
    if value not in QUALITIES:
        options = ", ".join(QUALITIES)
        raise UserInputError(f"Качество должно быть одним из: {options}.")
    return value


def validate_browser(name: str | None) -> str | None:
    if name is None:
        return None
    value = name.strip().lower()
    if value not in BROWSERS:
        options = ", ".join(BROWSERS)
        raise UserInputError(f"Браузер для cookies: {options}.")
    return value


def validate_cookies(path: Path | None) -> Path | None:
    if path is None:
        return None
    resolved = path.expanduser()
    if not resolved.is_file():
        raise UserInputError(f"Файл cookies не найден: {resolved}")
    return resolved


def sanitize_filename(name: str, *, max_length: int = 80) -> str:
    normalized = unicodedata.normalize("NFC", name or "")
    kept: list[str] = []
    for char in normalized:
        category = unicodedata.category(char)
        if category in {"Cc", "Cf"}:
            kept.append(" ")
            continue
        kept.append(char)
    text = _FORBIDDEN_CHARS.sub(" ", "".join(kept))
    text = re.sub(r"\s+", " ", text).strip(" .")
    text = re.sub(
        r"\.(?:avi|m4v|mkv|mov|mp4|mpeg|mpg|webm)$",
        "",
        text,
        count=1,
        flags=re.IGNORECASE,
    ).strip(" .")
    if not text:
        text = "video"
    if text.upper() in _RESERVED_WINDOWS:
        text = f"_{text}"
    if len(text) > max_length:
        text = text[:max_length].rstrip(" .")
    return text or "video"


def metadata_text(value: str | None, *, max_length: int = 180) -> str | None:
    if not value:
        return None
    cleaned = value.replace("\x00", " ")
    cleaned = re.sub(r"\s+", " ", cleaned).strip()
    if not cleaned:
        return None
    return cleaned[:max_length]


def ensure_output_dir(path: Path) -> Path:
    if path.exists() and not path.is_dir():
        raise UserInputError(f"{path} не является папкой.")
    try:
        path.mkdir(parents=True, exist_ok=True)
    except OSError as exc:
        reason = exc.strerror or "нет доступа"
        raise UserInputError(f"Не удалось создать папку {path}: {reason}") from exc
    return path


def allocate_output(directory: Path, title: str, *, force: bool = False) -> Path:
    directory = ensure_output_dir(directory)
    base = sanitize_filename(title)
    candidate = directory / f"{base}.mp4"
    if force or not candidate.exists():
        return candidate
    for number in range(2, 1001):
        candidate = directory / f"{base} ({number}).mp4"
        if not candidate.exists():
            return candidate
    raise UserInputError("Слишком много файлов с таким названием.")
