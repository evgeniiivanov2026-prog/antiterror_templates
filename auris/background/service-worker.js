import { assessWithAI, diagnoseAI } from "./ai.js";
import { extractArticleFromHtml } from "../lib/article.js";
import { applyMeta, cacheKey, evaluateLink } from "../lib/evaluate.js";
import { extractMeta } from "../lib/meta.js";
import { fuse } from "../lib/model.js";
import { buildUserPrompt, SYSTEM_PROMPT } from "../lib/prompt.js";
import { applyText, assessPlain, MIN_ARTICLE_CHARS } from "../lib/text.js";
import { DEFAULT_SECRET, DEFAULT_SETTINGS, HISTORY_KEY, SECRET_KEY, SETTINGS_KEY } from "../lib/defaults.js";

const cache = new Map();
const inflight = new Map();
const TTL = 20 * 60 * 1000;

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (sender.id !== chrome.runtime.id || !message?.type) return false;
  if (message.type === "auris:enrich") {
    enrich(message, sender).then(sendResponse).catch((error) => {
      sendResponse({ ok: false, error: error instanceof Error ? error.message : "enrich failed" });
    });
    return true;
  }
  if (message.type === "auris:assess") {
    assess(message).then(sendResponse).catch((error) => {
      sendResponse({ ok: false, error: error instanceof Error ? error.message : "assess failed" });
    });
    return true;
  }
  if (message.type === "auris:test-ai") {
    testAI().then(sendResponse).catch((error) => {
      sendResponse({ ok: false, message: error instanceof Error ? error.message : "test failed" });
    });
    return true;
  }
  return false;
});

chrome.storage.onChanged.addListener((changes, area) => {
  if (area === "local" && (changes[SETTINGS_KEY] || changes[SECRET_KEY])) cache.clear();
});

chrome.runtime.onInstalled.addListener(installMenus);
chrome.runtime.onStartup.addListener(installMenus);
installMenus();

chrome.contextMenus.onClicked.addListener((info, tab) => {
  if (!tab?.id) return;
  const mode = info.menuItemId === "auris-selection" ? "selection" : "page";
  chrome.tabs.sendMessage(tab.id, {
    type: "auris:check",
    mode,
    selectionText: info.selectionText || "",
  });
});

function installMenus() {
  chrome.contextMenus.removeAll(() => {
    chrome.contextMenus.create({
      id: "auris-page",
      title: "AURIS: проверить эту страницу",
      contexts: ["page"],
    });
    chrome.contextMenus.create({
      id: "auris-selection",
      title: "AURIS: проверить выделенное",
      contexts: ["selection"],
    });
  });
}

async function assess(message) {
  const controller = new AbortController();
  const signal = AbortSignal.any([controller.signal, AbortSignal.timeout(15000)]);
  return enrichLive(message, signal, controller.signal);
}

async function enrich(message, sender) {
  const tabId = sender.tab?.id ?? 0;
  inflight.get(tabId)?.abort();
  const controller = new AbortController();
  inflight.set(tabId, controller);
  const signal = AbortSignal.any([controller.signal, AbortSignal.timeout(15000)]);

  try {
    return await enrichLive(message, signal, controller.signal);
  } catch (error) {
    if (controller.signal.aborted || signal.aborted) return { ok: false, stale: true };
    throw error;
  } finally {
    if (inflight.get(tabId) === controller) inflight.delete(tabId);
  }
}

