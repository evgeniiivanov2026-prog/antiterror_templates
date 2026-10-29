import { attachHover } from "../content/hover.js";
import { createLens } from "../ui/card.js";
import { evaluateLink, shouldInspect } from "../lib/evaluate.js";
import { present } from "../lib/present.js";
import { fuse } from "../lib/model.js";

const settings = {
  enabled: true,
  hoverDelay: 160,
  newsOnly: true,
  deepFetch: true,
  aiMode: "auto",
};

attachHover({
  document,
  createLens,
  evaluate: evaluateLink,
  shouldInspect,
  present,
  getSettings: () => settings,
  requestEnrich: async (payload) => {
    await wait(720);
    const local = evaluateLink(payload);
    return fuse(local, simulate(local));
  },
});

const samples = [
  ["https://www.reuters.com/world/europe/ships-reroute-after-storm-2026", "Cargo ships reroute after a North Sea storm warning"],
  ["https://tass.ru/politika/1234567", "ТАСС: комментарий официального представителя"],
  ["https://www.theonion.com/nation-announces-it-did-the-thing", "Nation Announces It Did The Thing"],
  ["https://daily-miracle.xyz/shock", "ШОК Врачи скрывают это от вас!!!"],
];

const gallery = document.querySelector("#gallery");
for (const [href, anchorText] of samples) {
  const slot = document.createElement("div");
  slot.className = "slot";
  const lens = createLens(document);
  slot.append(lens.host);
  gallery.append(slot);
  const result = evaluateLink({ href, anchorText });
  lens.render(present(href.includes("daily-miracle") ? fuse(result, simulate(result)) : result));
  if (href.includes("daily-miracle")) {
    lens.host.shadowRoot.querySelector("[data-fold]").click();
  }
}

document.fonts?.ready?.then(() => {
  document.documentElement.dataset.fonts = "ready";
});

function simulate(local) {
  if (local.verdict === "satire") {
    return {
      score: 36,
      verdict: "satire",
      confidence: 0.86,
      summary: "Сатирический формат: текст не обещает фактов.",
      signals: ["Издание работает в жанре вымысла."],
      engine: "cloud",
    };
  }
  if (local.source?.tier === "high") {
    return {
      score: 90,
      verdict: "reliable",
      confidence: 0.84,
      summary: "Спокойный заголовок и информагентство с жёсткой проверкой фактов.",
      signals: ["Во входных данных нет следов фабрикации."],
      engine: "ondevice",
    };
  }
  if (local.flags?.harsh) {
    return {
      score: 12,
      verdict: "likely_fake",
      confidence: 0.78,
      summary: "Крикливый заголовок и одноразовый адрес сходятся в одну сторону.",
      signals: ["Обещание скрытой правды вместо источника.", "Зона домена типична для однодневок."],
      engine: "cloud",
    };
  }
  return {
    score: local.score,
    verdict: local.verdict,
    confidence: 0.62,
    summary: local.summary,
    signals: ["Модель не увидела новых следов сверх локальной оценки."],
    engine: "ondevice",
  };
}

function wait(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}
