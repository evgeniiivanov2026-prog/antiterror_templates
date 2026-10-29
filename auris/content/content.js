import { attachHover } from "./hover.js";
import { extractArticleFromDocument } from "../lib/article.js";
import { createLens } from "../ui/card.js";
import { evaluateLink, shouldInspect } from "../lib/evaluate.js";
import { present } from "../lib/present.js";
import { applyText } from "../lib/text.js";
import { DEFAULT_SETTINGS, SETTINGS_KEY } from "../lib/defaults.js";

if (document.documentElement.dataset.aurisPreview !== "true") {
  let settings = { ...DEFAULT_SETTINGS };

  chrome.storage.local.get(SETTINGS_KEY, (data) => {
    settings = { ...DEFAULT_SETTINGS, ...(data?.[SETTINGS_KEY] || {}) };
  });

  const hover = attachHover({
    document,
    createLens: (doc) => createLens(doc, { cssHref: chrome.runtime.getURL("ui/card.css") }),
    evaluate: evaluateLink,
    applyText,
    shouldInspect,
    present,
    getSettings: () => settings,
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
    if (area === "local" && changes[SETTINGS_KEY]?.newValue) {
      settings = { ...DEFAULT_SETTINGS, ...changes[SETTINGS_KEY].newValue };
      if (!settings.enabled) hover.hide(true);
    }
  });
}
