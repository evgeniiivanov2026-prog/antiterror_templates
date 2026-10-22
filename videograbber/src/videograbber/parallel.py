"""Несколько HTTP-соединений для одного большого файла."""

from __future__ import annotations

import os
import re
import threading
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen

PARALLEL_CONNECTIONS = 8
BUFFER_SIZE = 1024 * 1024
_MIN_BYTES = 512 * 1024
_SLICE_BYTES = 256 * 1024
_READ_BYTES = 256 * 1024
_ATTEMPTS = 3
_RANGE_TOTAL = re.compile(r"bytes\s+\d+\s*-\s*\d+\s*/\s*(\d+)", re.IGNORECASE)


class RangeNotSupported(Exception):
    """Сервер или файл лучше скачивать одним соединением."""


def download_parallel(
    url: str,
    dest: Path,
    headers: dict[str, str],
    on_bytes,
) -> None:
    """Скачать url в dest частями. Если диапазоны недоступны, бросает RangeNotSupported."""

    total = _probe_size(url, headers)
    if total < _MIN_BYTES:
        raise RangeNotSupported()
    workers = min(PARALLEL_CONNECTIONS, max(2, total // _SLICE_BYTES))
    slices = _slices(total, workers)
    dest.parent.mkdir(parents=True, exist_ok=True)
    descriptor = os.open(dest, os.O_CREAT | os.O_TRUNC | os.O_WRONLY, 0o644)
    try:
        os.ftruncate(descriptor, total)
    finally:
        os.close(descriptor)
    done = 0
    lock = threading.Lock()

    def report(amount: int) -> None:
        nonlocal done
        with lock:
            done = max(0, done + amount)
            on_bytes(done, total)

    def fetch(bounds: tuple[int, int]) -> None:
        start, end = bounds
        last_error: Exception | None = None
        for _attempt in range(_ATTEMPTS):
            accounted = 0

            def report_once(amount: int) -> None:
                nonlocal accounted
                accounted += amount
                report(amount)

            try:
                _fetch_slice(url, dest, headers, start, end, report_once)
                return
            except (OSError, URLError, HTTPError, TimeoutError, RangeNotSupported) as exc:
                if accounted:
                    report(-accounted)
                last_error = exc
        if last_error is not None:
            raise last_error
        raise OSError(f"Не удалось скачать диапазон {start}-{end}")

    try:
        with ThreadPoolExecutor(max_workers=len(slices)) as pool:
            futures = [pool.submit(fetch, bounds) for bounds in slices]
            for future in futures:
                future.result()
    except Exception:
        dest.unlink(missing_ok=True)
        raise RangeNotSupported() from None
    if dest.stat().st_size != total:
        dest.unlink(missing_ok=True)
        raise RangeNotSupported()


def _probe_size(url: str, headers: dict[str, str]) -> int:
    probe_headers = _prepare_headers(headers)
    probe_headers["Range"] = "bytes=0-0"
    request = Request(url, headers=probe_headers)
    try:
        try:
            response = urlopen(request, timeout=30)
        except HTTPError as exc:
            response = exc
    except (URLError, TimeoutError, OSError) as exc:
        raise RangeNotSupported() from exc
    with response:
        status = getattr(response, "status", None) or response.getcode()
        if status != 206:
            raise RangeNotSupported()
        match = _RANGE_TOTAL.search(response.headers.get("Content-Range") or "")
        if match is None:
            raise RangeNotSupported()
        response.read(1)
        return int(match.group(1))


def _fetch_slice(
    url: str,
    dest: Path,
    headers: dict[str, str],
    start: int,
    end: int,
    report,
) -> None:
    slice_headers = _prepare_headers(headers)
    slice_headers["Range"] = f"bytes={start}-{end}"
    request = Request(url, headers=slice_headers)
    expected = end - start + 1
    try:
        response = urlopen(request, timeout=60)
    except HTTPError as exc:
        response = exc
    with response:
        status = getattr(response, "status", None) or response.getcode()
        if status != 206:
            raise RangeNotSupported()
        got = 0
        with dest.open("r+b") as handle:
            handle.seek(start)
            while got < expected:
                block = response.read(min(_READ_BYTES, expected - got))
                if not block:
                    raise OSError("Соединение закрылось раньше конца фрагмента")
                handle.write(block)
                got += len(block)
                report(len(block))


def _prepare_headers(headers: dict[str, str]) -> dict[str, str]:
    prepared = {
        str(key): str(value)
        for key, value in headers.items()
        if key.lower() not in {"range", "accept-encoding", "host"}
    }
    prepared.setdefault("User-Agent", "videograbber")
    prepared["Accept-Encoding"] = "identity"
    return prepared


def _slices(total: int, workers: int) -> list[tuple[int, int]]:
    piece = max(1, total // workers)
    ranges: list[tuple[int, int]] = []
    start = 0
    for index in range(workers):
        end = total - 1 if index == workers - 1 else min(total - 1, start + piece - 1)
        ranges.append((start, end))
        start = end + 1
        if start >= total:
            break
    return ranges
