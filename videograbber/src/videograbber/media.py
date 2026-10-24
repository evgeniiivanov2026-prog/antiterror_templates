"""Имена файлов, разбор дорожек и сборка MP4 через ffmpeg."""

from __future__ import annotations

import json
import re
import shutil
import subprocess
import threading
from dataclasses import dataclass
from pathlib import Path

from videograbber.errors import DependencyError, MediaError, UserError

TRANSCODE_PRESET = "veryfast"
TRANSCODE_CRF = "20"
AUDIO_BITRATE = "192k"
_FILENAME_MAX = 120
_WINDOWS_RESERVED = {
    "CON",
    "PRN",
    "AUX",
    "NUL",
    *(f"COM{index}" for index in range(1, 10)),
    *(f"LPT{index}" for index in range(1, 10)),
}
_INVALID_FILENAME = re.compile(r'[<>:"/\\|?*\x00-\x1f]')
_QUALITY_HEIGHTS = {
    "2160": 2160,
    "1440": 1440,
    "1080": 1080,
    "720": 720,
    "480": 480,
    "360": 360,
    "240": 240,
    "144": 144,
}
MP3_BITRATE = "160k"


@dataclass(frozen=True)
class MediaInfo:
    """Свойства контейнера, нужные для сборки и отчёта."""

    duration: float | None
    width: int | None
    height: int | None
    video_codec: str
    audio_codecs: tuple[str, ...]


@dataclass(frozen=True)
class ProgressEvent:
    """Одна строка прогресса. transient переписывает предыдущую строку в терминале."""

    message: str
    transient: bool = False
    percent: int | None = None


def require_tools() -> None:
    missing = [name for name in ("ffmpeg", "ffprobe") if shutil.which(name) is None]
    if missing:
        joined = " и ".join(missing)
        raise DependencyError(
            f"Не найдено: {joined}. Установите пакет ffmpeg и повторите команду."
        )


def parse_quality(value: str) -> int | None:
    """Вернуть ограничение высоты или None для лучшего доступного качества."""

    if value == "best":
        return None
    height = _QUALITY_HEIGHTS.get(value)
    if height is None:
        allowed = ", ".join(("best", *_QUALITY_HEIGHTS))
        raise UserError(f"Неизвестное качество {value!r}. Доступно: {allowed}.")
    return height


def format_selector(max_height: int | None) -> str:
    """Сначала искать H.264+AAC нужной высоты, затем любые дорожки.

    У вертикального ролика ограничение качества — это ширина, а не высота кадра.
    Поэтому после набора с ``height<=`` идёт такой же набор с ``width<=``.
    """

    if max_height is None:
        return (
            "bestvideo[vcodec^=avc1]+bestaudio[acodec^=mp4a]/"
            "bestvideo[vcodec^=avc1]+bestaudio[acodec=aac]/"
            "bestvideo[ext=mp4]+bestaudio[ext=m4a]/"
            "bestvideo+bestaudio/best/best"
        )
    # У Rutube «360p» иногда чуть выше 360 по высоте кадра. Небольшой допуск
    # оставляет эту дорожку в выбранном качестве и не заставляет перекодировать её.
    limit = max_height + 16
    height = f"[height<={limit}]"
    portrait = f"[width<={limit}]"
    compatible = (
        f"bestvideo{height}[vcodec^=avc1]+bestaudio[acodec^=mp4a]/"
        f"bestvideo{height}[vcodec^=avc1]+bestaudio[acodec=aac]/"
        f"bestvideo{height}[ext=mp4]+bestaudio[ext=m4a]"
    )
    portrait_compatible = (
        f"bestvideo{portrait}[vcodec^=avc1]+bestaudio[acodec^=mp4a]/"
        f"bestvideo{portrait}[vcodec^=avc1]+bestaudio[acodec=aac]/"
        f"bestvideo{portrait}[ext=mp4]+bestaudio[ext=m4a]"
    )
    fallback = (
        f"bestvideo{height}+bestaudio/bestvideo{portrait}+bestaudio/"
        f"best{height}/best{portrait}/best"
    )
    return f"{compatible}/{portrait_compatible}/{fallback}"


def audio_format_selector() -> str:
    """Лучшая звуковая дорожка: сначала AAC в m4a, затем любой звук."""

    return "bestaudio[ext=m4a]/bestaudio[acodec^=mp4a]/bestaudio/best"


def scale_filter(info: MediaInfo, max_edge: int | None) -> str | None:
    """Уменьшить кадр, если меньшая сторона больше выбранного качества."""

    if not max_edge or not info.width or not info.height:
        return None
    if min(info.width, info.height) <= max_edge + 16:
        return None
    if info.width <= info.height:
        return f"scale={max_edge}:-2"
    return f"scale=-2:{max_edge}"


def sanitize_filename(name: str) -> str:
    cleaned = _INVALID_FILENAME.sub(" ", name)
    cleaned = re.sub(r"\s+", " ", cleaned).strip(" .")
    if not cleaned:
        cleaned = "video"
    if len(cleaned) > _FILENAME_MAX:
        cleaned = cleaned[:_FILENAME_MAX].rstrip(" .")
    if cleaned.upper() in _WINDOWS_RESERVED:
        cleaned = f"_{cleaned}"
    return cleaned


