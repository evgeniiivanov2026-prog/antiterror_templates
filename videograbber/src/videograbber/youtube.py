"""Ссылка YouTube: карточка ролика и качества, которые у него реально есть."""

from __future__ import annotations

import re
from pathlib import Path
from urllib.parse import parse_qs, urlsplit
from urllib.request import HTTPRedirectHandler, Request, build_opener

from videograbber.download import _clean_message
from videograbber.errors import DependencyError, DownloadError, UserError
from videograbber.grab import validate_url
from videograbber.media import format_bytes, format_duration

_ID = re.compile(r"^[A-Za-z0-9_-]{11}$")
_FORMAT_ID = re.compile(r"^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$")
_HOSTS = {
    "youtube.com",
    "m.youtube.com",
    "music.youtube.com",
    "youtube-nocookie.com",
    "youtu.be",
}
_LADDER = (
    (1080, "1080", "1080p Full HD"),
    (720, "720", "720p HD"),
    (480, "480", "480p"),
    (360, "360", "Экономный (360p)"),
    (240, "240", "Низкое (240p)"),
    (144, "144", "Минимальное (144p)"),
)
_MAX_FILE_BYTES = 1_500_000_000
_MP3_BITRATE = 192_000
_SINGLE_PATHS = {"shorts", "embed", "live", "v", "e"}


def video_id_from_url(url: str) -> str | None:
    parsed = urlsplit(url.strip())
    if parsed.scheme not in {"http", "https"}:
        return None
    host = parsed.netloc.lower().split("@")[-1].split(":")[0]
    if host.startswith("www."):
        host = host[4:]
    if host not in _HOSTS:
        return None
    if host == "youtu.be":
        segment = _first_segment(parsed.path)
        return segment if _ID.fullmatch(segment) else None
    parts = [part for part in parsed.path.split("/") if part]
    if parts and parts[0] in _SINGLE_PATHS and len(parts) > 1:
        segment = parts[1].split("&", 1)[0]
        return segment if _ID.fullmatch(segment) else None
    if not parts or parts[0] == "watch":
        values = parse_qs(parsed.query).get("v") or []
        if values and _ID.fullmatch(values[0]):
            return values[0]
    return None


def canonical_youtube_url(url: str) -> str:
    cleaned = validate_url(url)
    video_id = video_id_from_url(cleaned)
    if video_id is None:
        raise UserError(
            "Нужна ссылка на ролик YouTube: youtube.com/watch, youtu.be или youtube.com/shorts."
        )
    return f"https://www.youtube.com/watch?v={video_id}"


def short_edge(fmt: dict) -> int | None:
    width = _positive_int(fmt.get("width"))
    height = _positive_int(fmt.get("height"))
    if width and height:
        return min(width, height)
    return height or width


def format_spec_for_edge(formats: list[dict], max_edge: int) -> str | None:
    """Строка формата yt-dlp: видео не выше max_edge по короткой стороне плюс звук."""

    videos = [
        fmt
        for fmt in formats
        if _is_video(fmt) and (short_edge(fmt) or 0) <= max_edge and _FORMAT_ID.fullmatch(str(fmt.get("format_id") or ""))
    ]
    if not videos:
        return None
    video = max(videos, key=_video_rank)
    if _has_audio_track(video):
        return str(video["format_id"])
    audios = [
        fmt
        for fmt in formats
        if _is_audio(fmt) and _FORMAT_ID.fullmatch(str(fmt.get("format_id") or ""))
    ]
    if not audios:
        return str(video["format_id"])
    audio = max(audios, key=_audio_rank)
    return f"{video['format_id']}+{audio['format_id']}"


