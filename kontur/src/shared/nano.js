export const NANO_OPTS = {
  expectedInputs: [{ type: "text", languages: ["en"] }],
  expectedOutputs: [{ type: "text", languages: ["en"] }],
};

export async function nanoAvailability() {
  const LanguageModel = globalThis.LanguageModel;
  if (!LanguageModel?.availability) return { state: "unsupported" };
  try {
    const state = await LanguageModel.availability(NANO_OPTS);
    return { state: state || "unsupported" };
  } catch (error) {
    return { state: "unsupported", message: error?.message || String(error) };
  }
}

export function nanoStatusLabel(state) {
  switch (state) {
    case "available":
      return "Gemini Nano уже на устройстве";
    case "downloadable":
      return "Локальная модель ещё не скачана";
    case "downloading":
      return "Локальная модель скачивается";
    case "unavailable":
      return "На этом компьютере модель недоступна";
    default:
      return "Встроенной модели нет — останутся сигналы";
  }
}
