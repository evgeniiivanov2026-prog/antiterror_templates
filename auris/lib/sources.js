/** Стартовая карта репутации. Это не рейтинг стран и не приговор редакции. */

export const SOURCES = {
  "reuters.com": { tier: "high", name: "Reuters" },
  "apnews.com": { tier: "high", name: "Associated Press" },
  "afp.com": { tier: "high", name: "AFP" },
  "bbc.com": { tier: "high", name: "BBC" },
  "bbc.co.uk": { tier: "high", name: "BBC" },
  "npr.org": { tier: "high", name: "NPR" },
  "nature.com": { tier: "high", name: "Nature" },
  "science.org": { tier: "high", name: "Science" },
  "thelancet.com": { tier: "high", name: "The Lancet" },
  "nejm.org": { tier: "high", name: "NEJM" },
  "who.int": { tier: "high", name: "WHO" },
  "cdc.gov": { tier: "high", name: "CDC" },
  "nih.gov": { tier: "high", name: "NIH" },
  "nasa.gov": { tier: "high", name: "NASA" },
  "esa.int": { tier: "high", name: "ESA" },
  "un.org": { tier: "high", name: "ООН" },

  "snopes.com": { tier: "factcheck", name: "Snopes" },
  "factcheck.org": { tier: "factcheck", name: "FactCheck.org" },
  "politifact.com": { tier: "factcheck", name: "PolitiFact" },
  "fullfact.org": { tier: "factcheck", name: "Full Fact" },
  "stopfake.org": { tier: "factcheck", name: "StopFake" },
  "leadstories.com": { tier: "factcheck", name: "Lead Stories" },

  "nytimes.com": { tier: "established", name: "The New York Times" },
  "washingtonpost.com": { tier: "established", name: "The Washington Post" },
  "theguardian.com": { tier: "established", name: "The Guardian" },
  "wsj.com": { tier: "established", name: "The Wall Street Journal" },
  "ft.com": { tier: "established", name: "Financial Times" },
  "economist.com": { tier: "established", name: "The Economist" },
  "bloomberg.com": { tier: "established", name: "Bloomberg" },
  "propublica.org": { tier: "established", name: "ProPublica" },
  "dw.com": { tier: "established", name: "Deutsche Welle" },
  "france24.com": { tier: "established", name: "France 24" },
  "lemonde.fr": { tier: "established", name: "Le Monde" },
  "spiegel.de": { tier: "established", name: "Der Spiegel" },
  "cnn.com": { tier: "established", name: "CNN" },
  "nbcnews.com": { tier: "established", name: "NBC News" },
  "pbs.org": { tier: "established", name: "PBS" },
  "kyivindependent.com": { tier: "established", name: "The Kyiv Independent" },
  "meduza.io": { tier: "established", name: "Медуза" },
  "novayagazeta.eu": { tier: "established", name: "Новая газета" },
  "zona.media": { tier: "established", name: "Медиазона" },
  "holod.media": { tier: "established", name: "Холод" },
  "thebell.io": { tier: "established", name: "The Bell" },
  "istories.media": { tier: "established", name: "Важные истории" },
  "theins.ru": { tier: "established", name: "The Insider" },
  "svoboda.org": { tier: "established", name: "Радио Свобода" },
  "currenttime.tv": { tier: "established", name: "Настоящее Время" },
  "kommersant.ru": { tier: "established", name: "Коммерсантъ" },
  "vedomosti.ru": { tier: "established", name: "Ведомости" },
  "rbc.ru": { tier: "established", name: "РБК" },
  "interfax.ru": { tier: "established", name: "Интерфакс" },
  "forbes.ru": { tier: "established", name: "Forbes" },
  "fontanka.ru": { tier: "established", name: "Фонтанка" },
  "lenta.ru": { tier: "established", name: "Лента.ру" },
  "themoscowtimes.com": { tier: "established", name: "The Moscow Times" },

  "tass.ru": { tier: "state", name: "ТАСС" },
  "tass.com": { tier: "state", name: "ТАСС" },
  "ria.ru": { tier: "state", name: "РИА Новости" },
  "rt.com": { tier: "state", name: "RT" },
  "sputniknews.com": { tier: "state", name: "Sputnik" },
  "sputnikglobe.com": { tier: "state", name: "Sputnik" },
  "vesti.ru": { tier: "state", name: "Вести" },
  "1tv.ru": { tier: "state", name: "Первый канал" },
  "ntv.ru": { tier: "state", name: "НТВ" },
  "cgtn.com": { tier: "state", name: "CGTN" },
  "xinhuanet.com": { tier: "state", name: "Синьхуа" },
  "globaltimes.cn": { tier: "state", name: "Global Times" },

  "infowars.com": { tier: "low", name: "InfoWars" },
  "naturalnews.com": { tier: "low", name: "Natural News" },
  "beforeitsnews.com": { tier: "low", name: "Before It's News" },
  "worldnewsdailyreport.com": { tier: "low", name: "World News Daily Report" },
  "nationalreport.net": { tier: "low", name: "National Report" },
  "empirenews.net": { tier: "low", name: "Empire News" },
  "huzlers.com": { tier: "low", name: "Huzlers" },
  "yournewswire.com": { tier: "low", name: "YourNewsWire" },
  "newspunch.com": { tier: "low", name: "NewsPunch" },
  "worldtruth.tv": { tier: "low", name: "World Truth" },
  "neonnettle.com": { tier: "low", name: "Neon Nettle" },
  "awarenessact.com": { tier: "low", name: "Awareness Act" },
  "dailybuzzlive.com": { tier: "low", name: "Daily Buzz Live" },

  "theonion.com": { tier: "satire", name: "The Onion" },
  "clickhole.com": { tier: "satire", name: "ClickHole" },
  "babylonbee.com": { tier: "satire", name: "The Babylon Bee" },
  "thedailymash.co.uk": { tier: "satire", name: "The Daily Mash" },
  "waterfordwhispersnews.com": { tier: "satire", name: "Waterford Whispers News" },
  "fognews.ru": { tier: "satire", name: "Fognews" },
  "reductress.com": { tier: "satire", name: "Reductress" },
  "thehardtimes.net": { tier: "satire", name: "The Hard Times" },
  "thebetootaadvocate.com": { tier: "satire", name: "The Betoota Advocate" },
};

