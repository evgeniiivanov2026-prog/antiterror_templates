"""Скачивание ролика и публикация готового MP4."""

from __future__ import annotations

import logging
import tempfile
from dataclasses import dataclass
from pathlib import Path
from collections.abc import Callable

from videograbber.errors import DependencyError, DownloadFailed, MediaError, UserInputError
from videograbber.media import assemble_mp4, probe_media, require_ffmpeg
from videograbber.naming import (
    allocate_output,
    ensure_output_dir,
    redact_url,
    validate_browser,
    validate_cookies,
    validate_quality,
    validate_url,
)
from videograbber.present import format_duration

log = logging.getLogger("videograbber")

MEDIA_SUFFIXES = {
    ".aac",
    ".avi",
    ".flv",
    ".m4a",
    ".m4v",
    ".mkv",
    ".mov",
    ".mp3",
    ".mp4",
    ".mpeg",
    ".mpg",
    ".ogg",
    ".ogv",
    ".opus",
    ".ts",
    ".wav",
    ".webm",
    ".wmv",
}


@dataclass(frozen=True)
class Progress:
    phase: str
    percent: float | None = None
    speed: float | None = None
    eta: int | None = None
    title: str | None = None


@dataclass(frozen=True)
class Target:
    url: str
    title: str | None = None
    playlist_items: str | None = None


@dataclass(frozen=True)
class GrabResult:
    path: Path
    title: str
    bytes: int
    width: int
    height: int
    duration: float | None
    video_codec: str
    audio_codec: str | None
    mode: str


ProgressCallback = Callable[[Progress], None]


def format_selector(quality: str) -> str:
    chosen = validate_quality(quality)
    if chosen == "best":
        limit = ""
    else:
        limit = f"[height<={chosen}]"
    return (
        f"bestvideo{limit}[ext=mp4]+bestaudio[ext=m4a]/"
        f"bestvideo{limit}+bestaudio/"
        f"best{limit}[ext=mp4]/"
        f"best{limit}/"
        "best"
    )


def coerce_targets(info: dict, page_url: str, *, limit: int) -> list[Target]:
    if limit < 1:
        raise DownloadFailed("Лимит плейлиста должен быть больше нуля.")
    if info.get("_type") not in {"playlist", "multi_video"}:
        return [Target(page_url, _clean_title(info.get("title")))]
    entries = [entry for entry in info.get("entries") or [] if isinstance(entry, dict)]
    if not entries:
        raise DownloadFailed("Плейлист пуст или недоступен.")
    targets: list[Target] = []
    for fallback_index, entry in enumerate(entries[:limit], start=1):
        title = _clean_title(entry.get("title"))
        direct = _http_url(entry.get("webpage_url")) or _http_url(entry.get("url"))
        if direct:
            targets.append(Target(direct, title))
            continue
        index = entry.get("playlist_index") or fallback_index
        targets.append(Target(page_url, title, playlist_items=str(index)))
    return targets


def format_inspection(info: dict) -> str:
    if info.get("_type") in {"playlist", "multi_video"}:
        entries = [entry for entry in info.get("entries") or [] if isinstance(entry, dict)]
        count = info.get("playlist_count") or info.get("n_entries") or len(entries)
        lines = [
            f"Плейлист: {info.get('title') or 'без названия'}",
            f"Роликов: {count}",
        ]
        for entry in entries[:20]:
            lines.append(f"- {entry.get('title') or entry.get('id') or 'ролик'}")
        if len(entries) > 20:
            lines.append(f"… и ещё {len(entries) - 20}")
        return "\n".join(lines)

    heights = _heights(info)
    lines = [
        f"Название: {info.get('title') or '—'}",
        f"Длительность: {format_duration(_as_float(info.get('duration')))}",
        f"Источник: {info.get('extractor_key') or info.get('extractor') or '—'}",
    ]
    if heights:
        lines.append("Доступная высота: " + ", ".join(f"{height}p" for height in heights))
    else:
        lines.append("Доступная высота: как в файле")
    return "\n".join(lines)


