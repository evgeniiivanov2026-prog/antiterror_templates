import { attachHover } from "./hover.js";
import { extractArticleFromDocument } from "../lib/article.js";
import { createLens } from "../ui/card.js";
import { evaluateLink, shouldInspect } from "../lib/evaluate.js";
import { present } from "../lib/present.js";
import { applyText } from "../lib/text.js";
import { DEFAULT_SETTINGS, SETTINGS_KEY } from "../lib/defaults.js";
import { applyMemory, emptyMemory, MEMORY_KEY, normalizeMemory } from "../lib/learn.js";

if (document.documentElement.dataset.aurisPreview !== "true") {
  let settings = { ...DEFAULT_SETTINGS };
  let memory = emptyMemory();

  chrome.storage.local.get([SETTINGS_KEY, MEMORY_KEY], (data) => {
    settings = { ...DEFAULT_SETTINGS, ...(data?.[SETTINGS_KEY] || {}) };
    memory = normalizeMemory(data?.[MEMORY_KEY]);
  });

  const hover = attachHover({
    document,
    createLens: (doc) => createLens(doc, { cssHref: chrome.runtime.getURL("ui/card.css") }),
    evaluate: evaluateLink,
    applyText,
    shouldInspect,
    present,
    getSettings: () => settings,
    finish: (result) => {
      if (!result || result.memoryApplied) return result;
      return applyMemory(result, memory, { enabled: settings.learn !== false });
    },
    teach: (result, verdict) => new Promise((resolve) => {
      try {
        chrome.runtime.sendMessage({
          type: "auris:learn",
          domain: result?.domain || "",
          href: result?.href || "",
          title: result?.title || result?.domain || "",
          verdict,
          result,
        }, (response) => {
          if (chrome.runtime.lastError || !response?.ok) resolve(null);
          else {
            if (response.memory) memory = normalizeMemory(response.memory);
            resolve(response.result || null);
          }
        });
      } catch {
        resolve(null);
      }
    }),
    requestEnrich: (payload) => new Promise((resolve) => {
      const timer = setTimeout(() => resolve(null), 16000);
      try {
        chrome.runtime.sendMessage({ type: "auris:enrich", ...payload }, (response) => {
          clearTimeout(timer);
          if (chrome.runtime.lastError || !response?.ok) resolve(null);
          else resolve(response.result);
        });
      } catch {
        clearTimeout(timer);
        resolve(null);
      }
    }),
  });

  document.addEventListener("mouseup", (event) => {
    if (hover.owns(event.target)) return;
    setTimeout(() => hover.considerSelection(), 40);
  });

  chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
    if (message?.type !== "auris:check") return;
    const mode = message.mode === "selection" ? "selection" : "page";
    const text = mode === "selection"
      ? String(message.selectionText || document.getSelection?.() || "").replace(/\s+/g, " ").trim()
      : extractArticleFromDocument(document);
    hover.showCheck({
      mode,
      text,
      href: location.href,
      title: document.title || "",
    });
    sendResponse({ ok: true });
  });

  chrome.storage.onChanged.addListener((changes, area) => {
    if (area !== "local") return;
    if (changes[SETTINGS_KEY]?.newValue) {
      settings = { ...DEFAULT_SETTINGS, ...changes[SETTINGS_KEY].newValue };
      if (!settings.enabled) hover.hide(true);
    }
    if (changes[MEMORY_KEY]) memory = normalizeMemory(changes[MEMORY_KEY].newValue);
  });
}
