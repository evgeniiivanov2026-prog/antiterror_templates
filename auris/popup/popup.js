import { VERDICT_LABEL } from "../lib/copy.js";
import { DEFAULT_SECRET, DEFAULT_SETTINGS, HISTORY_KEY, SECRET_KEY, SETTINGS_KEY, WELCOME_KEY } from "../lib/defaults.js";
import { MEMORY_KEY, normalizeMemory, TEACH_VERDICTS } from "../lib/learn.js";
import { present } from "../lib/present.js";
import { assessPlain } from "../lib/text.js";

const $ = (id) => document.getElementById(id);
const hasChrome = Boolean(globalThis.chrome?.storage?.local && globalThis.chrome?.runtime?.id);

const fields = ["enabled", "aiMode", "newsOnly", "deepFetch", "hoverDelay", "learn"];
const secretFields = ["provider", "apiKey", "model", "endpoint", "folderId"];

if (hasChrome) boot();
else $("status").textContent = "Окно настроек открыто вне расширения. В Chrome здесь появятся ключ и история.";

$("hoverDelay").addEventListener("input", () => {
  $("delay-value").textContent = `${$("hoverDelay").value} мс`;
});
$("provider").addEventListener("change", syncProvider);
for (const id of [...fields, ...secretFields]) {
  $(id).addEventListener("change", save);
  if ($(id).type === "text" || $(id).type === "password" || $(id).type === "url" || $(id).type === "range") {
    $(id).addEventListener("input", save);
  }
}
$("test").addEventListener("click", testModel);
$("check-tab").addEventListener("click", checkTab);
$("check-text").addEventListener("click", checkText);
$("guide").addEventListener("click", openGuide);
$("forget").addEventListener("click", forgetMemory);
$("intro-open").addEventListener("click", openGuide);
$("intro-done").addEventListener("click", dismissIntro);

function boot() {
  chrome.storage.local.get([SETTINGS_KEY, SECRET_KEY, HISTORY_KEY, WELCOME_KEY, MEMORY_KEY], (data) => {
    const settings = { ...DEFAULT_SETTINGS, ...(data[SETTINGS_KEY] || {}) };
    const secret = { ...DEFAULT_SECRET, ...(data[SECRET_KEY] || {}) };
    fill(settings, secret);
    paintHistory(data[HISTORY_KEY] || []);
    paintMemory(data[MEMORY_KEY]);
    $("intro").hidden = Boolean(data[WELCOME_KEY]?.seen);
  });
  chrome.storage.onChanged.addListener((changes, area) => {
    if (area !== "local") return;
    if (changes[HISTORY_KEY]) paintHistory(changes[HISTORY_KEY].newValue || []);
    if (changes[MEMORY_KEY]) paintMemory(changes[MEMORY_KEY].newValue);
  });
}

function fill(settings, secret) {
  $("enabled").checked = settings.enabled;
  $("aiMode").value = settings.aiMode;
  $("newsOnly").checked = settings.newsOnly;
  $("deepFetch").checked = settings.deepFetch;
  $("learn").checked = settings.learn !== false;
  $("hoverDelay").value = String(settings.hoverDelay);
  $("delay-value").textContent = `${settings.hoverDelay} мс`;
  $("provider").value = secret.provider;
  $("apiKey").value = secret.apiKey;
  $("model").value = secret.model;
  $("endpoint").value = secret.endpoint;
  $("folderId").value = secret.folderId || "";
  syncProvider();
}

function syncProvider() {
  const provider = $("provider").value;
  $("endpoint-field").hidden = provider !== "openai";
  $("folder-field").hidden = provider !== "yandex";
  const placeholders = {
    yandex: "aliceai-llm",
    openai: "gpt-4.1-mini",
    gemini: "gemini-2.5-flash",
  };
  if (!$("model").value) $("model").placeholder = placeholders[provider] || placeholders.gemini;
}

syncProvider();

function openGuide() {
  if (!hasChrome) return;
  chrome.tabs.create({ url: chrome.runtime.getURL("welcome/welcome.html") });
}

function dismissIntro() {
  $("intro").hidden = true;
  if (!hasChrome) return;
  chrome.storage.local.set({ [WELCOME_KEY]: { seen: true, at: Date.now() } });
}

function save() {
  syncProvider();
  if (!hasChrome) return;
  const settings = {
    enabled: $("enabled").checked,
    aiMode: $("aiMode").value,
    newsOnly: $("newsOnly").checked,
    deepFetch: $("deepFetch").checked,
    learn: $("learn").checked,
    hoverDelay: Number($("hoverDelay").value),
  };
  const secret = {
    provider: $("provider").value,
    apiKey: $("apiKey").value.trim(),
    model: $("model").value.trim(),
    endpoint: $("endpoint").value.trim(),
    folderId: $("folderId").value.trim(),
  };
  chrome.storage.local.set({
    [SETTINGS_KEY]: settings,
    [SECRET_KEY]: secret,
  });
}

