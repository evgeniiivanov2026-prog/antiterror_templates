import json
import subprocess
import sys
from pathlib import Path

from tests.ffmpeg_util import make_mp4, serve_directory


def test_cli_help_and_version() -> None:
    help_run = subprocess.run(
        [sys.executable, "-m", "videograbber", "--help"],
        capture_output=True,
        text=True,
        check=False,
    )
    assert help_run.returncode == 0
    assert "serve" in help_run.stdout
    assert "grab" in help_run.stdout
    version = subprocess.run(
        [sys.executable, "-m", "videograbber", "--version"],
        capture_output=True,
        text=True,
        check=False,
    )
    assert version.returncode == 0
    assert "videograbber" in version.stdout


def test_cli_rejects_bad_url() -> None:
    result = subprocess.run(
        [sys.executable, "-m", "videograbber", "notaurl"],
        capture_output=True,
        text=True,
        check=False,
    )
    assert result.returncode == 2
    assert "http" in result.stderr


def test_cli_grab_writes_mp4_and_json(tmp_path: Path) -> None:
    library = tmp_path / "library"
    make_mp4(library / "clip.mp4", faststart=False)
    httpd = serve_directory(library)
    output = tmp_path / "out"
    try:
        url = f"http://127.0.0.1:{httpd.server_address[1]}/clip.mp4"
        result = subprocess.run(
            [
                sys.executable,
                "-m",
                "videograbber",
                "grab",
                url,
                "-o",
                str(output),
                "-q",
                "best",
                "--json",
            ],
            capture_output=True,
            text=True,
            check=False,
        )
    finally:
        httpd.shutdown()
        httpd.server_close()
    assert result.returncode == 0, result.stderr
    payload = json.loads(result.stdout)
    assert payload[0]["video_codec"] == "h264"
    assert payload[0]["audio_codec"] == "aac"
    assert Path(payload[0]["path"]).is_file()
    saved = list(output.glob("*.mp4"))
    assert len(saved) == 1
    assert saved[0].read_bytes()[4:8] == b"ftyp"


def test_cli_info(tmp_path: Path) -> None:
    library = tmp_path / "library"
    make_mp4(library / "clip.mp4", faststart=True)
    httpd = serve_directory(library)
    try:
        url = f"http://127.0.0.1:{httpd.server_address[1]}/clip.mp4"
        result = subprocess.run(
            [sys.executable, "-m", "videograbber", "info", url],
            capture_output=True,
            text=True,
            check=False,
        )
    finally:
        httpd.shutdown()
        httpd.server_close()
    assert result.returncode == 0, result.stderr
    assert "Название" in result.stdout
    assert "Длительность" in result.stdout
