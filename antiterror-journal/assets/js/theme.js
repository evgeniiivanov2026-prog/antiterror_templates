(function () {
  var root = document.documentElement;
  root.classList.add('has-js');

  var stored = null;
  try { stored = window.localStorage.getItem('atj-theme'); } catch (e) {}
  var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  var dark = stored ? stored === 'dark' : prefersDark;
  setTheme(dark);

  var themeButton = document.querySelector('[data-theme-toggle]');
  if (themeButton) {
    themeButton.addEventListener('click', function () {
      dark = root.getAttribute('data-theme') !== 'dark';
      setTheme(dark);
      try { window.localStorage.setItem('atj-theme', dark ? 'dark' : 'light'); } catch (e) {}
    });
  }

  function setTheme(isDark) {
    root.setAttribute('data-theme', isDark ? 'dark' : 'light');
    if (themeButton) {
      themeButton.setAttribute('aria-label', isDark ? 'Включить светлую тему' : 'Включить тёмную тему');
    }
  }

  var burger = document.querySelector('.atj-burger');
  var mobile = document.getElementById('atj-mobile');
  if (burger && mobile) {
    burger.addEventListener('click', function () {
      var open = burger.getAttribute('aria-expanded') === 'true';
      burger.setAttribute('aria-expanded', String(!open));
      burger.setAttribute('aria-label', open ? 'Открыть меню' : 'Закрыть меню');
      mobile.hidden = open;
    });
    mobile.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        burger.setAttribute('aria-expanded', 'false');
        mobile.hidden = true;
      }
    });
  }

  var search = document.getElementById('atj-search');
  var searchOpen = document.querySelector('[data-search-open]');
  var searchClose = document.querySelector('[data-search-close]');
  function toggleSearch(show) {
    if (!search) return;
    search.hidden = !show;
    if (searchOpen) searchOpen.setAttribute('aria-expanded', String(show));
    if (show) {
      var field = search.querySelector('input[type="search"]');
      if (field) field.focus();
    }
  }
  if (searchOpen) searchOpen.addEventListener('click', function () { toggleSearch(true); });
  if (searchClose) searchClose.addEventListener('click', function () { toggleSearch(false); });
  if (search) {
    search.addEventListener('click', function (event) {
      if (event.target === search) toggleSearch(false);
    });
  }
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') toggleSearch(false);
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
      event.preventDefault();
      toggleSearch(true);
    }
  });

  var targets = document.querySelectorAll('[data-observe] .atj-section__head, [data-observe] .atj-featured, [data-observe] .atj-card, [data-observe] .atj-portrait, [data-observe] .atj-ballot__item');
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    targets.forEach(function (target, index) {
      target.style.transitionDelay = Math.min(index % 4, 3) * 70 + 'ms';
      observer.observe(target);
    });
  } else {
    targets.forEach(function (target) { target.classList.add('is-visible'); });
  }

  var chapters = document.querySelectorAll('[id="cover"], [id="journal"], [id="memory"], [id="fund"]');
  var rails = document.querySelectorAll('[data-rail]');
  if (chapters.length && rails.length && 'IntersectionObserver' in window) {
    var railObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        rails.forEach(function (rail) {
          rail.classList.toggle('is-active', rail.getAttribute('data-rail') === entry.target.id);
        });
      });
    }, { threshold: 0.35 });
    chapters.forEach(function (chapter) { railObserver.observe(chapter); });
  }

  var progress = document.querySelector('[data-progress] span');
  var article = document.querySelector('.atj-article__content');
  if (progress && article) {
    window.addEventListener('scroll', function () {
      var rect = article.getBoundingClientRect();
      var total = article.offsetHeight - window.innerHeight;
      var passed = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
      progress.style.width = (passed / Math.max(total, 1)) * 100 + '%';
    }, { passive: true });
  }
}());
