"""Ссылка на ролик YouTube или Rutube и список качеств, которые у него реально есть."""

from __future__ import annotations

import base64
import re
from urllib.parse import parse_qsl, unquote, urlsplit
from urllib.request import HTTPRedirectHandler, Request, build_opener, urlopen

from videograbber.errors import DependencyError, DownloadError, UserError
from videograbber.grab import validate_url
from videograbber.media import format_bytes, format_duration

_ID = re.compile(r"^[A-Za-z0-9_-]{11}$")
_URL = re.compile(r"https?://[^\s<>]+", re.IGNORECASE)
_NOTE = re.compile(r"(\d{3,4})p", re.IGNORECASE)
_HOSTS = {
    "youtube.com",
    "www.youtube.com",
    "m.youtube.com",
    "music.youtube.com",
    "youtu.be",
    "www.youtu.be",
    "youtube-nocookie.com",
    "www.youtube-nocookie.com",
}
_RUTUBE_HOSTS = {"rutube.ru", "www.rutube.ru", "m.rutube.ru"}
_RUTUBE_ID = re.compile(r"^[0-9a-f]{32}$")
_RUTUBE_NUMERIC = re.compile(r"^[0-9]{1,12}$")
_RUTUBE_TOKEN = re.compile(r"^[A-Za-z0-9_-]{1,128}$")
_THUMB_HOSTS = {
    "i.ytimg.com",
    "pic.rutubelist.ru",
    "pic.rutube.ru",
    "pic.rtbcdn.ru",
    "static.rutube.ru",
}
_LADDER = (1080, 720, 480, 360, 240, 144)
_LABELS = {
    1080: "1080p Full HD",
    720: "720p HD",
    480: "480p",
    360: "Экономный (360p)",
    240: "Низкое (240p)",
    144: "Минимальное (144p)",
}
_AUDIO_LABEL = "Только аудио (MP3)"
# Час звука в 160 кбит/с — около 70 МБ, как у обычного MP3.
_MP3_KBPS = 160
MAX_FILE_BYTES = int(1.5 * 1024 * 1024 * 1024)


def normalize_youtube_url(url: str) -> str:
    """Привести ссылку на ролик к виду https://www.youtube.com/watch?v=ID."""

    cleaned = validate_url(_extract_url(url))
    parsed = urlsplit(cleaned)
    host = (parsed.hostname or "").lower()
    if host not in _HOSTS:
        raise UserError("Нужна ссылка на YouTube: обычное видео, Shorts или youtu.be.")
    video_id = _video_id(host, parsed)
    if video_id is None:
        raise UserError(
            "В ссылке не нашлось ролика. Плейлист и канал целиком не скачиваются — "
            "вставьте ссылку на само видео."
        )
    return f"https://www.youtube.com/watch?v={video_id}"


def normalize_watch_url(url: str) -> str:
    """Привести ссылку YouTube или Rutube к одному ролику."""

    cleaned = validate_url(_extract_url(url))
    host = (urlsplit(cleaned).hostname or "").lower()
    if host in _HOSTS:
        return normalize_youtube_url(cleaned)
    if host in _RUTUBE_HOSTS:
        return _normalize_rutube(urlsplit(cleaned))
    raise UserError("Нужна ссылка на YouTube или Rutube: ролик, Shorts или youtu.be.")


def preview_from_info(info: dict, thumbnail: str | None = None, url: str | None = None) -> dict:
    """Собрать карточку ролика из ответа yt-dlp без обращения к сети."""

    if info.get("is_live"):
        raise DownloadError("Прямой эфир ещё идёт, у него нет конечного файла.")
    options = build_options(info)
    if not options:
        raise DownloadError("У этого ролика нет файла, который можно скачать.")
    title = str(info.get("title") or "").strip() or "Видео"
    author = str(info.get("uploader") or info.get("channel") or "").strip()
    duration = _duration(info.get("duration"))
    payload = {
        "id": str(info.get("id") or ""),
        "title": title,
        "author": author,
        "duration": duration,
        "options": options,
    }
    if url:
        payload["url"] = url
    if duration is not None:
        payload["durationLabel"] = format_duration(duration)
    if thumbnail:
        payload["thumbnail"] = thumbnail
    return payload


