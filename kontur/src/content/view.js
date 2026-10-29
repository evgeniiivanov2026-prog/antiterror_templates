const ARC = "M 19.02 65.28 A 30 30 0 1 1 64.98 65.28";

const MARK = `
  <svg class="mark" viewBox="0 0 32 32" aria-hidden="true">
    <path d="M7.5 21.5a9.2 9.2 0 1 1 17 0" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"></path>
    <circle cx="16" cy="16.2" r="1.7" fill="currentColor"></circle>
  </svg>
`;

export function createCard() {
  const shell = document.createElement("div");
  shell.className = "shell";
  shell.dataset.state = "scan";
  shell.dataset.verdict = "";
  shell.setAttribute("role", "tooltip");
  shell.innerHTML = `
    <article class="card">
      <div class="noise" aria-hidden="true"></div>
      <header class="top">
        <div class="brand">${MARK}<span>КОНТУР</span></div>
        <div class="live"><i class="dot" aria-hidden="true"></i><span class="phase"></span></div>
      </header>
      <div class="hero">
        <div class="gauge" aria-hidden="true">
          <svg viewBox="0 0 84 84">
            <path class="track" d="${ARC}" pathLength="100"></path>
            <path class="value" d="${ARC}" pathLength="100"></path>
          </svg>
          <div class="gauge-num"></div>
        </div>
        <div class="hero-copy">
          <h2 class="verdict"></h2>
          <p class="confidence"></p>
          <p class="domain"></p>
        </div>
      </div>
      <p class="headline"></p>
      <div class="signals">
        ${["source", "language", "url", "page"]
          .map(
            (id) => `
          <div class="signal" data-id="${id}">
            <span class="k"></span>
            <span class="v"></span>
            <span class="bar" aria-hidden="true"><i></i></span>
          </div>`,
          )
          .join("")}
      </div>
      <p class="summary"></p>
      <footer class="foot">
        <p class="engine"></p>
        <p class="disclaimer">Автоматическая оценка. Не заменяет фактчек.</p>
      </footer>
    </article>
  `;

  const rows = [...shell.querySelectorAll(".signal")];
  const verdict = shell.querySelector(".verdict");
  const confidence = shell.querySelector(".confidence");
  const domain = shell.querySelector(".domain");
  const headline = shell.querySelector(".headline");
  const summary = shell.querySelector(".summary");
  const phase = shell.querySelector(".phase");
  const engine = shell.querySelector(".engine");
  const gaugeNum = shell.querySelector(".gauge-num");
  const gaugeValue = shell.querySelector(".value");

  function setState(model) {
    shell.dataset.state = model.state === "scan" ? "scan" : "ready";
    shell.dataset.verdict = model.verdict || "";
    verdict.textContent = model.verdictLabel || "";
    confidence.textContent = model.confidenceText || "";
    domain.textContent = model.domain || "";
    headline.textContent = model.headline || "";
    summary.textContent = model.summary || "";
    phase.textContent = model.phaseLabel || "";
    engine.textContent = model.engineNote || "";
    gaugeNum.textContent = model.gaugeText || "";
    if (model.state === "scan" || model.displayScore == null) gaugeValue.style.strokeDashoffset = "";
    else gaugeValue.style.strokeDashoffset = String(100 - Number(model.displayScore));

    for (const row of rows) {
      const signal = (model.signals || []).find((item) => item.id === row.dataset.id);
      const label = row.querySelector(".k");
      const detail = row.querySelector(".v");
      const bar = row.querySelector(".bar i");
      if (!signal) {
        row.hidden = true;
        continue;
      }
      row.hidden = false;
      label.textContent = signal.label || "";
      detail.textContent = signal.detail || "";
      if (signal.score == null) {
        row.dataset.tone = "idle";
        bar.style.width = "";
      } else {
        const score = Math.max(0, Math.min(100, Number(signal.score)));
        row.dataset.tone = score >= 70 ? "good" : score <= 42 ? "bad" : "mid";
        bar.style.width = `${Math.max(8, score)}%`;
      }
    }
  }

  return { shell, setState };
}

export function viewFromResult(result) {
  return {
    state: "ready",
    verdict: result.verdict,
    verdictLabel: result.verdictLabel,
    gaugeText: result.gaugeText,
    displayScore: result.displayScore,
    confidenceText: result.verdict === "satire" ? "пародия, не новость" : `уверенность ${result.confidence}%`,
    domain: result.domain,
    headline: result.headline,
    summary: result.summary,
    phaseLabel: result.phaseLabel,
    engineNote: result.engineNote,
    signals: result.signals,
  };
}

export function scanView(domain, headline) {
  return {
    state: "scan",
    verdict: "",
    verdictLabel: "Смотрим ссылку",
    gaugeText: "...",
    displayScore: null,
    confidenceText: "собираем сигналы",
    domain,
    headline: headline || domain,
    summary: "Сначала домен и формулировка, затем сама страница.",
    phaseLabel: "контур",
    engineNote: "Пока без вердикта",
    signals: [
      { id: "source", label: "Источник", detail: "смотрим домен", score: null },
      { id: "language", label: "Подача", detail: "читаем формулировку", score: null },
      { id: "url", label: "Адрес", detail: "разбираем ссылку", score: null },
      { id: "page", label: "Страница", detail: "ещё не открывали", score: null },
    ],
  };
}
