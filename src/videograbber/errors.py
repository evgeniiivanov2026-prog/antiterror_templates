"""Ошибки, текст которых можно показать человеку."""


class VideoGrabberError(Exception):
    """Общая ошибка videograbber."""


class UserInputError(VideoGrabberError):
    """Неверная ссылка, качество или параметр команды."""


class TooManyJobs(UserInputError):
    """Очередь локальных загрузок уже заполнена."""


class DependencyError(VideoGrabberError):
    """На компьютере нет ffmpeg или yt-dlp."""


class DownloadFailed(VideoGrabberError):
    """Источник не отдал видео."""


class MediaError(VideoGrabberError):
    """Файл не удалось прочитать или собрать в MP4."""


class FileNotReady(VideoGrabberError):
    """MP4 ещё собирается или задача не найдена среди готовых."""
