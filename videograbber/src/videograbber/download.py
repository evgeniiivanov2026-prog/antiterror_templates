"""Загрузка ролика во временный файл через yt-dlp."""

from __future__ import annotations

import re
import threading
import time
from dataclasses import dataclass
from pathlib import Path

from videograbber.errors import DependencyError, DownloadError
from videograbber.media import (
    ProgressEvent,
    audio_format_selector,
    format_bytes,
    format_selector,
)
from videograbber.parallel import (
    BUFFER_SIZE,
    PARALLEL_CONNECTIONS,
    RangeNotSupported,
    download_parallel,
)

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
_AUDIO_SUFFIXES = {
    ".m4a",
    ".mp3",
    ".opus",
    ".ogg",
    ".aac",
    ".flac",
    ".wav",
    ".webm",
    ".mp4",
    ".mkv",
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
        shown = None if percent is None else self._last_percent
        self._on_progress(ProgressEvent(message, transient=True, percent=shown))


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
        "retries": 10,
        "fragment_retries": 10,
        "extractor_retries": 3,
        "socket_timeout": 30,
        "buffersize": BUFFER_SIZE,
        "concurrent_fragment_downloads": PARALLEL_CONNECTIONS,
        "format_sort": ["res", "fps", "codec:h264", "acodec:aac", "br"],
        "progress_hooks": [DownloadProgress(on_progress)],
        "match_filter": reject_unsupported,
        "logger": _SilentLogger(),
        "windowsfilenames": True,
    }
    if cookiefile is not None:
        options["cookiefile"] = str(cookiefile)
    return options


def _can_accelerate(info: dict) -> bool:
    """Обычный HTTP-файл можно качать несколькими соединениями. Потоки HLS и DASH — нет."""

    if info.get("fragments") or info.get("requested_formats"):
        return False
    protocol = str(info.get("protocol") or "http").split("+", 1)[0]
    if protocol not in {"http", "https"}:
        return False
    url = str(info.get("url") or "")
    if not url.startswith(("http://", "https://")):
        return False
    lowered = url.lower()
    return ".m3u8" not in lowered and ".mpd" not in lowered and ".f4m" not in lowered


def _header_map(headers) -> dict[str, str]:
    mapped = {}
    for key, value in (headers or {}).items():
        if value is None:
            continue
        mapped[str(key)] = str(value)
    return mapped


def _open_fast(youtube_dl, options):
    class FastYoutubeDL(youtube_dl):
        def __init__(self, params):
            super().__init__(params)
            self._hook_lock = threading.Lock()

        def dl(self, name, info, subtitle=False, test=False):
            if subtitle or test or not _can_accelerate(info):
                return super().dl(name, info, subtitle=subtitle, test=test)
            try:
                download_parallel(
                    str(info["url"]),
                    Path(name),
                    _header_map(info.get("http_headers")),
                    lambda done, total: self._report(info, done, total),
                )
            except RangeNotSupported:
                return super().dl(name, info, subtitle=subtitle, test=test)
            self._report_finished(info)
            return True, True

        def _report(self, info: dict, done: int, total: int) -> None:
            payload = {
                "status": "downloading",
                "downloaded_bytes": done,
                "total_bytes": total,
                "info_dict": info,
            }
            with self._hook_lock:
                for hook in self._progress_hooks:
                    hook(payload)

        def _report_finished(self, info: dict) -> None:
            payload = {"status": "finished", "info_dict": info}
            with self._hook_lock:
                for hook in self._progress_hooks:
                    hook(payload)

    return FastYoutubeDL(options)


def download_video(
    url: str,
    directory: Path,
    max_height: int | None,
    on_progress,
    cookiefile: Path | None = None,
    selector: str | None = None,
    suffixes: set[str] | None = None,
) -> Source:
    try:
        import yt_dlp
        from yt_dlp.utils import DownloadError as YtDlpDownloadError
    except ImportError as exc:
        raise DependencyError(
            "Не установлен yt-dlp. Выполните: pip install videograbber"
        ) from exc

    options = build_ydl_options(directory, max_height, on_progress, cookiefile)
    if selector:
        options["format"] = selector
    try:
        with _open_fast(yt_dlp.YoutubeDL, options) as ydl:
            info = ydl.extract_info(url, download=True)
    except YtDlpDownloadError as exc:
        raise DownloadError(_clean_message(str(exc))) from exc
    if not info:
        raise DownloadError("По ссылке не нашлось видео.")
    selected = _single_video(info)
    if selected.get("is_live"):
        raise DownloadError("Прямой эфир нельзя сохранить как один конечный MP4.")
    return Source(
        path=_locate_download(directory, selected, suffixes),
        title=str(selected.get("title") or selected.get("id") or "video"),
        duration=_duration(selected.get("duration")),
    )


def download_audio(
    url: str,
    directory: Path,
    on_progress,
    cookiefile: Path | None = None,
) -> Source:
    return download_video(
        url,
        directory,
        None,
        on_progress,
        cookiefile,
        selector=audio_format_selector(),
        suffixes=_AUDIO_SUFFIXES,
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


def _locate_download(directory: Path, info: dict, suffixes: set[str] | None = None) -> Path:
    allowed = suffixes or _MEDIA_SUFFIXES
    candidates: list[Path] = []
    for item in info.get("requested_downloads") or []:
        for key in ("filepath", "filename"):
            if item.get(key):
                candidates.append(Path(item[key]))
    for key in ("filepath", "_filename"):
        if info.get(key):
            candidates.append(Path(info[key]))
    for path in candidates:
        existing = _existing_media(path, allowed)
        if existing is not None:
            return existing
    files = [
        path
        for path in directory.iterdir()
        if path.is_file() and path.suffix.lower() in allowed and path.stat().st_size > 0
    ]
    if not files:
        raise DownloadError("Видео скачано, но готовый файл не найден.")
    return max(files, key=lambda path: path.stat().st_size)


def _existing_media(path: Path, allowed: set[str]) -> Path | None:
    options = [
        path,
        *(path.with_suffix(ext) for ext in (".mkv", ".mp4", ".webm", ".m4a", ".mp3", ".opus", ".ogg")),
    ]
    for option in options:
        if option.is_file() and option.stat().st_size > 0 and option.suffix.lower() in allowed:
            return option
    return None


def _clean_message(message: str) -> str:
    text = _ANSI.sub("", message).strip()
    text = re.sub(r"^ERROR:\s*", "", text)
    line = next((item.strip() for item in text.splitlines() if item.strip()), "")
    if not line:
        return "Не удалось скачать видео."
    lowered = line.lower()
    if "not a bot" in lowered or "sign in to confirm" in lowered:
        return (
            "YouTube просит подтвердить, что запрос не от бота. "
            "Если ролик открывается в браузере, экспортируйте cookies в файл Netscape "
            "и запустите: videograbber web --cookies cookies.txt"
        )
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
