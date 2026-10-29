import { sanitizeSettings } from "../shared/engine.js";
import { nanoAvailability, nanoStatusLabel } from "../shared/nano.js";

const enabled = document.querySelector("#enabled");
const all = document.querySelector("#all");
const delay = document.querySelector("#delay");
const delayOut = document.querySelector("#delay-out");
const key = document.querySelector("#key");
const model = document.querySelector("#model");
const saved = document.querySelector("#saved");
const nano = document.querySelector("#nano");
const cloudStatus = document.querySelector("#cloud-status");
const modes = [...document.querySelectorAll('input[name="mode"]')];

function selectedMode() {
  return modes.find((item) => item.checked)?.value || "auto";
}

async function load() {
  const bag = await chrome.storage.local.get(["settings", "secret"]);
  const settings = sanitizeSettings(bag.settings);
  enabled.checked = settings.enabled;
  all.checked = settings.showOnAllLinks;
  delay.value = String(settings.delay);
  delayOut.textContent = String(settings.delay);
  model.value = settings.geminiModel;
  key.value = bag.secret?.geminiKey || "";
  for (const item of modes) item.checked = item.value === settings.aiMode;
  const availability = await nanoAvailability();
  nano.textContent = nanoStatusLabel(availability.state);
}

let timer = 0;
function queueSave() {
  clearTimeout(timer);
  timer = setTimeout(save, 180);
}

async function save() {
  const settings = sanitizeSettings({
    enabled: enabled.checked,
    showOnAllLinks: all.checked,
    delay: Number(delay.value),
    aiMode: selectedMode(),
    geminiModel: model.value.trim(),
  });
  await chrome.storage.local.set({ settings, secret: { geminiKey: key.value.trim() } });
  saved.textContent = "Сохранено";
}

for (const node of [enabled, all, model, key, ...modes]) {
  node.addEventListener("change", queueSave);
}
delay.addEventListener("input", () => {
  delayOut.textContent = delay.value;
  queueSave();
});

document.querySelector("#test").addEventListener("click", async () => {
  await save();
  cloudStatus.textContent = "Проверяем ключ…";
  const response = await chrome.runtime.sendMessage({ type: "test-cloud" });
  cloudStatus.textContent = response?.message || "Нет ответа";
});

document.querySelector("#clear").addEventListener("click", async () => {
  await chrome.runtime.sendMessage({ type: "clear-cache" });
  cloudStatus.textContent = "Кэш оценок очищен";
});

load();
