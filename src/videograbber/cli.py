"""Команды videograbber: скачать, посмотреть сведения, открыть страницу."""

from __future__ import annotations

import argparse
import json
import logging
import sys
import webbrowser
from pathlib import Path

from videograbber import __version__
from videograbber.errors import UserInputError, VideoGrabberError
from videograbber.grab import Progress, grab, inspect, require_ytdlp, result_payload
from videograbber.jobs import JobManager
from videograbber.media import require_ffmpeg
from videograbber.present import format_bytes, format_duration, mode_label
from videograbber.web import serve


def main(argv: list[str] | None = None) -> int:
    args_list = list(sys.argv[1:] if argv is None else argv)
    parser = build_parser()
    try:
        args = parser.parse_args(_inject_grab(args_list))
    except SystemExit as exc:
        code = exc.code
        return int(code) if isinstance(code, int) else 0
    if not hasattr(args, "func"):
        parser.print_help()
        return 2
    try:
        return args.func(args)
    except KeyboardInterrupt:
        print("Остановлено.", file=sys.stderr)
        return 130
    except UserInputError as exc:
        print(str(exc), file=sys.stderr)
        return 2
    except VideoGrabberError as exc:
        print(str(exc), file=sys.stderr)
        return 1


def build_parser() -> argparse.ArgumentParser:
    parser = argparse.ArgumentParser(
        prog="videograbber",
        description="Скачивает видео по ссылке и собирает полноценный MP4.",
    )
    parser.add_argument("--version", action="version", version=f"videograbber {__version__}")
    commands = parser.add_subparsers(dest="command")

    download = commands.add_parser("grab", help="Скачать ссылку и собрать MP4")
    _add_url(download)
    _add_download_options(download)
    download.add_argument(
        "--playlist",
        action="store_true",
        help="Скачать каждый ролик плейлиста отдельным MP4",
    )
    download.add_argument(
        "--playlist-limit",
        type=int,
        default=50,
        help="Сколько роликов плейлиста брать (по умолчанию 50, максимум 500)",
    )
    download.add_argument(
        "--force",
        action="store_true",
        help="Перезаписать MP4 с тем же именем",
    )
    download.add_argument(
        "--reencode",
        action="store_true",
        help="Всегда перекодировать в H.264/AAC, даже если исходник уже совместим",
    )
    download.add_argument("--json", action="store_true", help="Напечатать результат в JSON")
    download.set_defaults(func=cmd_grab)

    info = commands.add_parser("info", help="Показать сведения о ролике, не скачивая его")
    _add_url(info)
    _add_cookies(info)
    info.add_argument("--playlist", action="store_true", help="Показать состав плейлиста")
    info.add_argument("-v", "--verbose", action="store_true")
    info.set_defaults(func=cmd_info)

    server = commands.add_parser("serve", help="Открыть локальную страницу для вставки ссылки")
    server.add_argument("--host", default="127.0.0.1", help="Адрес (по умолчанию только этот компьютер)")
    server.add_argument("--port", type=int, default=8765)
    server.add_argument("-o", "--output", default="downloads", help="Папка для готовых MP4")
    server.add_argument("--open", action="store_true", help="Открыть страницу в браузере")
    server.add_argument("-v", "--verbose", action="store_true")
    server.set_defaults(func=cmd_serve)
    return parser


def cmd_grab(args: argparse.Namespace) -> int:
    _configure_logging(args.verbose)
    rendered = {"value": False}
    progress = _progress_printer(rendered) if sys.stderr.isatty() else None
    results = grab(
        args.url,
        Path(args.output),
        quality=args.quality,
        force=args.force,
        reencode=args.reencode,
        playlist=args.playlist,
        playlist_limit=args.playlist_limit,
        cookies=args.cookies,
        cookies_from_browser=args.cookies_from_browser,
        on_progress=progress,
    )
    if rendered["value"]:
        sys.stderr.write("\n")
    if args.json:
        json.dump(
            [result_payload(result) for result in results],
            sys.stdout,
            ensure_ascii=False,
            indent=2,
        )
        sys.stdout.write("\n")
        return 0
    for result in results:
        print(result.path)
        detail = (
            f"{result.width}x{result.height}, "
            f"{format_duration(result.duration)}, "
            f"{format_bytes(result.bytes)}, "
            f"{mode_label(result.mode)}"
        )
        print(detail, file=sys.stderr)
    return 0


