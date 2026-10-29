import { sanitizeSettings } from "../shared/engine.js";
import { NANO_OPTS, nanoAvailability, nanoStatusLabel } from "../shared/nano.js";

const status = document.querySelector("#status");
const download = document.querySelector("#download");
const enabled = document.querySelector("#enabled");
const count = document.querySelector("#count");
const options = document.querySelector("#options");

async function load() {
  const bag = await chrome.storage.local.get(["settings", "stats"]);
  const settings = sanitizeSettings(bag.settings);
  enabled.checked = settings.enabled;
  count.textContent = String(bag.stats?.scans || 0);
  await renderModel();
}

async function renderModel() {
  const { state } = await nanoAvailability();
  status.textContent = nanoStatusLabel(state);
  download.hidden = state !== "downloadable" && state !== "downloading";
}

enabled.addEventListener("change", async () => {
  const bag = await chrome.storage.local.get("settings");
  const settings = sanitizeSettings({ ...bag.settings, enabled: enabled.checked });
  await chrome.storage.local.set({ settings });
});

download.addEventListener("click", async () => {
  const LanguageModel = globalThis.LanguageModel;
  if (!LanguageModel?.create) {
    status.textContent = "Эта сборка Chrome не умеет скачивать модель";
    return;
  }
  download.disabled = true;
  try {
    const session = await LanguageModel.create({
      ...NANO_OPTS,
      monitor(monitor) {
        monitor.addEventListener("downloadprogress", (event) => {
          const pct = Math.round((event.loaded || 0) * 100);
          status.textContent = `Скачивание модели ${pct}%`;
        });
      },
    });
    session.destroy?.();
    status.textContent = nanoStatusLabel("available");
    download.hidden = true;
  } catch (error) {
    status.textContent = error?.message || "Не удалось скачать модель";
  } finally {
    download.disabled = false;
  }
});

options.addEventListener("click", () => chrome.runtime.openOptionsPage());

load();