def build_catalog(info: dict, video_id: str) -> dict:
    if info.get("is_live") or info.get("live_status") == "is_live":
        raise UserError("Прямой эфир ещё идёт. Сохранить можно запись, когда она появится.")
    if info.get("_type") == "playlist":
        entries = [entry for entry in info.get("entries") or [] if entry]
        if len(entries) != 1:
            raise UserError("Ссылка ведёт на плейлист. Откройте ролик и вставьте ссылку на само видео.")
        info = entries[0]
    formats = [fmt for fmt in (info.get("formats") or []) if isinstance(fmt, dict)]
    if not formats and info.get("url"):
        formats = [info]
    duration = _duration(info.get("duration"))
    options = _video_options(formats, duration)
    if _has_any_audio(formats, info):
        options.append(_mp3_option(duration))
    if not options:
        raise UserError("У этого ролика нет файла, который можно скачать.")
    author = info.get("uploader") or info.get("channel") or info.get("creator") or "YouTube"
    return {
        "id": video_id,
        "title": str(info.get("title") or "Видео"),
        "author": str(author),
        "duration": format_duration(duration) if duration else "",
        "thumbnail": f"/api/thumb/{video_id}",
        "options": options,
    }


def inspect_youtube(url: str, cookies: Path | None = None) -> dict:
    video_id = video_id_from_url(canonical_youtube_url(url))
    if video_id is None:
        raise UserError(
            "Нужна ссылка на ролик YouTube: youtube.com/watch, youtu.be или youtube.com/shorts."
        )
    if cookies is not None and not cookies.is_file():
        raise UserError(f"Файл cookies не найден: {cookies}")
    try:
        import yt_dlp
        from yt_dlp.utils import DownloadError as YtDlpDownloadError
    except ImportError as exc:
        raise DependencyError(
            "Не установлен yt-dlp. Выполните: pip install videograbber"
        ) from exc

    from videograbber.download import _SilentLogger

    options = {
        "quiet": True,
        "no_warnings": True,
        "noprogress": True,
        "noplaylist": True,
        "skip_download": True,
        "socket_timeout": 20,
        "retries": 2,
        "extractor_retries": 2,
        "logger": _SilentLogger(),
    }
    if cookies is not None:
        options["cookiefile"] = str(cookies)
    try:
        with yt_dlp.YoutubeDL(options) as ydl:
            info = ydl.extract_info(f"https://www.youtube.com/watch?v={video_id}", download=False)
    except YtDlpDownloadError as exc:
        raise DownloadError(_clean_message(str(exc))) from exc
    if not info:
        raise DownloadError("По ссылке не нашлось видео.")
    return build_catalog(info, video_id)


def fetch_thumbnail(video_id: str) -> bytes | None:
    if not _ID.fullmatch(video_id):
        return None
    url = f"https://i.ytimg.com/vi/{video_id}/hqdefault.jpg"
    request = Request(url, headers={"User-Agent": "videograbber"})
    opener = build_opener(_StayOnYtimg())
    try:
        with opener.open(request, timeout=8) as response:
            content_type = response.headers.get("Content-Type", "")
            if "image" not in content_type:
                return None
            data = response.read(2_000_000)
    except (OSError, ValueError):
        return None
    if not data:
        return None
    return data


