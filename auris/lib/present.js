import { ENGINE_LABEL, VERDICT_LABEL } from "./copy.js";

export function present(result, extra = {}) {
  const verdict = result?.verdict || "insufficient";
  const engine = result?.engine || "local";
  return {
    phase: extra.phase || "ready",
    pending: Boolean(extra.pending),
    score: Number.isFinite(result?.score) ? result.score : 50,
    verdict,
    verdictLabel: extra.phase === "loading" ? "Считываю" : (VERDICT_LABEL[verdict] || "Оценка"),
    summary: extra.phase === "loading"
      ? "Смотрю домен, формулировку и следы адреса."
      : (result?.summary || ""),
    confidence: Number.isFinite(result?.confidence) ? result.confidence : 0,
    engine,
    engineLabel: extra.pending && extra.phase !== "loading" ? "Уточняю" : (ENGINE_LABEL[engine] || ENGINE_LABEL.local),
    meters: result?.meters || { source: 20, rhetoric: 20, url: 20, cross: 12 },
    reasons: Array.isArray(result?.reasons) ? result.reasons : [],
    domain: result?.domain || "",
    href: result?.href || "",
    title: result?.title || "",
    textExcerpt: result?.textExcerpt || "",
    languageNote: result?.languageNote || "",
  };
}
