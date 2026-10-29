import { clamp } from "./clamp.js";
import { VERDICT_LABEL } from "./copy.js";
import { settle } from "./evaluate.js";

export const MEMORY_KEY = "auris.memory";
export const TEACH_VERDICTS = ["reliable", "suspicious", "likely_fake"];

const ANCHOR = {
  reliable: 86,
  suspicious: 40,
  likely_fake: 16,
};

export function emptyMemory() {
  return { domains: {}, lessons: [] };
}

export function normalizeMemory(value) {
  const source = value && typeof value === "object" ? value : {};
  const domains = {};
  for (const [domain, slot] of Object.entries(source.domains || {})) {
    if (!domain || !slot || typeof slot !== "object") continue;
    domains[domain] = {
      scores: Array.isArray(slot.scores) ? slot.scores.map(Number).filter(Number.isFinite).slice(-12) : [],
      corrections: countCorrections(slot.corrections),
      updated: Number(slot.updated) || 0,
    };
  }
  const lessons = Array.isArray(source.lessons) ? source.lessons.filter((item) => item?.domain && TEACH_VERDICTS.includes(item.verdict)).slice(0, 24) : [];
  return { domains, lessons };
}

export function rememberAuto(memory, result) {
  const next = normalizeMemory(memory);
  const domain = String(result?.domain || "");
  const score = Number(result?.baseScore ?? result?.score);
  if (!domain || !Number.isFinite(score)) return next;
  const slot = ensure(next, domain);
  slot.scores = [...slot.scores, clamp(Math.round(score), 0, 100)].slice(-12);
  slot.updated = Date.now();
  prune(next);
  return next;
}

export function rememberUser(memory, { domain, href = "", title = "", verdict } = {}) {
  const next = normalizeMemory(memory);
  const host = String(domain || "");
  if (!host || !TEACH_VERDICTS.includes(verdict)) return next;
  const slot = ensure(next, host);
  slot.corrections[verdict] += 1;
  slot.updated = Date.now();
  next.lessons = [{
    domain: host,
    href: String(href || "").slice(0, 300),
    title: String(title || host).slice(0, 140),
    verdict,
    at: Date.now(),
  }, ...next.lessons.filter((item) => item.domain !== host)].slice(0, 24);
  prune(next);
  return next;
}

export function forgetMemory() {
  return emptyMemory();
}

export function applyMemory(result, memory, { enabled = true } = {}) {
  if (!result) return result;
  const base = baseOf(result);
  const carried = {
    ...base,
    ...baseFields(base),
    memoryApplied: true,
    memoryNote: "",
    taught: "",
  };
  if (!enabled || !base.domain) return carried;
  const slot = normalizeMemory(memory).domains[base.domain];
  if (!slot) return carried;
  if (base.verdict === "satire" || base.flags?.satire) {
    return { ...carried, memoryNote: "Сатира в памяти не переучивается." };
  }

  const corrections = correctionCount(slot.corrections);
  const seen = slot.scores.length;
  const autoMean = seen ? slot.scores.reduce((sum, score) => sum + score, 0) / seen : base.score;
  if (!corrections && seen < 2) {
    return { ...carried, memoryNote: seen ? "Память: одна своя оценка, сдвига ещё нет." : "" };
  }

  const userMean = corrections ? userScore(slot.corrections) : autoMean;
  const weight = corrections
    ? Math.min(0.62, 0.34 + 0.14 * (corrections - 1))
    : Math.min(0.42, 0.05 * seen);
  const target = corrections ? userMean * 0.8 + autoMean * 0.2 : autoMean;
  let score = Math.round(base.score * (1 - weight) + target * weight);
  score = railScore(base, score);
  const flags = { ...base.flags };
  const taught = dominantCorrection(slot.corrections);
  if (corrections && (taught === "likely_fake" || taught === "suspicious")) flags.harsh = true;
  if (!corrections && seen >= 4 && autoMean <= 36) flags.harsh = true;
  const settled = settle(base, {
    score,
    flags,
    reasons: unique([memoryReason(slot), ...base.reasons]),
  });
  return {
    ...settled,
    ...baseFields(base),
    memoryApplied: true,
    memoryNote: memoryReason(slot),
    taught: taught || "",
  };
}

