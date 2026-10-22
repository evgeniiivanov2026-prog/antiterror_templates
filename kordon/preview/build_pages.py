#!/usr/bin/env python3
"""Собирает статические макеты публичных страниц XenForo для проверки стиля."""

from pathlib import Path

OUT = Path(__file__).resolve().parent

NAV = [
    ("index.html", "Форумы", "forum_list"),
    ("forum.html", "Профилактика", "forum_view"),
    ("thread.html", "Обсуждение", "thread_view"),
    ("forum.html#members", "Участники", "member_view"),
]


def avatar(code, size):
    return (
        f'<span class="avatar avatar--{size}">'
        f'<img src="/styles/kordon/avatars/{code}.svg" alt="" width="80" height="80">'
        f"</span>"
    )


def chrome(active_href, template):
    links = []
    for href, label, _tpl in NAV:
        current = ' aria-current="page"' if href == active_href else ""
        selected = " is-selected" if href == active_href else ""
        links.append(
            f'<li class="p-navEl{selected}"><a class="p-navEl-link" href="{href}"{current}>{label}</a></li>'
        )
    nav = "\n          ".join(links)
    return f"""<a class="u-srOnly" href="#content">К содержанию</a>
<div class="p-pageWrapper" id="top">
  <div class="p-navSticky p-navSticky--primary">
    <header class="p-header" id="header">
      <div class="p-header-inner">
        <div class="p-header-content">
          <div class="p-header-logo p-header-logo--text">
            <a href="index.html">Кордон</a>
          </div>
        </div>
      </div>
    </header>
    <nav class="p-nav" aria-label="Разделы">
      <div class="p-nav-inner">
        <button class="p-nav-menuTrigger" type="button" aria-label="Меню" aria-expanded="false"><span></span></button>
        <div class="p-nav-smallLogo"><a href="index.html">Кордон</a></div>
        <div class="p-nav-scroller">
          <ul class="p-nav-list">
          {nav}
          </ul>
        </div>
        <div class="p-nav-opposite">
          <div class="p-navgroup">
            <a class="p-navgroup-link p-navgroup-link--search" href="forum.html" aria-label="Поиск">
              <svg class="kordon-search" viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M15.5 15.5 20 20" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </a>
            <a class="p-navgroup-link p-navgroup-link--user" href="thread.html">
              {avatar("mk", "xxs")}
              <span class="p-navgroup-linkText">Марина</span>
            </a>
          </div>
        </div>
      </div>
    </nav>
    <div class="p-sectionLinks">
      <div class="p-sectionLinks-inner">
        <ul class="p-sectionLinks-list">
          <li><a class="p-sectionLinks-link{' is-selected' if template == 'forum_list' else ''}" href="index.html">Разделы</a></li>
          <li><a class="p-sectionLinks-link" href="forum.html">Что нового</a></li>
          <li><a class="p-sectionLinks-link" href="forum.html#rules">Правила разговора</a></li>
          <li><a class="p-sectionLinks-link" href="tel:112">Линия 112</a></li>
        </ul>
      </div>
    </div>
  </div>
"""


