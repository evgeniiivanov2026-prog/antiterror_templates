import { attachHover } from "./hover.js";
import { createLens } from "../ui/card.js";
import { evaluateLink, shouldInspect } from "../lib/evaluate.js";
import { present } from "../lib/present.js";
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
    shouldInspect,
    present,
    getSettings: () => settings,
    requestEnrich: (payload) => new Promise((resolve) => {
      const timer = setTimeout(() => resolve(null), 10000);
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

  chrome.storage.onChanged.addListener((changes, area) => {
    if (area === "local" && changes[SETTINGS_KEY]?.newValue) {
      settings = { ...DEFAULT_SETTINGS, ...changes[SETTINGS_KEY].newValue };
      if (!settings.enabled) hover.hide(true);
    }
  });
}
