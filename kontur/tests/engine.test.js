import assert from "node:assert/strict";
import test from "node:test";
import { extractArticle } from "../src/background/article.js";
import {
  blendAssessment,
  cacheKey,
  isArticleLike,
  normalizeModelVerdict,
  sanitizeSettings,
  scoreAssessment,
} from "../src/shared/engine.js";

const WIRE = "Central bank holds rates as inflation cools further this quarter";
const CALM_RU = "По данным Минфина, дефицит бюджета за квартал составил 1,2 триллиона рублей";
const STACKED = "СРОЧНО!!! Врачи молчат: чудо-средство, вы не поверите!!!";

test("a calm wire story reads as credible", () => {
  const result = scoreAssessment({
    url: "https://www.reuters.com/world/central-bank-holds-rates",
    anchorText: WIRE,
  });
  assert.equal(result.verdict, "credible");
  assert.ok(result.credibility >= 74 && result.credibility <= 95);
  assert.match(result.summary, /Reuters/);
  assert.equal(result.signals.length, 4);
});

test("a spicy headline does not turn Reuters into a fake", () => {
  const result = scoreAssessment({
    url: "https://www.reuters.com/world/shouting-headline",
    anchorText: "СРОЧНО!!! Вы не поверите, что сказал банк",
  });
  assert.notEqual(result.verdict, "likely_false");
});

test("documented fabrication domains stay low even with a calm line", () => {
  const result = scoreAssessment({
    url: "https://worldnewsdailyreport.com/city-opens-library",
    anchorText: "City opens a new library on Monday morning",
  });
  assert.equal(result.verdict, "likely_false");
  assert.ok(result.credibility <= 40);
  assert.match(result.summary, /выдуман/);
});

test("satire is labeled satire, not a hoax", () => {
  const result = scoreAssessment({
    url: "https://panorama.pub/politics/monday",
    anchorText: "Госдума предложила отменить понедельник насовсем",
  });
  assert.equal(result.verdict, "satire");
  assert.equal(result.credibility, null);
  assert.equal(result.gaugeText, "SAT");
  assert.match(result.summary, /сатир/i);
});

test("stacked clickbait on an unknown site looks false", () => {
  const result = scoreAssessment({
    url: "https://news-daily.example/health/secret",
    anchorText: STACKED,
  });
  assert.equal(result.verdict, "likely_false");
  assert.match(result.summary, /кликбейт|псевдомедицин|рекламн/i);
});

test("one clickbait phrase is not enough to call a story fake", () => {
  const result = scoreAssessment({
    url: "https://notes.example/archive/long-read-about-cities",
    anchorText: "Вы не поверите, как изменилась набережная за десять лет ремонта",
  });
  assert.equal(result.verdict, "mixed");
});

test("state media with a sourced sentence stays mixed", () => {
  const result = scoreAssessment({
    url: "https://tass.ru/ekonomika/1234567",
    anchorText: CALM_RU,
  });
  assert.equal(result.verdict, "mixed");
  assert.ok(result.credibility > 50 && result.credibility < 71);
  assert.equal(result.source.kind, "state");
  assert.match(result.summary, /государствен/);
});

test("official domains get credit without being in the news catalog", () => {
  const result = scoreAssessment({
    url: "https://www.energy.gov/articles/winter-grid-reliability-update",
    anchorText: "Department of Energy publishes the winter grid reliability update for operators",
  });
  assert.equal(result.source.kind, "official");
  assert.equal(result.verdict, "credible");
});

test("ordinary words do not trigger the shock pattern", () => {
  const result = scoreAssessment({
    url: "https://example.com/recipes/chocolate-cake",
    anchorText: "Шоколадный мешок с какао: рецепт на выходные для всей семьи дома",
  });
  assert.equal(result.signals.find((signal) => signal.id === "language").detail, "ровная подача");
  assert.ok(result.languageScore > 60);
});

test("shorteners hide the destination until the redirect is known", () => {
  const hidden = scoreAssessment({
    url: "https://bit.ly/abc123",
    anchorText: "Короткий адрес ведёт на длинный материал о бюджете города",
  });
  assert.ok(hidden.urlScore <= 50);
  assert.match(hidden.summary, /скрывает/);

  const opened = scoreAssessment({
    url: "https://www.reuters.com/world/central-bank-holds-rates",
    requestedUrl: "https://bit.ly/abc123",
    anchorText: WIRE,
  });
  assert.equal(opened.verdict, "credible");
  assert.match(opened.summary, /reuters\.com/i);
});

test("punycode is treated as a mask", () => {
  const result = scoreAssessment({
    url: "https://xn--e1afmapc.example/news/budget-vote-passes-tonight",
    anchorText: "Совет города согласовал бюджет на следующий год без поправок",
  });
  assert.ok(result.urlScore < 55);
  assert.match(result.signals.find((signal) => signal.id === "url").detail, /punycode/);
});