class _StayOnYtimg(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        if urlsplit(newurl).netloc != "i.ytimg.com":
            return None
        return super().redirect_request(req, fp, code, msg, headers, newurl)


def _video_options(formats: list[dict], duration: float | None) -> list[dict]:
    found = []
    for edge, option_id, label in _LADDER:
        group = [fmt for fmt in formats if _is_video(fmt) and _bucket(short_edge(fmt)) == edge]
        if not group:
            continue
        video = max(group, key=_video_rank)
        audio = None if _has_audio_track(video) else _best_audio(formats)
        size = _estimate_bytes(video, audio, duration)
        found.append(
            {
                "id": option_id,
                "label": label,
                "kind": "mp4",
                "hint": format_bytes(size) if size else "",
                "bytes": size,
            }
        )
    visible = [
        item
        for item in found
        if item["bytes"] is None or item["bytes"] <= _MAX_FILE_BYTES
    ]
    if not visible and found:
        visible = [min(found, key=lambda item: item["bytes"] or 0)]
    return [{key: item[key] for key in ("id", "label", "kind", "hint")} for item in visible]


def _mp3_option(duration: float | None) -> dict:
    size = int(_MP3_BITRATE / 8 * duration) if duration else None
    return {
        "id": "mp3",
        "label": "Только аудио (MP3)",
        "kind": "mp3",
        "hint": format_bytes(size) if size else "",
    }


def _bucket(edge: int | None) -> int | None:
    if edge is None or edge > 1110:
        return None
    for step, _option_id, _label in _LADDER:
        if edge >= step - 20:
            return step
    return None


def _best_audio(formats: list[dict]) -> dict | None:
    audios = [fmt for fmt in formats if _is_audio(fmt)]
    if not audios:
        return None
    return max(audios, key=_audio_rank)


def _has_any_audio(formats: list[dict], info: dict) -> bool:
    if _has_audio_track(info):
        return True
    return any(_has_audio_track(fmt) for fmt in formats)


def _is_video(fmt: dict) -> bool:
    if _is_storyboard(fmt):
        return False
    vcodec = fmt.get("vcodec")
    return bool(vcodec) and vcodec != "none" and short_edge(fmt) is not None


def _is_audio(fmt: dict) -> bool:
    vcodec = fmt.get("vcodec")
    return _has_audio_track(fmt) and (not vcodec or vcodec == "none")


def _has_audio_track(fmt: dict) -> bool:
    acodec = fmt.get("acodec")
    return bool(acodec) and acodec != "none"


def _is_storyboard(fmt: dict) -> bool:
    ext = str(fmt.get("ext") or "")
    note = str(fmt.get("format_note") or "").lower()
    protocol = str(fmt.get("protocol") or "")
    return ext in {"mhtml", "jpg", "png"} or "storyboard" in note or "mhtml" in protocol


def _video_rank(fmt: dict) -> tuple:
    vcodec = str(fmt.get("vcodec") or "")
    avc = 1 if vcodec.startswith("avc1") or vcodec == "h264" else 0
    return (
        short_edge(fmt) or 0,
        avc,
        1 if _has_audio_track(fmt) else 0,
        1 if fmt.get("ext") == "mp4" else 0,
        _bitrate(fmt),
    )


def _audio_rank(fmt: dict) -> tuple:
    acodec = str(fmt.get("acodec") or "")
    aac = 1 if acodec.startswith("mp4a") or acodec == "aac" else 0
    return (aac, 1 if fmt.get("ext") == "m4a" else 0, _bitrate(fmt))


def _estimate_bytes(video: dict, audio: dict | None, duration: float | None) -> int | None:
    video_size = _filesize(video)
    separate_audio = audio if audio and not _has_audio_track(video) else None
    audio_size = _filesize(separate_audio) if separate_audio else None
    if video_size and (separate_audio is None or audio_size):
        return video_size + (audio_size or 0)
    if duration:
        video_rate = _bitrate(video)
        audio_rate = 0.0 if _has_audio_track(video) else (_bitrate(separate_audio) or 128.0)
        if video_rate:
            return int((video_rate + audio_rate) * 1000 / 8 * duration)
    return video_size


def _filesize(fmt: dict | None) -> int | None:
    if not fmt:
        return None
    for key in ("filesize", "filesize_approx"):
        value = fmt.get(key)
        if isinstance(value, (int, float)) and value > 0:
            return int(value)
    return None


def _bitrate(fmt: dict | None) -> float:
    if not fmt:
        return 0.0
    for key in ("tbr", "vbr", "abr"):
        value = fmt.get(key)
        if isinstance(value, (int, float)) and value > 0:
            return float(value)
    return 0.0


def _duration(value) -> float | None:
    try:
        number = float(value)
    except (TypeError, ValueError):
        return None
    if number <= 0:
        return None
    return number


def _positive_int(value) -> int | None:
    try:
        number = int(value)
    except (TypeError, ValueError):
        return None
    if number <= 0:
        return None
    return number


def _first_segment(path: str) -> str:
    parts = [part for part in path.split("/") if part]
    if not parts:
        return ""
    return parts[0].split("&", 1)[0]
