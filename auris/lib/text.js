import { clamp } from "./clamp.js";
import { analyzeRhetoric, evaluateLink, settle } from "./evaluate.js";

export const MIN_ARTICLE_CHARS = 200;
export const SELECTION_CHARS = 160;

const ATTRIBUTION = /по данным|сообщ(?:ил[аи]?|ает)|заявил[аи]?|according to|said that|told reporters|агентство|цитирует/i;
const QUOTE = /[«"][^»"]{12,}[»"]/;
const DATE = /\b(19|20)\d{2}\b|\d{1,2}\s+(января|февраля|марта|апреля|мая|июня|июля|августа|сентября|октября|ноября|декабря|january|february|march|april|may|june|july|august|september|october|november|december)/i;
const ANON = /эксперты считают|учёные доказали|ученые доказали|источники сообщают|scientists say|experts agree/i;
const SHARE = /поделитесь|репостните|перешлите друзьям|share this|tag a friend/i;
const FIRST = /я лично|мне рассказали|я сам видел|i personally|my friend told me/i;

const SHORT_PAGE = "Похоже, это не статья: текста слишком мало, чтобы судить по содержанию.";
const SHORT_BIT = "Текста мало для содержательной оценки. Это не значит, что материал фейковый.";

export const LANGUAGE_NOTE = {
  ru: "Текст на русском. Язык сам по себе ничего не доказывает.",
  en: "Текст на английском. Язык сам по себе ничего не доказывает.",
  mixed: "В тексте смешаны русский и английский.",
  unknown: "",
};

export function detectLanguage(text) {
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

export function excerptOf(text, max = 320) {
  const value = String(text || "").replace(/\s+/g, " ").trim();
  if (value.length <= max) return value;
  const cut = value.slice(0, max);
  const space = cut.lastIndexOf(" ");
  return `${(space > 180 ? cut.slice(0, space) : cut).trim()}…`;
}

export function scoreArticleText(text) {
  const value = String(text || "").replace(/\s+/g, " ").trim();
  const language = detectLanguage(value);
  if (value.length < 80) {
    return { score: 50, penalty: 0, notes: [], meter: 50, language, enough: false };
  }
  let score = 62;
  const notes = [];
  if (ATTRIBUTION.test(value)) {
    score += 6;
    notes.push("Есть отсылка к источнику или участнику события.");
  }
  if (QUOTE.test(value)) score += 4;
  if (DATE.test(value)) score += 3;
  const rhetoric = analyzeRhetoric(value);
  if (rhetoric.penalty) notes.push(...rhetoric.notes);
  score -= rhetoric.penalty;
  if (ANON.test(value)) {
    score -= 8;
    notes.push("Опора на безымянных «экспертов» без имени и должности.");
  }
  if (SHARE.test(value)) {
    score -= 6;
    notes.push("Текст просит репост вместо проверяемого факта.");
  }
  if (FIRST.test(value)) {
    score -= 6;
    notes.push("Подача от первого лица без независимой опоры.");
  }
  return {
    score: clamp(Math.round(score), 8, 92),
    penalty: rhetoric.penalty,
    notes: notes.slice(0, 3),
    meter: rhetoric.meter,
    language,
    enough: value.length >= MIN_ARTICLE_CHARS,
  };
}

export function applyText(result, text, { mode = "hover" } = {}) {
  const value = String(text || "").replace(/\s+/g, " ").trim().slice(0, 8000);
  const language = detectLanguage(value);
  const carried = {
    ...result,
    language,
    languageNote: LANGUAGE_NOTE[language] || "",
    textExcerpt: excerptOf(value),
    textChars: value.length,
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
        "Со страницы прочитан только короткий фрагмент — вердикт на нём не строится.",
        ...result.reasons,
      ]).slice(0, 6),
    };
  }
  if (result.verdict === "satire" || result.flags?.satire) {
    return {
      ...carried,
      meters: { ...result.meters, rhetoric: scoreArticleText(value).meter },
    };
  }

  const scored = scoreArticleText(value);
  const weight = value.length < MIN_ARTICLE_CHARS ? 0.22 : (value.length < 600 ? 0.28 : 0.45);
  const blended = Math.round(result.score * (1 - weight) + scored.score * weight);
  const flags = {
    ...result.flags,
    harsh: Boolean(result.flags?.harsh) || scored.penalty >= 14,
  };
  return settle(result, {
    score: railScore(result, blended),
    flags,
    reasons: unique([...scored.notes, ...result.reasons]),
    rhetoricPenalty: Math.max(result.rhetoricPenalty || 0, scored.penalty),
    meters: {
      ...result.meters,
      rhetoric: scored.meter,
      cross: clamp(Math.round((result.meters?.cross || 34) * 0.55 + 74 * 0.45), 0, 100),
    },
    language,
    languageNote: carried.languageNote,
    textExcerpt: carried.textExcerpt,
    textChars: value.length,
  });
}

export function assessPlain(text, { href = "", title = "", mode = "paste" } = {}) {
  const label = String(title || "").slice(0, 300);
  const local = href
    ? evaluateLink({ href, anchorText: label })
    : neutralResult(label);
  if (label) local.title = label;
  return applyText(local, text, { mode });
}

function shortResult(result, summary) {
  return {
    ...result,
    verdict: result.verdict === "satire" ? "satire" : "insufficient",
    summary,
    reasons: unique([SHORT_BIT, ...result.reasons]).slice(0, 6),
    confidence: Math.min(result.confidence ?? 0.3, 0.38),
  };
}

function railScore(result, blended) {
  let score = blended;
  const tier = result.source?.tier;
  if ((tier === "high" || tier === "factcheck" || tier === "official" || tier === "registry") && score < 52) score = 52;
  if (tier === "state" && score < 40) score = 40;
  if ((tier === "low" || result.flags?.impersonation) && score > result.score) score = result.score;
  return clamp(score, 3, 96);
}

function neutralResult(title) {
  return {
    href: "",
    domain: "",
    score: 58,
    verdict: "insufficient",
    confidence: 0.32,
    summary: "Оценка только по тексту, без адреса страницы.",
    reasons: ["Адреса страницы нет — репутация домена не учитывается."],
    meters: { source: 40, rhetoric: 62, url: 36, cross: 30 },
    flags: { impersonation: false, satire: false, shortener: false, harsh: false, state: false },
    source: null,
    engine: "local",
    title: title || "",
    rhetoricPenalty: 0,
  };
}

function unique(items) {
  const seen = new Set();
  const out = [];
  for (const item of items) {
    const line = String(item || "").trim();
    if (!line || seen.has(line)) continue;
    seen.add(line);
    out.push(line);
  }
  return out;
}
