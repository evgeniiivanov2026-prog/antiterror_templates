const HOST_ID = "kontur-lens";

if (!globalThis.__KONTUR_LENS__) {
  globalThis.__KONTUR_LENS__ = true;
  boot();
}

async function boot() {
  let engine;
  let viewApi;
  let css = "";
  try {
    const fontRoot = chrome.runtime.getURL("assets/fonts/manrope-latin.woff2").replace(/manrope-latin\.woff2$/, "");
    const [engineMod, viewMod, cssText] = await Promise.all([
      import(chrome.runtime.getURL("src/shared/engine.js")),
      import(chrome.runtime.getURL("src/content/view.js")),
      fetch(chrome.runtime.getURL("src/content/card.css")).then((response) => response.text()),
    ]);
    engine = engineMod;
    viewApi = viewMod;
    css = cssText.replaceAll("../../assets/fonts/", fontRoot);
  } catch (error) {
    console.warn("[Контур] не удалось запустить карточку", error);
    return;
  }

  let settings = engine.sanitizeSettings({});
  let host = null;
  let card = null;
  let active = null;
  let overCard = false;
  let hideTimer = 0;
  let hovering = false;

  await refreshSettings();
  chrome.storage.onChanged.addListener((changes, area) => {
    if (area !== "local" || !changes.settings) return;
    settings = engine.sanitizeSettings(changes.settings.newValue);
    if (!settings.enabled) teardown();
  });

  document.addEventListener("mouseover", (event) => {
    if (!settings.enabled || !alive()) return;
    const anchor = anchorFrom(event);
    if (!anchor) return;
    if (anchor === active?.anchor) {
      hovering = true;
      clearTimeout(hideTimer);
      return;
    }
    arm(anchor, event);
  }, true);

  document.addEventListener("mouseout", (event) => {
    const anchor = anchorFrom(event);
    if (!anchor || anchor !== active?.anchor) return;
    hovering = false;
    const next = event.relatedTarget;
    if (next && host && (next === host || host.contains(next))) return;
    scheduleHide();
  }, true);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") teardown();
  }, true);

  window.addEventListener("scroll", () => {
    if (active?.visible) teardown();
  }, true);
  window.addEventListener("resize", () => {
    if (active?.visible && active.anchor) place(active.anchor);
  });

  function alive() {
    try {
      return Boolean(chrome.runtime?.id);
    } catch {
      return false;
    }
  }

  async function refreshSettings() {
    const bag = await chrome.storage.local.get("settings");
    settings = engine.sanitizeSettings(bag.settings);
  }

  function anchorFrom(event) {
    for (const node of event.composedPath()) {
      if (node instanceof HTMLAnchorElement && node.href) return node;
    }
    return null;
  }

  function linkText(anchor) {
    const text = (anchor.innerText || anchor.textContent || "").replace(/\s+/g, " ").trim();
    if (text) return text.slice(0, 280);
    const label = anchor.getAttribute("aria-label") || anchor.getAttribute("title") || "";
    if (label.trim()) return label.trim().slice(0, 280);
    return (anchor.querySelector("img[alt]")?.getAttribute("alt") || "").trim().slice(0, 280);
  }

  function domainOf(url) {
    try {
      return new URL(url).hostname.replace(/^www\./, "");
    } catch {
      return "";
    }
  }

  function ensure() {
    if (host) return;
    host = document.createElement("div");
    host.id = HOST_ID;
    const props = {
      position: "fixed",
      "z-index": "2147483647",
      top: "0",
      left: "0",
      width: "344px",
      margin: "0",
      padding: "0",
      border: "0",
      background: "transparent",
      "pointer-events": "none",
      display: "none",
    };
    for (const [key, value] of Object.entries(props)) host.style.setProperty(key, value, "important");
    const shadow = host.attachShadow({ mode: "open" });
    const style = document.createElement("style");
    style.textContent = css;
    card = viewApi.createCard();
    shadow.append(style, card.shell);
    card.shell.addEventListener("mouseenter", () => {
      overCard = true;
      clearTimeout(hideTimer);
    });
    card.shell.addEventListener("mouseleave", () => {
      overCard = false;
      scheduleHide();
    });
    (document.documentElement || document.body).append(host);
  }

  function arm(anchor, event) {
    const url = anchor.href;
    const text = linkText(anchor);
    let parsed = null;
    try {
      parsed = new URL(url, location.href);
    } catch {
      return;
    }
    if (!/^https?:$/.test(parsed.protocol)) return;
    if (parsed.origin === location.origin && parsed.pathname === location.pathname && parsed.search === location.search) return;
    const force = Boolean(event.altKey || settings.showOnAllLinks);
    if (!engine.isArticleLike({ url: parsed.href, text, force })) return;

    clearTimeout(hideTimer);
    hovering = true;
    teardown();
    const token = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    const record = { anchor, token, url: parsed.href, text, result: null, port: null, timers: [], visible: false };
    active = record;
    const delay = settings.delay ?? 280;
    record.timers.push(setTimeout(() => begin(record), Math.min(70, delay)));
    record.timers.push(setTimeout(() => reveal(record), delay));
  }

  function begin(record) {
    if (active !== record || !alive()) return;
    const port = chrome.runtime.connect({ name: "kontur" });
    record.port = port;
    port.onMessage.addListener((message) => {
      if (message?.token !== record.token || active !== record) return;
      record.result = message.result;
      if (record.visible) paint(record);
    });
    port.postMessage({ token: record.token, url: record.url, anchorText: record.text });
  }

  function reveal(record) {
    if (active !== record || (!hovering && !overCard)) return;
    ensure();
    record.visible = true;
    host.style.setProperty("display", "block", "important");
    paint(record);
    card.shell.classList.remove("is-in");
    void card.shell.offsetWidth;
    card.shell.classList.add("is-in");
    place(record.anchor);
  }

  function paint(record) {
    const model = record.result
      ? viewApi.viewFromResult(record.result)
      : viewApi.scanView(domainOf(record.url), record.text);
    card.setState(model);
    if (record.visible) place(record.anchor);
  }

  function place(anchor) {
    if (!host || !anchor.isConnected) return;
    const rect = anchor.getBoundingClientRect();
    const width = Math.min(344, Math.max(280, window.innerWidth - 16));
    host.style.setProperty("width", `${width}px`, "important");
    const height = host.offsetHeight || 320;
    let left = Math.min(Math.max(8, rect.left), Math.max(8, window.innerWidth - width - 8));
    let top = rect.bottom + 12;
    if (top + height > window.innerHeight - 8) top = Math.max(8, rect.top - height - 12);
    host.style.setProperty("top", `${top}px`, "important");
    host.style.setProperty("left", `${left}px`, "important");
  }

  function scheduleHide() {
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
      if (overCard || hovering) return;
      teardown();
    }, 140);
  }

  function teardown() {
    if (active) {
      for (const timer of active.timers) clearTimeout(timer);
      try {
        active.port?.disconnect();
      } catch {
        /* port already closed */
      }
    }
    active = null;
    overCard = false;
    if (host) host.style.setProperty("display", "none", "important");
  }
}
