import {
  MODEL_SYSTEM,
  buildModelPrompt,
  normalizeModelVerdict,
} from "../shared/engine.js";
import { NANO_OPTS } from "../shared/nano.js";

const GEMINI_SCHEMA = {
  type: "OBJECT",
  properties: {
    credibility: { type: "INTEGER" },
    confidence: { type: "INTEGER" },
    verdict: { type: "STRING", enum: ["credible", "mixed", "likely_false", "satire"] },
    summary: { type: "STRING" },
  },
  required: ["credibility", "confidence", "verdict", "summary"],
};

const NANO_SCHEMA = {
  type: "object",
  additionalProperties: false,
  properties: {
    credibility: { type: "number" },
    confidence: { type: "number" },
    verdict: { type: "string", enum: ["credible", "mixed", "likely_false", "satire"] },
    summary: { type: "string" },
  },
  required: ["credibility", "confidence", "verdict", "summary"],
};

let nanoSession = null;
let nanoPending = null;
let translator = null;

async function createNanoSession() {
  const LanguageModel = globalThis.LanguageModel;
  const base = {
    ...NANO_OPTS,
    initialPrompts: [{ role: "system", content: MODEL_SYSTEM }],
  };
  try {
    const params = await LanguageModel.params();
    return await LanguageModel.create({
      ...base,
      temperature: Math.min(0.2, params.maxTemperature),
      topK: params.defaultTopK,
    });
  } catch {
    return LanguageModel.create(base);
  }
}

function getNanoSession() {
  if (nanoSession) return Promise.resolve(nanoSession);
  if (!nanoPending) {
    nanoPending = createNanoSession()
      .then((session) => {
        nanoSession = session;
        return session;
      })
      .finally(() => {
        nanoPending = null;
      });
  }
  return nanoPending;
}

function dropNanoSession() {
  try {
    nanoSession?.destroy?.();
  } catch {
    /* already gone */
  }
  nanoSession = null;
}

async function toRussian(summary, signal) {
  if (!summary || /[А-Яа-яЁё]/.test(summary)) return summary;
  const Translator = globalThis.Translator;
  if (!Translator?.availability || !Translator?.create) return summary;
  try {
    const availability = await Translator.availability({ sourceLanguage: "en", targetLanguage: "ru" });
    if (availability !== "available") return summary;
    if (!translator) {
      translator = await Translator.create({ sourceLanguage: "en", targetLanguage: "ru" });
    }
    const translated = await translator.translate(summary, { signal });
    return typeof translated === "string" && /[А-Яа-яЁё]/.test(translated) ? translated : summary;
  } catch (error) {
    if (error?.name === "AbortError") throw error;
    return summary;
  }
}

async function classifyNano(prompt, signal) {
  const LanguageModel = globalThis.LanguageModel;
  if (!LanguageModel?.availability) return null;
  const availability = await LanguageModel.availability(NANO_OPTS);
  if (availability !== "available") return null;
  const session = await getNanoSession();
  let raw;
  try {
    raw = await session.prompt(prompt, { responseConstraint: NANO_SCHEMA, signal });
  } catch (error) {
    if (error?.name === "AbortError") throw error;
    dropNanoSession();
    const fresh = await getNanoSession();
    raw = await fresh.prompt(prompt, { signal });
  }
  const verdict = normalizeModelVerdict(typeof raw === "string" ? raw : JSON.stringify(raw));
  if (!verdict) return null;
  verdict.summary = await toRussian(verdict.summary, signal);
  verdict.provider = "nano";
  return verdict;
}

async function geminiRequest(key, model, prompt, signal, withSchema) {
  const endpoint = `https://generativelanguage.googleapis.com/v1beta/models/${encodeURIComponent(model)}:generateContent`;
  const generationConfig = { temperature: 0.2 };
  if (withSchema) {
    generationConfig.responseMimeType = "application/json";
    generationConfig.responseSchema = GEMINI_SCHEMA;
  }
  const response = await fetch(endpoint, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "x-goog-api-key": key,
    },
    body: JSON.stringify({
      systemInstruction: { parts: [{ text: MODEL_SYSTEM }] },
      contents: [{ role: "user", parts: [{ text: prompt }] }],
      generationConfig,
    }),
    signal,
  });
  const data = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = new Error(data.error?.message || `Gemini ${response.status}`);
    error.status = response.status;
    throw error;
  }
  return (data.candidates?.[0]?.content?.parts || []).map((part) => part.text || "").join("");
}

async function classifyCloud(prompt, settings, secret, signal) {
  const key = secret?.geminiKey?.trim();
  if (!key) return null;
  const preferred = settings.geminiModel || "gemini-2.5-flash";
  const models = preferred === "gemini-2.0-flash" ? [preferred] : [preferred, "gemini-2.0-flash"];
  let lastError = null;
  for (const model of models) {
    try {
      let raw;
      try {
        raw = await geminiRequest(key, model, prompt, signal, true);
      } catch (error) {
        if (error?.name === "AbortError") throw error;
        if (error.status === 404) throw error;
        raw = await geminiRequest(key, model, prompt, signal, false);
      }
      const verdict = normalizeModelVerdict(raw);
      if (!verdict) return null;
      verdict.summary = await toRussian(verdict.summary, signal);
      verdict.provider = "cloud";
      return verdict;
    } catch (error) {
      if (error?.name === "AbortError") throw error;
      lastError = error;
      if (error.status !== 404) break;
    }
  }
  if (lastError) throw lastError;
  return null;
}

export async function classifyWithModel(snapshot, settings, secret, signal) {
  if (!settings || settings.aiMode === "off") return null;
  const prompt = buildModelPrompt(snapshot, settings.aiMode === "cloud" ? "ru" : "en");
  if (settings.aiMode === "cloud") return classifyCloud(prompt, settings, secret, signal);
  return classifyNano(prompt, signal);
}

export async function testCloud(settings, secret) {
  const key = secret?.geminiKey?.trim();
  if (!key) return { ok: false, message: "Сначала вставьте ключ Gemini" };
  try {
    const raw = await geminiRequest(
      key,
      settings.geminiModel || "gemini-2.5-flash",
      'Return JSON {"credibility":50,"confidence":10,"verdict":"mixed","summary":"проверка ключа"}',
      AbortSignal.timeout(12000),
      true,
    );
    const verdict = normalizeModelVerdict(raw);
    return verdict
      ? { ok: true, message: "Ключ отвечает, модель вернула оценку" }
      : { ok: false, message: "Ключ ответил, но формат ответа странный" };
  } catch (error) {
    const message = String(error?.message || error).replaceAll(key, "•••").slice(0, 220);
    return { ok: false, message };
  }
}