def result_payload(result: GrabResult) -> dict[str, object]:
    return {
        "path": str(result.path),
        "title": result.title,
        "bytes": result.bytes,
        "width": result.width,
        "height": result.height,
        "duration": result.duration,
        "video_codec": result.video_codec,
        "audio_codec": result.audio_codec,
        "mode": result.mode,
    }


def inspect(
    url: str,
    *,
    playlist: bool = False,
    cookies: Path | None = None,
    cookies_from_browser: str | None = None,
) -> str:
    cleaned = validate_url(url)
    cookie_file = validate_cookies(cookies)
    browser = validate_browser(cookies_from_browser)
    _reject_both_cookie_sources(cookie_file, browser)
    info = _extract(
        cleaned,
        playlist=playlist,
        playlist_limit=50,
        cookies=cookie_file,
        cookies_from_browser=browser,
    )
    return format_inspection(info)


def grab(
    url: str,
    output_dir: Path,
    *,
    quality: str = "1080",
    force: bool = False,
    reencode: bool = False,
    playlist: bool = False,
    playlist_limit: int = 50,
    cookies: Path | None = None,
    cookies_from_browser: str | None = None,
    on_progress: ProgressCallback | None = None,
) -> list[GrabResult]:
    cleaned = validate_url(url)
    chosen_quality = validate_quality(quality)
    cookie_file = validate_cookies(cookies)
    browser = validate_browser(cookies_from_browser)
    _reject_both_cookie_sources(cookie_file, browser)
    if playlist_limit < 1 or playlist_limit > 500:
        raise UserInputError("Лимит плейлиста должен быть от 1 до 500.")
    require_ffmpeg()
    destination = ensure_output_dir(Path(output_dir))
    if playlist:
        info = _extract(
            cleaned,
            playlist=True,
            playlist_limit=playlist_limit,
            cookies=cookie_file,
            cookies_from_browser=browser,
            flat_playlist=True,
        )
        targets = coerce_targets(info, cleaned, limit=playlist_limit)
        full_count = info.get("playlist_count") or info.get("n_entries")
        if isinstance(full_count, int) and full_count > len(targets):
            log.warning(
                "В плейлисте %s роликов. Скачиваю первые %s.",
                full_count,
                len(targets),
            )
    else:
        targets = [Target(cleaned)]

    results: list[GrabResult] = []
    total = len(targets)
    for index, target in enumerate(targets, start=1):
        results.append(
            _grab_one(
                target,
                destination,
                quality=chosen_quality,
                force=force and total == 1,
                reencode=reencode,
                cookies=cookie_file,
                cookies_from_browser=browser,
                on_progress=_indexed_progress(on_progress, index, total),
            )
        )
    return results


def _grab_one(
    target: Target,
    output_dir: Path,
    *,
    quality: str,
    force: bool,
    reencode: bool,
    cookies: Path | None,
    cookies_from_browser: str | None,
    on_progress: ProgressCallback | None,
) -> GrabResult:
    if on_progress:
        on_progress(Progress(phase="download", percent=0, title=target.title))
    with tempfile.TemporaryDirectory(prefix="videograbber-") as temporary:
        folder = Path(temporary)
        downloaded = _download(
            target.url,
            folder,
            quality=quality,
            cookies=cookies,
            cookies_from_browser=cookies_from_browser,
            playlist_items=target.playlist_items,
            on_progress=on_progress,
            title_hint=target.title,
        )
        video_path, audio_path = _select_streams(folder)
        title = target.title or downloaded["title"]
        source_duration = downloaded["duration"]

        def finalize_progress(percent: float) -> None:
            if on_progress:
                on_progress(Progress(phase="finalize", percent=percent, title=title))

        published = allocate_output(output_dir, title, force=force)
        if on_progress:
            on_progress(Progress(phase="finalize", percent=0, title=title))
        try:
            assembled = assemble_mp4(
                video_path,
                published,
                audio_path,
                reencode=reencode,
                title=title,
                on_progress=finalize_progress,
            )
        except MediaError:
            raise
        info = assembled.info
        if (
            source_duration
            and info.duration
            and source_duration > 5
            and info.duration < source_duration * 0.5
        ):
            log.warning(
                "Длительность файла %.1f с меньше ожидаемых %.1f с.",
                info.duration,
                source_duration,
            )
        log.info("готово %s (%s)", published.name, assembled.mode)
        return GrabResult(
            path=published,
            title=title,
            bytes=info.size,
            width=info.width or 0,
            height=info.height or 0,
            duration=info.duration,
            video_codec=info.video_codec or "",
            audio_codec=info.audio_codec,
            mode=assembled.mode,
        )