SIDEBAR = f"""
<aside class="p-body-sidebar">
  <div class="block">
    <div class="block-container kordon-hotline">
      <p class="kordon-hotline-kicker">Линия доверия</p>
      <a class="kordon-hotline-number" href="tel:112">112</a>
      <p class="kordon-hotline-text">Единый номер экстренных служб. Если есть угроза, звоните сразу: где вы и что происходит. Это не чат форума.</p>
    </div>
  </div>
  <div class="block" id="members">
    <div class="block-container">
      <h3 class="block-minorHeader">Сейчас на форуме</h3>
      <div class="block-body block-row">
        <dl class="pairs pairs--justified"><dt>Темы</dt><dd>186</dd></dl>
        <dl class="pairs pairs--justified"><dt>Сообщения</dt><dd>2&nbsp;431</dd></dl>
        <dl class="pairs pairs--justified"><dt>Участники</dt><dd>914</dd></dl>
      </div>
      <div class="block-footer">Новый участник: Нина Савельева</div>
    </div>
  </div>
  <div class="block">
    <div class="block-container">
      <h3 class="block-minorHeader">На линии</h3>
      <div class="block-body block-row">
        <div class="contentRow">
          {avatar("ab", "xs")}
          <div class="contentRow-main"><a class="contentRow-title" href="thread.html">Артём Белов</a><div class="contentRow-minor u-muted">читает правила</div></div>
        </div>
        <div class="contentRow">
          {avatar("ns", "xs")}
          <div class="contentRow-main"><a class="contentRow-title" href="thread.html">Нина Савельева</a><div class="contentRow-minor u-muted">в разделе «Память»</div></div>
        </div>
      </div>
    </div>
  </div>
</aside>
"""


def footer():
    return """
  <footer class="p-footer">
    <div class="p-footer-inner">
      <div class="p-footer-row">
        <ul class="p-footer-linkList">
          <li><a href="index.html">Правила</a></li>
          <li><a href="forum.html">Профилактика</a></li>
          <li><a href="thread.html">Как устроен разговор</a></li>
          <li><a href="tel:112">112</a></li>
        </ul>
        <ul class="p-footer-linkList">
          <li><a href="#" data-kordon-theme="light">Кордон</a></li>
          <li><a href="#" data-kordon-theme="dark">Кордон · ночь</a></li>
        </ul>
      </div>
      <div class="p-footer-copyright">Кордон · образец оформления для XenForo. Тексты на макете вымышлены.</div>
    </div>
  </footer>
</div>
"""


def page(title, template, active, body):
    return f"""<!DOCTYPE html>
<html lang="ru" data-template="{template}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{title} — Кордон</title>
  <link rel="icon" href="/styles/kordon/mark.svg">
  <link rel="stylesheet" href="scaffold.css">
  <link rel="stylesheet" href="../extra.css">
</head>
<body class="template-{template}">
{chrome(active, template)}
  <div class="p-body">
    <div class="p-body-inner">
      <main class="p-body-main" id="content">
        <div class="p-body-content">
{body}
        </div>
        {SIDEBAR}
      </main>
    </div>
  </div>
{footer()}
<script src="preview.js"></script>
</body>
</html>
"""


