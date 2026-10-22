"""Загрузка ролика во временный файл через yt-dlp."""

from __future__ import annotations

import re
import time
from dataclasses import dataclass
from pathlib import Path

from videograbber.errors import DependencyError, DownloadError
from videograbber.media import ProgressEvent, format_bytes, format_selector

_ANSI = re.compile(r"\x1b\[[0-9;]*m")
_MEDIA_SUFFIXES = {
    ".mp4",
    ".mkv",
    ".webm",
    ".mov",
    ".m4v",
    ".flv",
    ".avi",
    ".ts",
    ".mpeg",
    ".mpg",
    ".3gp",
    ".ogv",
    ".wmv",
}


@dataclass(frozen=True)
class Source:
    path: Path
    title: str
    duration: float | None


class DownloadProgress:
    """Сжимает частые обновления yt-dlp в короткие строки статуса."""

    def __init__(self, on_progress) -> None:
        self._on_progress = on_progress
        self._last_at = 0.0
        self._last_percent = -1
        self._announced = False

    def __call__(self, data: dict) -> None:
        if self._on_progress is None:
            return
        info = data.get("info_dict") or {}
        title = info.get("title")
        if title and not self._announced:
            self._announced = True
            self._on_progress(ProgressEvent(f"Найдено: {title}"))
        status = data.get("status")
        label = _stream_label(info)
        if status == "finished":
            self._on_progress(ProgressEvent(f"{_finished_label(label)}"))
            self._last_percent = -1
            return
        if status != "downloading":
            return
        downloaded = data.get("downloaded_bytes") or 0
        total = data.get("total_bytes") or data.get("total_bytes_estimate")
        percent = int(downloaded * 100 / total) if total else None
        now = time.monotonic()
        if percent is not None and percent == self._last_percent and now - self._last_at < 0.25:
            return
        if percent is None and now - self._last_at < 0.5:
            return
        self._last_at = now
        if percent is None:
            message = f"Скачивание, {label}: {format_bytes(downloaded)}"
        else:
            self._last_percent = min(100, percent)
            message = (
                f"Скачивание, {label}: {self._last_percent}% ({format_bytes(downloaded)})"
            )
        self._on_progress(ProgressEvent(message, transient=True))


class _SilentLogger:
    def debug(self, message: str) -> None:
        return None

    def info(self, message: str) -> None:
        return None

    def warning(self, message: str) -> None:
        return None

    def error(self, message: str) -> None:
        return None


def reject_unsupported(info: dict, *, incomplete: bool = False) -> str | None:
    """Отсечь прямой эфир: у него нет конечного файла."""

    if info.get("is_live"):
        return "Прямой эфир нельзя сохранить как один конечный MP4."
    return None


def build_ydl_options(
    directory: Path,
    max_height: int | None,
    on_progress,
    cookiefile: Path | None = None,
) -> dict:
    options = {
        "format": format_selector(max_height),
        "outtmpl": str(directory / "media.%(ext)s"),
        "merge_output_format": "mkv",
        "noplaylist": True,
        "quiet": True,
        "no_warnings": True,
        "noprogress": True,
        "retries": 5,
        "fragment_retries": 5,
        "extractor_retries": 3,
        "socket_timeout": 30,
        "concurrent_fragment_downloads": 4,
        "progress_hooks": [DownloadProgress(on_progress)],
        "match_filter": reject_unsupported,
        "logger": _SilentLogger(),
        "windowsfilenames": True,
    }
    if cookiefile is not None:
        options["cookiefile"] = str(cookiefile)
    return options


def download_video(
    url: str,
    directory: Path,
    max_height: int | None,
    on_progress,
    cookiefile: Path | None = None,
) -> Source:
    try:
        import yt_dlp
        from yt_dlp.utils import DownloadError as YtDlpDownloadError
    except ImportError as exc:
        raise DependencyError(
            "Не установлен yt-dlp. Выполните: pip install videograbber"
        ) from exc

    options = build_ydl_options(directory, max_height, on_progress, cookiefile)
    try:
        with yt_dlp.YoutubeDL(options) as ydl:
            info = ydl.extract_info(url, download=True)
    except YtDlpDownloadError as exc:
        raise DownloadError(_clean_message(str(exc))) from exc
    if not info:
        raise DownloadError("По ссылке не нашлось видео.")
    selected = _single_video(info)
    if selected.get("is_live"):
        raise DownloadError("Прямой эфир нельзя сохранить как один конечный MP4.")
    return Source(
        path=_locate_download(directory, selected),
        title=str(selected.get("title") or selected.get("id") or "video"),
        duration=_duration(selected.get("duration")),
    )


def _single_video(info: dict) -> dict:
    if info.get("_type") != "playlist":
        return info
    entries = [entry for entry in info.get("entries") or [] if entry]
    if len(entries) == 1:
        return entries[0]
    if not entries:
        raise DownloadError("По ссылке не нашлось видео.")
    raise DownloadError(
        "Ссылка ведёт на плейлист. Откройте ролик и передайте ссылку на само видео."
    )


def _locate_download(directory: Path, info: dict) -> Path:
    candidates: list[Path] = []
    for item in info.get("requested_downloads") or []:
        for key in ("filepath", "filename"):
            if item.get(key):
                candidates.append(Path(item[key]))
    for key in ("filepath", "_filename"):
        if info.get(key):
            candidates.append(Path(info[key]))
    for path in candidates:
        existing = _existing_media(path)
        if existing is not None:
            return existing
    files = [
        path
        for path in directory.iterdir()
        if path.is_file() and path.suffix.lower() in _MEDIA_SUFFIXES and path.stat().st_size > 0
    ]
    if not files:
        raise DownloadError("Видео скачано, но готовый файл не найден.")
    return max(files, key=lambda path: path.stat().st_size)


def _existing_media(path: Path) -> Path | None:
    options = [path, *(path.with_suffix(ext) for ext in (".mkv", ".mp4", ".webm", ".mov"))]
    for option in options:
        if option.is_file() and option.stat().st_size > 0 and option.suffix.lower() in _MEDIA_SUFFIXES:
            return option
    return None


def _clean_message(message: str) -> str:
    text = _ANSI.sub("", message).strip()
    text = re.sub(r"^ERROR:\s*", "", text)
    line = next((item.strip() for item in text.splitlines() if item.strip()), "")
    if not line:
        return "Не удалось скачать видео."
    lowered = line.lower()
    if "cookie" in lowered or "sign in" in lowered or "logged-in" in lowered:
        line += " Если сайт пускает вас в браузере, повторите команду с --cookies."
    return line


def _duration(value) -> float | None:
    try:
        number = float(value)
    except (TypeError, ValueError):
        return None
    if number <= 0:
        return None
    return number


def _stream_label(info: dict) -> str:
    has_video = info.get("vcodec") not in (None, "none")
    has_audio = info.get("acodec") not in (None, "none")
    if has_video and has_audio:
        return "запись"
    if has_audio and not has_video:
        return "аудио"
    return "видео"


def _finished_label(label: str) -> str:
    return {
        "видео": "Видео загружено",
        "аудио": "Аудио загружено",
        "запись": "Запись загружена",
    }[label]
