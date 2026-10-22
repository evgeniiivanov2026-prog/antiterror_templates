"""Проверка медиафайла и сборка совместимого MP4.

Итоговый файл — контейнер MP4, видео H.264 (yuv420p), звук AAC
не громче стерео и атом moov в начале. Так ролик открывается в браузере
и в обычных плеерах, не дожидаясь полной загрузки.
"""

from __future__ import annotations

import json
import logging
import os
import shutil
import subprocess
import threading
from dataclasses import dataclass
from pathlib import Path

from videograbber.errors import DependencyError, MediaError
from videograbber.naming import metadata_text

log = logging.getLogger("videograbber")

_MP4_FORMATS = {"m4a", "mov", "mp4"}
_STANDARD_BRANDS = {"avc1", "dash", "iso2", "iso4", "isom", "mp41", "mp42"}
_MIN_BYTES = 256


@dataclass(frozen=True)
class MediaInfo:
    format_name: str
    duration: float | None
    size: int
    has_video: bool
    has_audio: bool
    video_codec: str | None
    audio_codec: str | None
    width: int | None
    height: int | None
    pix_fmt: str | None
    audio_channels: int | None
    faststart: bool
    brand: str | None


@dataclass(frozen=True)
class EncodePlan:
    mode: str
    video: str
    audio: str


@dataclass(frozen=True)
class AssembleResult:
    path: Path
    info: MediaInfo
    mode: str


def require_ffmpeg() -> None:
    missing = [name for name in ("ffmpeg", "ffprobe") if shutil.which(name) is None]
    if missing:
        joined = ", ".join(missing)
        raise DependencyError(f"Нужна программа ffmpeg. Не найдено: {joined}.")


def read_mp4_brand(path: Path) -> str | None:
    try:
        with path.open("rb") as handle:
            header = handle.read(16)
    except OSError:
        return None
    if len(header) < 12 or header[4:8] != b"ftyp":
        return None
    try:
        brand = header[8:12].decode("ascii")
    except UnicodeDecodeError:
        return None
    if not brand.isascii() or not brand.isprintable():
        return None
    return brand


def mp4_has_faststart(path: Path) -> bool:
    """True, если атом moov стоит раньше mdat и воспроизведение можно начать сразу."""
    try:
        with path.open("rb") as handle:
            while True:
                header = handle.read(8)
                if len(header) < 8:
                    return False
                size = int.from_bytes(header[:4], "big")
                kind = header[4:8]
                if kind == b"moov":
                    return True
                if kind == b"mdat":
                    return False
                header_len = 8
                if size == 1:
                    extra = handle.read(8)
                    if len(extra) < 8:
                        return False
                    size = int.from_bytes(extra, "big")
                    header_len = 16
                if size < header_len:
                    return False
                handle.seek(size - header_len, os.SEEK_CUR)
    except OSError:
        return False


def probe_media(path: Path) -> MediaInfo:
    require_ffmpeg()
    if not path.is_file():
        raise MediaError(f"Файл не найден: {path.name}")
    command = [
        "ffprobe",
        "-v",
        "error",
        "-print_format",
        "json",
        "-show_format",
        "-show_streams",
        str(path),
    ]
    try:
        completed = subprocess.run(
            command,
            check=True,
            capture_output=True,
            text=True,
            encoding="utf-8",
            errors="replace",
            timeout=60,
        )
    except subprocess.TimeoutExpired as exc:
        raise MediaError(f"ffprobe слишком долго читает {path.name}") from exc
    except subprocess.CalledProcessError as exc:
        detail = (exc.stderr or "").strip().splitlines()
        tail = detail[-1] if detail else "файл не читается"
        raise MediaError(f"Не удалось прочитать медиафайл: {tail}") from exc
    try:
        payload = json.loads(completed.stdout or "{}")
    except json.JSONDecodeError as exc:
        raise MediaError(f"ffprobe вернул непонятный ответ для {path.name}") from exc

    videos: list[dict] = []
    audios: list[dict] = []
    for stream in payload.get("streams") or []:
        if not isinstance(stream, dict):
            continue
        disposition = stream.get("disposition") or {}
        if stream.get("codec_type") == "video" and not disposition.get("attached_pic"):
            videos.append(stream)
        elif stream.get("codec_type") == "audio":
            audios.append(stream)
    video = videos[0] if videos else None
    audio = audios[0] if audios else None
    container = payload.get("format") or {}
    format_name = str(container.get("format_name") or "")
    duration = _as_float(container.get("duration"))
    if duration is None and video is not None:
        duration = _as_float(video.get("duration"))
    size = _as_int(container.get("size")) or path.stat().st_size
    brand = read_mp4_brand(path)
    faststart = bool(brand) and brand in _STANDARD_BRANDS and mp4_has_faststart(path)
    return MediaInfo(
        format_name=format_name,
        duration=duration,
        size=size,
        has_video=video is not None,
        has_audio=audio is not None,
        video_codec=_codec(video),
        audio_codec=_codec(audio),
        width=_as_int(video.get("width")) if video else None,
        height=_as_int(video.get("height")) if video else None,
        pix_fmt=video.get("pix_fmt") if video else None,
        audio_channels=_as_int(audio.get("channels")) if audio else None,
        faststart=faststart,
        brand=brand,
    )


