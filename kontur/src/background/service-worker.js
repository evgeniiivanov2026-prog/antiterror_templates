import { loadArticle } from "./article.js";
import { classifyWithModel, testCloud } from "./model.js";
import {
  blendAssessment,
  cacheKey,
  sanitizeSettings,
  scoreAssessment,
  snapshotFrom,
} from "../shared/engine.js";

const TTL_OK = 6 * 60 * 60 * 1000;
const TTL_FAIL = 15 * 60 * 1000;
const memory = new Map();

async function readConfig() {
  const bag = await chrome.storage.local.get(["settings", "secret"]);
  return {
    settings: sanitizeSettings(bag.settings),
    secret: {
      geminiKey: typeof bag.secret?.geminiKey === "string" ? bag.secret.geminiKey.trim() : "",
    },
  };
}

async function readCache(url) {
  const key = `k:${cacheKey(url)}`;
  const hit = memory.get(key);
  if (hit && hit.expires > Date.now()) return hit.result;
  try {
    const bag = await chrome.storage.session.get(key);
    const row = bag[key];
    if (row && row.expires > Date.now()) {
      memory.set(key, row);
      return row.result;
    }
  } catch {
    /* session storage can be unavailable during early startup */
  }
  return null;
}

async function writeCache(url, result) {
  const key = `k:${cacheKey(url)}`;
  const ok = result.signals?.find((signal) => signal.id === "page")?.score != null || result.engine !== "signals";
  const row = { expires: Date.now() + (ok ? TTL_OK : TTL_FAIL), result };
  memory.set(key, row);
  if (memory.size > 80) {
    const oldest = memory.keys().next().value;
    memory.delete(oldest);
  }
  try {
    await chrome.storage.session.set({ [key]: row });
  } catch {
    /* the in-memory copy still serves this browser session */
  }
}

async function bumpScans() {
  const bag = await chrome.storage.local.get("stats");
  const stats = bag.stats || { scans: 0 };
  stats.scans += 1;
  await chrome.storage.local.set({ stats });
}

function post(port, message) {
  try {
    port.postMessage(message);
  } catch {
    /* the hover already ended */
  }
}

chrome.runtime.onInstalled.addListener(async () => {
  const bag = await chrome.storage.local.get("settings");
  if (!bag.settings) {
    await chrome.storage.local.set({ settings: sanitizeSettings({}), secret: { geminiKey: "" } });
  }
});

chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
  if (!message || typeof message !== "object") return false;
  if (message.type === "clear-cache") {
    memory.clear();
    chrome.storage.session.clear().then(() => sendResponse({ ok: true })).catch(() => sendResponse({ ok: true }));
    return true;
  }
  if (message.type === "test-cloud") {
    readConfig()
      .then(({ settings, secret }) => testCloud(settings, secret))
      .then(sendResponse)
      .catch((error) => sendResponse({ ok: false, message: String(error?.message || error) }));
    return true;
  }
  return false;
});

chrome.runtime.onConnect.addListener((port) => {
  if (port.name !== "kontur") return;
  const controller = new AbortController();
  let bumped = false;
  port.onDisconnect.addListener(() => controller.abort());
  port.onMessage.addListener(async (message) => {
    const token = message?.token;
    const url = message?.url;
    if (!token || !url) return;
    const send = (result) => {
      post(port, { token, result });
      if (!bumped) {
        bumped = true;
        bumpScans().catch(() => {});
      }
    };
    try {
      const cached = await readCache(url);
      if (controller.signal.aborted) return;
      if (cached) {
        send(cached);
        return;
      }
      const anchorText = String(message.anchorText || "").slice(0, 280);
      const fast = scoreAssessment({ url, anchorText });
      send(fast);

      const fetchSignal = AbortSignal.any([controller.signal, AbortSignal.timeout(4500)]);
      let page = { pageStatus: "error", finalUrl: url };
      try {
        page = await loadArticle(url, fetchSignal);
      } catch (error) {
        if (error?.name === "AbortError") throw error;
        page = { pageStatus: "error", finalUrl: url };
      }
      if (controller.signal.aborted) return;

      let full = scoreAssessment({
        url: page.finalUrl || url,
        requestedUrl: url,
        anchorText,
        title: page.title,
        description: page.description,
        articleText: page.text,
        author: page.author,
        published: page.published,
        pageStatus: page.pageStatus,
      });
      send(full);

      const { settings, secret } = await readConfig();
      if (controller.signal.aborted) return;
      const modelSignal = AbortSignal.any([controller.signal, AbortSignal.timeout(8000)]);
      let model = null;
      try {
        model = await classifyWithModel(snapshotFrom({
          url: page.finalUrl || url,
          anchorText,
          title: page.title,
          description: page.description,
          articleText: page.text,
          author: page.author,
          published: page.published,
        }, full), settings, secret, modelSignal);
      } catch (error) {
        if (error?.name === "AbortError") throw error;
        model = null;
      }
      if (controller.signal.aborted) return;
      if (model) {
        full = blendAssessment(full, model);
        send(full);
      }
      await writeCache(url, full);
    } catch (error) {
      if (error?.name === "AbortError") return;
      console.warn("[Контур]", error);
    }
  });
});
