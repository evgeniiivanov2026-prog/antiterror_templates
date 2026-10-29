import { decodeEntities } from "./meta.js";

const NOISE = /<(script|style|noscript|svg|iframe)\b[^>]*>[\s\S]*?<\/\1>/gi;
const CHROME = /<(nav|footer|form|aside)\b[^>]*>[\s\S]*?<\/\1>/gi;

export function extractArticleFromHtml(html) {
  const prepared = String(html || "").slice(0, 180_000).replace(NOISE, " ");
  const article = longestRegion(prepared, "article");
  const main = longestRegion(prepared, "main");
  const bodyMatch = prepared.match(/<body\b[^>]*>([\s\S]*?)<\/body>/i);
  const body = tagsToText(bodyMatch ? bodyMatch[1] : prepared);
  const text = article.length >= 200
    ? article
    : (main.length >= 200 ? main : (article.length >= 80 ? article : body));
  return text.slice(0, 8000);
}

export function extractArticleFromDocument(doc) {
  if (!doc?.body || typeof doc.createTreeWalker !== "function") return "";
  const article = textOf(doc, doc.querySelector("article"));
  if (article.length >= 200) return article.slice(0, 8000);
  const main = textOf(doc, doc.querySelector("main") || doc.querySelector("[role='main']"));
  if (main.length >= 200) return main.slice(0, 8000);
  const body = textOf(doc, doc.body);
  const best = [article, main, body].sort((a, b) => b.length - a.length)[0] || "";
  return best.slice(0, 8000);
}

function longestRegion(html, tag) {
  const re = new RegExp(`<${tag}\\b[^>]*>([\\s\\S]*?)<\\/${tag}>`, "gi");
  let best = "";
  for (const match of html.matchAll(re)) {
    const text = tagsToText(match[1]);
    if (text.length > best.length) best = text;
  }
  return best;
}

function tagsToText(html) {
  const stripped = String(html || "")
    .replace(NOISE, " ")
    .replace(CHROME, " ")
    .replace(/<br\s*\/?>/gi, "\n")
    .replace(/<\/(p|div|li|h[1-6]|blockquote|section)>/gi, "\n")
    .replace(/<[^>]+>/g, " ");
  return decodeEntities(stripped).replace(/\s+/g, " ").trim();
}

function textOf(doc, root) {
  if (!root) return "";
  const chunks = [];
  const walker = doc.createTreeWalker(root, 4, {
    acceptNode(node) {
      const parent = node.parentElement;
      if (!parent?.closest) return 2;
      if (parent.closest("script,style,noscript,svg,iframe,nav,footer,form,aside")) return 2;
      return node.nodeValue && node.nodeValue.trim() ? 1 : 2;
    },
  });
  while (walker.nextNode()) chunks.push(walker.currentNode.nodeValue);
  return chunks.join(" ").replace(/\s+/g, " ").trim();
}
