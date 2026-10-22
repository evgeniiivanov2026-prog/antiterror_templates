"""Сценарий: ссылка → временный ролик → полноценный MP4."""

from __future__ import annotations

import os
import shutil
import subprocess
import tempfile
from dataclasses import dataclass
from pathlib import Path
from urllib.parse import urlsplit

from videograbber.download import download_video
from videograbber.errors import UserError
from videograbber.media import (
    ProgressEvent,
    assemble_mp4,
    moov_before_mdat,
    parse_quality,
    probe_media,
    require_tools,
    resolve_output_path,
    stream_copy_plan,
)


@dataclass(frozen=True)
class GrabResult:
    path: Path
    title: str
    size: int
    duration: float | None
    width: int | None
    height: int | None
    video_codec: str
    audio_codec: str | None
    remuxed: bool


def grab(
    url: str,
    output: Path | None = None,
    *,
    quality: str = "best",
    force: bool = False,
    cookies: Path | None = None,
    directory: Path | None = None,
    on_progress=None,
    downloader=None,
    runner=None,
) -> GrabResult:
    """Скачать url и записать один MP4, который открывается обычным плеером."""

    require_tools()
    cleaned = validate_url(url)
    if cookies is not None and not cookies.is_file():
        raise UserError(f"Файл cookies не найден: {cookies}")
    max_height = parse_quality(quality)
    fetch = downloader or download_video
    probe_runner = runner or default_runner
    download_kwargs = {} if cookies is None else {"cookiefile": cookies}
    with tempfile.TemporaryDirectory(prefix="videograbber-") as temporary:
        source = fetch(cleaned, Path(temporary), max_height, on_progress, **download_kwargs)
        inspected = probe_media(source.path, probe_runner)
        destination = resolve_output_path(output, source.title, directory or Path.cwd())
        if destination.exists() and not force:
            raise UserError(
                f"Файл уже существует: {destination}. "
                "Добавьте --force, чтобы перезаписать его."
            )
        copy_video, copy_audio = stream_copy_plan(inspected)
        remuxed = copy_video and copy_audio
        reusable = (
            remuxed
            and source.path.suffix.lower() == ".mp4"
            and moov_before_mdat(source.path)
        )
        if on_progress is not None:
            if reusable:
                action = "Файл уже MP4, сохраняю"
            elif remuxed:
                action = "Собираю MP4: копирую дорожки"
            else:
                action = "Собираю MP4: привожу к H.264 и AAC"
            on_progress(ProgressEvent(action))
        if reusable:
            destination.parent.mkdir(parents=True, exist_ok=True)
            shutil.copyfile(source.path, destination)
            packed = inspected
        else:
            partial = assemble_mp4(
                source.path,
                destination,
                inspected,
                on_progress,
                runner=runner,
            )
            try:
                packed = probe_media(partial, probe_runner)
                os.replace(partial, destination)
            except Exception:
                partial.unlink(missing_ok=True)
                raise
    audio = ", ".join(codec for codec in packed.audio_codecs if codec) or None
    return GrabResult(
        path=destination.resolve(),
        title=source.title,
        size=destination.stat().st_size,
        duration=packed.duration,
        width=packed.width,
        height=packed.height,
        video_codec=packed.video_codec,
        audio_codec=audio,
        remuxed=remuxed,
    )


def validate_url(url: str) -> str:
    cleaned = url.strip()
    parsed = urlsplit(cleaned)
    if parsed.scheme not in {"http", "https"} or not parsed.netloc:
        raise UserError("Нужна ссылка, которая начинается с http:// или https://.")
    return cleaned


def default_runner(command: list[str]) -> subprocess.CompletedProcess[str]:
    return subprocess.run(
        command,
        check=False,
        capture_output=True,
        text=True,
        errors="replace",
    )
