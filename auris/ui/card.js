const RADIUS = 46;
const CIRC = 2 * Math.PI * RADIUS;

if (typeof CSS !== "undefined" && CSS.registerProperty) {
  try {
    CSS.registerProperty({
      name: "--auris-angle",
      syntax: "<angle>",
      inherits: false,
      initialValue: "0deg",
    });
  } catch {
    /* already registered */
  }
}

export function createLens(doc = document, options = {}) {
  const host = doc.createElement("auris-lens");
  const shadow = host.attachShadow({ mode: "open" });
  const link = doc.createElement("link");
  link.rel = "stylesheet";
  link.href = options.cssHref || new URL("./card.css", import.meta.url).href;

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
    learn: panel.querySelector("[data-learn]"),
    memory: panel.querySelector("[data-memory]"),
    teach: [...panel.querySelectorAll("[data-teach]")],
    meters: {
      source: panel.querySelector('[data-meter="source"]'),
      rhetoric: panel.querySelector('[data-meter="rhetoric"]'),
      url: panel.querySelector('[data-meter="url"]'),
      cross: panel.querySelector('[data-meter="cross"]'),
    },
  };

  let pinned = false;
  let pinListener = () => {};
  let teachListener = () => {};

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

  for (const button of els.teach) {
    button.addEventListener("click", (event) => {
      event.preventDefault();
      event.stopPropagation();
      teachListener(button.dataset.teach);
    });
  }

  function render(view) {
    const loading = view.phase === "loading";
    panel.dataset.verdict = loading ? "loading" : view.verdict;
    panel.classList.toggle("is-loading", loading);
    panel.classList.toggle("is-pending", Boolean(view.pending) && !loading);
    els.engine.textContent = view.engineLabel || "";
    els.verdict.textContent = view.verdictLabel || "";
    els.summary.textContent = view.summary || "";
    els.domain.textContent = view.domain || "";
    els.confidence.textContent = loading || !view.confidence
      ? ""
      : `уверенность ${Math.round(view.confidence * 100)}%`;
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
    const showLearn = Boolean(view.learn) && !loading && Boolean(view.domain);
    els.learn.hidden = !showLearn;
    els.memory.hidden = !view.memoryNote;
    els.memory.textContent = view.memoryNote || "";
    for (const button of els.teach) {
      button.classList.toggle("is-on", button.dataset.teach === view.taught);
    }
  }

  function setPinned(next) {
    pinned = Boolean(next);
    els.pin.textContent = pinned ? "Открепить" : "Закрепить";
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
    },
    onTeach(listener) {
      teachListener = listener;
    },
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
        <div class="brand-sub">оценка до клика</div>
      </div>
      <div class="chip" data-engine>Локально</div>
    </header>
    <div class="hero">
      <div class="gauge">
        <svg viewBox="0 0 120 120" aria-hidden="true">
          <circle class="track" cx="60" cy="60" r="46"></circle>
          <g class="spin"><circle class="arc" data-arc cx="60" cy="60" r="46"></circle></g>
        </svg>
        <div class="gauge-readout">
          <strong data-score>0</strong>
          <span>индекс</span>
        </div>
      </div>
      <div>
        <div class="verdict" data-verdict-label>Считываю</div>
        <p class="summary" data-summary></p>
        <div class="meta-line">
          <span class="domain" data-domain></span>
          <span class="confidence" data-confidence></span>
        </div>
      </div>
    </div>
    <cite class="title-line" data-title hidden></cite>
    <div class="excerpt" data-excerpt hidden>
      <b>Фрагмент текста</b>
      <p data-excerpt-text></p>
      <div class="lang-note" data-lang hidden></div>
    </div>
    <ul class="meters">
      <li><span>Источник</span><b><i data-meter="source"></i></b></li>
      <li><span>Подача</span><b><i data-meter="rhetoric"></i></b></li>
      <li><span>Адрес</span><b><i data-meter="url"></i></b></li>
      <li><span>Сверка</span><b><i data-meter="cross"></i></b></li>
    </ul>
    <button type="button" class="fold" data-fold aria-expanded="false">
      <span>Разбор сигналов</span>
      <svg viewBox="0 0 14 14" fill="none" aria-hidden="true">
        <path d="M3 5.2 7 9l4-3.8" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </button>
    <ul class="reasons" data-reasons hidden></ul>
    <div class="learn" data-learn hidden>
      <p data-memory hidden></p>
      <div class="teach">
        <button type="button" data-teach="reliable">Надёжно</button>
        <button type="button" data-teach="suspicious">Сомнительно</button>
        <button type="button" data-teach="likely_fake">Фейк</button>
      </div>
    </div>
    <footer class="foot">
      <p>Вероятностная оценка, не вердикт фактчекера.</p>
      <button type="button" class="pin" data-pin>Закрепить</button>
    </footer>
  `;
  return panel;
}
