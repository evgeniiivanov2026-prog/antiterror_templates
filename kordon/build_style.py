#!/usr/bin/env python3
"""Собирает XML стилей XenForo из kordon/extra.css."""

from pathlib import Path
import zipfile

ROOT = Path(__file__).resolve().parent.parent
CSS_PATH = ROOT / "kordon" / "extra.css"
ASSET_FROM = 'url("/styles/kordon/'
ASSET_TO = 'url("{{ asset(\'kordon\') }}/'

LIGHT = {
    "title": "Кордон",
    "file": "style.xml",
    "zip": "kordon.zip",
    "description": "Гражданский форум памяти и профилактики: бумага, флотский, сигнальный. Дочерний стиль Default для XenForo 2.2 и 2.3. Не является оформлением государственного органа.",
    "palette": {
        "paletteColor1": "#172033",
        "paletteColor2": "#3e4858",
        "paletteColor3": "#5c6572",
        "paletteColor4": "#a39e93",
        "paletteColor5": "#e4dfd4",
        "paletteAccent1": "#861f32",
        "paletteAccent2": "#16233a",
        "paletteAccent3": "#6f5430",
        "paletteNeutral1": "#e7e1d6",
        "paletteNeutral2": "#f3f0e9",
        "paletteNeutral3": "#fffdfa",
    },
}

DARK = {
    "title": "Кордон · ночь",
    "file": "style-dark.xml",
    "zip": "kordon-night.zip",
    "description": "Ночная вариация «Кордона»: тёмная бумага, светлый текст, тот же сигнальный акцент. Импортируйте рядом со светлым стилем, родителем оставьте Default.",
    "palette": {
        "paletteColor1": "#f4f0e8",
        "paletteColor2": "#c5cdd8",
        "paletteColor3": "#9aa3b2",
        "paletteColor4": "#5c6778",
        "paletteColor5": "#2a3548",
        "paletteAccent1": "#f0c2cc",
        "paletteAccent2": "#e0c48a",
        "paletteAccent3": "#9db8aa",
        "paletteNeutral1": "#243044",
        "paletteNeutral2": "#182232",
        "paletteNeutral3": "#121a28",
    },
}

PALETTE_TITLES = {
    "paletteColor1": "Color 1",
    "paletteColor2": "Color 2",
    "paletteColor3": "Color 3",
    "paletteColor4": "Color 4",
    "paletteColor5": "Color 5",
    "paletteAccent1": "Accent 1",
    "paletteAccent2": "Accent 2",
    "paletteAccent3": "Accent 3",
    "paletteNeutral1": "Neutral 1",
    "paletteNeutral2": "Neutral 2",
    "paletteNeutral3": "Neutral 3",
}


def between(text, start, end):
    a = text.index(start)
    b = text.index(end, a) + len(end)
    return a, b


def css_for(mode, source):
    light_s, light_e = between(source, "/* KORDON-LIGHT-START */", "/* KORDON-LIGHT-END */")
    dark_s, dark_e = between(source, "/* KORDON-DARK-START */", "/* KORDON-DARK-END */")
    if mode == "light":
        css = source[:dark_s] + source[dark_e:]
    else:
        css = source[:light_s] + source[light_e:]
        css = css.replace("html.kordon-dark", ":root")
    if ASSET_FROM not in css:
        raise SystemExit("В extra.css нет путей /styles/kordon/")
    css = css.replace(ASSET_FROM, ASSET_TO)
    if "]]>" in css:
        raise SystemExit("CSS нельзя положить в CDATA: найдено ]]>")
    return css.strip() + "\n"


def properties(palette):
    chunks = []
    for index, (name, value) in enumerate(palette.items(), start=1):
        chunks.append(
            "    <property"
            f' property_name="{name}"'
            ' group_name="palette"'
            f' title="{PALETTE_TITLES[name]}"'
            ' description=""'
            ' property_type="value"'
            ' value_type="color"'
            ' depends_on=""'
            ' value_group=""'
            f' display_order="{index * 10}"'
            ' addon_id="">\n'
            f'      <value>"{value}"</value>\n'
            "    </property>"
        )
    return "\n".join(chunks)


def style_xml(meta, css):
    title = meta["title"].replace('"', "&quot;")
    description = (
        meta["description"]
        .replace("&", "&amp;")
        .replace('"', "&quot;")
        .replace("<", "&lt;")
    )
    return (
        '<?xml version="1.0" encoding="utf-8"?>\n'
        f'<style title="{title}" description="{description}"'
        ' user_selectable="1" base_version_id="2021670" export_version="2">\n'
        "  <assets>\n"
        '    <asset key="kordon" path="styles/kordon"/>\n'
        "  </assets>\n"
        "  <templates>\n"
        '    <template title="extra.less" type="public" addon_id="" version_id="1000070" version_string="1.0.0"><![CDATA[\n'
        f"{css}"
        "]]></template>\n"
        "  </templates>\n"
        "  <properties>\n"
        f"{properties(meta['palette'])}\n"
        "  </properties>\n"
        "</style>\n"
    )


def write_zip(path, xml_name, xml_text):
    asset_root = ROOT / "styles" / "kordon"
    with zipfile.ZipFile(path, "w", compression=zipfile.ZIP_DEFLATED) as archive:
        archive.writestr(xml_name if xml_name == "style.xml" else "style.xml", xml_text)
        for file in sorted(asset_root.rglob("*")):
            if file.is_file():
                archive.write(file, file.relative_to(ROOT).as_posix())


def main():
    source = CSS_PATH.read_text(encoding="utf-8")
    dist = ROOT / "kordon" / "dist"
    dist.mkdir(exist_ok=True)
    for meta in (LIGHT, DARK):
        css = css_for("light" if meta is LIGHT else "dark", source)
        xml = style_xml(meta, css)
        (ROOT / "kordon" / meta["file"]).write_text(xml, encoding="utf-8")
        write_zip(dist / meta["zip"], "style.xml", xml)
        print(meta["file"], len(xml))


if __name__ == "__main__":
    main()
