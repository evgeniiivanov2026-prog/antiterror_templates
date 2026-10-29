import { parseModelJson } from "../lib/model.js";

const GEMINI_MODEL = "gemini-2.5-flash";
const OPENAI_MODEL = "gpt-4.1-mini";
const OPENAI_URL = "https://api.openai.com/v1/chat/completions";

export async function assessWithAI({ mode, secret, prompt, system, signal }) {
  if (signal?.aborted || mode === "local") return null;
  if (mode === "ondevice") return tag(await onDevice(prompt, system), "ondevice");
  if (mode === "cloud") return tag(await cloud(prompt, system, secret, signal), "cloud");
  const localModel = await onDevice(prompt, system);
  if (localModel) return tag(localModel, "ondevice");
  if (secret?.apiKey && !signal?.aborted) return tag(await cloud(prompt, system, secret, signal), "cloud");
  return null;
}

export async function diagnoseAI(secret) {
  return {
    onDevice: await onDeviceStatus(),
    hasKey: Boolean(secret?.apiKey),
    provider: secret?.provider === "openai" ? "openai" : "gemini",
  };
}

async function onDevice(prompt, system) {
  try {
    const session = await sessionFor(system);
    if (!session?.prompt) return null;
    const result = await within(session.prompt(prompt), 8000, "");
    return parseModelJson(typeof result === "string" ? result : result?.text || "");
  } catch {
    sessionPromise = null;
    return null;
  }
}

let sessionPromise = null;

async function sessionFor(system) {
  const LanguageModel = getLanguageModel();
  if (!LanguageModel) return null;
  if ((await onDeviceStatus()) !== "available") return null;
  if (!sessionPromise) {
    sessionPromise = within(LanguageModel.create({
      temperature: 0.2,
      topK: 3,
      initialPrompts: [{ role: "system", content: system }],
    }), 2500, null).then((session) => {
      if (!session) sessionPromise = null;
      return session;
    });
  }
  return sessionPromise;
}

async function onDeviceStatus() {
  const LanguageModel = getLanguageModel();
  if (!LanguageModel?.availability) return "unavailable";
  const availability = await within(LanguageModel.availability(), 1200, "unavailable");
  if (typeof availability === "string") return availability || "unavailable";
  return availability?.availability || "unavailable";
}

function getLanguageModel() {
  return globalThis.LanguageModel || globalThis.ai?.languageModel || null;
}

async function cloud(prompt, system, secret, signal) {
  if (!secret?.apiKey || signal?.aborted) return null;
  const provider = secret.provider === "openai" ? "openai" : "gemini";
  const combined = signal ? AbortSignal.any([signal, AbortSignal.timeout(12000)]) : AbortSignal.timeout(12000);
  try {
    const text = provider === "gemini"
      ? await gemini(prompt, system, secret, combined)
      : await openai(prompt, system, secret, combined);
    return parseModelJson(text);
  } catch {
    return null;
  }
}

async function gemini(prompt, system, secret, signal) {
  const model = cleanModel(secret.model, GEMINI_MODEL);
  const response = await fetch(
    `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent`,
    {
      method: "POST",
      signal,
      headers: {
        "Content-Type": "application/json",
        "x-goog-api-key": secret.apiKey,
      },
      body: JSON.stringify({
        systemInstruction: { parts: [{ text: system }] },
        contents: [{ role: "user", parts: [{ text: prompt }] }],
        generationConfig: {
          temperature: 0.2,
          maxOutputTokens: 400,
          responseMimeType: "application/json",
        },
      }),
    },
  );
  if (!response.ok) throw new Error(`gemini ${response.status}`);
  const data = await response.json();
  return (data.candidates?.[0]?.content?.parts || []).map((part) => part.text || "").join("\n");
}

async function openai(prompt, system, secret, signal) {
  const endpoint = cleanEndpoint(secret.endpoint, OPENAI_URL);
  const model = cleanModel(secret.model, OPENAI_MODEL);
  const response = await fetch(endpoint, {
    method: "POST",
    signal,
    headers: {
      "Content-Type": "application/json",
      Authorization: `Bearer ${secret.apiKey}`,
    },
    body: JSON.stringify({
      model,
      temperature: 0.2,
      messages: [
        { role: "system", content: system },
        { role: "user", content: prompt },
      ],
    }),
  });
  if (!response.ok) throw new Error(`openai ${response.status}`);
  const data = await response.json();
  const content = data.choices?.[0]?.message?.content;
  if (typeof content === "string") return content;
  if (Array.isArray(content)) return content.map((part) => part?.text || part?.content || "").join("\n");
  return "";
}

function cleanModel(value, fallback) {
  const model = String(value || "").trim();
  return /^[a-zA-Z0-9._:-]{1,80}$/.test(model) ? model : fallback;
}

function cleanEndpoint(value, fallback) {
  try {
    const url = new URL(String(value || "").trim() || fallback);
    const local = url.hostname === "localhost" || url.hostname === "127.0.0.1";
    if (url.protocol === "https:" || (url.protocol === "http:" && local)) return url.toString();
  } catch {
    /* use fallback */
  }
  return fallback;
}

function within(promise, ms, fallback) {
  return new Promise((resolve) => {
    const timer = setTimeout(() => resolve(fallback), ms);
    Promise.resolve(promise).then(
      (value) => {
        clearTimeout(timer);
        resolve(value);
      },
      () => {
        clearTimeout(timer);
        resolve(fallback);
      },
    );
  });
}

function tag(result, engine) {
  if (!result) return null;
  return { ...result, engine };
}