def plan_encode(
    video: MediaInfo,
    audio_track: MediaInfo | None = None,
    *,
    reencode: bool = False,
) -> EncodePlan:
    if not video.has_video:
        raise MediaError("В скачанном файле нет видео.")
    if audio_track is not None and not audio_track.has_audio:
        raise MediaError("Отдельная дорожка не содержит звука.")
    has_audio = audio_track is not None or video.has_audio
    if audio_track is not None:
        audio_codec = audio_track.audio_codec
        audio_channels = audio_track.audio_channels
    elif video.has_audio:
        audio_codec = video.audio_codec
        audio_channels = video.audio_channels
    else:
        audio_codec = None
        audio_channels = None
    if reencode:
        return EncodePlan("transcode", "encode", "encode" if has_audio else "none")

    video_action = "copy" if _video_compatible(video) else "encode"
    if not has_audio:
        audio_action = "none"
    elif _audio_compatible(audio_codec, audio_channels):
        audio_action = "copy"
    else:
        audio_action = "encode"
    lossless = video_action == "copy" and audio_action in {"copy", "none"}
    if (
        lossless
        and audio_track is None
        and _is_mp4(video.format_name)
        and video.faststart
        and video.brand in _STANDARD_BRANDS
    ):
        return EncodePlan("ready", video_action, audio_action)
    if lossless:
        return EncodePlan("remux", video_action, audio_action)
    return EncodePlan("transcode", video_action, audio_action)


def assemble_mp4(
    video_path: Path,
    dest: Path,
    audio_path: Path | None = None,
    *,
    reencode: bool = False,
    title: str | None = None,
    on_progress=None,
) -> AssembleResult:
    """Собирает итоговый MP4. Готовый совместимый файл копируется без перекодирования."""
    require_ffmpeg()
    if video_path.resolve() == dest.resolve():
        raise MediaError("Исходный и итоговый файл совпадают.")
    video_info = probe_media(video_path)
    audio_info = probe_media(audio_path) if audio_path is not None else None
    plan = plan_encode(video_info, audio_info, reencode=reencode)
    dest.parent.mkdir(parents=True, exist_ok=True)
    temporary = dest.with_name(f".{dest.stem}.{os.getpid()}.part.mp4")
    source_has_audio = plan.audio != "none"
    try:
        if plan.mode == "ready":
            shutil.copyfile(video_path, temporary)
        else:
            duration = video_info.duration
            if audio_info is not None and audio_info.duration:
                duration = max(duration or 0, audio_info.duration)
            _encode(
                video_path,
                temporary,
                audio_path,
                plan,
                title=title,
                duration=duration,
                on_progress=on_progress,
            )
        final_info = probe_media(temporary)
        _verify(final_info, expect_audio=source_has_audio)
        os.replace(temporary, dest)
    except BaseException:
        temporary.unlink(missing_ok=True)
        raise
    published = probe_media(dest)
    return AssembleResult(path=dest, info=published, mode=plan.mode)


def _encode(
    video_path: Path,
    dest: Path,
    audio_path: Path | None,
    plan: EncodePlan,
    *,
    title: str | None,
    duration: float | None,
    on_progress,
) -> None:
    command = [
        "ffmpeg",
        "-y",
        "-hide_banner",
        "-loglevel",
        "error",
        "-nostats",
        "-progress",
        "pipe:1",
        "-fflags",
        "+genpts",
        "-i",
        str(video_path),
    ]
    if audio_path is not None:
        command += ["-fflags", "+genpts", "-i", str(audio_path)]
    command += ["-map", "0:V:0"]
    if plan.audio != "none":
        command += ["-map", "1:a:0" if audio_path is not None else "0:a:0"]
    if plan.video == "copy" and plan.audio in {"copy", "none"}:
        command += ["-c", "copy"]
    else:
        if plan.video == "copy":
            command += ["-c:v", "copy"]
        else:
            command += [
                "-c:v",
                "libx264",
                "-preset",
                "veryfast",
                "-crf",
                "20",
                "-pix_fmt",
                "yuv420p",
                "-profile:v",
                "high",
            ]
        if plan.audio == "copy":
            command += ["-c:a", "copy"]
        elif plan.audio == "encode":
            command += ["-c:a", "aac", "-b:a", "192k", "-ac", "2", "-ar", "48000"]
    safe_title = metadata_text(title)
    if safe_title:
        command += ["-metadata", f"title={safe_title}"]
    command += [
        "-avoid_negative_ts",
        "make_zero",
        "-f",
        "mp4",
        "-movflags",
        "+faststart",
        str(dest),
    ]
    _run_ffmpeg(command, duration=duration, on_progress=on_progress)


