"""Рисует иконки AURIS для manifest Chrome."""

from pathlib import Path

from PIL import Image, ImageDraw

OUT = Path(__file__).resolve().parents[1] / "assets" / "icons"


def draw(size: int) -> Image.Image:
    image = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(image)
    pad = max(1, round(size * 0.06))
    radius = max(2, round(size * 0.28))
    draw.rounded_rectangle(
        [pad, pad, size - pad - 1, size - pad - 1],
        radius=radius,
        fill=(9, 11, 16, 255),
    )
    cx = cy = size / 2
    outer = size * 0.29
    width = max(1, round(size * 0.055))
    draw.ellipse(
        [cx - outer, cy - outer, cx + outer, cy + outer],
        outline=(142, 246, 214, 255),
        width=width,
    )
    inner = size * 0.11
    draw.ellipse(
        [cx - inner, cy - inner, cx + inner, cy + inner],
        fill=(192, 176, 255, 255),
    )
    tick = max(1, round(size * 0.045))
    arm = size * 0.11
    gap = outer + size * 0.035
    for x1, y1, x2, y2 in (
        (cx, cy - gap - arm, cx, cy - gap),
        (cx, cy + gap, cx, cy + gap + arm),
        (cx - gap - arm, cy, cx - gap, cy),
        (cx + gap, cy, cx + gap + arm, cy),
    ):
        draw.line((x1, y1, x2, y2), fill=(142, 246, 214, 255), width=tick)
    return image


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    for size in (16, 32, 48, 128):
        draw(size).save(OUT / f"icon{size}.png")


if __name__ == "__main__":
    main()
