import re
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

import pytest

from videograbber.download import download_video
from videograbber.parallel import RangeNotSupported, _slices, download_parallel


def _serve(payload: bytes, honor_range: bool):
    ranges: list[str] = []

    class Handler(BaseHTTPRequestHandler):
        def do_GET(self):
            header = self.headers.get("Range")
            if header:
                ranges.append(header)
            if honor_range and header:
                match = re.match(r"bytes=(\d+)-(\d*)", header)
                start = int(match.group(1))
                end = int(match.group(2)) if match.group(2) else len(payload) - 1
                end = min(end, len(payload) - 1)
                body = payload[start : end + 1]
                self.send_response(206)
                self.send_header("Content-Range", f"bytes {start}-{end}/{len(payload)}")
                self.send_header("Content-Length", str(len(body)))
                self.send_header("Accept-Ranges", "bytes")
                self.end_headers()
                try:
                    self.wfile.write(body)
                except BrokenPipeError:
                    return
                return
            self.send_response(200)
            self.send_header("Content-Length", str(len(payload)))
            self.end_headers()
            try:
                self.wfile.write(payload)
            except BrokenPipeError:
                return

        def log_message(self, format, *args):
            return None

    server = ThreadingHTTPServer(("127.0.0.1", 0), Handler)
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    return server, ranges


def test_slices_cover_every_byte():
    ranges = _slices(1000, 4)
    assert ranges[0][0] == 0
    assert ranges[-1][1] == 999
    assert sum(end - start + 1 for start, end in ranges) == 1000


def test_parallel_download_matches_the_source(tmp_path):
    payload = bytes(index % 251 for index in range(1024 * 1024))
    server, ranges = _serve(payload, honor_range=True)
    try:
        destination = tmp_path / "out" / "file.bin"
        download_parallel(
            f"http://127.0.0.1:{server.server_address[1]}/wide.bin",
            destination,
            {},
            lambda done, total: None,
        )
    finally:
        server.shutdown()
    assert destination.read_bytes() == payload
    assert any(header != "bytes=0-0" for header in ranges)


def test_server_without_ranges_falls_back(tmp_path):
    server, _ranges = _serve(b"small-body", honor_range=False)
    try:
        with pytest.raises(RangeNotSupported):
            download_parallel(
                f"http://127.0.0.1:{server.server_address[1]}/clip.mp4",
                tmp_path / "clip.mp4",
                {},
                lambda done, total: None,
            )
    finally:
        server.shutdown()
    assert not (tmp_path / "clip.mp4").exists()


def test_download_video_uses_ranges_for_a_large_file(tmp_path):
    payload = b"\x00\x00\x00\x18ftypmp42" + bytes(900_000)
    server, ranges = _serve(payload, honor_range=True)
    try:
        source = download_video(
            f"http://127.0.0.1:{server.server_address[1]}/clip.mp4",
            tmp_path / "work",
            None,
            None,
        )
    finally:
        server.shutdown()
    assert source.path.read_bytes() == payload
    assert any(header.startswith("bytes=") and header != "bytes=0-0" for header in ranges)