test("an old date under a breaking tone is called out", () => {
  const result = scoreAssessment({
    url: "https://example.com/2024/03/12/city-budget-vote",
    anchorText: "Срочно: сегодня объявили итоги голосования",
    title: "Срочно: сегодня объявили итоги голосования",
    description: "Короткий материал без новых подробностей и без названных источников в лиде.",
    published: "2012-04-01T00:00:00.000Z",
    pageStatus: "ok",
  }, Date.parse("2026-10-29T00:00:00.000Z"));
  assert.match(result.summary, /дата публикации старая/);
});

test("article-like detection ignores navigation and keeps real headlines", () => {
  assert.equal(isArticleLike({ url: "https://example.com/", text: "Главная" }), false);
  assert.equal(isArticleLike({ url: "javascript:void(0)", text: "Новость", force: true }), false);
  assert.equal(isArticleLike({
    url: "https://example.com/2024/03/12/senate-passes-budget-bill",
    text: "Подробнее",
  }), true);
  assert.equal(isArticleLike({
    url: "https://example.com/story",
    text: "Сенат одобрил бюджет на следующий год",
  }), true);
});

test("cache keys drop tracking parameters", () => {
  assert.equal(
    cacheKey("https://example.com/a?b=1&utm_source=tg&fbclid=zzz"),
    "https://example.com/a?b=1",
  );
});

test("model json is parsed from a fenced blob and rejected when incomplete", () => {
  const parsed = normalizeModelVerdict('```json\n{"credibility":81,"confidence":70,"verdict":"credible","summary":"Ровная заметка."}\n```');
  assert.equal(parsed.verdict, "credible");
  assert.equal(parsed.credibility, 81);
  assert.equal(normalizeModelVerdict('{"verdict":"credible"}'), null);
});

test("a divergent model cannot relabel a wire as fake", () => {
  const base = scoreAssessment({
    url: "https://www.reuters.com/world/central-bank-holds-rates",
    anchorText: WIRE,
  });
  const blended = blendAssessment(base, {
    provider: "cloud",
    credibility: 5,
    confidence: 99,
    verdict: "likely_false",
    summary: "Это точно фейк, вот цитата которой не было.",
  });
  assert.notEqual(blended.verdict, "likely_false");
  assert.ok(blended.credibility >= 70);
  assert.equal(blended.summary.includes("точно фейк"), false);
});

test("an agreeing Russian summary can replace the heuristic line", () => {
  const base = scoreAssessment({
    url: "https://www.reuters.com/world/central-bank-holds-rates",
    anchorText: WIRE,
  });
  const blended = blendAssessment(base, {
    provider: "nano",
    credibility: base.credibility,
    confidence: 80,
    verdict: "credible",
    summary: "Ровная заметка агентства без признаков вброса.",
  });
  assert.match(blended.summary, /Ровная заметка/);
  assert.match(blended.engineNote, /устройстве/);
});

test("satire survives a model that calls it true", () => {
  const base = scoreAssessment({
    url: "https://panorama.pub/politics/monday",
    anchorText: "Госдума предложила отменить понедельник насовсем",
  });
  const blended = blendAssessment(base, {
    provider: "cloud",
    credibility: 95,
    confidence: 99,
    verdict: "credible",
    summary: "Это правда.",
  });
  assert.equal(blended.verdict, "satire");
});

test("the model may mark an unknown parody as satire", () => {
  const base = scoreAssessment({
    url: "https://example.com/local/moon-made-of-cheese",
    anchorText: "Local officials unveil a moon made of cheese and refuse further comment today",
  });
  const blended = blendAssessment(base, {
    provider: "nano",
    credibility: 28,
    confidence: 86,
    verdict: "satire",
    summary: "Reads as a parody rather than a report.",
  });
  assert.equal(blended.verdict, "satire");
  assert.match(blended.summary, /сатир/i);
});

test("settings are clamped and the key never rides along", () => {
  const settings = sanitizeSettings({ delay: 10, aiMode: "nope", geminiKey: "secret", geminiModel: "gemini-2.5-flash" });
  assert.equal(settings.delay, 120);
  assert.equal(settings.aiMode, "auto");
  assert.equal(settings.geminiKey, undefined);
});

test("article html yields a title, author and lead", () => {
  const extracted = extractArticle(`
    <html><head>
      <title>Старый title</title>
      <meta property="og:title" content="Городской бюджет принят">
      <meta name="description" content="Депутаты согласовали расходы на транспорт и школы вчера вечером.">
      <meta property="article:published_time" content="2026-10-28T18:00:00Z">
      <script type="application/ld+json">
        {"@type":"NewsArticle","headline":"Городской бюджет принят","author":{"name":"Анна Смирнова"},"datePublished":"2026-10-28"}
      </script>
    </head>
    <body><article><p>${"Депутаты согласовали расходы на транспорт и школы, а также опубликовали таблицу поправок. ".repeat(3)}</p></article></body></html>
  `);
  assert.equal(extracted.title, "Городской бюджет принят");
  assert.equal(extracted.author, "Анна Смирнова");
  assert.match(extracted.text, /Депутаты/);
  assert.equal(extracted.blocked, false);
});
