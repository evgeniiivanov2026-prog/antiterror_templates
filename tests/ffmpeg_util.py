"""Короткие ролики для проверки сборки MP4."""

from __future__ import annotations

import json
import subprocess
from functools import partial
from http.server import SimpleHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path


def run_ffmpeg(command: list[str]) -> None:
    completed = subprocess.run(command, capture_output=True, text=True)
    if completed.returncode != 0:
        raise AssertionError(completed.stderr or completed.stdout)


def make_mp4(
    path: Path,
    *,
    faststart: bool,
    audio: bool = True,
    duration: float = 2,
) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    command = [
        "ffmpeg",
        "-y",
        "-hide_banner",
        "-loglevel",
        "error",
        "-f",
        "lavfi",
        "-i",
        f"testsrc=size=320x240:rate=12:duration={duration}",
    ]
    if audio:
        command += [
            "-f",
            "lavfi",
            "-i",
            f"sine=frequency=440:sample_rate=44100:duration={duration}",
        ]
    command += ["-c:v", "libx264", "-pix_fmt", "yuv420p", "-preset", "ultrafast"]
    if audio:
        command += ["-shortest", "-c:a", "aac", "-b:a", "96k"]
    else:
        command += ["-an"]
    if faststart:
        command += ["-movflags", "+faststart"]
    command.append(str(path))
    run_ffmpeg(command)


def make_avi(path: Path, *, duration: float = 2) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    run_ffmpeg(
        [
            "ffmpeg",
            "-y",
            "-hide_banner",
            "-loglevel",
            "error",
            "-f",
            "lavfi",
            "-i",
            f"testsrc=size=320x240:rate=12:duration={duration}",
            "-f",
            "lavfi",
            "-i",
            f"sine=frequency=440:sample_rate=44100:duration={duration}",
            "-shortest",
            "-c:v",
            "mpeg4",
            "-q:v",
            "8",
            "-c:a",
            "pcm_s16le",
            str(path),
        ]
    )


def make_m4a(path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    run_ffmpeg(
        [
            "ffmpeg",
            "-y",
            "-hide_banner",
            "-loglevel",
            "error",
            "-f",
            "lavfi",
            "-i",
            "sine=frequency=440:sample_rate=44100:duration=2",
            "-c:a",
            "aac",
            "-b:a",
            "96k",
            str(path),
        ]
    )


def make_wav(path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    run_ffmpeg(
        [
            "ffmpeg",
            "-y",
            "-hide_banner",
            "-loglevel",
            "error",
            "-f",
            "lavfi",
            "-i",
            "sine=frequency=440:sample_rate=44100:duration=2",
            "-c:a",
            "pcm_s16le",
            str(path),
        ]
    )


def assert_decodes(path: Path) -> None:
    run_ffmpeg(["ffmpeg", "-v", "error", "-i", str(path), "-f", "null", "-"])


def read_title(path: Path) -> str:
    completed = subprocess.run(
        [
            "ffprobe",
            "-v",
            "error",
            "-print_format",
            "json",
            "-show_format",
            str(path),
        ],
        capture_output=True,
        text=True,
        check=True,
    )
    payload = json.loads(completed.stdout)
    tags = (payload.get("format") or {}).get("tags") or {}
    for key, value in tags.items():
        if key.lower() == "title":
            return str(value)
    return ""


class QuietHandler(SimpleHTTPRequestHandler):
    def log_message(self, format: str, *args) -> None:  # noqa: A003
        return


def serve_directory(directory: Path) -> ThreadingHTTPServer:
    handler = partial(QuietHandler, directory=str(directory))
    httpd = ThreadingHTTPServer(("127.0.0.1", 0), handler)
    httpd.daemon_threads = True
    import threading

    thread = threading.Thread(target=httpd.serve_forever, daemon=True)
    thread.start()
    return httpd
