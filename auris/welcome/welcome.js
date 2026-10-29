import { WELCOME_KEY } from "../lib/defaults.js";

document.getElementById("start").addEventListener("click", () => {
  const done = () => window.close();
  if (!globalThis.chrome?.storage?.local) {
    done();
    return;
  }
  chrome.storage.local.set({ [WELCOME_KEY]: { seen: true, at: Date.now() } }, done);
});