export function promptMemory(memory, domain) {
  const data = normalizeMemory(memory);
  const lines = [];
  const slot = domain ? data.domains[domain] : null;
  if (slot && (slot.scores.length || correctionCount(slot.corrections))) {
    const mean = slot.scores.length
      ? Math.round(slot.scores.reduce((sum, score) => sum + score, 0) / slot.scores.length)
      : "—";
    lines.push(`Этот домен уже встречался: своих оценок ${slot.scores.length}, средняя ${mean}. Поправки человека: ${formatCorrections(slot.corrections)}.`);
  }
  for (const lesson of data.lessons.slice(0, 4)) {
    lines.push(`Поправка: ${lesson.domain} человек отметил как «${VERDICT_LABEL[lesson.verdict] || lesson.verdict}».`);
  }
  return lines.join("\n");
}

function baseOf(result) {
  if (result.memoryApplied && Number.isFinite(result.baseScore)) {
    return {
      ...result,
      score: result.baseScore,
      verdict: result.baseVerdict || result.verdict,
      summary: result.baseSummary || result.summary,
      reasons: result.baseReasons || result.reasons,
    };
  }
  return result;
}

function baseFields(base) {
  return {
    baseScore: base.score,
    baseVerdict: base.verdict,
    baseSummary: base.summary,
    baseReasons: Array.isArray(base.reasons) ? base.reasons.slice(0, 6) : [],
  };
}

function railScore(base, score) {
  let next = score;
  if (base.flags?.impersonation) next = Math.min(next, base.score);
  const tier = base.source?.tier;
  if ((tier === "official" || tier === "registry") && next < Math.min(base.score, 52)) next = Math.min(base.score, 52);
  return clamp(Math.round(next), 3, 96);
}

function memoryReason(slot) {
  const bits = [];
  if (slot.scores.length) bits.push(`своих оценок ${slot.scores.length}`);
  const corrections = correctionCount(slot.corrections);
  if (corrections) bits.push(`поправок ${corrections}`);
  return `Память модели по домену: ${bits.join(", ")}.`;
}

function userScore(corrections) {
  let total = 0;
  let weight = 0;
  for (const verdict of TEACH_VERDICTS) {
    const count = corrections[verdict] || 0;
    total += ANCHOR[verdict] * count;
    weight += count;
  }
  return weight ? total / weight : 50;
}

function dominantCorrection(corrections) {
  let best = "";
  let count = 0;
  for (const verdict of TEACH_VERDICTS) {
    if ((corrections[verdict] || 0) > count) {
      best = verdict;
      count = corrections[verdict];
    }
  }
  return best;
}

function formatCorrections(corrections) {
  const parts = TEACH_VERDICTS.filter((verdict) => corrections[verdict]).map((verdict) => `${VERDICT_LABEL[verdict]} ${corrections[verdict]}`);
  return parts.length ? parts.join(", ") : "нет";
}

function correctionCount(corrections) {
  return TEACH_VERDICTS.reduce((sum, verdict) => sum + (corrections?.[verdict] || 0), 0);
}

function countCorrections(value) {
  return {
    reliable: Math.max(0, Number(value?.reliable) || 0),
    suspicious: Math.max(0, Number(value?.suspicious) || 0),
    likely_fake: Math.max(0, Number(value?.likely_fake) || 0),
  };
}

function ensure(memory, domain) {
  if (!memory.domains[domain]) {
    memory.domains[domain] = { scores: [], corrections: countCorrections(null), updated: 0 };
  }
  return memory.domains[domain];
}

function prune(memory) {
  const entries = Object.entries(memory.domains).sort((a, b) => (b[1].updated || 0) - (a[1].updated || 0)).slice(0, 80);
  memory.domains = Object.fromEntries(entries);
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
  return out.slice(0, 6);
}
