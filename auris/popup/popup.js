import { VERDICT_LABEL } from "../lib/copy.js";
import { DEFAULT_SECRET, DEFAULT_SETTINGS, HISTORY_KEY, SECRET_KEY, SETTINGS_KEY } from "../lib/defaults.js";
import { present } from "../lib/present.js";
import { assessPlain } from "../lib/text.js";

const $ = (id) => document.getElementById(id);
const hasChrome = Boolean(globalThis.chrome?.storage?.local && globalThis.chrome?.runtime?.id);

const fields = ["enabled", "aiMode", "newsOnly", "deepFetch", "hoverDelay"];
const secretFields = ["provider", "apiKey", "model", "endpoint"];

if (hasChrome) boot();
else $("status").textContent = "Окно настроек открыто вне расширения. В Chrome здесь появятся ключ и история.";

$("hoverDelay").addEventListener("input", () => {
  $("delay-value").textContent = `${$("hoverDelay").value} мс`;
});
$("provider").addEventListener("change", syncEndpoint);
for (const id of [...fields, ...secretFields]) {
  $(id).addEventListener("change", save);
  if ($(id).type === "text" || $(id).type === "password" || $(id).type === "url" || $(id).type === "range") {
    $(id).addEventListener("input", save);
  }
}
$("test").addEventListener("click", testModel);
$("check-tab").addEventListener("click", checkTab);
$("check-text").addEventListener("click", checkText);

function boot() {
  chrome.storage.local.get([SETTINGS_KEY, SECRET_KEY, HISTORY_KEY], (data) => {
    const settings = { ...DEFAULT_SETTINGS, ...(data[SETTINGS_KEY] || {}) };
    const secret = { ...DEFAULT_SECRET, ...(data[SECRET_KEY] || {}) };
    fill(settings, secret);
    paintHistory(data[HISTORY_KEY] || []);
  });
  chrome.storage.onChanged.addListener((changes, area) => {
    if (area === "local" && changes[HISTORY_KEY]) paintHistory(changes[HISTORY_KEY].newValue || []);
  });
}

function fill(settings, secret) {
  $("enabled").checked = settings.enabled;
  $("aiMode").value = settings.aiMode;
  $("newsOnly").checked = settings.newsOnly;
  $("deepFetch").checked = settings.deepFetch;
  $("hoverDelay").value = String(settings.hoverDelay);
  $("delay-value").textContent = `${settings.hoverDelay} мс`;
  $("provider").value = secret.provider;
  $("apiKey").value = secret.apiKey;
  $("model").value = secret.model;
  $("endpoint").value = secret.endpoint;
  syncEndpoint();
}

function syncEndpoint() {
  const openai = $("provider").value === "openai";
  $("endpoint-field").hidden = !openai;
  if (!$("model").value) {
    $("model").placeholder = openai ? "gpt-4.1-mini" : "gemini-2.5-flash";
  }
}

syncEndpoint();

function save() {
  syncEndpoint();
  if (!hasChrome) return;
  const settings = {
    enabled: $("enabled").checked,
    aiMode: $("aiMode").value,
    newsOnly: $("newsOnly").checked,
    deepFetch: $("deepFetch").checked,
    hoverDelay: Number($("hoverDelay").value),
  };
  const secret = {
    provider: $("provider").value,
    apiKey: $("apiKey").value.trim(),
    model: $("model").value.trim(),
    endpoint: $("endpoint").value.trim(),
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
