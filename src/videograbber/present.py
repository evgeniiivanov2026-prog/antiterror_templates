"""Короткое оформление длительности, размера и способа сборки."""

from __future__ import annotations

_MODES = {
    "ready": "уже совместимый MP4",
    "remux": "собран без перекодирования",
    "transcode": "перекодирован в H.264/AAC",
}


def format_duration(seconds: float | None) -> str:
    if seconds is None or seconds < 0:
        return "—"
    total = int(round(seconds))
    hours, rest = divmod(total, 3600)
    minutes, secs = divmod(rest, 60)
    if hours:
        return f"{hours}:{minutes:02d}:{secs:02d}"
    return f"{minutes}:{secs:02d}"


def format_bytes(amount: float | None) -> str:
    if amount is None or amount < 0:
        return "—"
    value = float(amount)
    units = ("Б", "КБ", "МБ", "ГБ", "ТБ")
    for unit in units[:-1]:
        if value < 1024:
            if unit == "Б":
                return f"{int(value)} {unit}"
            return f"{value:.1f} {unit}"
        value /= 1024
    return f"{value:.1f} {units[-1]}"


def mode_label(mode: str) -> str:
    return _MODES.get(mode, mode)