async function enrichLive(message, signal, superseded) {
  const settings = await getSettings();
  const secret = await getSecret();
  const mode = ["page", "selection", "paste", "hover"].includes(message.mode) ? message.mode : "hover";
  const href = String(message.href || "");
  const anchorText = String(message.anchorText || message.title || "").slice(0, 400);
  const givenText = String(message.text || "").slice(0, 8000);
  const key = cacheKey(href);
  if (mode === "hover") {
    const cached = cache.get(key);
    if (cached && Date.now() - cached.at < TTL) return { ok: true, result: cached.result, cached: true };
  }
  if (superseded.aborted) return { ok: false, stale: true };

  let local;
  let description = "";
  let body = givenText;
  if (mode === "hover") {
    local = evaluateLink({ href, anchorText });
    if (settings.deepFetch) {
      try {
        const fetched = await fetchPreview(href, signal);
        if (fetched.finalUrl && cacheKey(fetched.finalUrl) !== key) {
          const redirected = evaluateLink({ href: fetched.finalUrl, anchorText });
          redirected.reasons = [
            `Ссылка раскрывается как ${redirected.domain}.`,
            ...redirected.reasons,
          ].slice(0, 6);
          local = redirected;
        }
        description = fetched.meta.description || "";
        if (fetched.meta.title || fetched.meta.description) local = applyMeta(local, fetched.meta);
        body = extractArticleFromHtml(fetched.html);
        if (body) local = applyText(local, body, { mode: "hover" });
      } catch (error) {
        if (superseded.aborted) throw error;
        local = {
          ...local,
          reasons: [...local.reasons, "Текст страницы прочитать не удалось — оценка по адресу и тексту ссылки."].slice(0, 6),
        };
      }
    }
  } else {
    local = assessPlain(givenText, { href, title: anchorText, mode });
  }

  const thin = mode === "page"
    ? givenText.trim().length < MIN_ARTICLE_CHARS
    : (mode !== "hover" && givenText.trim().length < 80);
  let result = local;
  if (settings.aiMode !== "local" && !thin) {
    if (superseded.aborted) return { ok: false, stale: true };
    const ai = await assessWithAI({
      mode: settings.aiMode,
      secret,
      system: SYSTEM_PROMPT,
      prompt: buildUserPrompt({ ...local, anchorText, description, body }),
      signal,
    });
    if (superseded.aborted) return { ok: false, stale: true };
    if (ai) result = fuse(local, ai);
    else if (settings.aiMode === "cloud" || settings.aiMode === "ondevice") {
      result = {
        ...local,
        reasons: [...local.reasons, "Модель не ответила — оставлена локальная оценка."].slice(0, 6),
      };
    }
  }

  if (mode === "hover") cache.set(key, { at: Date.now(), result });
  if (result.href) await pushHistory(result);
  return { ok: true, result };
}

async function testAI() {
  const secret = await getSecret();
  const settings = await getSettings();
  const diagnosis = await diagnoseAI(secret);
  const ai = await assessWithAI({
    mode: settings.aiMode === "local" ? "auto" : settings.aiMode,
    secret,
    system: SYSTEM_PROMPT,
    prompt: buildUserPrompt({
      href: "https://www.reuters.com/world/example",
      domain: "reuters.com",
      anchorText: "Reuters: cargo ships reroute after a storm warning",
      title: "Cargo ships reroute after a storm warning",
      description: "A wire report about shipping routes.",
      score: 82,
      verdict: "reliable",
    }),
  });
  if (!ai) {
    return {
      ok: false,
      diagnosis,
      message: "Модель не ответила. Скачайте Gemini Nano в Chrome или сохраните ключ облака.",
    };
  }
  return {
    ok: true,
    diagnosis,
    engine: ai.engine,
    summary: ai.summary,
    message: ai.engine === "ondevice" ? "Ответил встроенный Gemini Nano." : "Ответило облако.",
  };
}

async function fetchPreview(href, signal) {
  const timeout = AbortSignal.timeout(5000);
  const combined = signal ? AbortSignal.any([signal, timeout]) : timeout;
  const response = await fetch(href, {
    method: "GET",
    redirect: "follow",
    credentials: "omit",
    signal: combined,
    headers: { Accept: "text/html,application/xhtml+xml;q=0.9,*/*;q=0.1" },
  });
  const finalUrl = response.url || href;
  const type = response.headers.get("content-type") || "";
  if (type && !/text\/html|application\/xhtml\+xml|text\/plain/i.test(type)) {
    return { finalUrl, meta: { title: "", description: "", siteName: "" }, html: "" };
  }
  const html = await readLimited(response, 150_000);
  return { finalUrl, meta: extractMeta(html), html };
}

async function readLimited(response, limit) {
  if (!response.body?.getReader) {
    const text = await response.text();
    return text.slice(0, limit);
  }
  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let received = 0;
  let html = "";
  while (received < limit) {
    const chunk = await reader.read();
    if (chunk.done) break;
    received += chunk.value.byteLength;
    html += decoder.decode(chunk.value, { stream: true });
  }
  try { await reader.cancel(); } catch { /* already closed */ }
  return html.slice(0, limit);
}

async function pushHistory(result) {
  const data = await chrome.storage.local.get(HISTORY_KEY);
  const history = Array.isArray(data[HISTORY_KEY]) ? data[HISTORY_KEY] : [];
  const item = {
    href: result.href,
    domain: result.domain,
    title: result.title || result.domain,
    score: result.score,
    verdict: result.verdict,
    engine: result.engine,
    at: Date.now(),
  };
  const next = [item, ...history.filter((entry) => entry.href !== item.href)].slice(0, 12);
  await chrome.storage.local.set({ [HISTORY_KEY]: next });
}

async function getSettings() {
  const data = await chrome.storage.local.get(SETTINGS_KEY);
  return { ...DEFAULT_SETTINGS, ...(data[SETTINGS_KEY] || {}) };
}

async function getSecret() {
  const data = await chrome.storage.local.get(SECRET_KEY);
  return { ...DEFAULT_SECRET, ...(data[SECRET_KEY] || {}) };
}