def build_options(info: dict) -> list[dict]:
    """Кнопки качеств: только то, что есть у ролика, не выше 1080p, плюс MP3."""

    duration = _duration(info.get("duration"))
    formats = [item for item in info.get("formats") or [] if isinstance(item, dict)]
    buckets: dict[int, list[dict]] = {}
    higher = False
    for fmt in formats:
        if not _is_video(fmt):
            continue
        quality = _quality_of(fmt)
        if quality is None:
            if _above_1080(fmt):
                higher = True
            continue
        buckets.setdefault(quality, []).append(fmt)
    if higher and 1080 not in buckets:
        buckets[1080] = []
    audios = [fmt for fmt in formats if _is_audio_only(fmt)]
    present = [step for step in _LADDER if step in buckets]
    smallest = present[-1] if present else None
    options: list[dict] = []
    for step in present:
        estimate = _estimate(buckets[step], audios, duration)
        if estimate is not None and estimate > MAX_FILE_BYTES and step != smallest:
            continue
        option: dict = {"id": str(step), "label": _LABELS[step], "kind": "video"}
        _attach_size(option, estimate)
        options.append(option)
    if any(_is_audio(fmt) for fmt in formats):
        option = {"id": "audio", "label": _AUDIO_LABEL, "kind": "audio"}
        if duration is not None:
            _attach_size(option, int(_MP3_KBPS * 1000 / 8 * duration))
        options.append(option)
    return options


def lookup_watch(url: str, cookiefile=None) -> dict:
    """Карточка ролика YouTube или Rutube: название, автор, превью и кнопки."""

    normalized = normalize_watch_url(url)
    info = dict(extract_youtube_info(normalized, cookiefile))
    host = (urlsplit(normalized).hostname or "").lower()
    if host in _RUTUBE_HOSTS:
        thumbnail = fetch_remote_thumbnail(str(info.get("thumbnail") or ""))
    else:
        video_id = str(info.get("id") or normalized.rsplit("=", 1)[-1])
        info["id"] = video_id
        thumbnail = fetch_thumbnail(video_id)
    return preview_from_info(info, thumbnail, url=normalized)


def lookup_youtube(url: str, cookiefile=None) -> dict:
    """Название, автор, превью и кнопки скачивания для одной ссылки."""

    normalized = normalize_youtube_url(url)
    info = dict(extract_youtube_info(normalized, cookiefile))
    video_id = str(info.get("id") or normalized.rsplit("=", 1)[-1])
    info["id"] = video_id
    return preview_from_info(info, fetch_thumbnail(video_id), url=normalized)


def extract_youtube_info(url: str, cookiefile=None) -> dict:
    try:
        import yt_dlp
        from yt_dlp.utils import DownloadError as YtDlpDownloadError
    except ImportError as exc:
        raise DependencyError("Не установлен yt-dlp. Выполните: pip install videograbber") from exc

    from videograbber.download import _SilentLogger, _clean_message, _single_video

    options = {
        "quiet": True,
        "no_warnings": True,
        "noplaylist": True,
        "skip_download": True,
        "socket_timeout": 20,
        "retries": 3,
        "extractor_retries": 2,
        "logger": _SilentLogger(),
    }
    if cookiefile is not None:
        options["cookiefile"] = str(cookiefile)
    try:
        with yt_dlp.YoutubeDL(options) as ydl:
            info = ydl.extract_info(url, download=False)
    except YtDlpDownloadError as exc:
        raise DownloadError(_clean_message(str(exc))) from exc
    if not info:
        raise DownloadError("По ссылке не нашлось видео.")
    return _single_video(info)


