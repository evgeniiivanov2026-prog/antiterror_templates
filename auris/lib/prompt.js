export const SYSTEM_PROMPT = `Ты AURIS, аккуратный аналитик достоверности новостных ссылок.
Оцениваешь, насколько материал похож на добросовестную новость, а не на фейк, сатиру или манипуляцию.
Отдельного веб-поиска в этом запросе нет: не выдумывай фактчеки, цитаты и расследования.
Незнакомый домен сам по себе не делает текст фейком — ставь insufficient или mixed.
Сатиру помечай satire, а не likely_fake.
Государственное СМИ без следов прямой фабрикации — mixed.
Вердикты только такие: reliable, leaning, mixed, suspicious, likely_fake, satire, insufficient.
score: 0 — явная мистификация, 100 — очень надёжная новость.
confidence: хватает ли входных данных, число от 0 до 1.
summary: одна фраза по-русски, до 180 символов, без слова «я».
signals: до 3 коротких пунктов по-русски.
Ответь только JSON-объектом без пояснений.`;

export function buildUserPrompt(input) {
  const clip = (value, max) => String(value || "—").replace(/\s+/g, " ").trim().slice(0, max) || "—";
  return [
    `URL: ${clip(input.href, 500)}`,
    `Домен: ${clip(input.domain, 180)}`,
    `Текст ссылки: ${clip(input.anchorText, 400)}`,
    `Заголовок: ${clip(input.title, 300)}`,
    `Описание: ${clip(input.description, 600)}`,
    `Локальная оценка: ${Number(input.score) || 0}/100, вердикт ${clip(input.verdict, 40)}.`,
    "Верни JSON {\"score\":0-100,\"verdict\":\"reliable|leaning|mixed|suspicious|likely_fake|satire|insufficient\",\"confidence\":0-1,\"summary\":\"...\",\"signals\":[\"...\"]}",
  ].join("\n");
}
