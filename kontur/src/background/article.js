const NAMED = {
  amp: "&",
  lt: "<",
  gt: ">",
  quot: '"',
  apos: "'",
  nbsp: " ",
  laquo: "«",
  raquo: "»",
  mdash: "—",
  ndash: "–",
  hellip: "…",
};

export function decodeEntities(value) {
  return String(value || "").replace(/&(#x?[0-9a-f]+|[a-z]+);/gi, (match, body) => {
    if (body[0] === "#") {
      const code = body[1] === "x" || body[1] === "X" ? Number.parseInt(body.slice(2), 16) : Number.parseInt(body.slice(1), 10);
      if (!Number.isFinite(code) || code < 0 || code > 0x10ffff) return match;
      try {
        return String.fromCodePoint(code);
      } catch {
        return match;
      }
    }
    return Object.prototype.hasOwnProperty.call(NAMED, body.toLowerCase()) ? NAMED[body.toLowerCase()] : match;
  });
}

function attr(tag, name) {
  const match = tag.match(new RegExp(`\\b${name}\\s*=\\s*(?:"([^"]*)"|'([^']*)'|([^\\s"'=<>]+))`, "i"));
  return match ? decodeEntities(match[1] ?? match[2] ?? match[3] ?? "") : "";
}

function metas(html) {
  const out = {};
  for (const tag of html.match(/<meta\b[^>]*>/gi) || []) {
    const key = (attr(tag, "property") || attr(tag, "name") || attr(tag, "itemprop")).toLowerCase();
    const content = attr(tag, "content");
    if (key && content && !out[key]) out[key] = content;
  }
  return out;
}

function flattenLd(data) {
  if (!data) return [];
  if (Array.isArray(data)) return data.flatMap(flattenLd);
  if (data["@graph"]) return flattenLd(data["@graph"]);
  return [data];
}

function personName(value) {
  if (!value) return "";
  if (typeof value === "string") return value;
  if (Array.isArray(value)) return personName(value[0]);
  if (typeof value === "object") return personName(value.name);
  return "";
}

function strip(html) {
  return decodeEntities(
    String(html || "")
      .replace(/<script[\s\S]*?<\/script>/gi, " ")
      .replace(/<style[\s\S]*?<\/style>/gi, " ")
      .replace(/<[^>]+>/g, " ")
      .replace(/\s+/g, " ")
      .trim(),
  );
}

export function extractArticle(html) {
  const source = String(html || "");
  const meta = metas(source);
  const titleTag = source.match(/<title[^>]*>([\s\S]*?)<\/title>/i);
  let title = "";
  let description = meta["og:description"] || meta.description || meta["twitter:description"] || "";
  let author = meta.author || meta["article:author"] || "";
  let published = meta["article:published_time"] || meta["datepublished"] || meta["publishdate"] || "";

  for (const block of source.matchAll(/<script[^>]*type=["']application\/ld\+json["'][^>]*>([\s\S]*?)<\/script>/gi)) {
    let data;
    try {
      data = JSON.parse(block[1].trim());
    } catch {
      continue;
    }
    for (const node of flattenLd(data)) {
      const type = node["@type"];
      const types = (Array.isArray(type) ? type : [type]).map((item) => String(item || ""));
      if (!types.some((item) => /Article|NewsArticle|Report/i.test(item))) continue;
      title = title || strip(node.headline || "");
      description = description || strip(node.description || "");
      author = author || strip(personName(node.author));
      published = published || node.datePublished || "";
    }
  }

  title = title || meta["og:title"] || meta["twitter:title"] || strip(titleTag?.[1] || "");

  const article = source.match(/<article\b[\s\S]*?<\/article>/i);
  const body = article ? article[0] : source;
  const paragraphs = [...body.matchAll(/<p\b[^>]*>([\s\S]*?)<\/p>/gi)]
    .map((match) => strip(match[1]))
    .filter((paragraph) => paragraph.length > 40);
  const text = paragraphs.slice(0, 6).join(" ").slice(0, 1500);
  const blocked = /just a moment|cf-browser-verification|attention required|подтвердите, что вы не робот/i.test(title);
  return {
    title: title.slice(0, 240),
    description: description.slice(0, 600),
    author: author.slice(0, 120),
    published: String(published || "").slice(0, 80),
    text,
    blocked,
  };
}

async function readLimited(response, max, signal) {
  if (!response.body?.getReader) {
    return (await response.text()).slice(0, max);
  }
  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let out = "";
  try {
    while (out.length < max) {
      if (signal?.aborted) {
        const error = new Error("Aborted");
        error.name = "AbortError";
        throw error;
      }
      const { done, value } = await reader.read();
      if (done) break;
      out += decoder.decode(value, { stream: true });
    }
  } finally {
    try {
      await reader.cancel();
    } catch {
      /* already closed */
    }
  }
  return out.slice(0, max);
}

export async function loadArticle(url, signal) {
  const response = await fetch(url, {
    redirect: "follow",
    credentials: "omit",
    signal,
    headers: { Accept: "text/html,application/xhtml+xml;q=0.9,*/*;q=0.1" },
  });
  const finalUrl = response.url || url;
  const type = (response.headers.get("content-type") || "").toLowerCase();
  if (!response.ok) return { pageStatus: "error", finalUrl, reason: String(response.status) };
  if (type && !/html|xml|text\/plain/.test(type)) {
    return { pageStatus: "skipped", finalUrl, reason: type.split(";")[0] };
  }
  const html = await readLimited(response, 350_000, signal);
  const extracted = extractArticle(html);
  if (extracted.blocked) return { pageStatus: "error", finalUrl, reason: "blocked" };
  return { pageStatus: "ok", finalUrl, ...extracted };
}
