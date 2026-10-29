export const SYSTEM_PROMPT = `Ты AURIS, аккуратный аналитик достоверности новостных ссылок.
Оцениваешь, насколько материал похож на добросовестную новость, а не на фейк, сатиру или манипуляцию.
Отдельного веб-поиска в этом запросе нет: не выдумывай фактчеки, цитаты и расследования.
Незнакомый домен сам по себе не делает текст фейком — ставь insufficient или mixed.
Если дан фрагмент текста, суди и по нему. Язык материала не повод снижать оценку.
Сатиру помечай satire, а не likely_fake.
Государственное СМИ без следов прямой фабрикации — mixed.
Память прошлых оценок и поправок человека учитывай, но она не отменяет сатиру и явный поддельный домен.
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
    `Фрагмент текста: ${clip(input.body, 1200)}`,
    `Локальная оценка: ${Number(input.score) || 0}/100, вердикт ${clip(input.verdict, 40)}.`,
    ...memoryLine(input.memory),
    "Верни JSON {\"score\":0-100,\"verdict\":\"reliable|leaning|mixed|suspicious|likely_fake|satire|insufficient\",\"confidence\":0-1,\"summary\":\"...\",\"signals\":[\"...\"]}",
  ].join("\n");
}

function memoryLine(value) {
  const text = String(value || "").trim().slice(0, 700);
  return text ? [text] : [];
}
