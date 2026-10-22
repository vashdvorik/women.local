from __future__ import annotations

import json
import os
import shutil
import subprocess
import tempfile
from urllib.parse import quote
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont, ImageOps

from prepare_partner_logos import normalize, split_combined


ROOT = Path(__file__).resolve().parent
PROJECT = ROOT.parents[1]
OUTPUT = ROOT / "final"
ATTACHED_FIRST = Path(r"C:\Users\Pavel\Downloads\Новая папка (6)\images.png")
ATTACHED_UNWOMEN_MOLDOVA = Path(r"C:\Users\Pavel\Downloads\Новая папка (6)\images.jpg")
ATTACHED_UN_MOLDOVA = Path(r"C:\Users\Pavel\Downloads\Новая папка (6)\2.png")
PUBLIC_PARTNERS = PROJECT / "public" / "images" / "partners"
RAW_INTL = ROOT / "международные партнеры"
RAW_LOCAL = ROOT / "Партнеры и вдохновители"
OFFICIAL_INTL = ROOT / "originals" / "международные партнеры"
OFFICIAL_LOCAL = ROOT / "originals" / "Партнеры и вдохновители"


def rel(path: Path) -> str:
    return str(path.relative_to(ROOT)).replace("\\", "/")


def process_raster(
    source: Path,
    destination: Path,
    note: str = "",
    remove_background: bool = True,
    remove_dark_background: bool = False,
    crop_box: tuple[int, int, int, int] | None = None,
) -> dict:
    original = Image.open(source)
    working = original.crop(crop_box) if crop_box is not None else original
    prepared, background_removed = normalize(
        working,
        remove_background=remove_background,
        remove_dark_background_layer=remove_dark_background,
    )
    destination.parent.mkdir(parents=True, exist_ok=True)
    prepared.save(destination, format="PNG", optimize=True)
    item = {
        "source": str(source),
        "output": str(destination.relative_to(OUTPUT)).replace("\\", "/"),
        "type": "png",
        "original_size": [original.width, original.height],
        "prepared_size": [prepared.width, prepared.height],
        "background_removed": background_removed,
    }
    if note:
        item["note"] = note
    return item


def copy_vector(source: Path, destination: Path, note: str = "") -> dict:
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, destination)
    item = {
        "source": str(source),
        "output": str(destination.relative_to(OUTPUT)).replace("\\", "/"),
        "type": "svg",
    }
    if note:
        item["note"] = note
    return item


