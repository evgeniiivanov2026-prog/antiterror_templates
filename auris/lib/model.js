import { asUnit, clamp } from "./clamp.js";
import { VERDICTS } from "./copy.js";

export function parseModelJson(text) {
  if (!text || typeof text !== "string") return null;
  const fenced = text.match(/```(?:json)?\s*([\s\S]*?)```/i);
  const raw = fenced ? fenced[1] : text;
  const start = raw.indexOf("{");
  const end = raw.lastIndexOf("}");
  if (start < 0 || end <= start) return null;
  try {
    const data = JSON.parse(raw.slice(start, end + 1));
    const score = Number(data.score);
    if (!Number.isFinite(score)) return null;
    const verdict = typeof data.verdict === "string" ? data.verdict.trim() : "";
    return {
      score: clamp(score, 0, 100),
      verdict: VERDICTS.has(verdict) ? verdict : null,
      confidence: asUnit(data.confidence, 0.55),
      summary: typeof data.summary === "string" ? data.summary.trim().slice(0, 240) : "",
      signals: Array.isArray(data.signals)
        ? data.signals.filter((item) => typeof item === "string" && item.trim()).slice(0, 3)
        : [],
    };
  } catch {
    return null;
  }
}

export function fuse(local, ai) {
  if (!ai || !Number.isFinite(Number(ai.score))) return { ...local, engine: local.engine || "local" };
  const aiScore = clamp(Number(ai.score), 0, 100);
  const weight = clamp(asUnit(ai.confidence, 0.55), 0.35, 0.75);
  let score = Math.round(local.score * (1 - weight) + aiScore * weight);
  let verdict = ai.verdict || local.verdict;
  const reasons = [...local.reasons];
  let railed = false;

  if (local.verdict === "satire" || local.flags?.satire) {
    verdict = "satire";
    railed = verdict !== ai.verdict;
  }
  if ((local.source?.tier === "low" || local.flags?.impersonation) && (verdict === "reliable" || verdict === "leaning")) {
    verdict = "suspicious";
    railed = true;
    reasons.push("Модель смягчила оценку, но адрес остаётся проблемным.");
  }
  if ((local.source?.tier === "high" || local.source?.tier === "factcheck") && (verdict === "likely_fake" || verdict === "suspicious")) {
    verdict = "mixed";
    railed = true;
    reasons.push("Модель сомневается, но репутация редакции этого не подтверждает. Нужен разбор конкретного текста.");
  }
  if (railed) score = Math.round(local.score * 0.7 + aiScore * 0.3);

  const diverge = Math.abs(local.score - aiScore) > 28;
  if (diverge) reasons.push("Локальные сигналы и модель разошлись — уверенность снижена.");
  for (const signal of ai.signals || []) {
    const line = signal.trim().slice(0, 220);
    if (line && !reasons.includes(line)) reasons.push(line);
  }

  const confidence = diverge
    ? Math.min(local.confidence, asUnit(ai.confidence, 0.5)) * 0.85
    : Math.max(local.confidence, asUnit(ai.confidence, 0.5));

  return {
    ...local,
    score: clamp(score, 0, 100),
    verdict,
    confidence: clamp(Number(confidence.toFixed(2)), 0.2, 0.92),
    summary: ai.summary || local.summary,
    reasons: reasons.slice(0, 6),
    engine: ai.engine || "ondevice",
    deepened: true,
    meters: {
      ...local.meters,
      cross: clamp(Math.round((local.meters.cross + (diverge ? 36 : 86)) / 2), 0, 100),
    },
  };
}
