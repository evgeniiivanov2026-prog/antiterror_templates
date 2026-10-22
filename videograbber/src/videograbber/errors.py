"""Ошибки, которые программа показывает пользователю."""


class VideograbberError(Exception):
    """Базовая ошибка videograbber."""


class UserError(VideograbberError):
    """Неверные аргументы или путь назначения."""


class DependencyError(VideograbberError):
    """На компьютере нет ffmpeg, ffprobe или yt-dlp."""


class DownloadError(VideograbberError):
    """Ссылку не удалось скачать."""


class MediaError(VideograbberError):
    """Файл скачан, но из него нельзя собрать MP4."""
