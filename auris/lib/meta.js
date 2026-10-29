export function extractMeta(html) {
  const slice = String(html || "").slice(0, 180_000);
  const titleTag = slice.match(/<title[^>]*>([^<]{1,300})<\/title>/i);
  return {
    title: readMeta(slice, "og:title") || decodeEntities(titleTag?.[1] || ""),
    description: readMeta(slice, "og:description") || readMeta(slice, "description"),
    siteName: readMeta(slice, "og:site_name"),
  };
}

function readMeta(html, key) {
  const escaped = key.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const patterns = [
    new RegExp(`<meta\\b[^>]{0,500}\\b(?:property|name)\\s*=\\s*["']${escaped}["'][^>]{0,500}>`, "i"),
    new RegExp(`<meta\\b[^>]{0,500}\\b(?:property|name)\\s*=\\s*["']${escaped}["'][^>]{0,500}/>`, "i"),
  ];
  for (const pattern of patterns) {
    const tag = html.match(pattern);
    if (!tag) continue;
    const content = tag[0].match(/\bcontent\s*=\s*["']([^"']*)["']/i);
    if (content) return decodeEntities(content[1]);
  }
  return "";
}

export function decodeEntities(value) {
  return String(value || "")
    .replace(/&nbsp;/g, " ")
    .replace(/&amp;/g, "&")
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">")
    .replace(/&quot;/g, "\"")
    .replace(/&#039;|&apos;/g, "'")
    .replace(/&#(\d+);/g, (_, code) => safeCodePoint(Number(code)))
    .replace(/&#x([0-9a-f]+);/gi, (_, code) => safeCodePoint(parseInt(code, 16)))
    .replace(/\s+/g, " ")
    .trim();
}

function safeCodePoint(code) {
  if (!Number.isFinite(code) || code < 0 || code > 0x10ffff) return "";
  try {
    return String.fromCodePoint(code);
  } catch {
    return "";
  }
}