def _download(
    url: str,
    directory: Path,
    *,
    quality: str,
    cookies: Path | None,
    cookies_from_browser: str | None,
    playlist_items: str | None,
    on_progress: ProgressCallback | None,
    title_hint: str | None,
) -> dict[str, object]:
    youtube_dl, download_error = _load_yt_dlp()
    options = _base_options(cookies, cookies_from_browser)
    options.update(
        {
            "format": format_selector(quality),
            "outtmpl": {"default": "media.%(ext)s"},
            "paths": {"home": str(directory), "temp": str(directory)},
            "merge_output_format": "mp4",
            "noplaylist": playlist_items is None,
            "overwrites": True,
            "progress_hooks": [_progress_hook(on_progress, title_hint)],
        }
    )
    if playlist_items is not None:
        options["playlist_items"] = playlist_items
    log.info("скачивание %s", redact_url(url))
    try:
        with youtube_dl(options) as client:
            info = client.extract_info(url, download=True)
    except download_error as exc:
        raise DownloadFailed(_clean_message(exc)) from exc
    if not isinstance(info, dict):
        raise DownloadFailed("Источник не вернул данные о видео.")
    if info.get("_type") in {"playlist", "multi_video"}:
        entries = [entry for entry in info.get("entries") or [] if isinstance(entry, dict)]
        if not entries:
            raise DownloadFailed("Плейлист пуст или недоступен.")
        info = entries[0]
    return {
        "title": _clean_title(info.get("title")) or _clean_title(info.get("id")) or "video",
        "duration": _as_float(info.get("duration")),
    }


def _progress_hook(on_progress: ProgressCallback | None, title_hint: str | None):
    def hook(event: dict) -> None:
        if on_progress is None:
            return
        status = event.get("status")
        info = event.get("info_dict") or {}
        title = title_hint or _clean_title(info.get("title"))
        if status == "finished":
            on_progress(Progress(phase="download", percent=100, title=title))
            return
        if status != "downloading":
            return
        total = event.get("total_bytes") or event.get("total_bytes_estimate")
        downloaded = event.get("downloaded_bytes")
        percent = None
        if isinstance(total, (int, float)) and isinstance(downloaded, (int, float)) and total > 0:
            percent = max(0.0, min(100.0, downloaded / total * 100))
        speed = event.get("speed")
        eta = event.get("eta")
        on_progress(
            Progress(
                phase="download",
                percent=percent,
                speed=float(speed) if isinstance(speed, (int, float)) and speed >= 0 else None,
                eta=int(eta) if isinstance(eta, (int, float)) and eta >= 0 else None,
                title=title,
            )
        )

    return hook


def _select_streams(directory: Path) -> tuple[Path, Path | None]:
    files = [
        path
        for path in directory.rglob("*")
        if path.is_file()
        and path.suffix.lower() in MEDIA_SUFFIXES
        and not path.name.endswith(".part")
    ]
    if not files:
        raise DownloadFailed("После загрузки не осталось видеофайла.")
    probed: list[tuple[Path, object]] = []
    for path in files:
        try:
            probed.append((path, probe_media(path)))
        except MediaError as exc:
            log.info("пропускаю %s: %s", path.name, exc)
    if not probed:
        raise DownloadFailed("Скачанный файл не читается как видео.")
    combined = [(path, info) for path, info in probed if info.has_video and info.has_audio]
    if combined:
        chosen = max(combined, key=lambda item: item[0].stat().st_size)
        return chosen[0], None
    videos = [(path, info) for path, info in probed if info.has_video]
    if not videos:
        raise DownloadFailed("По этой ссылке нет видео, только звук или страница.")
    video = max(videos, key=lambda item: item[0].stat().st_size)
    audios = [(path, info) for path, info in probed if info.has_audio and not info.has_video]
    audio = max(audios, key=lambda item: item[0].stat().st_size)[0] if audios else None
    return video[0], audio


