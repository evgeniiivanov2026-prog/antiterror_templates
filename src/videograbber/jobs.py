"""Очередь загрузок для локальной страницы."""

from __future__ import annotations

import logging
import queue
import threading
import time
import uuid
from dataclasses import dataclass, field
from pathlib import Path

from videograbber import __version__
from videograbber.errors import FileNotReady, TooManyJobs, VideoGrabberError
from videograbber.grab import GrabResult, Progress, grab
from videograbber.naming import redact_url, validate_quality, validate_url

log = logging.getLogger("videograbber")

_MAX_ACTIVE = 8
_MAX_STORED = 30


@dataclass
class Job:
    id: str
    url: str
    quality: str
    status: str = "queued"
    phase: str = "queued"
    percent: float | None = None
    speed: float | None = None
    eta: int | None = None
    title: str | None = None
    error: str | None = None
    result: dict | None = None
    output_path: Path | None = None
    created_at: float = 0.0


@dataclass
class JobManager:
    output_dir: Path
    grabber: object = field(default=grab)
    _jobs: dict[str, Job] = field(default_factory=dict)
    _lock: threading.Lock = field(default_factory=threading.Lock)
    _queue: queue.Queue = field(default_factory=queue.Queue)
    _stop: threading.Event = field(default_factory=threading.Event)
    _thread: threading.Thread | None = field(default=None, init=False)

    def __post_init__(self) -> None:
        self.output_dir = Path(self.output_dir)
        self._thread = threading.Thread(
            target=self._loop,
            name="videograbber-jobs",
            daemon=True,
        )
        self._thread.start()

    def health(self) -> dict[str, object]:
        return {
            "ok": True,
            "version": __version__,
            "output_dir": str(self.output_dir.resolve()),
        }

    def submit(self, url: str, quality: str = "1080") -> dict:
        cleaned = validate_url(url)
        chosen = validate_quality(quality)
        job = Job(
            id=uuid.uuid4().hex,
            url=cleaned,
            quality=chosen,
            created_at=_now(),
        )
        with self._lock:
            active = sum(1 for item in self._jobs.values() if item.status in {"queued", "running"})
            if active >= _MAX_ACTIVE:
                raise TooManyJobs("Уже идёт слишком много загрузок. Дождитесь окончания.")
            self._jobs[job.id] = job
            self._trim_finished()
        self._queue.put(job.id)
        return self.snapshot(job.id) or {}

    def snapshot(self, job_id: str) -> dict | None:
        with self._lock:
            job = self._jobs.get(job_id)
            if job is None:
                return None
            return _public_job(job)

    def list_jobs(self) -> list[dict]:
        with self._lock:
            jobs = sorted(self._jobs.values(), key=lambda item: item.created_at, reverse=True)
            return [_public_job(job) for job in jobs]

    def output_file(self, job_id: str) -> tuple[Path, str]:
        with self._lock:
            job = self._jobs.get(job_id)
            if job is None:
                raise KeyError(job_id)
            if job.status != "done" or job.output_path is None or job.result is None:
                raise FileNotReady("MP4 ещё не готов.")
            path = job.output_path
            filename = str(job.result.get("filename") or path.name)
        if not path.is_file():
            raise FileNotReady("Готовый файл уже удалён из папки.")
        root = self.output_dir.resolve()
        resolved = path.resolve()
        if not resolved.is_relative_to(root):
            raise FileNotReady("Файл лежит вне папки загрузок.")
        return resolved, Path(filename).name

    def close(self) -> None:
        self._stop.set()
        self._queue.put(None)
        if self._thread is not None:
            self._thread.join(timeout=2)

    def _loop(self) -> None:
        while not self._stop.is_set():
            job_id = self._queue.get()
            try:
                if job_id is None:
                    return
                self._run(job_id)
            finally:
                self._queue.task_done()

    def _run(self, job_id: str) -> None:
        with self._lock:
            job = self._jobs.get(job_id)
            if job is None:
                return
            url = job.url
            quality = job.quality
            job.status = "running"
            job.phase = "download"
        try:
            results = self.grabber(
                url,
                self.output_dir,
                quality=quality,
                on_progress=lambda event: self._apply(job_id, event),
            )
            if not results:
                raise VideoGrabberError("Не получилось собрать файл.")
            result = results[0]
            payload = _result_view(result)
            with self._lock:
                current = self._jobs.get(job_id)
                if current is None:
                    return
                current.status = "done"
                current.phase = "done"
                current.percent = 100
                current.speed = None
                current.eta = None
                current.error = None
                current.title = result.title
                current.result = payload
                current.output_path = result.path
        except VideoGrabberError as exc:
            self._fail(job_id, str(exc))
        except Exception:
            log.exception("задача %s завершилась с ошибкой", job_id)
            self._fail(job_id, "Не получилось скачать или собрать MP4.")

    def _apply(self, job_id: str, event: Progress) -> None:
        with self._lock:
            job = self._jobs.get(job_id)
            if job is None or job.status in {"done", "error"}:
                return
            job.status = "running"
            job.phase = event.phase or job.phase
            if event.percent is not None:
                job.percent = max(0.0, min(100.0, float(event.percent)))
            job.speed = _finite(event.speed)
            job.eta = event.eta if event.eta is not None and event.eta >= 0 else None
            if event.title:
                job.title = event.title

    def _fail(self, job_id: str, message: str) -> None:
        with self._lock:
            job = self._jobs.get(job_id)
            if job is None:
                return
            job.status = "error"
            job.phase = "error"
            job.error = message
            job.speed = None
            job.eta = None

    def _trim_finished(self) -> None:
        if len(self._jobs) <= _MAX_STORED:
            return
        finished = [job for job in self._jobs.values() if job.status in {"done", "error"}]
        finished.sort(key=lambda item: item.created_at)
        while len(self._jobs) > _MAX_STORED and finished:
            self._jobs.pop(finished.pop(0).id, None)


def _public_job(job: Job) -> dict:
    return {
        "id": job.id,
        "url": redact_url(job.url),
        "quality": job.quality,
        "status": job.status,
        "phase": job.phase,
        "percent": job.percent,
        "speed_bps": job.speed,
        "eta_seconds": job.eta,
        "title": job.title,
        "error": job.error,
        "result": job.result,
    }


def _result_view(result: GrabResult) -> dict[str, object]:
    return {
        "title": result.title,
        "filename": result.path.name,
        "bytes": result.bytes,
        "width": result.width,
        "height": result.height,
        "duration": result.duration,
        "video_codec": result.video_codec,
        "audio_codec": result.audio_codec,
        "mode": result.mode,
    }


def _finite(value: float | None) -> float | None:
    if value is None:
        return None
    try:
        number = float(value)
    except (TypeError, ValueError):
        return None
    if number != number or number in {float("inf"), float("-inf")} or number < 0:
        return None
    return number


def _now() -> float:
    return time.time()