def resolve_output_path(
    output: Path | None,
    title: str,
    cwd: Path,
    suffix: str = ".mp4",
) -> Path:
    filename = f"{sanitize_filename(title)}{suffix}"
    if output is None:
        return cwd / filename
    if output.exists() and output.is_dir():
        return output / filename
    if output.suffix.lower() == suffix:
        return output
    if output.suffix == "":
        return output / filename
    raise UserError(f"Укажите файл с расширением {suffix} или папку для сохранения.")


def format_bytes(size: float) -> str:
    value = float(size)
    for unit in ("Б", "КБ", "МБ", "ГБ", "ТБ"):
        if value < 1024 or unit == "ТБ":
            if unit == "Б":
                return f"{int(value)} {unit}"
            return f"{value:.1f} {unit}"
        value /= 1024
    return f"{int(size)} Б"


def format_duration(seconds: float | None) -> str:
    if seconds is None:
        return "длительность неизвестна"
    total = max(0, int(round(seconds)))
    hours, remainder = divmod(total, 3600)
    minutes, secs = divmod(remainder, 60)
    if hours:
        return f"{hours}:{minutes:02d}:{secs:02d}"
    return f"{minutes}:{secs:02d}"


def build_ffprobe_command(source: Path) -> list[str]:
    return [
        "ffprobe",
        "-v",
        "error",
        "-print_format",
        "json",
        "-show_format",
        "-show_streams",
        str(source),
    ]


def parse_ffprobe(payload: dict, *, require_video: bool = True) -> MediaInfo:
    videos: list[dict] = []
    audios: list[dict] = []
    for stream in payload.get("streams") or []:
        if stream.get("codec_type") == "video":
            if (stream.get("disposition") or {}).get("attached_pic") == 1:
                continue
            videos.append(stream)
        elif stream.get("codec_type") == "audio":
            audios.append(stream)
    if not videos:
        if require_video:
            raise MediaError("В скачанной записи нет видеопотока.")
        container = payload.get("format") or {}
        return MediaInfo(
            duration=_positive_float(container.get("duration")),
            width=None,
            height=None,
            video_codec="",
            audio_codecs=tuple(str(stream.get("codec_name") or "") for stream in audios),
        )
    video = videos[0]
    container = payload.get("format") or {}
    duration = _positive_float(container.get("duration"))
    if duration is None:
        duration = _positive_float(video.get("duration"))
    return MediaInfo(
        duration=duration,
        width=_positive_int(video.get("width")),
        height=_positive_int(video.get("height")),
        video_codec=str(video.get("codec_name") or ""),
        audio_codecs=tuple(str(stream.get("codec_name") or "") for stream in audios),
    )


def stream_copy_plan(info: MediaInfo) -> tuple[bool, bool]:
    """Копировать дорожку без перекодирования, только если она уже H.264 или AAC."""

    copy_video = info.video_codec == "h264"
    copy_audio = all(codec == "aac" for codec in info.audio_codecs)
    return copy_video, copy_audio


def build_ffmpeg_command(
    source: Path,
    dest: Path,
    info: MediaInfo,
    max_edge: int | None = None,
) -> list[str]:
    copy_video, copy_audio = stream_copy_plan(info)
    scale = scale_filter(info, max_edge)
    if scale:
        copy_video = False
    command = [
        "ffmpeg",
        "-hide_banner",
        "-nostdin",
        "-nostats",
        "-loglevel",
        "error",
        "-progress",
        "pipe:1",
        "-y",
        "-i",
        str(source),
        "-map",
        "0:V:0",
        "-map",
        "0:a?",
        "-map_metadata",
        "0",
        "-map_chapters",
        "0",
    ]
    if scale:
        command.extend(["-vf", scale])
    command.extend(["-c:v", "copy" if copy_video else "libx264"])
    if not copy_video:
        command.extend(
            [
                "-threads",
                "0",
                "-preset",
                TRANSCODE_PRESET,
                "-crf",
                TRANSCODE_CRF,
                "-pix_fmt",
                "yuv420p",
            ]
        )
    if info.audio_codecs:
        if copy_audio:
            command.extend(["-c:a", "copy"])
        else:
            command.extend(["-c:a", "aac", "-b:a", AUDIO_BITRATE])
    # Формат задаём явно: временное имя не должно влиять на выбор контейнера.
    command.extend(["-movflags", "+faststart", "-f", "mp4", str(dest)])
    return command


def probe_media(path: Path, runner, *, require_video: bool = True) -> MediaInfo:
    result = runner(build_ffprobe_command(path))
    if result.returncode != 0:
        detail = (result.stderr or result.stdout or "ffprobe завершился с ошибкой").strip()
        raise MediaError(f"Не удалось прочитать видео: {detail}")
    try:
        payload = json.loads(result.stdout or "{}")
    except json.JSONDecodeError as exc:
        raise MediaError("ffprobe вернул непонятный ответ.") from exc
    return parse_ffprobe(payload, require_video=require_video)


