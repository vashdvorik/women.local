from __future__ import annotations

import json
import os
from collections import deque
from pathlib import Path

import numpy as np
from PIL import Image, ImageDraw, ImageEnhance, ImageFilter, ImageFont, ImageOps


ROOT = Path(__file__).resolve().parent
OUTPUT = Path(os.environ.get("PARTNER_LOGO_OUTPUT", str(ROOT / "ready")))
INTERNATIONAL = ROOT / "международные партнеры"
PARTNERS = ROOT / "Партнеры и вдохновители"


def edge_connected_mask(candidate: np.ndarray, transparent: np.ndarray | None = None) -> np.ndarray:
    """Find candidate pixels connected to the image edge or transparency.

    Only connected pixels are removed, so details inside a mark are preserved.
    This is deliberately conservative for brand artwork.
    """
    height, width = candidate.shape
    seen = np.zeros_like(candidate, dtype=bool)
    queue: deque[tuple[int, int]] = deque()

    for x in range(width):
        if candidate[0, x]:
            seen[0, x] = True
            queue.append((0, x))
        if candidate[height - 1, x] and not seen[height - 1, x]:
            seen[height - 1, x] = True
            queue.append((height - 1, x))
    for y in range(height):
        if candidate[y, 0] and not seen[y, 0]:
            seen[y, 0] = True
            queue.append((y, 0))
        if candidate[y, width - 1] and not seen[y, width - 1]:
            seen[y, width - 1] = True
            queue.append((y, width - 1))

    if transparent is not None:
        for y, x in zip(*np.where(transparent)):
            if not seen[y, x]:
                seen[y, x] = True
                queue.append((int(y), int(x)))

    while queue:
        y, x = queue.popleft()
        for next_y, next_x in ((y - 1, x), (y + 1, x), (y, x - 1), (y, x + 1)):
            if 0 <= next_y < height and 0 <= next_x < width:
                if candidate[next_y, next_x] and not seen[next_y, next_x]:
                    seen[next_y, next_x] = True
                    queue.append((next_y, next_x))

    return seen


def edge_background_mask(rgb: np.ndarray, alpha: np.ndarray | None = None) -> np.ndarray:
    light = rgb.min(axis=2) >= 242
    neutral = (rgb.max(axis=2) - rgb.min(axis=2)) <= 28
    candidate = light & neutral
    transparent = alpha == 0 if alpha is not None else None
    return edge_connected_mask(candidate, transparent)


def dark_edge_background_mask(rgb: np.ndarray, alpha: np.ndarray | None = None) -> np.ndarray:
    dark = rgb.max(axis=2) <= 55
    neutral = (rgb.max(axis=2) - rgb.min(axis=2)) <= 35
    candidate = dark & neutral
    transparent = alpha == 0 if alpha is not None else None
    return edge_connected_mask(candidate, transparent)


def remove_white_background(image: Image.Image) -> tuple[Image.Image, bool]:
    rgba = image.convert("RGBA")
    pixels = np.array(rgba)
    mask = edge_background_mask(pixels[:, :, :3], pixels[:, :, 3])
    if not mask.any():
        return rgba, False

    pixels[mask, 3] = 0
    return Image.fromarray(pixels, mode="RGBA"), True


def remove_dark_background(image: Image.Image) -> tuple[Image.Image, bool]:
    rgba = image.convert("RGBA")
    pixels = np.array(rgba)
    mask = dark_edge_background_mask(pixels[:, :, :3], pixels[:, :, 3])
    if not mask.any():
        return rgba, False

    pixels[mask, 3] = 0
    return Image.fromarray(pixels, mode="RGBA"), True


def normalize(
    image: Image.Image,
    remove_background: bool = True,
    remove_dark_background_layer: bool = False,
) -> tuple[Image.Image, bool]:
    image = image.convert("RGBA")
    background_removed = False
    if remove_dark_background_layer:
        image, dark_removed = remove_dark_background(image)
        background_removed = background_removed or dark_removed
    if remove_background:
        image, white_removed = remove_white_background(image)
        background_removed = background_removed or white_removed

    alpha = image.getchannel("A")
    bbox = alpha.getbbox()
    if bbox is None:
        return image, background_removed

    # Remove accidental empty borders, then keep a small transparent safety margin.
    image = image.crop(bbox)
    margin = max(8, round(max(image.width, image.height) * 0.045))
    canvas = Image.new("RGBA", (image.width + margin * 2, image.height + margin * 2), (0, 0, 0, 0))
    canvas.alpha_composite(image, (margin, margin))
    image = canvas

    # Make all deliverables comfortably retina-ready for the current CSS sizes.
    target_long_side = 900
    long_side = max(image.width, image.height)
    if long_side < target_long_side:
        scale = target_long_side / long_side
        size = (round(image.width * scale), round(image.height * scale))
        image = image.resize(size, Image.Resampling.LANCZOS)
        image = image.filter(ImageFilter.UnsharpMask(radius=1.0, percent=82, threshold=3))
    elif long_side > target_long_side * 2:
        scale = target_long_side * 2 / long_side
        size = (round(image.width * scale), round(image.height * scale))
        image = image.resize(size, Image.Resampling.LANCZOS)

    # Keep colors faithful while removing only compression haze from opaque artwork.
    image = ImageEnhance.Color(image).enhance(1.02)
    return image, background_removed