def cmd_info(args: argparse.Namespace) -> int:
    _configure_logging(args.verbose)
    print(
        inspect(
            args.url,
            playlist=args.playlist,
            cookies=args.cookies,
            cookies_from_browser=args.cookies_from_browser,
        )
    )
    return 0


def cmd_serve(args: argparse.Namespace) -> int:
    _configure_logging(args.verbose)
    if args.port < 1 or args.port > 65535:
        raise UserInputError("Порт должен быть от 1 до 65535.")
    require_ffmpeg()
    require_ytdlp()
    manager = JobManager(Path(args.output))
    try:
        httpd = serve(args.host, args.port, manager)
    except OSError as exc:
        reason = exc.strerror or "порт занят"
        print(f"Не удалось открыть {args.host}:{args.port}: {reason}", file=sys.stderr)
        manager.close()
        return 1
    host, port = httpd.server_address[:2]
    shown_host = "127.0.0.1" if host in {"0.0.0.0", "::"} else host
    url = f"http://{shown_host}:{port}/"
    print(f"videograbber слушает {url}")
    print(f"MP4 сохраняются в {manager.output_dir.resolve()}")
    print("Остановить: Ctrl+C")
    if args.host in {"0.0.0.0", "::"}:
        print(
            "Сервер открыт не только для этого компьютера. Не публикуйте его в интернет.",
            file=sys.stderr,
        )
    if args.open:
        webbrowser.open(url)
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nОстановка.")
    finally:
        httpd.shutdown()
        httpd.server_close()
        manager.close()
    return 0


def _add_url(parser: argparse.ArgumentParser) -> None:
    parser.add_argument("url", help="Ссылка http:// или https://")


def _add_cookies(parser: argparse.ArgumentParser) -> None:
    parser.add_argument("--cookies", type=Path, help="Файл cookies в формате Netscape")
    parser.add_argument(
        "--cookies-from-browser",
        metavar="BROWSER",
        help="Взять cookies из браузера: chrome, firefox, edge, chromium, brave, opera, vivaldi, safari",
    )


def _add_download_options(parser: argparse.ArgumentParser) -> None:
    parser.add_argument(
        "-o",
        "--output",
        default="downloads",
        help="Папка для MP4 (по умолчанию ./downloads)",
    )
    parser.add_argument(
        "-q",
        "--quality",
        default="1080",
        help="best, 2160, 1440, 1080, 720, 480 или 360",
    )
    _add_cookies(parser)
    parser.add_argument("-v", "--verbose", action="store_true")


def _inject_grab(argv: list[str]) -> list[str]:
    if not argv:
        return argv
    head = argv[0]
    known = {"grab", "info", "serve", "-h", "--help", "--version"}
    if head in known or head.startswith("-"):
        return argv
    return ["grab", *argv]


def _configure_logging(verbose: bool) -> None:
    logging.basicConfig(
        level=logging.DEBUG if verbose else logging.WARNING,
        format="%(levelname)s %(message)s",
        force=True,
    )


def _progress_printer(rendered: dict[str, bool]):
    last = {"text": ""}

    def render(event: Progress) -> None:
        label = {"download": "Скачивание", "finalize": "Сборка MP4"}.get(event.phase, "Подготовка")
        parts = [label]
        if event.title:
            parts.append(" ".join(event.title.split()))
        if event.percent is not None:
            parts.append(f"{event.percent:5.1f}%")
        if event.speed:
            parts.append(format_bytes(event.speed) + "/с")
        if event.eta is not None:
            parts.append("ещё " + format_duration(event.eta))
        text = "  ".join(parts)
        if text == last["text"]:
            return
        last["text"] = text
        rendered["value"] = True
        sys.stderr.write("\r" + text[:140].ljust(140))
        sys.stderr.flush()

    return render
