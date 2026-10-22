import pytest

from videograbber.cli import Reporter, build_parser, render_result, run
from videograbber.errors import DependencyError, DownloadError
from videograbber.grab import GrabResult
from videograbber.media import ProgressEvent


def _result(path) -> GrabResult:
    return GrabResult(
        path=path,
        title="Ролик",
        size=2048,
        duration=65,
        width=1280,
        height=720,
        video_codec="h264",
        audio_codec="aac",
        remuxed=True,
    )


def test_help_mentions_mp4(capsys):
    with pytest.raises(SystemExit) as caught:
        run(["--help"])
    assert caught.value.code == 0
    assert "MP4" in capsys.readouterr().out


def test_missing_cookie_file_is_a_usage_error(tmp_path, capsys):
    missing = tmp_path / "cookies.txt"
    assert run(["https://videos.example/watch", "--cookies", str(missing)]) == 2
    assert "cookies" in capsys.readouterr().err


def test_rejects_a_value_that_is_not_a_link(capsys):
    assert run(["не ссылка"]) == 2
    assert "http://" in capsys.readouterr().err


def test_quiet_prints_only_the_path(monkeypatch, tmp_path, capsys):
    destination = tmp_path / "clip.mp4"

    def fake_grab(url, output=None, **kwargs):
        assert url == "https://videos.example/watch"
        assert kwargs["quality"] == "720"
        assert kwargs["force"] is True
        return _result(destination)

    monkeypatch.setattr("videograbber.cli.grab", fake_grab)
    assert run(["https://videos.example/watch", "--quiet", "--quality", "720", "--force"]) == 0
    captured = capsys.readouterr()
    assert captured.out.strip() == str(destination)
    assert captured.err == ""


def test_prints_human_summary(monkeypatch, tmp_path, capsys):
    destination = tmp_path / "clip.mp4"
    monkeypatch.setattr("videograbber.cli.grab", lambda *args, **kwargs: _result(destination))
    assert run(["https://videos.example/watch", "-o", str(destination)]) == 0
    output = capsys.readouterr().out
    assert f"Готово: {destination}" in output
    assert "1280×720" in output
    assert "дорожки скопированы" in output


def test_download_error_is_a_failure(monkeypatch, capsys):
    def fake_grab(*args, **kwargs):
        raise DownloadError("Видео недоступно")

    monkeypatch.setattr("videograbber.cli.grab", fake_grab)
    assert run(["https://videos.example/missing"]) == 1
    assert "Видео недоступно" in capsys.readouterr().err


def test_missing_ffmpeg_has_its_own_exit_code(monkeypatch, capsys):
    def fake_grab(*args, **kwargs):
        raise DependencyError("Не найдено: ffmpeg")

    monkeypatch.setattr("videograbber.cli.grab", fake_grab)
    assert run(["https://videos.example/watch"]) == 3
    assert "ffmpeg" in capsys.readouterr().err


def test_render_result_describes_transcode(tmp_path):
    result = _result(tmp_path / "a.mp4")
    text = render_result(
        GrabResult(
            path=result.path,
            title=result.title,
            size=result.size,
            duration=None,
            width=None,
            height=None,
            video_codec="h264",
            audio_codec=None,
            remuxed=False,
        )
    )
    assert "H.264 + AAC" in text
    assert "без звука" in text
    assert "длительность неизвестна" in text


def test_reporter_breaks_the_line_after_transient_status():
    class Stream:
        def __init__(self):
            self.tty = True
            self.parts: list[str] = []

        def isatty(self):
            return self.tty

        def write(self, text):
            self.parts.append(text)

        def flush(self):
            return None

    stream = Stream()
    reporter = Reporter(stream)
    reporter(ProgressEvent("Скачивание, видео: 10%", transient=True))
    reporter(ProgressEvent("Видео загружено"))
    assert stream.parts[0].startswith("\r")
    assert stream.parts[1] == "\n"
    assert stream.parts[2] == "Видео загружено\n"


def test_parser_accepts_output_flag():
    args = build_parser().parse_args(["https://example.com/v", "-o", "film.mp4"])
    assert args.output.name == "film.mp4"
    assert args.quality == "best"