def extract_fintech_mark(image: Image.Image) -> Image.Image:
    """Conservatively extract the mark from the provided low-res photo card.

    The source has a tiled grey backdrop. Bright triangle pixels and the dark
    wordmark are isolated without redrawing the brand or inventing text.
    """
    rgba = image.convert("RGBA")
    pixels = np.array(rgba)
    rgb = pixels[:, :, :3]
    rgb_int = rgb.astype(np.int16)
    gray = rgb.mean(axis=2)
    height, width = gray.shape
    yy, xx = np.indices(gray.shape)

    triangle_area = (xx >= 5) & (xx < min(width, 80)) & (yy >= 8) & (yy < min(height, 78))
    triangle = triangle_area & ((gray > 205) | ((rgb_int[:, :, 2] - rgb_int[:, :, 0] > 35) & (gray > 135)))
    wordmark_area = (xx >= min(width, 80)) & (yy >= 8) & (yy < min(height, 94))
    wordmark = wordmark_area & (gray < 58)
    mask = triangle | wordmark

    pixels[:, :, 3] = np.where(mask, 255, 0).astype("uint8")
    extracted = Image.fromarray(pixels, mode="RGBA")
    prepared, _ = normalize(extracted, remove_background=False)
    return prepared


def save_prepared(
    source: Path,
    destination: Path,
    remove_background: bool = True,
    remove_dark_background_layer: bool = False,
    crop_box: tuple[int, int, int, int] | None = None,
) -> dict:
    original = Image.open(source)
    working = original.crop(crop_box) if crop_box is not None else original
    prepared, background_removed = normalize(
        working,
        remove_background=remove_background,
        remove_dark_background_layer=remove_dark_background_layer,
    )
    destination.parent.mkdir(parents=True, exist_ok=True)
    prepared.save(destination, format="PNG", optimize=True)
    return {
        "source": str(source.relative_to(ROOT)).replace("\\", "/"),
        "output": str(destination.relative_to(ROOT)).replace("\\", "/"),
        "original_size": [original.width, original.height],
        "prepared_size": [prepared.width, prepared.height],
        "background_removed": background_removed,
    }


def split_combined(source: Path) -> list[tuple[str, Image.Image]]:
    image, _ = remove_white_background(Image.open(source))
    alpha = np.array(image.getchannel("A"))
    columns = np.any(alpha > 0, axis=0)
    groups: list[tuple[int, int]] = []
    start = None
    for index, present in enumerate(columns):
        if present and start is None:
            start = index
        elif not present and start is not None:
            groups.append((start, index))
            start = None
    if start is not None:
        groups.append((start, len(columns)))

    # Merge neighboring text fragments into the same wordmark, then split the
    # two intended marks at the large blank gap between them.
    merged: list[tuple[int, int]] = []
    for left, right in groups:
        if not merged or left - merged[-1][1] > 12:
            merged.append((left, right))
        else:
            merged[-1] = (merged[-1][0], right)
    groups = [(left, right) for left, right in merged if right - left > 20]
    if len(groups) != 2:
        raise RuntimeError(f"Expected two logos in {source.name}, found {len(groups)} regions")

    names = [("artcor", image.crop((max(0, left - 18), 0, min(image.width, right + 18), image.height))) for left, right in groups[:1]]
    names.append(("contact", image.crop((max(0, groups[1][0] - 18), 0, min(image.width, groups[1][1] + 18), image.height))))
    return names