def _run_ffmpeg(command: list[str], *, duration: float | None, on_progress) -> None:
    process = subprocess.Popen(
        command,
        stdin=subprocess.DEVNULL,
        stdout=subprocess.PIPE,
        stderr=subprocess.PIPE,
        text=True,
        encoding="utf-8",
        errors="replace",
    )
    stderr_parts: list[str] = []

    def read_stderr() -> None:
        if process.stderr is not None:
            stderr_parts.append(process.stderr.read())

    reader = threading.Thread(target=read_stderr, name="videograbber-ffmpeg-stderr", daemon=True)
    reader.start()
    code = 1
    try:
        if process.stdout is None:
            raise MediaError("ffmpeg не вернул ход сборки.")
        for line in process.stdout:
            _consume_progress(line, duration, on_progress)
        code = process.wait()
    except BaseException:
        if process.poll() is None:
            process.kill()
            try:
                process.wait(timeout=10)
            except subprocess.TimeoutExpired:
                pass
        raise
    finally:
        reader.join(timeout=5)
    if code != 0:
        detail = " ".join("".join(stderr_parts).split())
        if len(detail) > 300:
            detail = detail[-300:]
        message = "ffmpeg не смог собрать MP4"
        if detail:
            message = f"{message}: {detail}"
        raise MediaError(message)
    _emit_progress(on_progress, 100)


def _consume_progress(line: str, duration: float | None, on_progress) -> None:
    # У ffmpeg поле out_time_ms исторически хранит микросекунды, как и out_time_us.
    text = line.strip()
    if not text.startswith("out_time_us=") and not text.startswith("out_time_ms="):
        return
    if text.startswith("out_time_ms="):
        return
    raw = text.split("=", 1)[1]
    if not raw.lstrip("-").isdigit():
        return
    microseconds = int(raw)
    if not duration or duration <= 0:
        return
    percent = min(99.0, max(0.0, microseconds / 1_000_000 / duration * 100))
    _emit_progress(on_progress, percent)


def _emit_progress(on_progress, percent: float) -> None:
    if on_progress is None:
        return
    try:
        on_progress(percent)
    except Exception:
        log.exception("обработчик прогресса сборки завершился с ошибкой")


def _verify(info: MediaInfo, *, expect_audio: bool) -> None:
    if info.size < _MIN_BYTES:
        raise MediaError("Собранный MP4 получился пустым.")
    if not info.has_video or info.video_codec != "h264" or info.pix_fmt != "yuv420p":
        raise MediaError("В итоговом файле нет совместимого видео H.264.")
    if not info.width or not info.height:
        raise MediaError("У итогового видео нет размера кадра.")
    if not _is_mp4(info.format_name) or info.brand not in _STANDARD_BRANDS:
        raise MediaError("Итоговый файл не является обычным MP4.")
    if not info.faststart:
        raise MediaError("В MP4 не удалось перенести сведения о ролике в начало файла.")
    if expect_audio and info.audio_codec != "aac":
        raise MediaError("В итоговом файле нет звука AAC.")
    if not expect_audio and info.has_audio:
        raise MediaError("В итоговом файле появилась лишняя звуковая дорожка.")


def _video_compatible(info: MediaInfo) -> bool:
    return bool(
        info.has_video
        and info.video_codec == "h264"
        and info.pix_fmt == "yuv420p"
        and info.width
        and info.height
    )


def _audio_compatible(codec: str | None, channels: int | None) -> bool:
    if codec != "aac":
        return False
    if channels is not None and channels > 2:
        return False
    return True


def _is_mp4(format_name: str) -> bool:
    names = {part.strip() for part in format_name.split(",")}
    return bool(names & _MP4_FORMATS)


def _codec(stream: dict | None) -> str | None:
    if not stream:
        return None
    codec = stream.get("codec_name")
    if not isinstance(codec, str) or not codec or codec == "none":
        return None
    return codec


def _as_float(value: object) -> float | None:
    if value is None or value == "N/A":
        return None
    try:
        number = float(value)
    except (TypeError, ValueError):
        return None
    if number < 0:
        return None
    return number


def _as_int(value: object) -> int | None:
    if isinstance(value, bool) or value is None:
        return None
    try:
        number = int(value)
    except (TypeError, ValueError):
        return None
    return number
