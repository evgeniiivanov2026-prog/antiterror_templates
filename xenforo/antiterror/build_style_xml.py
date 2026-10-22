#!/usr/bin/env python3
"""Собирает style.xml из extra.less и проверяет, что импортный файл целостен."""

from __future__ import annotations

import json
import subprocess
import sys
import xml.etree.ElementTree as ET
from pathlib import Path
from xml.sax.saxutils import escape

ROOT = Path(__file__).resolve().parent
LESS = ROOT / "extra.less"
XML = ROOT / "style.xml"
PREVIEW_CSS = ROOT / "preview" / "extra.css"

PROPERTIES = [
    ("paletteColor1", "palette", "Color 1", "#152033", "color", "primary", 1000),
    ("paletteColor2", "palette", "Color 2", "#5c6774", "color", "primary", 1100),
    ("paletteColor3", "palette", "Color 3", "#8a8174", "color", "primary", 1200),
    ("paletteColor4", "palette", "Color 4", "#ddd6c8", "color", "primary", 1300),
    ("paletteColor5", "palette", "Color 5", "#efeae1", "color", "primary", 1400),
    ("paletteNeutral1", "palette", "Neutral 1", "#fbf9f4", "color", "neutral", 2000),
    ("paletteNeutral2", "palette", "Neutral 2", "#f7f4ee", "color", "neutral", 2100),
    ("paletteNeutral3", "palette", "Neutral 3", "#efeae1", "color", "neutral", 2200),
    ("paletteAccent1", "palette", "Accent 1", "#f6f1e7", "color", "accent", 3000),
    ("paletteAccent2", "palette", "Accent 2", "#9a7b3c", "color", "accent", 3100),
    ("paletteAccent3", "palette", "Accent 3", "#8e2433", "color", "accent", 3200),
    ("pageBg", "color", "Page background color", "#efeae1", "color", "contentBackground", 100),
    ("textColor", "color", "Text color", "#152033", "color", "text", 200),
    ("textColorMuted", "color", "Muted text color", "#5c6774", "color", "text", 210),
    ("textColorDimmed", "color", "Dimmed text color", "#8a8174", "color", "text", 220),
    ("textColorEmphasized", "color", "Emphasized text color", "#0e1626", "color", "text", 230),
    ("textColorFeature", "color", "Feature text color", "#f6f1e7", "color", "text", 240),
    ("linkColor", "color", "Link color", "#6e1b28", "color", "links", 300),
    ("linkHoverColor", "color", "Link hover color", "#8e2433", "color", "links", 310),
    ("contentBg", "color", "Content background color", "#fbf9f4", "color", "contentBackground", 400),
    ("contentAltBg", "color", "Alternate content background color", "#f7f4ee", "color", "contentBackground", 410),
    ("contentHighlightBg", "color", "Highlighted content background color", "#f6f1e7", "color", "contentBackground", 420),
    ("contentAccentBg", "color", "Accented content background color", "#162033", "color", "contentBackground", 430),
    ("chromeBg", "color", "Chrome background color", "#0e1626", "color", "chrome", 500),
    ("chromeTextColor", "color", "Chrome text color", "#f6f1e7", "color", "chrome", 510),
    ("subNavBg", "color", "Sub-navigation background", "#162033", "color", "chrome", 520),
    ("borderColor", "color", "Border color", "#ddd6c8", "color", "borders", 600),
    ("borderColorHeavy", "color", "Heavy border color", "#cfc6b6", "color", "borders", 610),
    ("borderColorLight", "color", "Light border color", "#efeae1", "color", "borders", 620),
    ("borderColorFaint", "color", "Faint border color", "#f3eee4", "color", "borders", 630),
    ("borderColorAccentContent", "color", "Accent content border color", "#9a7b3c", "color", "borders", 640),
    ("borderColorFeature", "color", "Feature border color", "#e2c78a", "color", "borders", 650),
    ("borderColorHighlight", "color", "Highlight border color", "#9a7b3c", "color", "borders", 660),
    ("buttonBg", "color", "Button background", "#f7f4ee", "color", "buttons", 700),
    ("buttonTextColor", "color", "Button text color", "#152033", "color", "buttons", 710),
    ("buttonPrimaryBg", "color", "Primary button background", "#8e2433", "color", "buttons", 720),
    ("buttonCtaBg", "color", "Call-to-action button background", "#0e1626", "color", "buttons", 730),
    ("inlineModHighlightColor", "color", "Inline moderation highlight", "rgba(154, 123, 60, .16)", "color", "misc", 800),
    ("selectedItemBgColor", "color", "Selected item background", "#f6f1e7", "color", "misc", 810),
    ("selectedItemColor", "color", "Selected item text", "#0e1626", "color", "misc", 820),
    ("successBg", "color", "Success message background", "#f3f7f4", "color", "messages", 900),
    ("successColor", "color", "Success message text", "#152033", "color", "messages", 910),
    ("successFeatureColor", "color", "Success feature color", "#2f6b4f", "color", "messages", 920),
    ("warningBg", "color", "Warning message background", "#fbf7ee", "color", "messages", 930),
    ("warningColor", "color", "Warning message text", "#152033", "color", "messages", 940),
    ("warningFeatureColor", "color", "Warning feature color", "#9a7b3c", "color", "messages", 950),
    ("errorBg", "color", "Error message background", "#fbf4f4", "color", "messages", 960),
    ("errorColor", "color", "Error message text", "#152033", "color", "messages", 970),
    ("errorFeatureColor", "color", "Error feature color", "#8e2433", "color", "messages", 980),
    ("styleType", "color", "Style type", "light", "string", "", 50),
    ("fontFamilyBody", "fonts", "Body font", 'Manrope, "Segoe UI", "Helvetica Neue", Arial, sans-serif', "string", "", 100),
    ("fontFamilyUi", "fonts", "UI font", 'Manrope, "Segoe UI", "Helvetica Neue", Arial, sans-serif', "string", "", 110),
    ("fontFamilyCode", "fonts", "Code font", '"IBM Plex Mono", ui-monospace, Menlo, Consolas, monospace', "string", "", 120),
    ("fontSizeNormal", "fonts", "Normal font size", "16px", "unit", "", 200),
    ("borderRadiusSmall", "border", "Small border radius", "0px", "unit", "", 100),
    ("borderRadiusMedium", "border", "Medium border radius", "0px", "unit", "", 110),
    ("borderRadiusLarge", "border", "Large border radius", "0px", "unit", "", 120),
    ("avatarBorderRadius", "avatar", "Avatar border radius", "0px", "unit", "", 100),
    ("pageWidthMax", "page", "Maximum page width", "1180px", "unit", "", 100),
]


