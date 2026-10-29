(() => {
  // ../../workspace/auris/content/hover.js
  function attachHover(env) {
    const lens = env.createLens(env.document);
    lens.host.style.position = "fixed";
    lens.host.style.zIndex = "2147483646";
    lens.host.style.display = "none";
    lens.host.style.left = "0px";
    lens.host.style.top = "0px";
    env.document.documentElement.append(lens.host);
    let anchor = null;
    let token = 0;
    let openTimer = 0;
    let closeTimer = 0;
    let pinned = false;
    lens.onPin((next) => {
      pinned = next;
    });
    const win = env.document.defaultView;
    env.document.addEventListener("mouseover", (event) => {
      if (!env.getSettings().enabled) return;
      if (String(win.getSelection?.() || "").trim().length > 40) return;
      if (event.target === lens.host) {
        win.clearTimeout(closeTimer);
        return;
      }
      const link = event.target?.closest?.("a[href]");
      if (!link) return;
      if (link === anchor) {
        win.clearTimeout(closeTimer);
        return;
      }
      win.clearTimeout(openTimer);
      const delay = Number(env.getSettings().hoverDelay) || 260;
      openTimer = win.setTimeout(() => openFor(link), delay);
    }, true);
    env.document.addEventListener("mouseout", (event) => {
      const link = event.target?.closest?.("a[href]");
      if (!link) return;
      win.clearTimeout(openTimer);
      if (pinned) return;
      const next = event.relatedTarget;
      if (next && (next === lens.host || lens.host.contains(next))) return;
      closeTimer = win.setTimeout(() => {
        if (anchor === link) hide(false);
      }, 160);
    }, true);
    lens.host.addEventListener("mouseenter", () => win.clearTimeout(closeTimer));
    lens.host.addEventListener("mouseleave", () => {
      if (!pinned) closeTimer = win.setTimeout(() => hide(false), 140);
    });
    env.document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") hide(true);
    });
    win.addEventListener("scroll", () => {
      if (!anchor) return;
      if (pinned) place(anchor);
      else hide(false);
    }, true);
    if (win.ResizeObserver) {
      const observer = new win.ResizeObserver(() => {
        if (anchor && lens.host.style.display !== "none") place(anchor);
      });
      observer.observe(lens.host);
    }
    async function openFor(link) {
      const href = link.href;
      const anchorText = (link.innerText || link.getAttribute("aria-label") || link.title || "").trim().slice(0, 400);
      const settings = env.getSettings();
      if (!env.shouldInspect({ href, anchorText, pageUrl: win.location.href }, settings)) return;
      anchor = link;
      token += 1;
      const myToken = token;
      pinned = false;
      lens.resetPin();
      const local = env.evaluate({ href, anchorText });
      const pending = settings.aiMode !== "local" || settings.deepFetch;
      lens.render(env.present(local, { phase: "ready", pending }));
      lens.host.style.visibility = "hidden";
      lens.host.style.display = "block";
      place(link);
      lens.host.style.visibility = "visible";
      if (!pending) return;
      const enriched = await env.requestEnrich({ href, anchorText });
      if (myToken !== token || !enriched) {
        if (myToken === token) lens.render(env.present(local, { pending: false }));
        return;
      }
      lens.render(env.present(enriched, { pending: false }));
      place(link);
    }
    function hide(force) {
      if (pinned && !force) return;
      token += 1;
      anchor = null;
      pinned = false;
      lens.resetPin();
      lens.host.style.display = "none";
    }
    function place(link) {
      const rect = link.getBoundingClientRect();
      const card = lens.host.getBoundingClientRect();
      const gap = 12;
      let top = rect.bottom + gap;
      if (top + card.height > win.innerHeight - 8) top = rect.top - card.height - gap;
      if (top < 8) top = Math.max(8, win.innerHeight - card.height - 8);
      let left = rect.left;
      if (left + card.width > win.innerWidth - 8) left = win.innerWidth - card.width - 8;
      if (left < 8) left = 8;
      lens.host.style.left = `${Math.round(left)}px`;
      lens.host.style.top = `${Math.round(top)}px`;
    }
    async function showCheck({ mode = "page", text = "", href = "", title = "", point = null } = {}) {
      if (!env.getSettings().enabled) return;
      win.clearTimeout(openTimer);
      win.clearTimeout(closeTimer);
      token += 1;
      const myToken = token;
      anchor = null;
      lens.setPinned(true);
      const settings = env.getSettings();
      const local = env.evaluate({ href, anchorText: String(title || "").slice(0, 400) });
      const viewed = typeof env.applyText === "function" ? env.applyText(local, text, { mode }) : local;
      const length = String(text || "").trim().length;
      const pending = settings.aiMode !== "local" && length >= (mode === "page" ? 200 : 80);
      lens.render(env.present(viewed, { pending }));
      revealAt(point);
      if (!pending) return;
      const enriched = await env.requestEnrich({
        href,
        anchorText: String(title || "").slice(0, 400),
        title: title || "",
        text: String(text || "").slice(0, 8e3),
        mode
      });
      if (myToken !== token || !enriched) {
        if (myToken === token) lens.render(env.present(viewed, { pending: false }));
        return;
      }
      lens.render(env.present(enriched, { pending: false }));
    }
    function considerSelection() {
      if (!env.getSettings().enabled) return;
      const sel = win.getSelection?.();
      if (!sel || sel.isCollapsed || !sel.rangeCount) return;
      const text = String(sel).replace(/\s+/g, " ").trim();
      if (text.length < 160) return;
      if (inside(sel.anchorNode) || inside(sel.focusNode)) return;
      const parent = sel.anchorNode?.nodeType === 1 ? sel.anchorNode : sel.anchorNode?.parentElement;
      if (parent?.closest?.("input, textarea, select, [contenteditable='true']")) return;
      const rect = sel.getRangeAt(0).getBoundingClientRect();
      if (!rect || rect.width === 0 && rect.height === 0) return;
      showCheck({
        mode: "selection",
        text,
        href: win.location.href,
        title: env.document.title || "",
        point: { x: rect.left, y: rect.bottom }
      });
    }
    function revealAt(point) {
      lens.host.style.visibility = "hidden";
      lens.host.style.display = "block";
      const card = lens.host.getBoundingClientRect();
      const corner = !point;
      let left = corner ? win.innerWidth - card.width - 16 : point.x;
      let top = corner ? 16 : point.y + 12;
      if (!corner && top + card.height > win.innerHeight - 8) top = point.y - card.height - 12;
      if (top + card.height > win.innerHeight - 8) top = Math.max(8, win.innerHeight - card.height - 8);
      if (top < 8) top = 8;
      if (left + card.width > win.innerWidth - 8) left = win.innerWidth - card.width - 8;
      if (left < 8) left = 8;
      lens.host.style.left = `${Math.round(left)}px`;
      lens.host.style.top = `${Math.round(top)}px`;
      lens.host.style.visibility = "visible";
    }
    function inside(node) {
      if (!node) return false;
      const el = node.nodeType === 1 ? node : node.parentElement;
      if (!el) return false;
      if (el === lens.host || lens.host.contains(el)) return true;
      const root = el.getRootNode?.();
      return Boolean(root && root.host === lens.host);
    }
    return {
      hide,
      showCheck,
      considerSelection,
      owns: inside
    };
  }

  // ../../workspace/auris/lib/article.js
  function extractArticleFromDocument(doc) {
    if (!doc?.body || typeof doc.createTreeWalker !== "function") return "";
    const article = textOf(doc, doc.querySelector("article"));
    if (article.length >= 200) return article.slice(0, 8e3);
    const main = textOf(doc, doc.querySelector("main") || doc.querySelector("[role='main']"));
    if (main.length >= 200) return main.slice(0, 8e3);
    const body = textOf(doc, doc.body);
    const best = [article, main, body].sort((a, b) => b.length - a.length)[0] || "";
    return best.slice(0, 8e3);
  }
  function textOf(doc, root) {
    if (!root) return "";
    const chunks = [];
    const walker = doc.createTreeWalker(root, 4, {
      acceptNode(node) {
        const parent = node.parentElement;
        if (!parent?.closest) return 2;
        if (parent.closest("script,style,noscript,svg,iframe,nav,footer,form,aside")) return 2;
        return node.nodeValue && node.nodeValue.trim() ? 1 : 2;
      }
    });
    while (walker.nextNode()) chunks.push(walker.currentNode.nodeValue);
    return chunks.join(" ").replace(/\s+/g, " ").trim();
  }

  // ../../workspace/auris/ui/card.js
  var import_meta2 = {};
  var RADIUS = 46;
  var CIRC = 2 * Math.PI * RADIUS;
  if (typeof CSS !== "undefined" && CSS.registerProperty) {
    try {
      CSS.registerProperty({
        name: "--auris-angle",
        syntax: "<angle>",
        inherits: false,
        initialValue: "0deg"
      });
    } catch {
    }
  }
  function createLens(doc = document, options = {}) {
    const host = doc.createElement("auris-lens");
    const shadow = host.attachShadow({ mode: "open" });
    const link = doc.createElement("link");
    link.rel = "stylesheet";
    link.href = options.cssHref || new URL("./card.css", import_meta2.url).href;
    const frame = doc.createElement("div");
    frame.className = "frame";
    frame.append(buildPanel(doc));
    shadow.append(link, frame);
    const panel = frame.querySelector(".panel");
    const els = {
      frame,
      panel,
      engine: panel.querySelector("[data-engine]"),
      score: panel.querySelector("[data-score]"),
      verdict: panel.querySelector("[data-verdict-label]"),
      summary: panel.querySelector("[data-summary]"),
      domain: panel.querySelector("[data-domain]"),
      confidence: panel.querySelector("[data-confidence]"),
      title: panel.querySelector("[data-title]"),
      excerpt: panel.querySelector("[data-excerpt]"),
      excerptText: panel.querySelector("[data-excerpt-text]"),
      lang: panel.querySelector("[data-lang]"),
      arc: panel.querySelector("[data-arc]"),
      fold: panel.querySelector("[data-fold]"),
      reasons: panel.querySelector("[data-reasons]"),
      pin: panel.querySelector("[data-pin]"),
      meters: {
        source: panel.querySelector('[data-meter="source"]'),
        rhetoric: panel.querySelector('[data-meter="rhetoric"]'),
        url: panel.querySelector('[data-meter="url"]'),
        cross: panel.querySelector('[data-meter="cross"]')
      }
    };
    let pinned = false;
    let pinListener = () => {
    };
    els.arc.style.strokeDasharray = `${CIRC} ${CIRC}`;
    els.arc.style.strokeDashoffset = `${CIRC}`;
    els.fold.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      const open = els.fold.getAttribute("aria-expanded") === "true";
      els.fold.setAttribute("aria-expanded", open ? "false" : "true");
      els.reasons.hidden = open;
    });
    els.pin.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      setPinned(!pinned);
    });
    function render(view) {
      const loading = view.phase === "loading";
      panel.dataset.verdict = loading ? "loading" : view.verdict;
      panel.classList.toggle("is-loading", loading);
      panel.classList.toggle("is-pending", Boolean(view.pending) && !loading);
      els.engine.textContent = view.engineLabel || "";
      els.verdict.textContent = view.verdictLabel || "";
      els.summary.textContent = view.summary || "";
      els.domain.textContent = view.domain || "";
      els.confidence.textContent = loading || !view.confidence ? "" : `\u0443\u0432\u0435\u0440\u0435\u043D\u043D\u043E\u0441\u0442\u044C ${Math.round(view.confidence * 100)}%`;
      els.title.hidden = !view.title;
      els.title.textContent = view.title || "";
      els.excerpt.hidden = !view.textExcerpt;
      els.excerptText.textContent = view.textExcerpt || "";
      els.lang.hidden = !view.languageNote;
      els.lang.textContent = view.languageNote || "";
      paintReasons(doc, els.reasons, view.reasons || []);
      const score = loading ? 18 : Math.round(view.score ?? 0);
      els.score.textContent = String(score);
      els.score.dataset.n = String(score);
      const offset = CIRC * (1 - score / 100);
      if (loading) {
        els.arc.style.strokeDasharray = "54 236";
      } else {
        els.arc.style.strokeDasharray = `${CIRC} ${CIRC}`;
        els.arc.style.strokeDashoffset = `${offset}`;
      }
      for (const key of Object.keys(els.meters)) {
        const value = loading ? 12 : Math.round(view.meters?.[key] ?? 0);
        els.meters[key].style.width = `${value}%`;
      }
    }
    function setPinned(next) {
      pinned = Boolean(next);
      els.pin.textContent = pinned ? "\u041E\u0442\u043A\u0440\u0435\u043F\u0438\u0442\u044C" : "\u0417\u0430\u043A\u0440\u0435\u043F\u0438\u0442\u044C";
      els.pin.classList.toggle("is-on", pinned);
      els.frame.classList.toggle("is-pinned", pinned);
      pinListener(pinned);
    }
    return {
      host,
      render,
      resetPin() {
        setPinned(false);
      },
      setPinned,
      get pinned() {
        return pinned;
      },
      onPin(listener) {
        pinListener = listener;
      }
    };
  }
  function paintReasons(doc, list, reasons) {
    list.replaceChildren();
    for (const reason of reasons) {
      const item = doc.createElement("li");
      item.textContent = reason;
      list.append(item);
    }
  }
  function buildPanel(doc) {
    const panel = doc.createElement("article");
    panel.className = "panel";
    panel.dataset.verdict = "loading";
    panel.setAttribute("role", "tooltip");
    panel.setAttribute("aria-live", "polite");
    panel.innerHTML = `
    <header class="top">
      <div class="mark" aria-hidden="true">
        <svg viewBox="0 0 32 32" fill="none">
          <circle cx="16" cy="16" r="10" stroke="#8ef6d6" stroke-width="1.6"/>
          <circle cx="16" cy="16" r="4.5" fill="#c0b0ff"/>
          <path d="M16 4.5v3.2M16 24.3v3.2M4.5 16h3.2M24.3 16h3.2" stroke="#8ef6d6" stroke-width="1.4" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="brand-wrap">
        <div class="brand">AURIS</div>
        <div class="brand-sub">\u043E\u0446\u0435\u043D\u043A\u0430 \u0434\u043E \u043A\u043B\u0438\u043A\u0430</div>
      </div>
      <div class="chip" data-engine>\u041B\u043E\u043A\u0430\u043B\u044C\u043D\u043E</div>
    </header>
    <div class="hero">
      <div class="gauge">
        <svg viewBox="0 0 120 120" aria-hidden="true">
          <circle class="track" cx="60" cy="60" r="46"></circle>
          <g class="spin"><circle class="arc" data-arc cx="60" cy="60" r="46"></circle></g>
        </svg>
        <div class="gauge-readout">
          <strong data-score>0</strong>
          <span>\u0438\u043D\u0434\u0435\u043A\u0441</span>
        </div>
      </div>
      <div>
        <div class="verdict" data-verdict-label>\u0421\u0447\u0438\u0442\u044B\u0432\u0430\u044E</div>
        <p class="summary" data-summary></p>
        <div class="meta-line">
          <span class="domain" data-domain></span>
          <span class="confidence" data-confidence></span>
        </div>
      </div>
    </div>
    <cite class="title-line" data-title hidden></cite>
    <div class="excerpt" data-excerpt hidden>
      <b>\u0424\u0440\u0430\u0433\u043C\u0435\u043D\u0442 \u0442\u0435\u043A\u0441\u0442\u0430</b>
      <p data-excerpt-text></p>
      <div class="lang-note" data-lang hidden></div>
    </div>
    <ul class="meters">
      <li><span>\u0418\u0441\u0442\u043E\u0447\u043D\u0438\u043A</span><b><i data-meter="source"></i></b></li>
      <li><span>\u041F\u043E\u0434\u0430\u0447\u0430</span><b><i data-meter="rhetoric"></i></b></li>
      <li><span>\u0410\u0434\u0440\u0435\u0441</span><b><i data-meter="url"></i></b></li>
      <li><span>\u0421\u0432\u0435\u0440\u043A\u0430</span><b><i data-meter="cross"></i></b></li>
    </ul>
    <button type="button" class="fold" data-fold aria-expanded="false">
      <span>\u0420\u0430\u0437\u0431\u043E\u0440 \u0441\u0438\u0433\u043D\u0430\u043B\u043E\u0432</span>
      <svg viewBox="0 0 14 14" fill="none" aria-hidden="true">
        <path d="M3 5.2 7 9l4-3.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <ul class="reasons" data-reasons hidden></ul>
    <footer class="foot">
      <p>\u0412\u0435\u0440\u043E\u044F\u0442\u043D\u043E\u0441\u0442\u043D\u0430\u044F \u043E\u0446\u0435\u043D\u043A\u0430, \u043D\u0435 \u0432\u0435\u0440\u0434\u0438\u043A\u0442 \u0444\u0430\u043A\u0442\u0447\u0435\u043A\u0435\u0440\u0430.</p>
      <button type="button" class="pin" data-pin>\u0417\u0430\u043A\u0440\u0435\u043F\u0438\u0442\u044C</button>
    </footer>
  `;
    return panel;
  }

  // ../../workspace/auris/lib/clamp.js
  function clamp(value, min, max) {
    return Math.min(max, Math.max(min, value));
  }

  // ../../workspace/auris/lib/copy.js
  var VERDICT_LABEL = {
    reliable: "\u0414\u043E\u0441\u0442\u043E\u0432\u0435\u0440\u043D\u043E",
    leaning: "\u0421\u043A\u043E\u0440\u0435\u0435 \u0434\u0430",
    mixed: "\u0421\u043C\u0435\u0448\u0430\u043D\u043D\u043E",
    suspicious: "\u0421\u043E\u043C\u043D\u0438\u0442\u0435\u043B\u044C\u043D\u043E",
    likely_fake: "\u041F\u043E\u0445\u043E\u0436\u0435 \u043D\u0430 \u0444\u0435\u0439\u043A",
    satire: "\u0421\u0430\u0442\u0438\u0440\u0430",
    insufficient: "\u041C\u0430\u043B\u043E \u0434\u0430\u043D\u043D\u044B\u0445"
  };
  var VERDICT_SUMMARY = {
    reliable: "\u0420\u0435\u0434\u0430\u043A\u0446\u0438\u044F \u0441 \u0436\u0451\u0441\u0442\u043A\u043E\u0439 \u043F\u0440\u043E\u0432\u0435\u0440\u043A\u043E\u0439 \u0444\u0430\u043A\u0442\u043E\u0432. \u041F\u043E\u0432\u043E\u0434 \u0434\u043B\u044F \u0434\u043E\u0432\u0435\u0440\u0438\u044F \u0435\u0441\u0442\u044C, \u043F\u0435\u0440\u0432\u043E\u0438\u0441\u0442\u043E\u0447\u043D\u0438\u043A \u0432\u0441\u0451 \u0440\u0430\u0432\u043D\u043E \u043F\u043E\u043B\u0435\u0437\u0435\u043D.",
    leaning: "\u041F\u043E\u0445\u043E\u0436\u0435 \u043D\u0430 \u043E\u0431\u044B\u0447\u043D\u044B\u0439 \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u043E\u0439 \u0440\u0435\u0434\u0430\u043A\u0446\u0438\u0438. \u041A\u0440\u0438\u0442\u0438\u0447\u043D\u044B\u0445 \u0441\u043B\u0435\u0434\u043E\u0432 \u0444\u0435\u0439\u043A\u0430 \u043D\u0435 \u0432\u0438\u0434\u043D\u043E.",
    mixed: "\u0421\u0438\u0433\u043D\u0430\u043B\u044B \u0441\u043C\u0435\u0448\u0430\u043D\u043D\u044B\u0435: \u0438\u0441\u0442\u043E\u0447\u043D\u0438\u043A \u0438\u043B\u0438 \u043F\u043E\u0434\u0430\u0447\u0430 \u043D\u0435 \u0434\u0430\u044E\u0442 \u0447\u0438\u0441\u0442\u043E\u0439 \u043E\u0446\u0435\u043D\u043A\u0438.",
    suspicious: "\u0415\u0441\u0442\u044C \u0437\u0430\u043C\u0435\u0442\u043D\u044B\u0435 \u043F\u0440\u0438\u0437\u043D\u0430\u043A\u0438 \u043C\u0430\u043D\u0438\u043F\u0443\u043B\u044F\u0442\u0438\u0432\u043D\u043E\u0439 \u043F\u043E\u0434\u0430\u0447\u0438 \u0438\u043B\u0438 \u0441\u043E\u043C\u043D\u0438\u0442\u0435\u043B\u044C\u043D\u043E\u0433\u043E \u0430\u0434\u0440\u0435\u0441\u0430.",
    likely_fake: "\u041D\u0435\u0441\u043A\u043E\u043B\u044C\u043A\u043E \u043D\u0435\u0437\u0430\u0432\u0438\u0441\u0438\u043C\u044B\u0445 \u0441\u043B\u0435\u0434\u043E\u0432 \u0443\u043A\u0430\u0437\u044B\u0432\u0430\u044E\u0442 \u043D\u0430 \u0444\u0430\u0431\u0440\u0438\u043A\u0430\u0446\u0438\u044E \u0438\u043B\u0438 \u0445\u0440\u043E\u043D\u0438\u0447\u0435\u0441\u043A\u0438 \u043D\u0435\u043D\u0430\u0434\u0451\u0436\u043D\u044B\u0439 \u0438\u0441\u0442\u043E\u0447\u043D\u0438\u043A.",
    satire: "\u042D\u0442\u043E \u0441\u0430\u0442\u0438\u0440\u0430. \u0422\u0430\u043A\u043E\u0439 \u0442\u0435\u043A\u0441\u0442 \u043D\u0435 \u0441\u0442\u043E\u0438\u0442 \u0447\u0438\u0442\u0430\u0442\u044C \u043A\u0430\u043A \u043D\u043E\u0432\u043E\u0441\u0442\u044C.",
    insufficient: "\u041C\u0430\u043B\u043E \u0434\u0430\u043D\u043D\u044B\u0445, \u0447\u0442\u043E\u0431\u044B \u043D\u0430\u0437\u0432\u0430\u0442\u044C \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B \u0444\u0435\u0439\u043A\u043E\u043C \u0438\u043B\u0438 \u043F\u043E\u0434\u0442\u0432\u0435\u0440\u0434\u0438\u0442\u044C \u0435\u0433\u043E."
  };
  var ENGINE_LABEL = {
    local: "\u041B\u043E\u043A\u0430\u043B\u044C\u043D\u043E",
    ondevice: "Gemini Nano",
    cloud: "\u041E\u0431\u043B\u0430\u043A\u043E"
  };
  var VERDICTS = new Set(Object.keys(VERDICT_LABEL));

  // ../../workspace/auris/lib/sources.js
  var SOURCES = {
    "reuters.com": { tier: "high", name: "Reuters" },
    "apnews.com": { tier: "high", name: "Associated Press" },
    "afp.com": { tier: "high", name: "AFP" },
    "bbc.com": { tier: "high", name: "BBC" },
    "bbc.co.uk": { tier: "high", name: "BBC" },
    "npr.org": { tier: "high", name: "NPR" },
    "nature.com": { tier: "high", name: "Nature" },
    "science.org": { tier: "high", name: "Science" },
    "thelancet.com": { tier: "high", name: "The Lancet" },
    "nejm.org": { tier: "high", name: "NEJM" },
    "who.int": { tier: "high", name: "WHO" },
    "cdc.gov": { tier: "high", name: "CDC" },
    "nih.gov": { tier: "high", name: "NIH" },
    "nasa.gov": { tier: "high", name: "NASA" },
    "esa.int": { tier: "high", name: "ESA" },
    "un.org": { tier: "high", name: "\u041E\u041E\u041D" },
    "snopes.com": { tier: "factcheck", name: "Snopes" },
    "factcheck.org": { tier: "factcheck", name: "FactCheck.org" },
    "politifact.com": { tier: "factcheck", name: "PolitiFact" },
    "fullfact.org": { tier: "factcheck", name: "Full Fact" },
    "stopfake.org": { tier: "factcheck", name: "StopFake" },
    "leadstories.com": { tier: "factcheck", name: "Lead Stories" },
    "nytimes.com": { tier: "established", name: "The New York Times" },
    "washingtonpost.com": { tier: "established", name: "The Washington Post" },
    "theguardian.com": { tier: "established", name: "The Guardian" },
    "wsj.com": { tier: "established", name: "The Wall Street Journal" },
    "ft.com": { tier: "established", name: "Financial Times" },
    "economist.com": { tier: "established", name: "The Economist" },
    "bloomberg.com": { tier: "established", name: "Bloomberg" },
    "propublica.org": { tier: "established", name: "ProPublica" },
    "dw.com": { tier: "established", name: "Deutsche Welle" },
    "france24.com": { tier: "established", name: "France 24" },
    "lemonde.fr": { tier: "established", name: "Le Monde" },
    "spiegel.de": { tier: "established", name: "Der Spiegel" },
    "cnn.com": { tier: "established", name: "CNN" },
    "nbcnews.com": { tier: "established", name: "NBC News" },
    "pbs.org": { tier: "established", name: "PBS" },
    "kyivindependent.com": { tier: "established", name: "The Kyiv Independent" },
    "meduza.io": { tier: "established", name: "\u041C\u0435\u0434\u0443\u0437\u0430" },
    "novayagazeta.eu": { tier: "established", name: "\u041D\u043E\u0432\u0430\u044F \u0433\u0430\u0437\u0435\u0442\u0430" },
    "zona.media": { tier: "established", name: "\u041C\u0435\u0434\u0438\u0430\u0437\u043E\u043D\u0430" },
    "holod.media": { tier: "established", name: "\u0425\u043E\u043B\u043E\u0434" },
    "thebell.io": { tier: "established", name: "The Bell" },
    "istories.media": { tier: "established", name: "\u0412\u0430\u0436\u043D\u044B\u0435 \u0438\u0441\u0442\u043E\u0440\u0438\u0438" },
    "theins.ru": { tier: "established", name: "The Insider" },
    "svoboda.org": { tier: "established", name: "\u0420\u0430\u0434\u0438\u043E \u0421\u0432\u043E\u0431\u043E\u0434\u0430" },
    "currenttime.tv": { tier: "established", name: "\u041D\u0430\u0441\u0442\u043E\u044F\u0449\u0435\u0435 \u0412\u0440\u0435\u043C\u044F" },
    "kommersant.ru": { tier: "established", name: "\u041A\u043E\u043C\u043C\u0435\u0440\u0441\u0430\u043D\u0442\u044A" },
    "vedomosti.ru": { tier: "established", name: "\u0412\u0435\u0434\u043E\u043C\u043E\u0441\u0442\u0438" },
    "rbc.ru": { tier: "established", name: "\u0420\u0411\u041A" },
    "interfax.ru": { tier: "established", name: "\u0418\u043D\u0442\u0435\u0440\u0444\u0430\u043A\u0441" },
    "forbes.ru": { tier: "established", name: "Forbes" },
    "fontanka.ru": { tier: "established", name: "\u0424\u043E\u043D\u0442\u0430\u043D\u043A\u0430" },
    "lenta.ru": { tier: "established", name: "\u041B\u0435\u043D\u0442\u0430.\u0440\u0443" },
    "themoscowtimes.com": { tier: "established", name: "The Moscow Times" },
    "tass.ru": { tier: "state", name: "\u0422\u0410\u0421\u0421" },
    "tass.com": { tier: "state", name: "\u0422\u0410\u0421\u0421" },
    "ria.ru": { tier: "state", name: "\u0420\u0418\u0410 \u041D\u043E\u0432\u043E\u0441\u0442\u0438" },
    "rt.com": { tier: "state", name: "RT" },
    "sputniknews.com": { tier: "state", name: "Sputnik" },
    "sputnikglobe.com": { tier: "state", name: "Sputnik" },
    "vesti.ru": { tier: "state", name: "\u0412\u0435\u0441\u0442\u0438" },
    "1tv.ru": { tier: "state", name: "\u041F\u0435\u0440\u0432\u044B\u0439 \u043A\u0430\u043D\u0430\u043B" },
    "ntv.ru": { tier: "state", name: "\u041D\u0422\u0412" },
    "cgtn.com": { tier: "state", name: "CGTN" },
    "xinhuanet.com": { tier: "state", name: "\u0421\u0438\u043D\u044C\u0445\u0443\u0430" },
    "globaltimes.cn": { tier: "state", name: "Global Times" },
    "infowars.com": { tier: "low", name: "InfoWars" },
    "naturalnews.com": { tier: "low", name: "Natural News" },
    "beforeitsnews.com": { tier: "low", name: "Before It's News" },
    "worldnewsdailyreport.com": { tier: "low", name: "World News Daily Report" },
    "nationalreport.net": { tier: "low", name: "National Report" },
    "empirenews.net": { tier: "low", name: "Empire News" },
    "huzlers.com": { tier: "low", name: "Huzlers" },
    "yournewswire.com": { tier: "low", name: "YourNewsWire" },
    "newspunch.com": { tier: "low", name: "NewsPunch" },
    "worldtruth.tv": { tier: "low", name: "World Truth" },
    "neonnettle.com": { tier: "low", name: "Neon Nettle" },
    "awarenessact.com": { tier: "low", name: "Awareness Act" },
    "dailybuzzlive.com": { tier: "low", name: "Daily Buzz Live" },
    "theonion.com": { tier: "satire", name: "The Onion" },
    "clickhole.com": { tier: "satire", name: "ClickHole" },
    "babylonbee.com": { tier: "satire", name: "The Babylon Bee" },
    "thedailymash.co.uk": { tier: "satire", name: "The Daily Mash" },
    "waterfordwhispersnews.com": { tier: "satire", name: "Waterford Whispers News" },
    "fognews.ru": { tier: "satire", name: "Fognews" },
    "reductress.com": { tier: "satire", name: "Reductress" },
    "thehardtimes.net": { tier: "satire", name: "The Hard Times" },
    "thebetootaadvocate.com": { tier: "satire", name: "The Betoota Advocate" }
  };
  var BRANDS = [
    { name: "Reuters", re: /(^|\.)reuters([.-]|$)/, real: ["reuters.com"] },
    { name: "BBC", re: /(^|\.)bbc([.-]|$)/, real: ["bbc.com", "bbc.co.uk"] },
    { name: "CNN", re: /(^|\.)cnn([.-]|$)/, real: ["cnn.com"] },
    { name: "Associated Press", re: /(^|\.)(apnews|associatedpress)([.-]|$)/, real: ["apnews.com"] },
    { name: "The New York Times", re: /(^|\.)(nytimes|newyorktimes)([.-]|$)/, real: ["nytimes.com"] },
    { name: "The Guardian", re: /(^|\.)(theguardian|guardian)([.-]|$)/, real: ["theguardian.com"] },
    { name: "\u0420\u0418\u0410 \u041D\u043E\u0432\u043E\u0441\u0442\u0438", re: /(^|\.)ria([.-]|$)/, real: ["ria.ru"] },
    { name: "\u0422\u0410\u0421\u0421", re: /(^|\.)tass([.-]|$)/, real: ["tass.ru", "tass.com"] },
    { name: "\u0420\u0411\u041A", re: /(^|\.)rbc([.-]|$)/, real: ["rbc.ru"] },
    { name: "\u041C\u0435\u0434\u0443\u0437\u0430", re: /(^|\.)meduza([.-]|$)/, real: ["meduza.io"] },
    { name: "\u041A\u043E\u043C\u043C\u0435\u0440\u0441\u0430\u043D\u0442\u044A", re: /(^|\.)kommersant([.-]|$)/, real: ["kommersant.ru"] },
    { name: "\u041B\u0435\u043D\u0442\u0430.\u0440\u0443", re: /(^|\.)lenta([.-]|$)/, real: ["lenta.ru"] }
  ];
  var SUSPICIOUS_TLDS = /* @__PURE__ */ new Set([
    "xyz",
    "top",
    "click",
    "gq",
    "ml",
    "cf",
    "tk",
    "buzz",
    "rest",
    "monster",
    "country",
    "download",
    "review",
    "fit",
    "cam",
    "quest",
    "cyou",
    "icu"
  ]);
  var SHORTENERS = /* @__PURE__ */ new Set([
    "bit.ly",
    "tinyurl.com",
    "t.co",
    "goo.gl",
    "ow.ly",
    "is.gd",
    "buff.ly",
    "vk.cc",
    "clck.ru",
    "cutt.ly",
    "rb.gy",
    "tiny.cc",
    "shorturl.at"
  ]);
  function lookupSource(hostname) {
    const host = String(hostname || "").replace(/^www\./i, "").toLowerCase();
    if (!host) return null;
    const parts = host.split(".");
    for (let index = 0; index < parts.length - 1; index += 1) {
      const candidate = parts.slice(index).join(".");
      if (SOURCES[candidate]) return { ...SOURCES[candidate], domain: candidate };
    }
    return null;
  }

  // ../../workspace/auris/lib/evaluate.js
  var FILE_RE = /\.(pdf|zip|rar|7z|png|jpe?g|gif|webp|svg|mp4|mp3|wav|docx?|xlsx?|pptx?|apk|dmg)($|\?)/i;
  var RHETORIC_RULES = [
    {
      re: new RegExp("\u0448\u043E\u043A(?:\u0438\u0440\u0443\u044E\u0449\\p{L}*|\u0438\u0440\u043E\u0432\u0430\u043B\\p{L}*)?|\u0441\u0435\u043D\u0441\u0430\u0446|\u0432\u0440\u0430\u0447\u0438 (?:\u0432 \u0448\u043E\u043A\u0435|\u0441\u043A\u0440\u044B\u0432\u0430\\p{L}*|\u043C\u043E\u043B\u0447\u0430\u0442|\u0432 \u0443\u0436\u0430\u0441\u0435)|\u0432\u0430\u043C \u043D\u0435 \u0440\u0430\u0441\u0441\u043A\u0430\u0436\u0443\u0442|\u044D\u0442\u043E \u0441\u043A\u0440\u044B\u0432\u0430\u044E\u0442|\u0437\u0430\u043F\u0440\u0435\u0449[\u0435\u0451]\u043D\u043D\\p{L}{1,6} \u043F\u0440\u0430\u0432\u0434|\u0443\u0441\u043F\u0435\u0439(?:\u0442\u0435)? \u043F\u043E\u0441\u043C\u043E\u0442\u0440\u0435\u0442\u044C|\u043D\u0438\u043A\u0442\u043E \u043D\u0435 \u043E\u0436\u0438\u0434\u0430\u043B|\u0441\u043B\u0438\u0432 \u043F\u0440\u0430\u0432\u0434", "iu"),
      penalty: 16,
      note: "\u0424\u043E\u0440\u043C\u0443\u043B\u0438\u0440\u043E\u0432\u043A\u0430 \u043E\u0431\u0435\u0449\u0430\u0435\u0442 \u0442\u0430\u0439\u043D\u0443 \u0438 \u0448\u043E\u043A \u0432\u043C\u0435\u0441\u0442\u043E \u0444\u0430\u043A\u0442\u043E\u0432."
    },
    {
      re: /you won'?t believe|shocking|doctors hate|they don'?t want you|wake up,? sheeple|share before|mainstream media hides|what happened next/i,
      penalty: 16,
      note: "\u041A\u043B\u0438\u043A\u0431\u0435\u0439\u0442: \u044D\u043C\u043E\u0446\u0438\u044F \u0432\u043C\u0435\u0441\u0442\u043E \u043F\u0440\u043E\u0432\u0435\u0440\u044F\u0435\u043C\u043E\u0433\u043E \u0444\u0430\u043A\u0442\u0430."
    },
    {
      re: /[!]{3,}|[?]{3,}/,
      penalty: 8,
      note: "\u041A\u0430\u0441\u043A\u0430\u0434 \u0432\u043E\u0441\u043A\u043B\u0438\u0446\u0430\u043D\u0438\u0439 \u0438\u043B\u0438 \u0432\u043E\u043F\u0440\u043E\u0441\u043E\u0432."
    },
    {
      re: /100\s*%|гарантированно вылеч|чипирован|рептилоид|плоская земля|биоробот/i,
      penalty: 18,
      note: "\u0410\u0431\u0441\u043E\u043B\u044E\u0442\u043D\u044B\u0435 \u0438\u043B\u0438 \u043A\u043E\u043D\u0441\u043F\u0438\u0440\u043E\u043B\u043E\u0433\u0438\u0447\u0435\u0441\u043A\u0438\u0435 \u0444\u043E\u0440\u043C\u0443\u043B\u044B."
    }
  ];
  function shouldInspect({ href, anchorText = "", pageUrl = "" } = {}, settings = {}) {
    const url = safeUrl(href);
    if (!url || !/^https?:$/.test(url.protocol)) return false;
    if (FILE_RE.test(url.pathname)) return false;
    if (pageUrl && isSameDocumentHash(url, pageUrl)) return false;
    if (settings.newsOnly === false) return true;
    return isStoryLink(url, anchorText);
  }
  function evaluateLink({ href, anchorText = "" } = {}) {
    const url = safeUrl(href);
    if (!url || !/^https?:$/.test(url.protocol)) {
      return blank(href, "\u041D\u0435\u043F\u043E\u0445\u043E\u0436\u0435 \u043D\u0430 \u043E\u0431\u044B\u0447\u043D\u0443\u044E \u0432\u0435\u0431-\u0441\u0441\u044B\u043B\u043A\u0443.");
    }
    const domain = url.hostname.replace(/^www\./i, "").toLowerCase();
    const source = lookupSource(domain);
    const text = `${anchorText || ""} ${decodePath(url.pathname)}`.replace(/\s+/g, " ").trim();
    const rhetoric = analyzeRhetoric(text);
    const flags = {
      impersonation: false,
      satire: false,
      shortener: false,
      harsh: false,
      state: source?.tier === "state"
    };
    const reasons = [];
    let score = 50;
    const meters = {
      source: 48,
      rhetoric: rhetoric.meter,
      url: 78,
      cross: source ? 62 : 34
    };
    if (source?.tier === "satire") {
      flags.satire = true;
      reasons.push(`${source.name} \u043F\u0443\u0431\u043B\u0438\u043A\u0443\u0435\u0442 \u0441\u0430\u0442\u0438\u0440\u0443. \u042D\u0442\u043E \u0432\u044B\u043C\u044B\u0441\u0435\u043B \u043F\u043E \u0444\u043E\u0440\u043C\u0430\u0442\u0443, \u0430 \u043D\u0435 \u043D\u043E\u0432\u043E\u0441\u0442\u044C.`);
      return pack({
        href: url.href,
        domain,
        score: 38,
        verdict: "satire",
        confidence: 0.86,
        summary: VERDICT_SUMMARY.satire,
        reasons,
        meters: { source: 34, rhetoric: 28, url: 70, cross: 30 },
        flags,
        source: { name: source.name, tier: source.tier, domain: source.domain },
        rhetoricPenalty: 0
      });
    }
    if (SHORTENERS.has(domain)) {
      flags.shortener = true;
      meters.url = 32;
      meters.cross = 22;
      reasons.push("\u0421\u0435\u0440\u0432\u0438\u0441 \u043A\u043E\u0440\u043E\u0442\u043A\u0438\u0445 \u0441\u0441\u044B\u043B\u043E\u043A \u0441\u043A\u0440\u044B\u0432\u0430\u0435\u0442 \u043A\u043E\u043D\u0435\u0447\u043D\u044B\u0439 \u0430\u0434\u0440\u0435\u0441.");
    }
    if (source?.tier === "high" || source?.tier === "factcheck") {
      score += source.tier === "factcheck" ? 34 : 32;
      meters.source = source.tier === "factcheck" ? 94 : 90;
      meters.cross = 84;
      reasons.push(
        source.tier === "factcheck" ? `${source.name} \u2014 \u0444\u0430\u043A\u0442\u0447\u0435\u043A\u0438\u043D\u0433\u043E\u0432\u0430\u044F \u0440\u0435\u0434\u0430\u043A\u0446\u0438\u044F.` : `${source.name} \u0432\u0445\u043E\u0434\u0438\u0442 \u0432 \u043A\u043E\u043D\u0442\u0443\u0440 \u0440\u0435\u0434\u0430\u043A\u0446\u0438\u0439 \u0441 \u0436\u0451\u0441\u0442\u043A\u043E\u0439 \u043F\u0440\u043E\u0432\u0435\u0440\u043A\u043E\u0439 \u0444\u0430\u043A\u0442\u043E\u0432.`
      );
    } else if (source?.tier === "established") {
      score += 18;
      meters.source = 74;
      meters.cross = 66;
      reasons.push(`\u0423 ${source.name} \u0435\u0441\u0442\u044C \u0440\u0435\u0434\u0430\u043A\u0446\u0438\u044F \u0438 \u043F\u0440\u0430\u043A\u0442\u0438\u043A\u0430 \u043F\u043E\u043F\u0440\u0430\u0432\u043E\u043A. \u042D\u0442\u043E \u043D\u0435 \u043E\u0441\u0432\u043E\u0431\u043E\u0436\u0434\u0430\u0435\u0442 \u0442\u0435\u043A\u0441\u0442 \u043E\u0442 \u043F\u0440\u043E\u0432\u0435\u0440\u043A\u0438.`);
    } else if (source?.tier === "state") {
      meters.source = 42;
      meters.cross = 38;
      reasons.push(`${source.name} \u2014 \u0433\u043E\u0441\u0443\u0434\u0430\u0440\u0441\u0442\u0432\u0435\u043D\u043D\u043E\u0435 \u0438\u043B\u0438 \u0430\u0444\u0444\u0438\u043B\u0438\u0440\u043E\u0432\u0430\u043D\u043D\u043E\u0435 \u043C\u0435\u0434\u0438\u0430. \u042D\u0442\u043E \u043D\u0435 \u044F\u0440\u043B\u044B\u043A \xAB\u0444\u0435\u0439\u043A\xBB, \u043D\u043E \u043F\u043E\u0432\u0435\u0441\u0442\u043A\u0430 \u043C\u043E\u0436\u0435\u0442 \u0431\u044B\u0442\u044C \u0443\u043F\u0440\u0430\u0432\u043B\u044F\u0435\u043C\u043E\u0439.`);
    } else if (source?.tier === "low") {
      score -= 36;
      meters.source = 10;
      meters.cross = 16;
      flags.harsh = true;
      reasons.push(`${source.name} \u043C\u043D\u043E\u0433\u043E\u043A\u0440\u0430\u0442\u043D\u043E \u043E\u0442\u043C\u0435\u0447\u0430\u043B\u0441\u044F \u0444\u0430\u043A\u0442\u0447\u0435\u043A\u0435\u0440\u0430\u043C\u0438 \u0437\u0430 \u0432\u044B\u0434\u0443\u043C\u0430\u043D\u043D\u044B\u0435 \u0438\u043B\u0438 \u0432\u0432\u043E\u0434\u044F\u0449\u0438\u0435 \u0432 \u0437\u0430\u0431\u043B\u0443\u0436\u0434\u0435\u043D\u0438\u0435 \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B\u044B.`);
    } else if (!flags.shortener) {
      reasons.push("\u0414\u043E\u043C\u0435\u043D\u0430 \u043D\u0435\u0442 \u0432 \u043B\u043E\u043A\u0430\u043B\u044C\u043D\u043E\u0439 \u0431\u0430\u0437\u0435 \u0440\u0435\u043F\u0443\u0442\u0430\u0446\u0438\u0438. \u041D\u0435\u0438\u0437\u0432\u0435\u0441\u0442\u043D\u043E\u0441\u0442\u044C \u2014 \u043D\u0435 \u0434\u043E\u043A\u0430\u0437\u0430\u0442\u0435\u043B\u044C\u0441\u0442\u0432\u043E \u0444\u0435\u0439\u043A\u0430.");
    }
    const impersonation = source ? null : detectImpersonation(domain);
    if (impersonation) {
      flags.impersonation = true;
      flags.harsh = true;
      score -= 36;
      meters.source = 8;
      meters.url = 16;
      reasons.unshift(`\u0410\u0434\u0440\u0435\u0441 \u043C\u0430\u0441\u043A\u0438\u0440\u0443\u0435\u0442\u0441\u044F \u043F\u043E\u0434 ${impersonation}, \u043D\u043E \u044D\u0442\u043E \u043D\u0435 \u043E\u0444\u0438\u0446\u0438\u0430\u043B\u044C\u043D\u044B\u0439 \u0434\u043E\u043C\u0435\u043D.`);
    }
    if (url.protocol !== "https:") {
      score -= 12;
      meters.url -= 26;
      flags.harsh = true;
      reasons.push("\u0421\u0442\u0440\u0430\u043D\u0438\u0446\u0430 \u043E\u0442\u043A\u0440\u044B\u0432\u0430\u0435\u0442\u0441\u044F \u0431\u0435\u0437 HTTPS.");
    }
    const tld = domain.split(".").pop();
    if (!source && SUSPICIOUS_TLDS.has(tld)) {
      score -= 16;
      meters.url -= 22;
      flags.harsh = true;
      reasons.push(`\u0417\u043E\u043D\u0430 .${tld} \u0447\u0430\u0441\u0442\u043E \u0432\u0441\u0442\u0440\u0435\u0447\u0430\u0435\u0442\u0441\u044F \u0443 \u043E\u0434\u043D\u043E\u0440\u0430\u0437\u043E\u0432\u044B\u0445 \u0441\u0430\u0439\u0442\u043E\u0432.`);
    }
    const labels = domain.split(".").slice(0, -1).join(".");
    if (!source && (labels.match(/-/g) || []).length >= 3) {
      score -= 8;
      meters.url -= 10;
      flags.harsh = true;
      reasons.push("\u0412 \u0430\u0434\u0440\u0435\u0441\u0435 \u043C\u043D\u043E\u0433\u043E \u0434\u0435\u0444\u0438\u0441\u043E\u0432 \u2014 \u0442\u0438\u043F\u0438\u0447\u043D\u044B\u0439 \u043F\u0440\u0438\u0451\u043C \u043F\u043E\u0434\u0434\u0435\u043B\u044C\u043D\u044B\u0445 \u0441\u0430\u0439\u0442\u043E\u0432.");
    }
    if (!source && /\d{4,}/.test(labels)) {
      score -= 6;
      meters.url -= 8;
      flags.harsh = true;
    }
    if (rhetoric.penalty) {
      score -= rhetoric.penalty;
      flags.harsh = flags.harsh || rhetoric.penalty >= 14;
      for (const note of rhetoric.notes) reasons.push(note);
    }
    if ([...url.searchParams.keys()].length > 7) {
      score -= 4;
      meters.url -= 6;
    }
    score = clamp(Math.round(score), 3, 96);
    const verdict = decideVerdict({ score, source, flags });
    const confidence = decideConfidence({ source, flags, rhetoric });
    const summary = summaryFor({ verdict, source, flags });
    return pack({
      href: url.href,
      domain,
      score,
      verdict,
      confidence,
      summary,
      reasons,
      meters: normalizeMeters(meters),
      flags,
      source: source ? { name: source.name, tier: source.tier, domain: source.domain } : null,
      rhetoricPenalty: rhetoric.penalty
    });
  }
  function analyzeRhetoric(text) {
    const value = String(text || "").replace(/\s+/g, " ").trim();
    if (!value) return { meter: 74, penalty: 0, notes: [] };
    let penalty = 0;
    const notes = [];
    for (const rule of RHETORIC_RULES) {
      if (rule.re.test(value)) {
        penalty += rule.penalty;
        notes.push(rule.note);
      }
    }
    const letters = value.replace(/[^A-Za-zА-Яа-яЁё]/g, "");
    const caps = value.replace(/[^A-ZА-ЯЁ]/g, "");
    if (letters.length >= 12 && caps.length / letters.length > 0.72) {
      penalty += 10;
      notes.push("\u0422\u0435\u043A\u0441\u0442 \u043F\u043E\u0447\u0442\u0438 \u0446\u0435\u043B\u0438\u043A\u043E\u043C \u043D\u0430\u0431\u0440\u0430\u043D \u043A\u0430\u043F\u0441\u043E\u043C.");
    }
    penalty = Math.min(penalty, 32);
    return {
      meter: Math.round(clamp(88 - penalty * 2.5, 8, 92)),
      penalty,
      notes: notes.slice(0, 2)
    };
  }
  function settle(result, patch = {}) {
    const flags = patch.flags || result.flags;
    const score = clamp(Math.round(patch.score ?? result.score), 3, 96);
    const source = result.source || null;
    const verdict = patch.verdict || decideVerdict({ score, source, flags });
    const reasons = (patch.reasons || result.reasons || []).slice(0, 6);
    const rhetoricPenalty = patch.rhetoricPenalty ?? result.rhetoricPenalty ?? 0;
    const rest = { ...patch };
    delete rest.score;
    delete rest.flags;
    delete rest.verdict;
    delete rest.reasons;
    delete rest.rhetoricPenalty;
    delete rest.summary;
    delete rest.confidence;
    return {
      ...result,
      ...rest,
      score,
      flags,
      verdict,
      reasons,
      rhetoricPenalty,
      summary: patch.summary || summaryFor({ verdict, source, flags }),
      confidence: patch.confidence ?? decideConfidence({ source, flags, rhetoric: { penalty: rhetoricPenalty } })
    };
  }
  function summaryFor({ verdict, source, flags }) {
    if (flags.impersonation) {
      return "\u0410\u0434\u0440\u0435\u0441 \u043F\u043E\u0434\u0440\u0430\u0436\u0430\u0435\u0442 \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u043E\u043C\u0443 \u0438\u0437\u0434\u0430\u043D\u0438\u044E. \u0422\u0430\u043A\u043E\u0439 \u043F\u0440\u0438\u0451\u043C \u0447\u0430\u0441\u0442\u043E \u0438\u0441\u043F\u043E\u043B\u044C\u0437\u0443\u044E\u0442 \u043F\u043E\u0434\u0434\u0435\u043B\u044C\u043D\u044B\u0435 \u0441\u0442\u0440\u0430\u043D\u0438\u0446\u044B.";
    }
    if (flags.shortener && verdict === "insufficient") {
      return "\u041F\u043E\u043A\u0430 \u0432\u0438\u0434\u0435\u043D \u0442\u043E\u043B\u044C\u043A\u043E \u043A\u043E\u0440\u043E\u0442\u043A\u0438\u0439 \u0430\u0434\u0440\u0435\u0441. \u0423\u0433\u043B\u0443\u0431\u043B\u0451\u043D\u043D\u0430\u044F \u043F\u0440\u043E\u0432\u0435\u0440\u043A\u0430 \u043F\u043E\u043F\u0440\u043E\u0431\u0443\u0435\u0442 \u0440\u0430\u0441\u043A\u0440\u044B\u0442\u044C, \u043A\u0443\u0434\u0430 \u043E\u043D \u0432\u0435\u0434\u0451\u0442.";
    }
    if (source?.tier === "state" && verdict === "mixed") {
      return `${source.name} \u2014 \u0433\u043E\u0441\u0443\u0434\u0430\u0440\u0441\u0442\u0432\u0435\u043D\u043D\u043E\u0435 \u0438\u043B\u0438 \u0430\u0444\u0444\u0438\u043B\u0438\u0440\u043E\u0432\u0430\u043D\u043D\u043E\u0435 \u043C\u0435\u0434\u0438\u0430. \u042D\u0442\u043E \u043D\u0435 \u044F\u0440\u043B\u044B\u043A \xAB\u0444\u0435\u0439\u043A\xBB, \u043D\u043E \u043F\u043E\u0432\u0435\u0441\u0442\u043A\u0430 \u043C\u043E\u0436\u0435\u0442 \u0431\u044B\u0442\u044C \u0443\u043F\u0440\u0430\u0432\u043B\u044F\u0435\u043C\u043E\u0439.`;
    }
    if (source?.tier === "low" && (verdict === "likely_fake" || verdict === "suspicious")) {
      return `${source.name} \u043C\u043D\u043E\u0433\u043E\u043A\u0440\u0430\u0442\u043D\u043E \u043E\u0442\u043C\u0435\u0447\u0430\u043B\u0441\u044F \u0444\u0430\u043A\u0442\u0447\u0435\u043A\u0435\u0440\u0430\u043C\u0438 \u0437\u0430 \u0432\u044B\u0434\u0443\u043C\u0430\u043D\u043D\u044B\u0435 \u0438\u043B\u0438 \u0432\u0432\u043E\u0434\u044F\u0449\u0438\u0435 \u0432 \u0437\u0430\u0431\u043B\u0443\u0436\u0434\u0435\u043D\u0438\u0435 \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B\u044B.`;
    }
    if (!source && verdict === "mixed" && !flags.harsh) {
      return "\u0422\u0435\u043A\u0441\u0442 \u0432\u044B\u0433\u043B\u044F\u0434\u0438\u0442 \u0441\u043E\u0431\u0440\u0430\u043D\u043D\u0435\u0435 \u043A\u043B\u0438\u043A\u0431\u0435\u0439\u0442\u0430, \u043D\u043E \u0434\u043E\u043C\u0435\u043D\u0430 \u043D\u0435\u0442 \u0432 \u0431\u0430\u0437\u0435 \u0440\u0435\u043F\u0443\u0442\u0430\u0446\u0438\u0438. \u042D\u0442\u043E \u043D\u0435 \u043F\u043E\u0434\u0442\u0432\u0435\u0440\u0436\u0434\u0435\u043D\u0438\u0435 \u0438 \u043D\u0435 \u0444\u0435\u0439\u043A.";
    }
    if (!source && (verdict === "leaning" || verdict === "reliable")) {
      return "\u041F\u043E \u0430\u0434\u0440\u0435\u0441\u0443 \u043D\u0435 \u0432\u0438\u0434\u043D\u043E \u0438\u0437\u0432\u0435\u0441\u0442\u043D\u043E\u0439 \u0440\u0435\u0434\u0430\u043A\u0446\u0438\u0438. \u0412\u044B\u0441\u043E\u043A\u0438\u0439 \u0438\u043D\u0434\u0435\u043A\u0441 \u0441\u0430\u043C \u043F\u043E \u0441\u0435\u0431\u0435 \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B \u043D\u0435 \u043F\u043E\u0434\u0442\u0432\u0435\u0440\u0436\u0434\u0430\u0435\u0442.";
    }
    return VERDICT_SUMMARY[verdict];
  }
  function decideVerdict({ score, source, flags }) {
    if (source?.tier === "satire" || flags.satire) return "satire";
    if (flags.shortener && !flags.impersonation && score > 28) return "insufficient";
    if (flags.impersonation) return score < 42 ? "likely_fake" : "suspicious";
    if (source?.tier === "low") return score < 55 ? "likely_fake" : "suspicious";
    if (source?.tier === "state") return score < 40 ? "suspicious" : "mixed";
    if (!source && !flags.harsh) return score >= 64 ? "mixed" : "insufficient";
    if (score < 34) return "likely_fake";
    if (score < 48) return "suspicious";
    if (score < 64) return "mixed";
    if (score < 82) return "leaning";
    return "reliable";
  }
  function decideConfidence({ source, flags, rhetoric }) {
    let confidence = 0.34;
    if (source) confidence += 0.28;
    if (source && ["high", "low", "satire", "factcheck"].includes(source.tier)) confidence += 0.16;
    if (flags.impersonation) confidence += 0.18;
    if (flags.harsh) confidence += 0.08;
    if (!source && rhetoric.penalty === 0) confidence -= 0.04;
    return clamp(Number(confidence.toFixed(2)), 0.22, 0.9);
  }
  function detectImpersonation(domain) {
    for (const brand of BRANDS) {
      if (brand.re.test(domain)) return brand.name;
    }
    return null;
  }
  function isStoryLink(url, anchorText) {
    const domain = url.hostname.replace(/^www\./i, "").toLowerCase();
    if (lookupSource(domain) || SHORTENERS.has(domain)) return true;
    const text = String(anchorText || "").trim();
    if (text.length >= 40 && /\s/.test(text)) return true;
    if (/\/(news|article|articles|story|stories|politics|world|novosti|newsroom|press)\//i.test(url.pathname)) return true;
    if (/\/20\d{2}\/\d{2}\//.test(url.pathname)) return true;
    const slug = url.pathname.split("/").filter(Boolean).pop() || "";
    return slug.length >= 24 && slug.includes("-") && text.length >= 18;
  }
  function isSameDocumentHash(url, pageUrl) {
    try {
      const page = new URL(pageUrl);
      return url.origin === page.origin && url.pathname === page.pathname && url.search === page.search && Boolean(url.hash);
    } catch {
      return false;
    }
  }
  function pack(result) {
    return {
      href: result.href,
      domain: result.domain,
      score: result.score,
      verdict: result.verdict,
      confidence: result.confidence,
      summary: result.summary,
      reasons: result.reasons.slice(0, 6),
      meters: result.meters,
      flags: result.flags,
      source: result.source,
      engine: "local",
      title: "",
      rhetoricPenalty: result.rhetoricPenalty || 0
    };
  }
  function blank(href, summary) {
    return {
      href: String(href || ""),
      domain: "",
      score: 50,
      verdict: "insufficient",
      confidence: 0.22,
      summary,
      reasons: [summary],
      meters: { source: 40, rhetoric: 50, url: 40, cross: 20 },
      flags: { impersonation: false, satire: false, shortener: false, harsh: false, state: false },
      source: null,
      engine: "local",
      title: "",
      rhetoricPenalty: 0
    };
  }
  function normalizeMeters(meters) {
    return {
      source: clamp(Math.round(meters.source), 0, 100),
      rhetoric: clamp(Math.round(meters.rhetoric), 0, 100),
      url: clamp(Math.round(meters.url), 0, 100),
      cross: clamp(Math.round(meters.cross), 0, 100)
    };
  }
  function safeUrl(href) {
    try {
      return new URL(href);
    } catch {
      return null;
    }
  }
  function decodePath(pathname) {
    try {
      return decodeURIComponent(pathname).replace(/[-_/]+/g, " ");
    } catch {
      return pathname.replace(/[-_/]+/g, " ");
    }
  }

  // ../../workspace/auris/lib/present.js
  function present(result, extra = {}) {
    const verdict = result?.verdict || "insufficient";
    const engine = result?.engine || "local";
    return {
      phase: extra.phase || "ready",
      pending: Boolean(extra.pending),
      score: Number.isFinite(result?.score) ? result.score : 50,
      verdict,
      verdictLabel: extra.phase === "loading" ? "\u0421\u0447\u0438\u0442\u044B\u0432\u0430\u044E" : VERDICT_LABEL[verdict] || "\u041E\u0446\u0435\u043D\u043A\u0430",
      summary: extra.phase === "loading" ? "\u0421\u043C\u043E\u0442\u0440\u044E \u0434\u043E\u043C\u0435\u043D, \u0444\u043E\u0440\u043C\u0443\u043B\u0438\u0440\u043E\u0432\u043A\u0443 \u0438 \u0441\u043B\u0435\u0434\u044B \u0430\u0434\u0440\u0435\u0441\u0430." : result?.summary || "",
      confidence: Number.isFinite(result?.confidence) ? result.confidence : 0,
      engine,
      engineLabel: extra.pending && extra.phase !== "loading" ? "\u0423\u0442\u043E\u0447\u043D\u044F\u044E" : ENGINE_LABEL[engine] || ENGINE_LABEL.local,
      meters: result?.meters || { source: 20, rhetoric: 20, url: 20, cross: 12 },
      reasons: Array.isArray(result?.reasons) ? result.reasons : [],
      domain: result?.domain || "",
      href: result?.href || "",
      title: result?.title || "",
      textExcerpt: result?.textExcerpt || "",
      languageNote: result?.languageNote || ""
    };
  }

  // ../../workspace/auris/lib/text.js
  var MIN_ARTICLE_CHARS = 200;
  var ATTRIBUTION = /по данным|сообщ(?:ил[аи]?|ает)|заявил[аи]?|according to|said that|told reporters|агентство|цитирует/i;
  var QUOTE = /[«"][^»"]{12,}[»"]/;
  var DATE = /\b(19|20)\d{2}\b|\d{1,2}\s+(января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря|january|february|march|april|may|june|july|august|september|october|november|december)/i;
  var ANON = /эксперты считают|учёные доказали|ученые доказали|источники сообщают|scientists say|experts agree/i;
  var SHARE = /поделитесь|репостните|перешлите друзьям|share this|tag a friend/i;
  var FIRST = /я лично|мне рассказали|я сам видел|i personally|my friend told me/i;
  var SHORT_PAGE = "\u041F\u043E\u0445\u043E\u0436\u0435, \u044D\u0442\u043E \u043D\u0435 \u0441\u0442\u0430\u0442\u044C\u044F: \u0442\u0435\u043A\u0441\u0442\u0430 \u0441\u043B\u0438\u0448\u043A\u043E\u043C \u043C\u0430\u043B\u043E, \u0447\u0442\u043E\u0431\u044B \u0441\u0443\u0434\u0438\u0442\u044C \u043F\u043E \u0441\u043E\u0434\u0435\u0440\u0436\u0430\u043D\u0438\u044E.";
  var SHORT_BIT = "\u0422\u0435\u043A\u0441\u0442\u0430 \u043C\u0430\u043B\u043E \u0434\u043B\u044F \u0441\u043E\u0434\u0435\u0440\u0436\u0430\u0442\u0435\u043B\u044C\u043D\u043E\u0439 \u043E\u0446\u0435\u043D\u043A\u0438. \u042D\u0442\u043E \u043D\u0435 \u0437\u043D\u0430\u0447\u0438\u0442, \u0447\u0442\u043E \u043C\u0430\u0442\u0435\u0440\u0438\u0430\u043B \u0444\u0435\u0439\u043A\u043E\u0432\u044B\u0439.";
  var LANGUAGE_NOTE = {
    ru: "\u0422\u0435\u043A\u0441\u0442 \u043D\u0430 \u0440\u0443\u0441\u0441\u043A\u043E\u043C. \u042F\u0437\u044B\u043A \u0441\u0430\u043C \u043F\u043E \u0441\u0435\u0431\u0435 \u043D\u0438\u0447\u0435\u0433\u043E \u043D\u0435 \u0434\u043E\u043A\u0430\u0437\u044B\u0432\u0430\u0435\u0442.",
    en: "\u0422\u0435\u043A\u0441\u0442 \u043D\u0430 \u0430\u043D\u0433\u043B\u0438\u0439\u0441\u043A\u043E\u043C. \u042F\u0437\u044B\u043A \u0441\u0430\u043C \u043F\u043E \u0441\u0435\u0431\u0435 \u043D\u0438\u0447\u0435\u0433\u043E \u043D\u0435 \u0434\u043E\u043A\u0430\u0437\u044B\u0432\u0430\u0435\u0442.",
    mixed: "\u0412 \u0442\u0435\u043A\u0441\u0442\u0435 \u0441\u043C\u0435\u0448\u0430\u043D\u044B \u0440\u0443\u0441\u0441\u043A\u0438\u0439 \u0438 \u0430\u043D\u0433\u043B\u0438\u0439\u0441\u043A\u0438\u0439.",
    unknown: ""
  };
  function detectLanguage(text) {
    const value = String(text || "");
    const cyr = (value.match(/[А-Яа-яЁё]/g) || []).length;
    const lat = (value.match(/[A-Za-z]/g) || []).length;
    const letters = cyr + lat;
    if (letters < 20) return "unknown";
    if (cyr / letters >= 0.7) return "ru";
    if (lat / letters >= 0.7) return "en";
    if (cyr > 8 && lat > 8) return "mixed";
    return "unknown";
  }
  function excerptOf(text, max = 320) {
    const value = String(text || "").replace(/\s+/g, " ").trim();
    if (value.length <= max) return value;
    const cut = value.slice(0, max);
    const space = cut.lastIndexOf(" ");
    return `${(space > 180 ? cut.slice(0, space) : cut).trim()}\u2026`;
  }
  function scoreArticleText(text) {
    const value = String(text || "").replace(/\s+/g, " ").trim();
    const language = detectLanguage(value);
    if (value.length < 80) {
      return { score: 50, penalty: 0, notes: [], meter: 50, language, enough: false };
    }
    let score = 62;
    const notes = [];
    if (ATTRIBUTION.test(value)) {
      score += 6;
      notes.push("\u0415\u0441\u0442\u044C \u043E\u0442\u0441\u044B\u043B\u043A\u0430 \u043A \u0438\u0441\u0442\u043E\u0447\u043D\u0438\u043A\u0443 \u0438\u043B\u0438 \u0443\u0447\u0430\u0441\u0442\u043D\u0438\u043A\u0443 \u0441\u043E\u0431\u044B\u0442\u0438\u044F.");
    }
    if (QUOTE.test(value)) score += 4;
    if (DATE.test(value)) score += 3;
    const rhetoric = analyzeRhetoric(value);
    if (rhetoric.penalty) notes.push(...rhetoric.notes);
    score -= rhetoric.penalty;
    if (ANON.test(value)) {
      score -= 8;
      notes.push("\u041E\u043F\u043E\u0440\u0430 \u043D\u0430 \u0431\u0435\u0437\u044B\u043C\u044F\u043D\u043D\u044B\u0445 \xAB\u044D\u043A\u0441\u043F\u0435\u0440\u0442\u043E\u0432\xBB \u0431\u0435\u0437 \u0438\u043C\u0435\u043D\u0438 \u0438 \u0434\u043E\u043B\u0436\u043D\u043E\u0441\u0442\u0438.");
    }
    if (SHARE.test(value)) {
      score -= 6;
      notes.push("\u0422\u0435\u043A\u0441\u0442 \u043F\u0440\u043E\u0441\u0438\u0442 \u0440\u0435\u043F\u043E\u0441\u0442 \u0432\u043C\u0435\u0441\u0442\u043E \u043F\u0440\u043E\u0432\u0435\u0440\u044F\u0435\u043C\u043E\u0433\u043E \u0444\u0430\u043A\u0442\u0430.");
    }
    if (FIRST.test(value)) {
      score -= 6;
      notes.push("\u041F\u043E\u0434\u0430\u0447\u0430 \u043E\u0442 \u043F\u0435\u0440\u0432\u043E\u0433\u043E \u043B\u0438\u0446\u0430 \u0431\u0435\u0437 \u043D\u0435\u0437\u0430\u0432\u0438\u0441\u0438\u043C\u043E\u0439 \u043E\u043F\u043E\u0440\u044B.");
    }
    return {
      score: clamp(Math.round(score), 8, 92),
      penalty: rhetoric.penalty,
      notes: notes.slice(0, 3),
      meter: rhetoric.meter,
      language,
      enough: value.length >= MIN_ARTICLE_CHARS
    };
  }
  function applyText(result, text, { mode = "hover" } = {}) {
    const value = String(text || "").replace(/\s+/g, " ").trim().slice(0, 8e3);
    const language = detectLanguage(value);
    const carried = {
      ...result,
      language,
      languageNote: LANGUAGE_NOTE[language] || "",
      textExcerpt: excerptOf(value),
      textChars: value.length
    };
    if (!value) return { ...result, language: "unknown", languageNote: "", textExcerpt: "", textChars: 0 };
    const explicit = mode === "page" || mode === "selection" || mode === "paste";
    if (value.length < 80) {
      if (!explicit) return carried;
      return shortResult(carried, mode === "page" ? SHORT_PAGE : SHORT_BIT);
    }
    if (mode === "page" && value.length < MIN_ARTICLE_CHARS) return shortResult(carried, SHORT_PAGE);
    if (mode === "hover" && value.length < MIN_ARTICLE_CHARS) {
      return {
        ...carried,
        reasons: unique([
          "\u0421\u043E \u0441\u0442\u0440\u0430\u043D\u0438\u0446\u044B \u043F\u0440\u043E\u0447\u0438\u0442\u0430\u043D \u0442\u043E\u043B\u044C\u043A\u043E \u043A\u043E\u0440\u043E\u0442\u043A\u0438\u0439 \u0444\u0440\u0430\u0433\u043C\u0435\u043D\u0442 \u2014 \u0432\u0435\u0440\u0434\u0438\u043A\u0442 \u043D\u0430 \u043D\u0451\u043C \u043D\u0435 \u0441\u0442\u0440\u043E\u0438\u0442\u0441\u044F.",
          ...result.reasons
        ]).slice(0, 6)
      };
    }
    if (result.verdict === "satire" || result.flags?.satire) {
      return {
        ...carried,
        meters: { ...result.meters, rhetoric: scoreArticleText(value).meter }
      };
    }
    const scored = scoreArticleText(value);
    const weight = value.length < MIN_ARTICLE_CHARS ? 0.22 : value.length < 600 ? 0.28 : 0.45;
    const blended = Math.round(result.score * (1 - weight) + scored.score * weight);
    const flags = {
      ...result.flags,
      harsh: Boolean(result.flags?.harsh) || scored.penalty >= 14
    };
    return settle(result, {
      score: railScore(result, blended),
      flags,
      reasons: unique([...scored.notes, ...result.reasons]),
      rhetoricPenalty: Math.max(result.rhetoricPenalty || 0, scored.penalty),
      meters: {
        ...result.meters,
        rhetoric: scored.meter,
        cross: clamp(Math.round((result.meters?.cross || 34) * 0.55 + 74 * 0.45), 0, 100)
      },
      language,
      languageNote: carried.languageNote,
      textExcerpt: carried.textExcerpt,
      textChars: value.length
    });
  }
  function shortResult(result, summary) {
    return {
      ...result,
      verdict: result.verdict === "satire" ? "satire" : "insufficient",
      summary,
      reasons: unique([SHORT_BIT, ...result.reasons]).slice(0, 6),
      confidence: Math.min(result.confidence ?? 0.3, 0.38)
    };
  }
  function railScore(result, blended) {
    let score = blended;
    const tier = result.source?.tier;
    if ((tier === "high" || tier === "factcheck") && score < 52) score = 52;
    if (tier === "state" && score < 40) score = 40;
    if ((tier === "low" || result.flags?.impersonation) && score > result.score) score = result.score;
    return clamp(score, 3, 96);
  }
  function unique(items) {
    const seen = /* @__PURE__ */ new Set();
    const out = [];
    for (const item of items) {
      const line = String(item || "").trim();
      if (!line || seen.has(line)) continue;
      seen.add(line);
      out.push(line);
    }
    return out;
  }

  // ../../workspace/auris/lib/defaults.js
  var DEFAULT_SETTINGS = {
    enabled: true,
    hoverDelay: 260,
    newsOnly: true,
    deepFetch: true,
    aiMode: "auto"
  };
  var SETTINGS_KEY = "auris.settings";

  // ../../workspace/auris/content/content.js
  if (document.documentElement.dataset.aurisPreview !== "true") {
    let settings = { ...DEFAULT_SETTINGS };
    chrome.storage.local.get(SETTINGS_KEY, (data) => {
      settings = { ...DEFAULT_SETTINGS, ...data?.[SETTINGS_KEY] || {} };
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
        const timer = setTimeout(() => resolve(null), 16e3);
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
      })
    });
    document.addEventListener("mouseup", (event) => {
      if (hover.owns(event.target)) return;
      setTimeout(() => hover.considerSelection(), 40);
    });
    chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
      if (message?.type !== "auris:check") return;
      const mode = message.mode === "selection" ? "selection" : "page";
      const text = mode === "selection" ? String(message.selectionText || document.getSelection?.() || "").replace(/\s+/g, " ").trim() : extractArticleFromDocument(document);
      hover.showCheck({
        mode,
        text,
        href: location.href,
        title: document.title || ""
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
})();
