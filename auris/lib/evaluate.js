import { clamp } from "./clamp.js";
import { VERDICT_SUMMARY } from "./copy.js";
import { lookupRu } from "./ru-bank.js";
import { BRANDS, SHORTENERS, SUSPICIOUS_TLDS, lookupSource } from "./sources.js";

const FILE_RE = /\.(pdf|zip|rar|7z|png|jpe?g|gif|webp|svg|mp4|mp3|wav|docx?|xlsx?|pptx?|apk|dmg)($|\?)/i;

const RHETORIC_RULES = [
  {
    re: /шок(?:ирующ\p{L}*|ировал\p{L}*)?|сенсац|врачи (?:в шоке|скрыва\p{L}*|молчат|в ужасе)|вам не расскажут|это скрывают|запрещ[её]нн\p{L}{1,6} правд|успей(?:те)? посмотреть|никто не ожидал|слив правд/iu,
    penalty: 16,
    note: "Формулировка обещает тайну и шок вместо фактов.",
  },
  {
    re: /you won'?t believe|shocking|doctors hate|they don'?t want you|wake up,? sheeple|share before|mainstream media hides|what happened next/i,
    penalty: 16,
    note: "Кликбейт: эмоция вместо проверяемого факта.",
  },
  {
    re: /[!]{3,}|[?]{3,}/,
    penalty: 8,
    note: "Каскад восклицаний или вопросов.",
  },
  {
    re: /100\s*%|гарантированно вылеч|чипирован|рептилоид|плоская земля|биоробот/i,
    penalty: 18,
    note: "Абсолютные или конспирологические формулы.",
  },
];

export function cacheKey(href) {
  const url = safeUrl(href);
  if (!url) return String(href || "");
  url.hash = "";
  return url.toString();
}

export function shouldInspect({ href, anchorText = "", pageUrl = "" } = {}, settings = {}) {
  const url = safeUrl(href);
  if (!url || !/^https?:$/.test(url.protocol)) return false;
  if (FILE_RE.test(url.pathname)) return false;
  if (pageUrl && isSameDocumentHash(url, pageUrl)) return false;
  if (settings.newsOnly === false) return true;
  return isStoryLink(url, anchorText);
}

