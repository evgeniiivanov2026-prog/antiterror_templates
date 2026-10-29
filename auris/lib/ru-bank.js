/**
 * Банк российских источников для русской аудитории.
 * Сайт из банка отмечается как хороший (знакомый официальный или редакционный).
 * Сайта нет в банке — как хороший он не отмечается.
 *
 * Росфинмониторинг не публикует белый список новостных сайтов: его открытый
 * перечень — люди и организации, связанные с экстремизмом или терроризмом.
 * Сюда входит официальный сайт самой службы и другие открытые сайты ведомств,
 * плюс известные российские редакции. Это не копия того перечня и не приговор
 * сайтам за его пределами.
 */

const OFFICIAL = {
  "kremlin.ru": "Президент России",
  "government.ru": "Правительство России",
  "duma.gov.ru": "Госдума",
  "council.gov.ru": "Совет Федерации",
  "scrf.gov.ru": "Совет безопасности",
  "mid.ru": "МИД России",
  "mil.ru": "Минобороны",
  "mvd.ru": "МВД",
  "mchs.gov.ru": "МЧС",
  "sledcom.ru": "Следственный комитет",
  "genproc.gov.ru": "Генпрокуратура",
  "vsrf.ru": "Верховный суд",
  "ksrf.ru": "Конституционный суд",
  "cbr.ru": "Банк России",
  "fedsfm.ru": "Росфинмониторинг",
  "minfin.gov.ru": "Минфин",
  "nalog.gov.ru": "ФНС",
  "rosstat.gov.ru": "Росстат",
  "publication.pravo.gov.ru": "Официальное опубликование",
  "pravo.gov.ru": "Официальный интернет-портал правовой информации",
  "digital.gov.ru": "Минцифры",
  "economy.gov.ru": "Минэкономразвития",
  "minzdrav.gov.ru": "Минздрав",
  "edu.gov.ru": "Минпросвещения",
  "minobrnauki.gov.ru": "Минобрнауки",
  "mintrans.ru": "Минтранс",
  "minstroyrf.gov.ru": "Минстрой",
  "minpromtorg.gov.ru": "Минпромторг",
  "mnr.gov.ru": "Минприроды",
  "culture.gov.ru": "Минкультуры",
  "fas.gov.ru": "ФАС",
  "roskomnadzor.gov.ru": "Роскомнадзор",
  "rkn.gov.ru": "Роскомнадзор",
  "rospotrebnadzor.ru": "Роспотребнадзор",
  "rostrud.gov.ru": "Роструд",
  "rosreestr.gov.ru": "Росреестр",
  "customs.gov.ru": "ФТС",
  "sfr.gov.ru": "Социальный фонд",
  "pfr.gov.ru": "Социальный фонд",
  "gosuslugi.ru": "Госуслуги",
  "mos.ru": "Правительство Москвы",
  "gov.spb.ru": "Правительство Санкт-Петербурга",
  "data.gov.ru": "Портал открытых данных",
};

const STATE_MEDIA = {
  "tass.ru": "ТАСС",
  "tass.com": "ТАСС",
  "ria.ru": "РИА Новости",
  "rg.ru": "Российская газета",
  "1tv.ru": "Первый канал",
  "ntv.ru": "НТВ",
  "vesti.ru": "Вести",
  "smotrim.ru": "Смотрим",
  "tvzvezda.ru": "Телеканал «Звезда»",
  "5-tv.ru": "Пятый канал",
  "rt.com": "RT",
  "sputniknews.com": "Sputnik",
  "sputnikglobe.com": "Sputnik",
  "iz.ru": "Известия",
};

const NEWSROOM = {
  "interfax.ru": "Интерфакс",
  "rbc.ru": "РБК",
  "kommersant.ru": "Коммерсантъ",
  "vedomosti.ru": "Ведомости",
  "forbes.ru": "Forbes",
  "bfm.ru": "Business FM",
  "dp.ru": "Деловой Петербург",
  "fontanka.ru": "Фонтанка",
  "lenta.ru": "Лента.ру",
  "gazeta.ru": "Газета.Ru",
  "aif.ru": "Аргументы и факты",
  "kp.ru": "Комсомольская правда",
  "mk.ru": "Московский комсомолец",
  "ura.news": "URA.RU",
  "e1.ru": "E1",
  "ngs.ru": "НГС",
  "74.ru": "74.ру",
  "kommersant.com": "Коммерсантъ",
};

function fill(table, kind) {
  const bank = {};
  for (const [domain, name] of Object.entries(table)) bank[domain] = { name, kind };
  return bank;
}

export const RU_BANK = {
  ...fill(OFFICIAL, "official"),
  ...fill(STATE_MEDIA, "state"),
  ...fill(NEWSROOM, "newsroom"),
};

export function lookupRu(hostname) {
  const host = String(hostname || "").replace(/^www\./i, "").toLowerCase();
  if (!host) return null;
  const parts = host.split(".");
  for (let index = 0; index < parts.length - 1; index += 1) {
    const candidate = parts.slice(index).join(".");
    if (RU_BANK[candidate]) return { ...RU_BANK[candidate], domain: candidate };
  }
  return null;
}