function checkTab() {
  if (!hasChrome) return;
  $("check-tab").disabled = true;
  chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
    const tab = tabs?.[0];
    if (!tab?.id || !/^https?:/i.test(tab.url || "")) {
      $("check-tab").disabled = false;
      $("status").textContent = "Откройте обычную http-страницу: на системных вкладках AURIS не запускается.";
      return;
    }
    chrome.tabs.sendMessage(tab.id, { type: "auris:check", mode: "page" }, () => {
      $("check-tab").disabled = false;
      if (chrome.runtime.lastError) {
        $("status").textContent = "На этой вкладке AURIS ещё не запущен. Обновите страницу и повторите.";
        return;
      }
      window.close();
    });
  });
}

function checkText() {
  const text = $("paste").value.trim();
  if (text.length < 40) {
    $("status").textContent = "Вставьте хотя бы несколько предложений.";
    return;
  }
  paintCheck(assessPlain(text, { mode: "paste" }));
  if (!hasChrome) {
    $("status").textContent = "Показана локальная оценка текста.";
    return;
  }
  $("check-text").disabled = true;
  $("status").textContent = "Уточняю по тексту…";
  chrome.runtime.sendMessage({ type: "auris:assess", mode: "paste", text }, (response) => {
    $("check-text").disabled = false;
    if (chrome.runtime.lastError || !response?.ok) {
      $("status").textContent = "Показана локальная оценка текста.";
      return;
    }
    paintCheck(response.result);
    $("status").textContent = "Оценка по вставленному тексту.";
  });
}

function paintCheck(result) {
  const view = present(result);
  const box = $("check-result");
  box.hidden = false;
  box.className = view.verdict;
  box.querySelector("[data-score]").textContent = String(view.score);
  box.querySelector("[data-label]").textContent = view.verdictLabel;
  box.querySelector("[data-summary]").textContent = view.summary;
  const excerpt = box.querySelector("[data-excerpt]");
  excerpt.hidden = !view.textExcerpt;
  excerpt.textContent = view.textExcerpt || "";
}

function testModel() {
  if (!hasChrome) return;
  $("test").disabled = true;
  $("status").textContent = "Спрашиваю модель…";
  chrome.runtime.sendMessage({ type: "auris:test-ai" }, (response) => {
    $("test").disabled = false;
    if (chrome.runtime.lastError) {
      $("status").textContent = "Фоновый воркер не ответил. Откройте любую вкладку и повторите.";
      return;
    }
    const diagnosis = response?.diagnosis;
    const extra = diagnosis
      ? ` Nano: ${diagnosis.onDevice}. Ключ: ${diagnosis.hasKey ? "есть" : "нет"}.`
      : "";
    $("status").textContent = `${response?.message || "Нет ответа."}${extra}`;
  });
}

function forgetMemory() {
  if (!hasChrome) return;
  $("forget").disabled = true;
  chrome.runtime.sendMessage({ type: "auris:forget" }, (response) => {
    $("forget").disabled = false;
    if (chrome.runtime.lastError || !response?.ok) {
      $("status").textContent = "Память не очистилась. Откройте обычную вкладку и повторите.";
      return;
    }
    paintMemory(response.memory);
    $("status").textContent = "Память модели очищена. Она хранилась только в этом браузере.";
  });
}

function paintMemory(value) {
  const memory = normalizeMemory(value);
  const list = $("memory");
  const rows = Object.entries(memory.domains)
    .map(([domain, slot]) => ({ domain, slot }))
    .sort((a, b) => (b.slot.updated || 0) - (a.slot.updated || 0))
    .slice(0, 8);
  list.replaceChildren();
  if (!rows.length) {
    const empty = document.createElement("li");
    empty.className = "empty";
    empty.textContent = "Пока пусто. Оценки появятся после проверок.";
    list.append(empty);
    return;
  }
  for (const { domain, slot } of rows) {
    const corrections = TEACH_VERDICTS.reduce((sum, verdict) => sum + (slot.corrections[verdict] || 0), 0);
    const row = document.createElement("li");
    const score = document.createElement("div");
    score.className = "score";
    score.textContent = slot.scores.length
      ? String(Math.round(slot.scores.reduce((sum, item) => sum + item, 0) / slot.scores.length))
      : "–";
    const copy = document.createElement("div");
    copy.className = "item";
    const title = document.createElement("b");
    title.textContent = domain;
    const detail = document.createElement("span");
    detail.textContent = `своих оценок ${slot.scores.length}, поправок ${corrections}`;
    copy.append(title, detail);
    row.append(score, copy);
    list.append(row);
  }
}

function paintHistory(items) {
  const list = $("history");
  list.replaceChildren();
  if (!items.length) {
    const empty = document.createElement("li");
    empty.className = "empty";
    empty.textContent = "Пока пусто. Наведите на заголовок новости.";
    list.append(empty);
    return;
  }
  for (const item of items) {
    const row = document.createElement("li");
    row.className = item.verdict || "insufficient";
    const score = document.createElement("div");
    score.className = "score";
    score.textContent = String(item.score ?? "–");
    const copy = document.createElement("div");
    copy.className = "item";
    const title = document.createElement("b");
    title.textContent = VERDICT_LABEL[item.verdict] || "Оценка";
    const domain = document.createElement("span");
    domain.textContent = item.title || item.domain || item.href;
    copy.append(title, domain);
    row.append(score, copy);
    list.append(row);
  }
}