export function evaluateLink({ href, anchorText = "" } = {}) {
  const url = safeUrl(href);
  if (!url || !/^https?:$/.test(url.protocol)) {
    return blank(href, "Непохоже на обычную веб-ссылку.");
  }

  const domain = url.hostname.replace(/^www\./i, "").toLowerCase();
  const ru = lookupRu(domain);
  const listed = ru ? null : lookupSource(domain);
  const source = ru
    ? { name: ru.name, tier: ru.kind === "official" ? "official" : "registry", domain: ru.domain }
    : listed;
  const text = `${anchorText || ""} ${decodePath(url.pathname)}`.replace(/\s+/g, " ").trim();
  const rhetoric = analyzeRhetoric(text);
  const flags = {
    impersonation: false,
    satire: false,
    shortener: false,
    harsh: false,
    state: ru?.kind === "state" || listed?.tier === "state",
    unlisted: false,
  };
  const reasons = [];
  let score = 50;
  const meters = {
    source: 48,
    rhetoric: rhetoric.meter,
    url: 78,
    cross: source ? 62 : 34,
  };

  if (source?.tier === "satire") {
    flags.satire = true;
    reasons.push(`${source.name} публикует сатиру. Это вымысел по формату, а не новость.`);
    return pack({
      href: url.href,
      domain,
      score: 38,
      verdict: "satire",
      confidence: 0.86,
      summary: VERDICT_SUMMARY.satire,
      reasons,
      meters: { source: 34, rhetoric: 28, url: 70, cross: 30 },
      flags,
      source: { name: source.name, tier: source.tier, domain: source.domain },
      rhetoricPenalty: 0,
    });
  }

  if (SHORTENERS.has(domain)) {
    flags.shortener = true;
    meters.url = 32;
    meters.cross = 22;
    reasons.push("Сервис коротких ссылок скрывает конечный адрес.");
  }

  if (ru?.kind === "official") {
    score += 34;
    meters.source = 90;
    meters.cross = 82;
    reasons.push(`${ru.name} входит в банк российских источников: это официальный сайт.`);
  } else if (ru) {
    score += ru.kind === "state" ? 16 : 18;
    meters.source = ru.kind === "state" ? 72 : 76;
    meters.cross = 66;
    reasons.push(ru.kind === "state"
      ? `${ru.name} есть в банке российских источников. Это государственное медиа: источник знакомый, и это не ярлык «фейк».`
      : `${ru.name} есть в банке российских источников.`);
  } else if (listed?.tier === "low") {
    score -= 36;
    meters.source = 10;
    meters.cross = 16;
    flags.harsh = true;
    reasons.push(`${listed.name} многократно отмечался за выдуманные или вводящие в заблуждение материалы.`);
  } else if (listed && ["high", "factcheck", "established", "state"].includes(listed.tier)) {
    flags.unlisted = true;
    score += listed.tier === "high" || listed.tier === "factcheck" ? 6 : 2;
    meters.source = 52;
    meters.cross = 46;
    reasons.push(`${listed.name} известен, но сайта нет в банке российских источников. Как хороший он не отмечается.`);
  } else if (!flags.shortener) {
    flags.unlisted = true;
    reasons.push("Сайта нет в банке российских источников. Как хороший он не отмечается. Само по себе это не доказательство фейка.");
  }

  const impersonation = source ? null : detectImpersonation(domain);
  if (impersonation) {
    flags.impersonation = true;
    flags.harsh = true;
    score -= 36;
    meters.source = 8;
    meters.url = 16;
    reasons.unshift(`Адрес маскируется под ${impersonation}, но это не официальный домен.`);
  }

  if (url.protocol !== "https:") {
    score -= 12;
    meters.url -= 26;
    flags.harsh = true;
    reasons.push("Страница открывается без HTTPS.");
  }

  const tld = domain.split(".").pop();
  if (!source && SUSPICIOUS_TLDS.has(tld)) {
    score -= 16;
    meters.url -= 22;
    flags.harsh = true;
    reasons.push(`Зона .${tld} часто встречается у одноразовых сайтов.`);
  }

  const labels = domain.split(".").slice(0, -1).join(".");
  if (!source && (labels.match(/-/g) || []).length >= 3) {
    score -= 8;
    meters.url -= 10;
    flags.harsh = true;
    reasons.push("В адресе много дефисов — типичный приём поддельных сайтов.");
  }

  if (!source && /\d{4,}/.test(labels)) {
    score -= 6;
    meters.url -= 8;
    flags.harsh = true;
  }

  if (rhetoric.penalty) {
    score -= rhetoric.penalty;
    flags.harsh = flags.harsh || rhetoric.penalty >= 14;
    for (const note of rhetoric.notes) reasons.push(note);
  }

  if ([...url.searchParams.keys()].length > 7) {
    score -= 4;
    meters.url -= 6;
  }

  score = clamp(Math.round(score), 3, 96);
  const verdict = decideVerdict({ score, source, flags });
  const confidence = decideConfidence({ source, flags, rhetoric });
  const summary = summaryFor({ verdict, source, flags });

  return pack({
    href: url.href,
    domain,
    score,
    verdict,
    confidence,
    summary,
    reasons,
    meters: normalizeMeters(meters),
    flags,
    source: source ? { name: source.name, tier: source.tier, domain: source.domain } : null,
    rhetoricPenalty: rhetoric.penalty,
  });
}

export function applyMeta(result, meta = {}) {
  const title = String(meta.title || "").trim();
  const description = String(meta.description || "").trim();
  const extra = [title, description].filter(Boolean).join(" — ");
  if (!extra) return { ...result, title: title || result.title || "" };

  const rhetoric = analyzeRhetoric(extra);
  if (rhetoric.penalty <= (result.rhetoricPenalty || 0) || result.verdict === "satire") {
    return { ...result, title: title || result.title || "" };
  }

  const added = rhetoric.penalty - (result.rhetoricPenalty || 0);
  const score = clamp(result.score - added, 3, 96);
  const flags = { ...result.flags, harsh: result.flags.harsh || rhetoric.penalty >= 14 };
  const source = result.source ? { ...result.source } : null;
  const verdict = decideVerdict({ score, source, flags });
  const reasons = [...result.reasons];
  for (const note of rhetoric.notes) {
    if (!reasons.includes(note)) reasons.unshift(note);
  }
  const summary = summaryFor({ verdict, source, flags });

  return {
    ...result,
    title,
    score,
    verdict,
    summary,
    reasons: reasons.slice(0, 6),
    meters: normalizeMeters({ ...result.meters, rhetoric: rhetoric.meter }),
    flags,
    rhetoricPenalty: rhetoric.penalty,
    confidence: decideConfidence({ source, flags, rhetoric }),
  };
}