INDEX = """
          <ul class="notices notices--block" aria-label="Объявление">
            <li class="notice notice--primary"><div class="notice-content">3 сентября — день солидарности в борьбе с терроризмом. В разделе «Память» только материалы, которые редакция может проверить. Не публикуйте чужие биографии без согласия семьи.</div></li>
          </ul>
          <div class="block block--category">
            <div class="block-container">
              <h2 class="block-header"><a href="forum.html">Редакция</a><span class="block-desc">Как устроен форум и что считается доказанным</span></h2>
              <div class="block-body">
                <div class="node node--forum node--read">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="forum.html">Объявления</a></h3>
                      <div class="node-description">Служебные сообщения редакции: календарь памятных дат и правила выпуска.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Темы</dt><dd>24</dd></dl>
                      <dl class="pairs pairs--rows"><dt>Сообщения</dt><dd>180</dd></dl>
                    </div>
                    <div class="node-extra">
                      <div class="node-extra-row"><a class="node-extra-title" href="thread.html">Расписание публикаций на ноябрь</a></div>
                      <div class="node-extra-row u-muted">22 октября · Редакция</div>
                    </div>
                  </div>
                </div>
                <div class="node node--forum node--read">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="forum.html#rules">Правила и модерация</a></h3>
                      <div class="node-description">Без слухов, без чужих фотографий, без инструкций. Спор идёт о фактах и заботе.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Темы</dt><dd>8</dd></dl>
                      <dl class="pairs pairs--rows"><dt>Сообщения</dt><dd>96</dd></dl>
                    </div>
                    <div class="node-extra">
                      <div class="node-extra-row"><a class="node-extra-title" href="thread.html">Что удаляет модератор</a></div>
                      <div class="node-extra-row u-muted">вчера · Павел Ершов</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="block block--category">
            <div class="block-container">
              <h2 class="block-header"><a href="thread.html">Память</a><span class="block-desc">Имена публикуются только с проверяемым источником</span></h2>
              <div class="block-body">
                <div class="node node--forum node--unread">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="thread.html">Имена и даты</a></h3>
                      <div class="node-description">Как редакция сверяет дату, место и согласие семьи, прежде чем открыть тему.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Темы</dt><dd>40</dd></dl>
                      <dl class="pairs pairs--rows"><dt>Сообщения</dt><dd>520</dd></dl>
                    </div>
                    <div class="node-extra">
                      <div class="node-extra-row"><a class="node-extra-title" href="thread.html">Как устроена проверка материала</a></div>
                      <div class="node-extra-row u-muted">сегодня · Марина Кольцова</div>
                    </div>
                  </div>
                </div>
                <div class="node node--forum node--read">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="forum.html">Семейные свидетельства</a></h3>
                      <div class="node-description">Письма и фотографии присылает семья. Редакция не дописывает биографию.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Темы</dt><dd>18</dd></dl>
                      <dl class="pairs pairs--rows"><dt>Сообщения</dt><dd>240</dd></dl>
                    </div>
                    <div class="node-extra">
                      <div class="node-extra-row"><a class="node-extra-title" href="forum.html">Форма согласия на публикацию</a></div>
                      <div class="node-extra-row u-muted">12 октября · Нина Савельева</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="block block--category">
            <div class="block-container">
              <h2 class="block-header"><a href="forum.html">Профилактика</a><span class="block-desc">Разговор без запугивания</span></h2>
              <div class="block-body">
                <div class="node node--forum node--unread">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="thread.html">Разговор с детьми</a></h3>
                      <div class="node-description">Как отвечать на тревожные новости в классе и дома, не показывая кадры.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Темы</dt><dd>31</dd></dl>
                      <dl class="pairs pairs--rows"><dt>Сообщения</dt><dd>640</dd></dl>
                    </div>
                    <div class="node-extra">
                      <div class="node-extra-row"><a class="node-extra-title" href="thread.html">Как говорить с классом о тревожных новостях</a></div>
                      <div class="node-extra-row u-muted">сегодня · Марина Кольцова</div>
                    </div>
                  </div>
                </div>
                <div class="node node--forum">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="forum.html">Школа и двор</a></h3>
                      <div class="node-description">Тревога у входа, незнакомый свёрток, закрытая дверь. Куда звонить, а не что разбирать.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Темы</dt><dd>22</dd></dl>
                      <dl class="pairs pairs--rows"><dt>Сообщения</dt><dd>410</dd></dl>
                    </div>
                    <div class="node-extra">
                      <div class="node-extra-row"><a class="node-extra-title" href="forum.html">Памятка для дежурного учителя</a></div>
                      <div class="node-extra-row u-muted">18 октября · Артём Белов</div>
                    </div>
                  </div>
                </div>
                <div class="node node--link">
                  <div class="node-body">
                    <span class="node-icon" aria-hidden="true"><i></i></span>
                    <div class="node-main">
                      <h3 class="node-title"><a href="tel:112">Куда сообщать</a></h3>
                      <div class="node-description">112 — экстренные службы. Форум не принимает оперативные сообщения.</div>
                    </div>
                    <div class="node-stats">
                      <dl class="pairs pairs--rows"><dt>Переход</dt><dd>112</dd></dl>
                    </div>
                    <div class="node-extra"><div class="node-extra-row u-muted">телефон, не тема</div></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
"""


