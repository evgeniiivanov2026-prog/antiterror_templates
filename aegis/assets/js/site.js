(function () {
  document.documentElement.classList.add('has-js');

  var root = document.documentElement;
  var stored = null;
  try { stored = localStorage.getItem('aegis-theme'); } catch (e) {}
  var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  var dark = stored ? stored === 'dark' : prefersDark;
  root.setAttribute('data-theme', dark ? 'dark' : 'light');

  var i18n = (window.aegisTheme && window.aegisTheme.i18n) || {};
  var themeButton = document.querySelector('[data-theme-toggle]');
  if (themeButton) {
    themeButton.setAttribute('aria-label', dark ? (i18n.light || 'Light') : (i18n.dark || 'Dark'));
    themeButton.addEventListener('click', function () {
      dark = root.getAttribute('data-theme') !== 'dark';
      root.setAttribute('data-theme', dark ? 'dark' : 'light');
      themeButton.setAttribute('aria-label', dark ? (i18n.light || 'Light') : (i18n.dark || 'Dark'));
      try { localStorage.setItem('aegis-theme', dark ? 'dark' : 'light'); } catch (e) {}
    });
  }

  var menuButton = document.querySelector('.mob-hamburger');
  var mobileMenu = document.getElementById('site-mobile-menu');
  if (menuButton && mobileMenu) {
    menuButton.addEventListener('click', function () {
      var open = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!open));
      menuButton.setAttribute('aria-label', open ? (i18n.openMenu || 'Open') : (i18n.closeMenu || 'Close'));
      mobileMenu.hidden = open;
    });
    mobileMenu.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        menuButton.setAttribute('aria-expanded', 'false');
        mobileMenu.hidden = true;
      }
    });
  }

  var search = document.getElementById('site-search');
  var openSearch = document.querySelector('[data-search-open]');
  var closeSearch = document.querySelector('[data-search-close]');
  function setSearch(open) {
    if (!search || !openSearch) return;
    search.hidden = !open;
    openSearch.setAttribute('aria-expanded', String(open));
    if (open) {
      var field = search.querySelector('input[type="search"]');
      if (field) field.focus();
    }
  }
  if (openSearch) openSearch.addEventListener('click', function () { setSearch(search.hidden); });
  if (closeSearch) closeSearch.addEventListener('click', function () { setSearch(false); });
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      setSearch(false);
      if (menuButton && mobileMenu) {
        menuButton.setAttribute('aria-expanded', 'false');
        mobileMenu.hidden = true;
      }
    }
  });

  var toTop = document.querySelector('[data-to-top]');
  if (toTop) {
    window.addEventListener('scroll', function () {
      toTop.hidden = window.scrollY < 640;
    }, { passive: true });
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  var reveals = document.querySelectorAll('.reveal, .ballot-card, .memory-card, .story-card');
  if ('IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible', 'reveal');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    reveals.forEach(function (node, index) {
      node.classList.add('reveal');
      node.style.transitionDelay = Math.min(index % 5, 4) * 60 + 'ms';
      observer.observe(node);
    });
  } else {
    reveals.forEach(function (node) { node.classList.add('is-visible'); });
  }

  function api(path, body) {
    var cfg = window.aegisTheme || {};
    return fetch((cfg.rest || '/wp-json/aegis/v1/') + path, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': cfg.nonce || ''
      },
      credentials: 'same-origin',
      body: JSON.stringify(body || {})
    }).then(function (res) {
      return res.json().then(function (data) {
        if (!res.ok) throw new Error(data.message || (i18n.voteError || 'Error'));
        return data;
      });
    });
  }

  document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-vote]');
    if (!button || button.disabled) return;
    var id = button.getAttribute('data-vote');
    var box = button.closest('.vote-control');
    button.disabled = true;
    api('vote', { id: Number(id) }).then(function (data) {
      if (box) {
        box.classList.add('is-voted');
        var count = box.querySelector('[data-vote-count]');
        if (count && typeof data.votes !== 'undefined') count.textContent = String(data.votes);
      }
      button.textContent = data.already ? (i18n.already || data.message) : (i18n.voted || data.message);
    }).catch(function (err) {
      button.disabled = false;
      button.textContent = err.message || (i18n.voteError || 'Error');
    });
  });

  var form = document.querySelector('[data-propose-form]');
  if (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var status = form.querySelector('[data-form-status]');
      var payload = {
        title: (form.title && form.title.value) || '',
        description: (form.description && form.description.value) || '',
        author: (form.author && form.author.value) || '',
        topic: (form.topic && form.topic.value) || '',
        website: (form.website && form.website.value) || ''
      };
      var submit = form.querySelector('button[type="submit"]');
      submit.disabled = true;
      api('propose', payload).then(function (data) {
        if (status) status.textContent = data.message || '';
        form.reset();
      }).catch(function (err) {
        if (status) status.textContent = err.message || '';
      }).then(function () {
        submit.disabled = false;
      });
    });
  }
}());