def assemble_mp4(
    source: Path,
    dest: Path,
    info: MediaInfo,
    on_progress,
    runner=None,
    max_edge: int | None = None,
) -> Path:
    """Собрать временный MP4 рядом с назначением. Вызывающий сам ставит файл на место."""

    dest.parent.mkdir(parents=True, exist_ok=True)
    partial = dest.with_suffix(".part.mp4")
    command = build_ffmpeg_command(source, partial, info, max_edge)
    try:
        if runner is None:
            _run_ffmpeg(command, info.duration, on_progress)
        else:
            result = runner(command)
            if result.returncode != 0:
                raise MediaError(_ffmpeg_error(result.stderr))
        if not partial.exists() or partial.stat().st_size == 0:
            raise MediaError("ffmpeg не создал MP4-файл.")
    except BaseException:
        partial.unlink(missing_ok=True)
        raise
    return partial


def build_mp3_command(source: Path, dest: Path) -> list[str]:
    return [
        "ffmpeg",
        "-hide_banner",
        "-nostdin",
        "-nostats",
        "-loglevel",
        "error",
        "-progress",
        "pipe:1",
        "-y",
        "-i",
        str(source),
        "-vn",
        "-map_metadata",
        "0",
        "-c:a",
        "libmp3lame",
        "-b:a",
        MP3_BITRATE,
        "-f",
        "mp3",
        str(dest),
    ]


def extract_mp3(
    source: Path,
    dest: Path,
    duration: float | None,
    on_progress,
    runner=None,
) -> Path:
    """Собрать временный MP3 рядом с назначением. Вызывающий сам ставит файл на место."""

    dest.parent.mkdir(parents=True, exist_ok=True)
    partial = dest.with_suffix(".part.mp3")
    command = build_mp3_command(source, partial)
    try:
        if runner is None:
            _run_ffmpeg(command, duration, on_progress, label="Сборка MP3")
        else:
            result = runner(command)
            if result.returncode != 0:
                raise MediaError(_ffmpeg_error(result.stderr, "MP3"))
        if not partial.exists() or partial.stat().st_size == 0:
            raise MediaError("ffmpeg не создал MP3-файл.")
    except BaseException:
        partial.unlink(missing_ok=True)
        raise
    return partial


def parse_ffmpeg_time(value: str) -> float | None:
    if not value or value == "N/A":
        return None
    parts = value.split(":")
    if len(parts) != 3:
        return None
    try:
        hours = int(parts[0])
        minutes = int(parts[1])
        seconds = float(parts[2])
    except ValueError:
        return None
    return hours * 3600 + minutes * 60 + seconds


def _run_ffmpeg(
    command: list[str],
    duration: float | None,
    on_progress,
    label: str = "Сборка MP4",
) -> None:
    process = subprocess.Popen(
        command,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True,
        errors="replace",
    )
    stderr_lines: list[str] = []

    def collect_stderr() -> None:
        if process.stderr is None:
            return
        stderr_lines.extend(process.stderr)

    thread = threading.Thread(target=collect_stderr)
    thread.start()
    last_percent = -1
    if process.stdout is not None:
        for raw_line in process.stdout:
            line = raw_line.strip()
            if on_progress is None or duration is None or not line.startswith("out_time="):
                continue
            current = parse_ffmpeg_time(line.split("=", 1)[1])
            if current is None or duration <= 0:
                continue
            percent = min(99, int(current * 100 / duration))
            if percent == last_percent:
                continue
            last_percent = percent
            on_progress(ProgressEvent(f"{label}: {percent}%", transient=True, percent=percent))
    code = process.wait()
    thread.join()
    if code != 0:
        kind = "MP3" if label.endswith("MP3") else "MP4"
        raise MediaError(_ffmpeg_error("".join(stderr_lines), kind))
    if on_progress is not None:
        on_progress(ProgressEvent(f"{label}: 100%", transient=True, percent=100))


def _ffmpeg_error(stderr: str | None, kind: str = "MP4") -> str:
    lines = [line.strip() for line in (stderr or "").splitlines() if line.strip()]
    if not lines:
        return f"ffmpeg не смог собрать {kind}."
    return f"ffmpeg не смог собрать {kind}: {' '.join(lines[-3:])}"


def moov_before_mdat(path: Path) -> bool:
    """Проверить, что заголовок MP4 стоит в начале и файл можно отдавать сразу."""

    try:
        handle = path.open("rb")
    except OSError:
        return False
    with handle:
        seen_moov = False
        while True:
            header = handle.read(8)
            if len(header) < 8:
                return False
            size = int.from_bytes(header[:4], "big")
            kind = header[4:8]
            header_len = 8
            if size == 1:
                extended = handle.read(8)
                if len(extended) < 8:
                    return False
                size = int.from_bytes(extended, "big")
                header_len = 16
            if size == 0:
                return seen_moov if kind == b"mdat" else False
            if size < header_len:
                return False
            if kind == b"moov":
                seen_moov = True
            elif kind == b"mdat":
                return seen_moov
            handle.seek(size - header_len, 1)


def _positive_float(value) -> float | None:
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