FORUM = """
          <ul class="p-breadcrumbs" aria-label="Навигация">
            <li><a href="index.html">Форумы</a></li>
            <li><a href="forum.html">Профилактика</a></li>
            <li><span>Разговор с детьми</span></li>
          </ul>
          <div class="p-title">
            <h1 class="p-title-value">Разговор с детьми</h1>
            <div class="p-title-pageAction"><a class="button button--primary" href="thread.html">Новая тема</a></div>
          </div>
          <p class="p-description">Как отвечать на страх после новостей. Без кадров, без пересказа чужих «сводок», с понятным следующим шагом для взрослого.</p>
          <div class="block">
            <div class="block-container">
              <div class="block-filterBar"><div class="filterBar"><span>Сортировка: последние сообщения</span><a href="forum.html">Фильтры</a></div></div>
              <div class="structItemContainer">
                <div class="structItem structItem--thread is-sticky">
                  <div class="structItem-cell structItem-cell--icon">{av_rk}</div>
                  <div class="structItem-cell structItem-cell--main">
                    <div class="structItem-title"><span class="label label--primary">Важно</span><a href="thread.html">Правила этого раздела</a></div>
                    <div class="structItem-minor"><ul class="structItem-parts"><li><a class="username username--staff" href="thread.html">Редакция</a></li><li>12 сентября</li></ul></div>
                  </div>
                  <div class="structItem-cell structItem-cell--meta"><dl class="pairs pairs--justified"><dt>Ответы</dt><dd>6</dd></dl><dl class="pairs pairs--justified"><dt>Просмотры</dt><dd>1&nbsp;240</dd></dl></div>
                  <div class="structItem-cell structItem-cell--latest"><a href="thread.html">3 октября</a><div class="structItem-minor">Павел Ершов</div></div>
                </div>
                <div class="structItem structItem--thread is-unread">
                  <div class="structItem-cell structItem-cell--icon">{av_mk}</div>
                  <div class="structItem-cell structItem-cell--main">
                    <div class="structItem-title"><a href="thread.html">Как говорить с классом о тревожных новостях</a></div>
                    <div class="structItem-minor"><ul class="structItem-parts"><li><a class="username" href="thread.html">Марина Кольцова</a></li><li>сегодня в 09:40</li></ul></div>
                  </div>
                  <div class="structItem-cell structItem-cell--meta"><dl class="pairs pairs--justified"><dt>Ответы</dt><dd>18</dd></dl><dl class="pairs pairs--justified"><dt>Просмотры</dt><dd>640</dd></dl></div>
                  <div class="structItem-cell structItem-cell--latest"><a href="thread.html">сегодня</a><div class="structItem-minor">Артём Белов</div></div>
                </div>
                <div class="structItem structItem--thread">
                  <div class="structItem-cell structItem-cell--icon">{av_ns}</div>
                  <div class="structItem-cell structItem-cell--main">
                    <div class="structItem-title"><span class="label">Памятка</span><a href="thread.html">Что сказать ребёнку, если он увидел чужой пересказ</a></div>
                    <div class="structItem-minor"><ul class="structItem-parts"><li><a class="username" href="thread.html">Нина Савельева</a></li><li>19 октября</li></ul></div>
                  </div>
                  <div class="structItem-cell structItem-cell--meta"><dl class="pairs pairs--justified"><dt>Ответы</dt><dd>9</dd></dl><dl class="pairs pairs--justified"><dt>Просмотры</dt><dd>388</dd></dl></div>
                  <div class="structItem-cell structItem-cell--latest"><a href="thread.html">20 октября</a><div class="structItem-minor">Нина Савельева</div></div>
                </div>
                <div class="structItem structItem--thread">
                  <div class="structItem-cell structItem-cell--icon">{av_pe}</div>
                  <div class="structItem-cell structItem-cell--main">
                    <div class="structItem-title"><a href="forum.html">Почему нельзя пересылать непроверенные «сводки»</a></div>
                    <div class="structItem-minor"><ul class="structItem-parts"><li><a class="username username--moderator" href="forum.html">Павел Ершов</a></li><li>2 октября</li></ul></div>
                  </div>
                  <div class="structItem-cell structItem-cell--meta"><dl class="pairs pairs--justified"><dt>Ответы</dt><dd>27</dd></dl><dl class="pairs pairs--justified"><dt>Просмотры</dt><dd>1&nbsp;102</dd></dl></div>
                  <div class="structItem-cell structItem-cell--latest"><a href="forum.html">11 октября</a><div class="structItem-minor">Ирина Волкова</div></div>
                </div>
              </div>
            </div>
          </div>
          <nav class="pageNavWrapper" aria-label="Страницы">
            <div class="pageNav">
              <ul class="pageNav-main" style="display:flex;gap:4px;list-style:none;margin:0;padding:0;">
                <li class="pageNav-page pageNav-page--current"><a href="forum.html">1</a></li>
                <li class="pageNav-page"><a href="forum.html">2</a></li>
                <li class="pageNav-page"><a href="forum.html">3</a></li>
              </ul>
            </div>
          </nav>
""".format(
    av_rk=avatar("rk", "s"),
    av_mk=avatar("mk", "s"),
    av_ns=avatar("ns", "s"),
    av_pe=avatar("pe", "s"),
)