def property_xml(row: tuple) -> str:
    name, group, title, value, value_type, value_group, order = row
    params = "hidePalette=true" if group == "palette" else ""
    return (
        f'    <property property_name="{escape(name)}" group_name="{escape(group)}" '
        f'title="{escape(title)}" description="" property_type="value" '
        f'value_type="{escape(value_type)}" depends_on="" value_group="{escape(value_group)}" '
        f'display_order="{order}" addon_id="">\n'
        f"      <value_parameters>{escape(params)}</value_parameters>\n"
        f"      <value>{escape(json.dumps(value, ensure_ascii=False))}</value>\n"
        f"    </property>"
    )


def build_xml(less: str) -> str:
    if "]]>" in less:
        raise SystemExit("extra.less contains a CDATA terminator")
    description = "Тёмный хром, бумажная колонка, латунь и малиновый сигнал действия."
    props = "\n".join(property_xml(row) for row in PROPERTIES)
    return (
        '<?xml version="1.0" encoding="utf-8"?>\n'
        '<style title="Контур — Антитеррор" '
        f'description="{escape(description)}" '
        'user_selectable="1" base_version_id="2021270" export_version="2">\n'
        "  <assets/>\n"
        "  <templates>\n"
        '    <template title="extra.less" type="public" addon_id="" '
        'version_id="1000010" version_string="1.0.0"><![CDATA['
        f"{less}]]></template>\n"
        "  </templates>\n"
        "  <properties>\n"
        f"{props}\n"
        "  </properties>\n"
        "</style>\n"
    )


def compile_preview(less: str) -> None:
    source = less + "\n@at-font-path: '../fonts';\n"
    cmd = ["npx", "--yes", "-p", "less", "lessc", "-"]
    result = subprocess.run(cmd, input=source, text=True, capture_output=True, check=False)
    if result.returncode != 0:
        sys.stderr.write(result.stderr)
        raise SystemExit(result.returncode)
    banner = "/* Превью-сборка. В XenForo вставляют extra.less или импортируют style.xml, не этот файл. */\n"
    PREVIEW_CSS.write_text(banner + result.stdout, encoding="utf-8")


def main() -> None:
    less = LESS.read_text(encoding="utf-8")
    if not less.endswith("\n"):
        less += "\n"
    xml = build_xml(less)
    XML.write_text(xml, encoding="utf-8")
    root = ET.fromstring(xml)
    template = root.find("./templates/template")
    assert template is not None and template.text
    assert "@at-ink:" in template.text
    assert len(root.findall("./properties/property")) == len(PROPERTIES)
    compile_preview(less)
    print(f"wrote {XML.name} and {PREVIEW_CSS.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