export function analyzeRhetoric(text) {
  const value = String(text || "").replace(/\s+/g, " ").trim();
  if (!value) return { meter: 74, penalty: 0, notes: [] };
  let penalty = 0;
  const notes = [];
  for (const rule of RHETORIC_RULES) {
    if (rule.re.test(value)) {
      penalty += rule.penalty;
      notes.push(rule.note);
    }
  }
  const letters = value.replace(/[^A-Za-zА-Яа-яЁё]/g, "");
  const caps = value.replace(/[^A-ZА-ЯЁ]/g, "");
  if (letters.length >= 12 && caps.length / letters.length > 0.72) {
    penalty += 10;
    notes.push("Текст почти целиком набран капсом.");
  }
  penalty = Math.min(penalty, 32);
  return {
    meter: Math.round(clamp(88 - penalty * 2.5, 8, 92)),
    penalty,
    notes: notes.slice(0, 2),
  };
}

export function settle(result, patch = {}) {
  const flags = patch.flags || result.flags;
  const score = clamp(Math.round(patch.score ?? result.score), 3, 96);
  const source = result.source || null;
  const verdict = patch.verdict || decideVerdict({ score, source, flags });
  const reasons = (patch.reasons || result.reasons || []).slice(0, 6);
  const rhetoricPenalty = patch.rhetoricPenalty ?? result.rhetoricPenalty ?? 0;
  const rest = { ...patch };
  delete rest.score;
  delete rest.flags;
  delete rest.verdict;
  delete rest.reasons;
  delete rest.rhetoricPenalty;
  delete rest.summary;
  delete rest.confidence;
  return {
    ...result,
    ...rest,
    score,
    flags,
    verdict,
    reasons,
    rhetoricPenalty,
    summary: patch.summary || summaryFor({ verdict, source, flags }),
    confidence: patch.confidence ?? decideConfidence({ source, flags, rhetoric: { penalty: rhetoricPenalty } }),
  };
}

function summaryFor({ verdict, source, flags }) {
  if (flags.impersonation) {
    return "Адрес подражает известному изданию. Такой приём часто используют поддельные страницы.";
  }
  if (flags.shortener && verdict === "insufficient") {
    return "Пока виден только короткий адрес. Углублённая проверка попробует раскрыть, куда он ведёт.";
  }
  if (flags.unlisted && source && verdict === "mixed") {
    return `${source.name} известен, но сайта нет в банке российских источников. Как хороший он не отмечается.`;
  }
  if (flags.unlisted && !source && (verdict === "insufficient" || verdict === "mixed")) {
    return "Сайта нет в банке российских источников. Как хороший он не отмечается, и это ещё не значит, что материал фейковый.";
  }
  if (source?.tier === "official") {
    return `${source.name} входит в банк российских источников: это официальный сайт.`;
  }
  if (source?.tier === "registry" && flags.state) {
    return `${source.name} есть в банке российских источников. Это государственное медиа: источник знакомый, и это не ярлык «фейк».`;
  }
  if (source?.tier === "registry") {
    return `${source.name} есть в банке российских источников.`;
  }
  if (source?.tier === "state" && verdict === "mixed") {
    return `${source.name} — государственное или аффилированное медиа. Это не ярлык «фейк», но повестка может быть управляемой.`;
  }
  if (source?.tier === "low" && (verdict === "likely_fake" || verdict === "suspicious")) {
    return `${source.name} многократно отмечался фактчекерами за выдуманные или вводящие в заблуждение материалы.`;
  }
  if (!source && verdict === "mixed" && !flags.harsh) {
    return "Текст выглядит собраннее кликбейта, но домена нет в базе репутации. Это не подтверждение и не фейк.";
  }
  if (!source && (verdict === "leaning" || verdict === "reliable")) {
    return "По адресу не видно известной редакции. Высокий индекс сам по себе материал не подтверждает.";
  }
  return VERDICT_SUMMARY[verdict];
}