def rasterize_svg(source: Path) -> Image.Image:
    edge = Path(os.environ.get("EDGE_PATH", r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"))
    if not edge.exists():
        raise FileNotFoundError(f"Edge is required to rasterize SVG: {edge}")

    with tempfile.TemporaryDirectory(prefix="partner-svg-", dir=str(OUTPUT)) as temp_dir:
        temp = Path(temp_dir)
        screenshot = temp / "render.png"
        profile = temp / "profile"
        file_url = "file:///" + quote(str(source).replace("\\", "/"), safe="/:_")
        subprocess.run(
            [
                str(edge),
                "--headless=new",
                "--no-sandbox",
                "--disable-gpu",
                "--disable-extensions",
                f"--user-data-dir={profile}",
                f"--screenshot={screenshot}",
                "--window-size=1800,1800",
                "--default-background-color=00000000",
                file_url,
            ],
            check=True,
            stdout=subprocess.DEVNULL,
            stderr=subprocess.DEVNULL,
        )
        with Image.open(screenshot) as rendered:
            return rendered.convert("RGBA").copy()


def process_svg_as_png(source: Path, destination: Path, note: str = "") -> dict:
    rendered = rasterize_svg(source)
    prepared, _ = normalize(rendered, remove_background=False)
    destination.parent.mkdir(parents=True, exist_ok=True)
    prepared.save(destination, format="PNG", optimize=True)
    item = {
        "source": str(source),
        "output": str(destination.relative_to(OUTPUT)).replace("\\", "/"),
        "type": "png",
        "original_format": "svg",
        "prepared_size": [prepared.width, prepared.height],
    }
    if note:
        item["note"] = note
    return item


def make_dark_variant(source: Path, destination: Path, color: str = "#241711") -> dict:
    original = Image.open(source).convert("RGBA")
    prepared, _ = normalize(original, remove_background=False)
    background = Image.new("RGBA", prepared.size, color)
    background.alpha_composite(prepared)
    destination.parent.mkdir(parents=True, exist_ok=True)
    background.convert("RGB").save(destination, format="PNG", optimize=True)
    return {
        "source": str(source),
        "output": str(destination.relative_to(OUTPUT)).replace("\\", "/"),
        "type": "png",
        "variant": "dark-background",
        "prepared_size": [background.width, background.height],
        "note": "Official white mark placed on a dark neutral background for light cards.",
    }


def make_contact_sheet(files: list[Path], destination: Path) -> None:
    columns = 4
    card_width, card_height = 360, 250
    rows = (len(files) + columns - 1) // columns
    sheet = Image.new("RGB", (columns * card_width, rows * card_height), "#f3eee9")
    draw = ImageDraw.Draw(sheet)
    font = ImageFont.load_default()

    for index, path in enumerate(files):
        x = (index % columns) * card_width
        y = (index // columns) * card_height
        tile_size = (card_width - 24, 185)
        tile_bg = Image.new("RGBA", tile_size, (255, 255, 255, 255))
        tile = ImageOps.contain(Image.open(path).convert("RGBA"), tile_size, Image.Resampling.LANCZOS)
        tile_bg.alpha_composite(tile, ((tile_size[0] - tile.width) // 2, (tile_size[1] - tile.height) // 2))
        sheet.paste(tile_bg.convert("RGB"), (x + 12, y + 12))
        label = str(path.relative_to(OUTPUT)).replace("\\", "/")
        draw.text((x + 14, y + 210), label, fill="#2a1913", font=font)

    destination.parent.mkdir(parents=True, exist_ok=True)
    sheet.save(destination, format="PNG", optimize=True)


def main() -> None:
    png_intl = OUTPUT / "png" / "международные партнеры"
    png_local = OUTPUT / "png" / "Партнеры и вдохновители"
    svg_intl = OUTPUT / "svg" / "международные партнеры"
    svg_local = OUTPUT / "svg" / "Партнеры и вдохновители"
    manifest: list[dict] = []

    international = [
        ("01-kingdom-of-the-netherlands.png", ATTACHED_FIRST, "User-provided high-resolution horizontal lockup."),
        ("02-un-women.png", ATTACHED_UNWOMEN_MOLDOVA, "User-provided clean UN Women Moldova lockup."),
        ("03-united-nations-moldova.png", ATTACHED_UN_MOLDOVA, "User-provided clean United Nations Moldova lockup."),
        ("04-undp.png", OFFICIAL_INTL / "04-undp-official.png", "Official UNDP source; preserved as supplied."),
    ]
    for item in international:
        filename, source, note, *options = item
        remove_background = options[0] if options else True
        remove_dark_background = options[1] if len(options) > 1 else False
        crop_box = options[2] if len(options) > 2 else None
        manifest.append(process_raster(source, png_intl / filename, note, remove_background, remove_dark_background, crop_box))
    manifest.append(process_svg_as_png(
        OFFICIAL_INTL / "05-nrc.svg",
        png_intl / "05-nrc.png",
        "Rasterized from the official SVG at high resolution for site use.",
    ))

    international_vectors = [
        ("01-kingdom-of-the-netherlands.svg", OFFICIAL_INTL / "01-kingdom-of-the-netherlands.svg"),
        ("03-united-nations-emblem.svg", OFFICIAL_INTL / "03-united-nations-emblem.svg"),
        ("05-nrc.svg", OFFICIAL_INTL / "05-nrc.svg"),
    ]
    for filename, source in international_vectors:
        manifest.append(copy_vector(source, svg_intl / filename, "Untouched official vector source."))

    local = [
        ("01-eba-moldova.png", PUBLIC_PARTNERS / "local-eba.png", "Current clean site asset from the official partner set."),
        ("02-afam.png", PUBLIC_PARTNERS / "local-afam.png", "Current clean site asset from the official partner set; official SVG is also included below."),
        ("03-youth-4-innovation.png", RAW_LOCAL / "3.png", "Official logo supplied as a small raster source; preserved without redraw."),
        ("04-glia-impact-hub.png", PUBLIC_PARTNERS / "local-glia.png", "Current clean site asset from the official partner set."),
        ("05-centrul-parteneriat-pentru-dezvoltare.png", PUBLIC_PARTNERS / "local-progen.png", "Current clean site asset from the official partner set."),
        ("07-ziphouse.png", RAW_LOCAL / "7.png", "Clean horizontal lockup from the supplied site source; official SVG is also included below."),
        ("08-multicolor-mark.png", RAW_LOCAL / "8.png", "Supplied source preserved without redraw."),
        ("09-moldova-in-development.png", PUBLIC_PARTNERS / "coordinator-ida.png", "Current clean site asset from the official partner set."),
        ("10-creative-association.png", PUBLIC_PARTNERS / "coordinator-creative.png", "Current clean site asset from the official partner set."),
        ("11-social-entrepreneurship-platform.png", PUBLIC_PARTNERS / "coordinator-platform.png", "Current clean site asset from the official partner set."),
        ("12-woman-up.png", OFFICIAL_LOCAL / "12-woman-up.png", "Official source from the project website."),
        ("13-startup-moldova.png", RAW_LOCAL / "13.png", "Clean black-and-green site lockup from the supplied source."),
    ]
    for item in local:
        filename, source, note, *options = item
        remove_background = options[0] if options else True
        manifest.append(process_raster(source, png_local / filename, note, remove_background))
    # The vector is kept below; the clean current site PNG is more suitable for
    # a light card than a browser screenshot of the SVG.

    # The original file contains two separate marks. Split them without redrawing.
    combined = RAW_LOCAL / "4,5.png"
    for key, image in split_combined(combined):
        destination = png_local / f"14-{key}.png"
        prepared, background_removed = normalize(image, remove_background=False)
        destination.parent.mkdir(parents=True, exist_ok=True)
        prepared.save(destination, format="PNG", optimize=True)
        manifest.append({
            "source": str(combined),
            "output": str(destination.relative_to(OUTPUT)).replace("\\", "/"),
            "type": "png",
            "original_size": [image.width, image.height],
            "prepared_size": [prepared.width, prepared.height],
            "background_removed": background_removed,
            "note": "Split from the combined source file without redraw.",
        })

    manifest.append(copy_vector(OFFICIAL_LOCAL / "02-afam.svg", svg_local / "02-afam.svg", "Untouched official vector source."))
    manifest.append(copy_vector(OFFICIAL_LOCAL / "07-ziphouse.svg", svg_local / "07-ziphouse.svg", "Untouched official vector source."))
    manifest.append(copy_vector(OFFICIAL_LOCAL / "14-artcor-official-white.svg", svg_local / "14-artcor-official-white.svg", "Untouched official white vector source; use on dark background."))

    transparent_fintech = png_local / "transparent" / "06-fintech-hub.png"
    manifest.append(process_raster(
        OFFICIAL_LOCAL / "06-fintech-hub-official-white.png",
        transparent_fintech,
        "Untouched official white mark on transparency; use on a dark surface.",
        remove_background=False,
    ))
    manifest.append(make_dark_variant(transparent_fintech, png_local / "06-fintech-hub.png"))

    OUTPUT.mkdir(parents=True, exist_ok=True)
    (OUTPUT / "manifest.json").write_text(json.dumps(manifest, ensure_ascii=False, indent=2), encoding="utf-8")
    png_files = sorted(path for path in (OUTPUT / "png").rglob("*.png") if "transparent" not in path.parts)
    make_contact_sheet(png_files, OUTPUT / "preview.png")

    print(json.dumps({"count": len(manifest), "output": str(OUTPUT), "png_count": len(png_files)}, ensure_ascii=False))


if __name__ == "__main__":
    main()