def make_contact_sheet(files: list[Path], destination: Path) -> None:
    columns = 4
    card_width, card_height = 300, 220
    rows = (len(files) + columns - 1) // columns
    sheet = Image.new("RGB", (columns * card_width, rows * card_height), "#f3eee9")
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.load_default()

    for index, path in enumerate(files):
        x = (index % columns) * card_width
        y = (index // columns) * card_height
        tile = Image.new("RGBA", (card_width - 20, 170), (255, 255, 255, 255))
        tile = ImageOps.contain(Image.open(path).convert("RGBA"), tile.size, Image.Resampling.LANCZOS)
        tile_bg = Image.new("RGBA", (card_width - 20, 170), (255, 255, 255, 255))
        tile_bg.alpha_composite(tile, ((card_width - 20 - tile.width) // 2, (170 - tile.height) // 2))
        sheet.paste(tile_bg.convert("RGB"), (x + 10, y + 10))
        label = str(path.relative_to(OUTPUT)).replace("\\", "/")
        draw.text((x + 12, y + 190), label, fill="#2a1913", font=font)

    destination.parent.mkdir(parents=True, exist_ok=True)
    sheet.save(destination, format="PNG", optimize=True)


def main() -> None:
    OUTPUT.mkdir(parents=True, exist_ok=True)
    manifest: list[dict] = []

    international_names = {
        "1.png": "01-kingdom-of-the-netherlands.png",
        "2.png": "02-un-women.png",
        "3.png": "03-united-nations-moldova.png",
        "4.png": "04-undp.png",
        "5.png": "05-nrc.png",
    }
    for source_name, output_name in international_names.items():
        source = INTERNATIONAL / source_name
        item = save_prepared(
            source,
            OUTPUT / "международные партнеры" / output_name,
            remove_dark_background_layer=source_name in {"2.png", "3.png"},
            crop_box={
                "2.png": (2, 4, 70, 49),
                "3.png": (2, 10, 71, 55),
            }.get(source_name),
        )
        if source_name == "4.png":
            item["note"] = "Original artwork is preserved; enlarged for web/retina use."
        manifest.append(item)

    partner_names = {
        "1.png": "01-eba-moldova.png",
        "2.png": "02-afam.png",
        "3.png": "03-youth-4-innovation.png",
        "4.png": "04-glia-impact-hub.png",
        "5.png": "05-centrul-parteneriat-pentru-dezvoltare.png",
        "6.png": "06-fintech-hub.png",
        "7.png": "07-ziphouse.png",
        "8.png": "08-multicolor-mark.png",
        "9.png": "09-moldova-in-development.png",
        "10.png": "10-creative-association.png",
        "11.png": "11-social-entrepreneurship-platform.png",
        "12.jpg": "12-woman-up.png",
        "13.png": "13-startup-moldova.png",
    }
    for source_name, output_name in partner_names.items():
        source = PARTNERS / source_name
        destination = OUTPUT / "Партнеры и вдохновители" / output_name
        if source_name == "6.png":
            original = Image.open(source)
            prepared = extract_fintech_mark(original)
            destination.parent.mkdir(parents=True, exist_ok=True)
            prepared.save(destination, format="PNG", optimize=True)
            item = {
                "source": str(source.relative_to(ROOT)).replace("\\", "/"),
                "output": str(destination.relative_to(ROOT)).replace("\\", "/"),
                "original_size": [original.width, original.height],
                "prepared_size": [prepared.width, prepared.height],
                "background_removed": True,
                "note": "Photographic backdrop removed conservatively; an original/vector logo is recommended for maximum fidelity."
            }
        else:
            item = save_prepared(source, destination)
        manifest.append(item)

    combined = PARTNERS / "4,5.png"
    for key, image in split_combined(combined):
        destination = OUTPUT / "Партнеры и вдохновители" / f"14-{key}.png"
        prepared, background_removed = normalize(image, remove_background=False)
        destination.parent.mkdir(parents=True, exist_ok=True)
        prepared.save(destination, format="PNG", optimize=True)
        manifest.append({
            "source": str(combined.relative_to(ROOT)).replace("\\", "/"),
            "output": str(destination.relative_to(ROOT)).replace("\\", "/"),
            "original_size": [image.width, image.height],
            "prepared_size": [prepared.width, prepared.height],
            "background_removed": background_removed,
            "note": "Split from the combined source file into a separate logo asset.",
        })

    (OUTPUT / "manifest.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")
    make_contact_sheet(sorted(path for path in OUTPUT.rglob("*.png") if path.name != "preview.png"), OUTPUT / "preview.png")
    print(json.dumps({"count": len(manifest), "output": str(OUTPUT)}, ensure_ascii=False))


if __name__ == "__main__":
    main()