function decideVerdict({ score, source, flags }) {
  if (source?.tier === "satire" || flags.satire) return "satire";
  if (flags.shortener && !flags.impersonation && score > 28) return "insufficient";
  if (flags.impersonation) return score < 42 ? "likely_fake" : "suspicious";
  if (source?.tier === "low") return score < 55 ? "likely_fake" : "suspicious";
  if (source?.tier === "official") return score < 64 ? "leaning" : "reliable";
  if (source?.tier === "registry") return score < 40 ? "suspicious" : "leaning";
  if (flags.unlisted && source && ["high", "factcheck", "established", "state"].includes(source.tier)) {
    return score < 40 ? "suspicious" : "mixed";
  }
  if (source?.tier === "state") return score < 40 ? "suspicious" : "mixed";
  if (!source && !flags.harsh) return score >= 64 ? "mixed" : "insufficient";
  if (score < 34) return "likely_fake";
  if (score < 48) return "suspicious";
  if (score < 64) return "mixed";
  if (score < 82) return "leaning";
  return "reliable";
}

function decideConfidence({ source, flags, rhetoric }) {
  let confidence = 0.34;
  if (source) confidence += 0.28;
  if (source && ["high", "low", "satire", "factcheck"].includes(source.tier)) confidence += 0.16;
  if (flags.impersonation) confidence += 0.18;
  if (flags.harsh) confidence += 0.08;
  if (!source && rhetoric.penalty === 0) confidence -= 0.04;
  return clamp(Number(confidence.toFixed(2)), 0.22, 0.9);
}

function detectImpersonation(domain) {
  for (const brand of BRANDS) {
    if (brand.re.test(domain)) return brand.name;
  }
  return null;
}

function isStoryLink(url, anchorText) {
  const domain = url.hostname.replace(/^www\./i, "").toLowerCase();
  if (lookupSource(domain) || SHORTENERS.has(domain)) return true;
  const text = String(anchorText || "").trim();
  if (text.length >= 40 && /\s/.test(text)) return true;
  if (/\/(news|article|articles|story|stories|politics|world|novosti|newsroom|press)\//i.test(url.pathname)) return true;
  if (/\/20\d{2}\/\d{2}\//.test(url.pathname)) return true;
  const slug = url.pathname.split("/").filter(Boolean).pop() || "";
  return slug.length >= 24 && slug.includes("-") && text.length >= 18;
}

function isSameDocumentHash(url, pageUrl) {
  try {
    const page = new URL(pageUrl);
    return url.origin === page.origin
      && url.pathname === page.pathname
      && url.search === page.search
      && Boolean(url.hash);
  } catch {
    return false;
  }
}

function pack(result) {
  return {
    href: result.href,
    domain: result.domain,
    score: result.score,
    verdict: result.verdict,
    confidence: result.confidence,
    summary: result.summary,
    reasons: result.reasons.slice(0, 6),
    meters: result.meters,
    flags: result.flags,
    source: result.source,
    engine: "local",
    title: "",
    rhetoricPenalty: result.rhetoricPenalty || 0,
  };
}

function blank(href, summary) {
  return {
    href: String(href || ""),
    domain: "",
    score: 50,
    verdict: "insufficient",
    confidence: 0.22,
    summary,
    reasons: [summary],
    meters: { source: 40, rhetoric: 50, url: 40, cross: 20 },
    flags: { impersonation: false, satire: false, shortener: false, harsh: false, state: false, unlisted: false },
    source: null,
    engine: "local",
    title: "",
    rhetoricPenalty: 0,
  };
}

function normalizeMeters(meters) {
  return {
    source: clamp(Math.round(meters.source), 0, 100),
    rhetoric: clamp(Math.round(meters.rhetoric), 0, 100),
    url: clamp(Math.round(meters.url), 0, 100),
    cross: clamp(Math.round(meters.cross), 0, 100),
  };
}

function safeUrl(href) {
  try {
    return new URL(href);
  } catch {
    return null;
  }
}

function decodePath(pathname) {
  try {
    return decodeURIComponent(pathname).replace(/[-_/]+/g, " ");
  } catch {
    return pathname.replace(/[-_/]+/g, " ");
  }
}
