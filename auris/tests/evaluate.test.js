import assert from "node:assert/strict";
import test from "node:test";
import { BRANDS, lookupSource } from "../lib/sources.js";
import { applyMeta, evaluateLink, shouldInspect } from "../lib/evaluate.js";
import { extractMeta } from "../lib/meta.js";
import { fuse, parseModelJson } from "../lib/model.js";
import { buildUserPrompt } from "../lib/prompt.js";

test("reuters looks reliable", () => {
  const result = evaluateLink({
    href: "https://www.reuters.com/world/europe/ships-reroute-after-storm-2026",
    anchorText: "Cargo ships reroute after a North Sea storm warning",
  });
  assert.equal(result.verdict, "reliable");
  assert.ok(result.score >= 80);
  assert.equal(result.source.tier, "high");
});

test("state media is mixed, not labeled fake", () => {
  const result = evaluateLink({
    href: "https://tass.ru/politika/1234567",
    anchorText: "ТАСС: комментарий официального представителя",
  });
  assert.equal(result.verdict, "mixed");
  assert.equal(result.source.tier, "state");
  assert.match(result.summary, /не ярлык «фейк»/);
});

test("satire keeps its own verdict", () => {
  const result = evaluateLink({
    href: "https://www.theonion.com/nation-announces-it-did-the-thing",
    anchorText: "Nation Announces It Did The Thing",
  });
  assert.equal(result.verdict, "satire");
});

test("unknown calm page is insufficient data", () => {
  const result = evaluateLink({
    href: "https://example.com/about",
    anchorText: "About",
  });
  assert.equal(result.verdict, "insufficient");
  assert.ok(result.score >= 45 && result.score <= 60);
});

test("clickbait on a disposable domain looks fake", () => {
  const result = evaluateLink({
    href: "https://daily-miracle.xyz/shock",
    anchorText: "ШОК Врачи скрывают это от вас!!!",
  });
  assert.equal(result.verdict, "likely_fake");
  assert.ok(result.score < 34);
  assert.equal(result.flags.harsh, true);
});

test("brand impersonation is not trusted", () => {
  const result = evaluateLink({
    href: "https://reuters.xyz/breaking-news",
    anchorText: "Reuters breaking news",
  });
  assert.equal(result.flags.impersonation, true);
  assert.equal(result.verdict, "likely_fake");
  assert.ok(result.score < 20);
});

test("plain http is a suspicious signal", () => {
  const result = evaluateLink({
    href: "http://example.com/news/city-council-approves-budget-plan",
    anchorText: "City council approves the annual budget",
  });
  assert.equal(result.verdict, "suspicious");
});

test("short links stay inconclusive until redirect", () => {
  const result = evaluateLink({
    href: "https://bit.ly/abc123",
    anchorText: "read this",
  });
  assert.equal(result.verdict, "insufficient");
  assert.equal(result.flags.shortener, true);
});

test("known low-credibility domain stays likely fake", () => {
  const result = evaluateLink({
    href: "https://www.infowars.com/post/secret-cure",
    anchorText: "Officials quietly changed the guidance",
  });
  assert.equal(result.verdict, "likely_fake");
});

test("established newsroom lands on leaning", () => {
  const result = evaluateLink({
    href: "https://www.kommersant.ru/doc/123456",
    anchorText: "Минфин уточнил параметры бюджета",
  });
  assert.equal(result.verdict, "leaning");
  assert.equal(result.source.name, "Коммерсантъ");
});

test("subdomain inherits the parent newsroom", () => {
  const result = evaluateLink({
    href: "https://edition.cnn.com/2026/10/29/world/port-reopens",
    anchorText: "Port reopens after overnight closure",
  });
  assert.equal(result.source.domain, "cnn.com");
  assert.equal(lookupSource("news.bbc.co.uk").domain, "bbc.co.uk");
});

test("headline can pull a reliable source down to mixed", () => {
  const local = evaluateLink({
    href: "https://www.reuters.com/world/example",
    anchorText: "Cargo ships reroute",
  });
  const refined = applyMeta(local, {
    title: "ШОК Врачи скрывают это от вас!!!",
    description: "Срочная сенсация",
  });
  assert.ok(refined.score < local.score);
  assert.equal(refined.verdict, "mixed");
  assert.equal(refined.title, "ШОК Врачи скрывают это от вас!!!");
});

test("inspection filter skips files, mail, and in-page hashes", () => {
  assert.equal(shouldInspect({ href: "mailto:desk@example.com" }), false);
  assert.equal(shouldInspect({ href: "https://example.com/files/report.pdf" }), false);
  assert.equal(shouldInspect({
    href: "https://example.com/story#comments",
    pageUrl: "https://example.com/story",
  }), false);
  assert.equal(shouldInspect({
    href: "https://www.reuters.com/world/europe/ships",
    anchorText: "Ships",
  }), true);
  assert.equal(shouldInspect({
    href: "https://example.com/about",
    anchorText: "About",
  }), false);
  assert.equal(shouldInspect({
    href: "https://example.com/about",
    anchorText: "About",
  }, { newsOnly: false }), true);
});

test("every impersonation brand resolves to a real source", () => {
  for (const brand of BRANDS) {
    assert.ok(brand.real.some((domain) => brand.re.test(domain) && lookupSource(domain)));
  }
});

test("model json parses fenced payloads and rejects noise", () => {
  const parsed = parseModelJson("```json\n{\"score\":\"71\",\"verdict\":\"leaning\",\"confidence\":80,\"summary\":\"Спокойный тон.\",\"signals\":[\"Есть редакция\"]}\n```");
  assert.equal(parsed.score, 71);
  assert.equal(parsed.verdict, "leaning");
  assert.equal(parsed.confidence, 0.8);
  assert.equal(parseModelJson("no json here"), null);
});

test("fusion does not let a model call a wire service fake", () => {
  const local = evaluateLink({
    href: "https://www.reuters.com/world/example",
    anchorText: "Cargo ships reroute after a storm warning",
  });
  const fused = fuse(local, {
    score: 8,
    verdict: "likely_fake",
    confidence: 0.9,
    summary: "Модель сомневается.",
    signals: ["Мало контекста"],
    engine: "cloud",
  });
  assert.equal(fused.verdict, "mixed");
  assert.ok(fused.score > 40);
  assert.equal(fused.engine, "cloud");
});

test("fusion keeps satire and blocks whitewashing a lookalike domain", () => {
  const satire = evaluateLink({
    href: "https://www.theonion.com/story",
    anchorText: "A satirical headline",
  });
  assert.equal(fuse(satire, { score: 90, verdict: "reliable", confidence: 0.9, summary: "Надёжно" }).verdict, "satire");

  const lookalike = evaluateLink({
    href: "https://reuters.xyz/story",
    anchorText: "Official report",
  });
  const fused = fuse(lookalike, { score: 95, verdict: "reliable", confidence: 0.95, summary: "Можно верить" });
  assert.equal(fused.verdict, "suspicious");
});

test("meta extractor reads open graph tags", () => {
  const meta = extractMeta(`
    <html><head>
      <meta content="Шторм &amp; порт" property="og:title">
      <meta name="description" content="Короткое описание">
      <title>ignored</title>
    </head></html>
  `);
  assert.equal(meta.title, "Шторм & порт");
  assert.equal(meta.description, "Короткое описание");
  assert.match(buildUserPrompt({ href: "https://example.com", domain: "example.com", score: 50, verdict: "insufficient" }), /example\.com/);
});