def _extract(
    url: str,
    *,
    playlist: bool,
    playlist_limit: int,
    cookies: Path | None,
    cookies_from_browser: str | None,
    flat_playlist: bool = False,
) -> dict:
    youtube_dl, download_error = _load_yt_dlp()
    options = _base_options(cookies, cookies_from_browser)
    options["noplaylist"] = not playlist
    if playlist:
        options["playlistend"] = playlist_limit
    if flat_playlist:
        options["extract_flat"] = "in_playlist"
    try:
        with youtube_dl(options) as client:
            info = client.extract_info(url, download=False)
    except download_error as exc:
        raise DownloadFailed(_clean_message(exc)) from exc
    if not isinstance(info, dict):
        raise DownloadFailed("Источник не вернул данные о видео.")
    return info


def _base_options(cookies: Path | None, cookies_from_browser: str | None) -> dict:
    options: dict = {
        "quiet": True,
        "no_warnings": True,
        "noprogress": True,
        "retries": 5,
        "fragment_retries": 5,
        "extractor_retries": 3,
        "file_access_retries": 3,
        "socket_timeout": 30,
        "concurrent_fragment_downloads": 4,
        "logger": _Logger(),
    }
    if cookies is not None:
        options["cookiefile"] = str(cookies)
    if cookies_from_browser is not None:
        options["cookiesfrombrowser"] = (cookies_from_browser,)
    return options


def _indexed_progress(
    callback: ProgressCallback | None,
    index: int,
    total: int,
) -> ProgressCallback | None:
    if callback is None or total <= 1:
        return callback

    def wrapped(event: Progress) -> None:
        prefix = f"[{index}/{total}] "
        title = f"{prefix}{event.title}" if event.title else prefix.strip()
        callback(
            Progress(
                phase=event.phase,
                percent=event.percent,
                speed=event.speed,
                eta=event.eta,
                title=title,
            )
        )

    return wrapped


def require_ytdlp() -> None:
    _load_yt_dlp()


def _load_yt_dlp():
    try:
        from yt_dlp import YoutubeDL
        from yt_dlp.utils import DownloadError
    except ImportError as exc:
        raise DependencyError(
            "Не установлен пакет yt-dlp. Выполните: pip install -e ."
        ) from exc
    return YoutubeDL, DownloadError


def _reject_both_cookie_sources(cookies: Path | None, browser: str | None) -> None:
    if cookies is not None and browser is not None:
        raise UserInputError("Укажите либо файл cookies, либо браузер, но не оба сразу.")


def _clean_message(exc: BaseException) -> str:
    text = " ".join(str(exc).split())
    if text.lower().startswith("error:"):
        text = text.split(":", 1)[1].strip()
    if len(text) > 500:
        text = text[:500].rstrip() + "…"
    return text or "Не удалось скачать видео."


def _clean_title(value: object) -> str | None:
    if not isinstance(value, str):
        return None
    text = " ".join(value.split()).strip()
    return text or None


def _http_url(value: object) -> str:
    if not isinstance(value, str):
        return ""
    if value.startswith("http://") or value.startswith("https://"):
        return value
    return ""


def _heights(info: dict) -> list[int]:
    found: set[int] = set()
    for fmt in info.get("formats") or []:
        if not isinstance(fmt, dict) or fmt.get("vcodec") in {None, "none"}:
            continue
        height = fmt.get("height")
        if isinstance(height, int) and height > 0:
            found.add(height)
    return sorted(found, reverse=True)


def _as_float(value: object) -> float | None:
    if isinstance(value, bool) or value is None:
        return None
    try:
        number = float(value)
    except (TypeError, ValueError):
        return None
    if number < 0:
        return None
    return number


class _Logger:
    def debug(self, message: str) -> None:
        if message.startswith("[debug] "):
            log.debug("%s", message)
        else:
            log.debug("%s", message)

    def info(self, message: str) -> None:
        log.info("%s", message)

    def warning(self, message: str) -> None:
        text = str(message)
        if "cookies" in text.lower():
            log.warning("yt-dlp сообщил предупреждение, связанное с cookies")
            return
        log.warning("%s", text)

    def error(self, message: str) -> None:
        log.error("%s", message)
