"""Командная строка videograbber."""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

from videograbber import __version__
from videograbber.errors import DependencyError, DownloadError, MediaError, UserError
from videograbber.grab import GrabResult, grab
from videograbber.media import ProgressEvent, format_bytes, format_duration

_QUALITIES = ("best", "2160", "1440", "1080", "720", "480", "360")


class Reporter:
    """Печатает прогресс. В терминале строка загрузки обновляется на месте."""

    def __init__(self, stream) -> None:
        self._stream = stream
        self._transient = False

    def __call__(self, event: ProgressEvent) -> None:
        if event.transient and self._stream.isatty():
            self._stream.write(f"\r{event.message}        ")
            self._stream.flush()
            self._transient = True
            return
        if self._transient:
            self._stream.write("\n")
            self._transient = False
        self._stream.write(f"{event.message}\n")
        self._stream.flush()

    def close(self) -> None:
        if self._transient:
            self._stream.write("\n")
            self._stream.flush()
            self._transient = False


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        prog="videograbber",
        description="Скачивает видео по ссылке и собирает полноценный MP4-файл.",
        epilog=(
            "Примеры:\n"
            "  videograbber https://example.com/watch?v=123\n"
            "  videograbber https://example.com/clip --quality 720 -o ~/Videos/clip.mp4\n"
            "\n"
            "Если исходник уже H.264 и AAC, дорожки копируются без потери качества.\n"
            "Иначе программа перекодирует их так, чтобы файл открывался в обычном плеере."
        ),
        formatter_class=argparse.RawDescriptionHelpFormatter,
    )
    parser.add_argument("url", help="Ссылка на страницу видео или на сам файл")
    parser.add_argument(
        "-o",
        "--output",
        type=Path,
        help="Куда сохранить: файл .mp4 или папка. По умолчанию — имя ролика в текущей папке",
    )
    parser.add_argument(
        "--quality",
        default="best",
        choices=_QUALITIES,
        help="Максимальная высота кадра. best — лучшее доступное качество (по умолчанию)",
    )
    parser.add_argument(
        "--force",
        action="store_true",
        help="Перезаписать файл, если он уже есть",
    )
    parser.add_argument(
        "--cookies",
        type=Path,
        help="Файл cookies Netscape, если сайт просит войти в аккаунт",
    )
    parser.add_argument(
        "--quiet",
        action="store_true",
        help="Вывести только путь к готовому файлу",
    )
    parser.add_argument("--version", action="version", version=f"videograbber {__version__}")
    return parser


def render_result(result: GrabResult) -> str:
    mode = "дорожки скопированы" if result.remuxed else "H.264 + AAC"
    if result.width and result.height:
        resolution = f"{result.width}×{result.height}"
    else:
        resolution = "размер кадра неизвестен"
    audio = result.audio_codec or "без звука"
    return (
        f"Готово: {result.path}\n"
        f"{format_bytes(result.size)}, {format_duration(result.duration)}, "
        f"{resolution}, видео {result.video_codec}, звук {audio}, {mode}"
    )


def run(argv: list[str] | None = None) -> int:
    parser = build_parser()
    args = parser.parse_args(argv)
    reporter = None if args.quiet else Reporter(sys.stderr)
    try:
        result = grab(
            args.url,
            output=args.output,
            quality=args.quality,
            force=args.force,
            cookies=args.cookies,
            on_progress=reporter,
        )
    except UserError as exc:
        print(str(exc), file=sys.stderr)
        return 2
    except DependencyError as exc:
        print(str(exc), file=sys.stderr)
        return 3
    except (DownloadError, MediaError) as exc:
        print(str(exc), file=sys.stderr)
        return 1
    except KeyboardInterrupt:
        print("\nОстановлено.", file=sys.stderr)
        return 130
    except OSError as exc:
        print(f"Не удалось записать файл: {exc}", file=sys.stderr)
        return 1
    finally:
        if reporter is not None:
            reporter.close()
    if args.quiet:
        print(result.path)
    else:
        print(render_result(result))
    return 0


def main(argv: list[str] | None = None) -> None:
    sys.exit(run(argv))