export const BRANDS = [
  { name: "Reuters", re: /(^|\.)reuters([.-]|$)/, real: ["reuters.com"] },
  { name: "BBC", re: /(^|\.)bbc([.-]|$)/, real: ["bbc.com", "bbc.co.uk"] },
  { name: "CNN", re: /(^|\.)cnn([.-]|$)/, real: ["cnn.com"] },
  { name: "Associated Press", re: /(^|\.)(apnews|associatedpress)([.-]|$)/, real: ["apnews.com"] },
  { name: "The New York Times", re: /(^|\.)(nytimes|newyorktimes)([.-]|$)/, real: ["nytimes.com"] },
  { name: "The Guardian", re: /(^|\.)(theguardian|guardian)([.-]|$)/, real: ["theguardian.com"] },
  { name: "РИА Новости", re: /(^|\.)ria([.-]|$)/, real: ["ria.ru"] },
  { name: "ТАСС", re: /(^|\.)tass([.-]|$)/, real: ["tass.ru", "tass.com"] },
  { name: "РБК", re: /(^|\.)rbc([.-]|$)/, real: ["rbc.ru"] },
  { name: "Медуза", re: /(^|\.)meduza([.-]|$)/, real: ["meduza.io"] },
  { name: "Коммерсантъ", re: /(^|\.)kommersant([.-]|$)/, real: ["kommersant.ru"] },
  { name: "Лента.ру", re: /(^|\.)lenta([.-]|$)/, real: ["lenta.ru"] },
];

export const SUSPICIOUS_TLDS = new Set([
  "xyz", "top", "click", "gq", "ml", "cf", "tk", "buzz", "rest",
  "monster", "country", "download", "review", "fit", "cam", "quest",
  "cyou", "icu",
]);

export const SHORTENERS = new Set([
  "bit.ly", "tinyurl.com", "t.co", "goo.gl", "ow.ly", "is.gd", "buff.ly",
  "vk.cc", "clck.ru", "cutt.ly", "rb.gy", "tiny.cc", "shorturl.at",
]);

export function lookupSource(hostname) {
  const host = String(hostname || "").replace(/^www\./i, "").toLowerCase();
  if (!host) return null;
  const parts = host.split(".");
  for (let index = 0; index < parts.length - 1; index += 1) {
    const candidate = parts.slice(index).join(".");
    if (SOURCES[candidate]) return { ...SOURCES[candidate], domain: candidate };
  }
  return null;
}
