(function () {
  document.documentElement.classList.add('has-js');

  var root = document.documentElement;
  var storageKey = 'vigilum-theme';
  var pref = root.dataset.themePref || 'system';
  var stored = null;
  try { stored = window.localStorage.getItem(storageKey); } catch (e) { stored = null; }

  function systemDark() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  }

  function apply(theme) {
    var resolved = theme === 'system' ? (systemDark() ? 'dark' : 'light') : theme;
    root.setAttribute('data-theme', resolved);
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', resolved === 'dark' ? '#111824' : '#16233a');
  }

  var current = stored || pref;
  apply(current);

  var toggle = document.querySelector('[data-theme-toggle]');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      apply(next);
      try { window.localStorage.setItem(storageKey, next); } catch (e) {}
      toggle.setAttribute('aria-label', next === 'dark' ? 'Включить светлую тему' : 'Включить тёмную тему');
    });
  }

  var menuButton = document.querySelector('.mob-hamburger');
  var mobileMenu = document.getElementById('site-mobile-menu');
  if (menuButton && mobileMenu) {
    menuButton.addEventListener('click', function () {
      var open = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!open));
      menuButton.setAttribute('aria-label', open ? 'Открыть меню' : 'Закрыть меню');
      mobileMenu.hidden = open;
    });
    mobileMenu.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        menuButton.setAttribute('aria-expanded', 'false');
        menuButton.setAttribute('aria-label', 'Открыть меню');
        mobileMenu.hidden = true;
      }
    });
  }

  var searchToggle = document.querySelector('[data-search-toggle]');
  var searchPanel = document.getElementById('header-search');
  if (searchToggle && searchPanel) {
    searchToggle.addEventListener('click', function () {
      var open = searchToggle.getAttribute('aria-expanded') === 'true';
      searchToggle.setAttribute('aria-expanded', String(!open));
      searchPanel.hidden = open;
      if (!open) {
        var input = searchPanel.querySelector('input[type="search"]');
        if (input) input.focus();
      }
    });
  }

  var progress = document.querySelector('[data-reading-progress]');
  if (progress && document.querySelector('.article .entry-content')) {
    progress.hidden = false;
    var onScroll = function () {
      var article = document.querySelector('.article .entry-content');
      if (!article) return;
      var rect = article.getBoundingClientRect();
      var total = article.offsetHeight - window.innerHeight;
      var passed = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
      progress.style.width = (passed / Math.max(total, 1)) * 100 + '%';
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  document.querySelectorAll('[data-copy]').forEach(function (button) {
    button.addEventListener('click', function () {
      var value = button.getAttribute('data-copy');
      if (navigator.clipboard && value) {
        navigator.clipboard.writeText(value).then(function () {
          button.textContent = 'Ссылка скопирована';
        });
      }
    });
  });

  var targets = document.querySelectorAll('.section-head, .mission__intro, .mission__body, .card, .principles article');
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    targets.forEach(function (target, index) {
      target.style.transitionDelay = Math.min(index % 5, 4) * 60 + 'ms';
      observer.observe(target);
    });
  } else {
    targets.forEach(function (target) { target.classList.add('is-visible'); });
  }
}());