THREAD = f"""
          <ul class="p-breadcrumbs" aria-label="Навигация">
            <li><a href="index.html">Форумы</a></li>
            <li><a href="forum.html">Профилактика</a></li>
            <li><a href="forum.html">Разговор с детьми</a></li>
          </ul>
          <div class="p-title">
            <h1 class="p-title-value">Как говорить с классом о тревожных новостях</h1>
            <div class="p-title-pageAction"><a class="button button--primary" href="#reply">Ответить</a></div>
          </div>
          <p class="p-description"><span class="label label--primary">Памятка</span> Черновик для классного руководителя. Это не инструкция службам и не разбор происшествия.</p>
          <div class="block block--messages">
            <div class="block-container">
              <div class="block-body">
                <article class="message message--post">
                  <div class="message-inner">
                    <div class="message-cell message-cell--user">
                      <section class="message-user">
                        <div class="message-avatar">{avatar("mk", "m")}</div>
                        <h4 class="message-name"><a href="thread.html" class="username">Марина Кольцова</a></h4>
                        <h5 class="message-userTitle">Педагог, Москва</h5>
                        <div class="userBanner">Участник</div>
                        <div class="message-userExtras">
                          <dl class="pairs pairs--justified"><dt>Сообщения</dt><dd>1&nbsp;204</dd></dl>
                          <dl class="pairs pairs--justified"><dt>Реакции</dt><dd>860</dd></dl>
                        </div>
                      </section>
                    </div>
                    <div class="message-cell message-cell--main">
                      <header class="message-attribution">
                        <ul class="listInline message-attribution-main">
                          <li><a href="#post-1"><time datetime="2026-10-22">22 октября 2026, 09:40</time></a></li>
                        </ul>
                        <span class="message-attribution-opposite"><a href="#post-1">#1</a></span>
                      </header>
                      <div class="message-content">
                        <article class="message-body">
                          <div class="bbWrapper">
                            <p>После громкой новости дети приносят в класс обрывки чужих сообщений. Задача взрослого — вернуть разговор на землю, а не добавить подробностей.</p>
                            <h2>Короткий порядок</h2>
                            <ol>
                              <li>Отделите факт, который уже сказали официально, от пересказа.</li>
                              <li>Не открывайте фото и видео. Скажите прямо, что кадры не помогают понять, что делать.</li>
                              <li>Назовите номер: при угрозе звонят 112, форум для этого не подходит.</li>
                              <li>Верните детям ощущение рядом стоящего взрослого: учитель, родитель, охрана школы.</li>
                            </ol>
                            <div class="bbCodeBlock bbCodeBlock--quote">
                              <div class="bbCodeBlock-title">Из правил раздела</div>
                              <div class="bbCodeBlock-content">Не публикуйте имена, адреса школ и непроверенные «сводки». Если человеку нужна помощь сейчас — это телефон, не тема.</div>
                            </div>
                            <p>Если формулировка для вашего возраста уже есть у школьного психолога, начните с неё. Этот текст — только каркас разговора.</p>
                          </div>
                        </article>
                      </div>
                      <footer class="message-footer">
                        <div class="message-actionBar actionBar"><ul class="actionBar-set"><li><a class="actionBar-action" href="#reply">Ответить</a></li><li><a class="actionBar-action" href="forum.html">В раздел</a></li></ul></div>
                        <div class="message-signature">Марина ведёт разговоры в школе. В подписи нет контактов для «срочных наводок».</div>
                      </footer>
                    </div>
                  </div>
                </article>
                <article class="message message--post" id="post-2">
                  <div class="message-inner">
                    <div class="message-cell message-cell--user">
                      <section class="message-user">
                        <div class="message-avatar">{avatar("pe", "m")}</div>
                        <h4 class="message-name"><a href="thread.html" class="username username--moderator">Павел Ершов</a></h4>
                        <h5 class="message-userTitle">Модератор</h5>
                        <div class="userBanner">Редакция</div>
                        <div class="message-userExtras">
                          <dl class="pairs pairs--justified"><dt>Сообщения</dt><dd>4&nbsp;102</dd></dl>
                          <dl class="pairs pairs--justified"><dt>Реакции</dt><dd>2&nbsp;330</dd></dl>
                        </div>
                      </section>
                    </div>
                    <div class="message-cell message-cell--main">
                      <header class="message-attribution">
                        <ul class="listInline"><li><a href="#post-2"><time datetime="2026-10-22T11:05">22 октября 2026, 11:05</time></a></li></ul>
                        <span><a href="#post-2">#2</a></span>
                      </header>
                      <div class="message-content">
                        <article class="message-body">
                          <div class="bbWrapper">
                            <p>Закрепляю границу раздела. Сообщения с чужими адресами, схемами прохода и просьбой «посмотреть, что в свёртке» снимаются. Для этого есть 112.</p>
                            <p>Можно обсуждать, как успокоить класс, как говорить с родителями и как не разгонять слух.</p>
                          </div>
                        </article>
                      </div>
                      <footer class="message-footer"><div class="message-actionBar"><ul class="actionBar-set"><li><a class="actionBar-action" href="#reply">Ответить</a></li></ul></div></footer>
                    </div>
                  </div>
                </article>
              </div>
            </div>
          </div>
          <div class="block message--quickReply" id="reply">
            <div class="block-container">
              <h3 class="block-minorHeader">Ваш ответ</h3>
              <div class="block-body block-row">
                <form action="thread.html" method="get">
                  <label class="u-srOnly" for="reply-text">Текст ответа</label>
                  <textarea class="input" id="reply-text" name="message" placeholder="Пишите спокойно. Без чужих персональных данных."></textarea>
                  <div style="margin-top:12px"><button class="button button--primary" type="submit">Отправить</button> <button class="button button--link" type="reset">Очистить</button></div>
                </form>
              </div>
            </div>
          </div>
"""


def main():
    pages = {
        "index.html": page("Форумы", "forum_list", "index.html", INDEX),
        "forum.html": page("Разговор с детьми", "forum_view", "forum.html", FORUM),
        "thread.html": page("Как говорить с классом", "thread_view", "thread.html", THREAD),
    }
    for name, html in pages.items():
        (OUT / name).write_text(html, encoding="utf-8")
        print(name, len(html))


if __name__ == "__main__":
    main()