class _ThumbRedirect(HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        host = (urlsplit(newurl).hostname or "").lower()
        if host not in _THUMB_HOSTS:
            return None
        return super().redirect_request(req, fp, code, msg, headers, newurl)


def fetch_remote_thumbnail(url: str) -> str | None:
    """Скачать превью только с известных хостов картинок YouTube и Rutube."""

    parsed = urlsplit(url.strip())
    host = (parsed.hostname or "").lower()
    if parsed.scheme not in {"http", "https"} or host not in _THUMB_HOSTS:
        return None
    request = Request(url, headers={"User-Agent": "videograbber"})
    try:
        with build_opener(_ThumbRedirect).open(request, timeout=8) as response:
            payload = response.read(160_001)
    except (OSError, ValueError):
        return None
    if len(payload) > 160_000:
        return None
    return _data_url(payload)


def fetch_thumbnail(video_id: str) -> str | None:
    if not _ID.fullmatch(video_id):
        return None
    request = Request(
        f"https://i.ytimg.com/vi/{video_id}/hqdefault.jpg",
        headers={"User-Agent": "videograbber"},
    )
    try:
        with urlopen(request, timeout=8) as response:
            payload = response.read(160_001)
    except (OSError, ValueError):
        return None
    if len(payload) > 160_000:
        return None
    return _data_url(payload)


def _extract_url(value: str) -> str:
    text = value.strip()
    match = _URL.search(text)
    if match is None:
        return text
    return match.group(0).rstrip(").,]>\"'")


def _normalize_rutube(parsed) -> str:
    parts = [part for part in unquote(parsed.path).strip("/").split("/") if part]
    token = _access_token(parsed.query)
    kind = "public"
    raw = None
    if len(parts) >= 3 and parts[0] == "video" and parts[1] == "private":
        raw = parts[2]
        kind = "private"
    elif len(parts) >= 3 and parts[0] == "video" and parts[1] == "embed":
        raw = parts[2]
        kind = "numeric"
    elif len(parts) >= 2 and parts[0] == "video" and parts[1] not in {"person", "embed", "private"}:
        raw = parts[1]
    elif len(parts) >= 2 and parts[0] == "shorts":
        raw = parts[1]
    elif len(parts) >= 2 and parts[0] == "embed":
        raw = parts[1]
    elif len(parts) >= 3 and parts[0] == "play" and parts[1] == "embed":
        raw = parts[2]
    elif len(parts) >= 4 and parts[0] == "live" and parts[1] == "video" and parts[2] == "private":
        raw = parts[3]
        kind = "private"
    elif len(parts) >= 3 and parts[0] == "live" and parts[1] == "video" and parts[2] != "private":
        raw = parts[2]
    if raw and _RUTUBE_ID.fullmatch(raw.lower()):
        video_id = raw.lower()
        path = "video/private" if kind == "private" else "video"
        normalized = f"https://rutube.ru/{path}/{video_id}/"
        if token:
            normalized += f"?p={token}"
        return normalized
    numeric = kind == "numeric" or (parts[:1] == ["embed"]) or (parts[:2] == ["play", "embed"])
    if raw and numeric and _RUTUBE_NUMERIC.fullmatch(raw):
        normalized = f"https://rutube.ru/play/embed/{raw}"
        if token:
            normalized += f"?p={token}"
        return normalized
    raise UserError(
        "В ссылке не нашлось ролика. Плейлист и канал целиком не скачиваются — "
        "вставьте ссылку на само видео."
    )


def _access_token(query: str) -> str:
    value = _query_value(query, "p")
    if value and _RUTUBE_TOKEN.fullmatch(value):
        return value
    return ""


def _video_id(host: str, parsed) -> str | None:
    parts = [part for part in unquote(parsed.path).strip("/").split("/") if part]
    if host in {"youtu.be", "www.youtu.be"}:
        return _valid_id(parts[0]) if parts else None
    if not parts:
        return None
    if parts[0] in {"shorts", "embed", "live", "v", "e", "watch"} and len(parts) >= 2:
        found = _valid_id(parts[1])
        if found:
            return found
    if parts[0] == "watch":
        return _valid_id(_query_value(parsed.query, "v"))
    return None


def _query_value(query: str, name: str) -> str:
    for key, value in parse_qsl(query, keep_blank_values=False):
        if key == name and value:
            return value
    return ""


def _valid_id(value: str) -> str | None:
    if _ID.fullmatch(value or ""):
        return value
    return None


def _is_video(fmt: dict) -> bool:
    if fmt.get("vcodec") in (None, "none"):
        return False
    note = str(fmt.get("format_note") or "").lower()
    if "storyboard" in note:
        return False
    if str(fmt.get("ext") or "").lower() in {"mhtml", "jpg", "jpeg", "png", "webp"}:
        return False
    return True


def _is_audio(fmt: dict) -> bool:
    return fmt.get("acodec") not in (None, "none")


def _is_audio_only(fmt: dict) -> bool:
    return _is_audio(fmt) and fmt.get("vcodec") in (None, "none")


def _quality_of(fmt: dict) -> int | None:
    note = str(fmt.get("format_note") or "")
    if "storyboard" in note.lower():
        return None
    match = _NOTE.search(note)
    if match:
        value = int(match.group(1))
        if value in _LABELS:
            return value
        if value > 1080:
            return None
    width = _positive(fmt.get("width"))
    height = _positive(fmt.get("height"))
    if width is None or height is None:
        return None
    small = min(width, height)
    if small > 1080 + 16:
        return None
    for step in _LADDER:
        if small >= step - 16:
            return step
    return None


def _above_1080(fmt: dict) -> bool:
    match = _NOTE.search(str(fmt.get("format_note") or ""))
    if match and int(match.group(1)) > 1080:
        return True
    width = _positive(fmt.get("width"))
    height = _positive(fmt.get("height"))
    if width is None or height is None:
        return False
    return min(width, height) > 1080 + 16


def _estimate(group: list[dict], audios: list[dict], duration: float | None) -> int | None:
    if not group:
        return None
    progressive = [fmt for fmt in group if _is_audio(fmt)]
    pool = progressive or group

    def rate(fmt: dict) -> float:
        try:
            return float(fmt.get("tbr") or 0)
        except (TypeError, ValueError):
            return 0

    size = _media_bytes(max(pool, key=rate), duration)
    if size is None or progressive or not audios:
        return size
    audio = max(audios, key=lambda fmt: _rate(fmt.get("abr") or fmt.get("tbr")))
    audio_size = _media_bytes(audio, duration)
    if audio_size is None:
        return size
    return size + audio_size


def _media_bytes(fmt: dict, duration: float | None) -> int | None:
    for key in ("filesize", "filesize_approx"):
        number = _positive(fmt.get(key))
        if number is not None:
            return number
    kbps = _rate(fmt.get("tbr") or fmt.get("vbr") or fmt.get("abr"))
    if kbps <= 0 or not duration:
        return None
    return int(kbps * 1000 / 8 * duration)


def _attach_size(option: dict, size: int | None) -> None:
    if size is None or size <= 0:
        return
    option["size"] = size
    option["sizeLabel"] = format_bytes(size)


def _duration(value) -> float | None:
    try:
        number = float(value)
    except (TypeError, ValueError):
        return None
    if number <= 0:
        return None
    return number


def _positive(value) -> int | None:
    try:
        number = int(float(value))
    except (TypeError, ValueError):
        return None
    if number <= 0:
        return None
    return number


def _rate(value) -> float:
    try:
        number = float(value)
    except (TypeError, ValueError):
        return 0
    if number <= 0:
        return 0
    return number


def _data_url(payload: bytes) -> str | None:
    if payload.startswith(b"\xff\xd8\xff"):
        mime = "image/jpeg"
    elif payload.startswith(b"\x89PNG\r\n\x1a\n"):
        mime = "image/png"
    elif len(payload) >= 12 and payload.startswith(b"RIFF") and payload[8:12] == b"WEBP":
        mime = "image/webp"
    else:
        return None
    encoded = base64.b64encode(payload).decode("ascii")
    return f"data:{mime};base64,{encoded}"
