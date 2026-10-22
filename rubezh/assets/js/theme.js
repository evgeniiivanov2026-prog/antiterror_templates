(function () {
  var root = document.documentElement;
  root.classList.add("has-js");

  var stored = null;
  try { stored = localStorage.getItem("rubezh-theme"); } catch (e) {}
  var prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
  var dark = stored ? stored === "dark" : prefersDark;
  applyTheme(dark);

  var i18n = (window.rubezhTheme && window.rubezhTheme.i18n) || {};

  var themeButton = document.querySelector("[data-theme-toggle]");
  if (themeButton) {
    themeButton.addEventListener("click", function () {
      dark = root.getAttribute("data-theme") !== "dark";
      applyTheme(dark);
      try { localStorage.setItem("rubezh-theme", dark ? "dark" : "light"); } catch (e) {}
    });
  }

  function applyTheme(isDark) {
    root.setAttribute("data-theme", isDark ? "dark" : "light");
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute("content", isDark ? "#0e1218" : "#152033");
    if (themeButton) {
      themeButton.setAttribute("aria-label", isDark ? (i18n.light || "Включить светлую тему") : (i18n.dark || "Включить тёмную тему"));
    }
  }

  var menuButton = document.querySelector(".mob-hamburger");
  var mobileMenu = document.getElementById("site-mobile-menu");
  if (menuButton && mobileMenu) {
    menuButton.addEventListener("click", function () {
      var open = menuButton.getAttribute("aria-expanded") === "true";
      menuButton.setAttribute("aria-expanded", String(!open));
      menuButton.setAttribute("aria-label", open ? (i18n.openMenu || "Открыть меню") : (i18n.closeMenu || "Закрыть меню"));
      mobileMenu.hidden = open;
    });
    mobileMenu.addEventListener("click", function (event) {
      if (event.target.closest("a")) {
        menuButton.setAttribute("aria-expanded", "false");
        mobileMenu.hidden = true;
      }
    });
  }

  var search = document.getElementById("site-search");
  var searchOpen = document.querySelector("[data-search-open]");
  var searchClose = document.querySelector("[data-search-close]");
  function setSearch(open) {
    if (!search) return;
    search.hidden = !open;
    if (searchOpen) searchOpen.setAttribute("aria-expanded", String(open));
    if (open) {
      var input = search.querySelector("input[type='search']");
      if (input) input.focus();
    }
  }
  if (searchOpen) searchOpen.addEventListener("click", function () { setSearch(true); });
  if (searchClose) searchClose.addEventListener("click", function () { setSearch(false); });
  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      setSearch(false);
      if (menuButton && mobileMenu) {
        menuButton.setAttribute("aria-expanded", "false");
        mobileMenu.hidden = true;
      }
    }
    if ((event.metaKey || event.ctrlKey) && event.key === "k") {
      event.preventDefault();
      setSearch(true);
    }
  });

  var header = document.querySelector(".site-header");
  var onScroll = function () {
    if (!header) return;
    header.classList.toggle("is-condensed", window.scrollY > 24);
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  var progress = document.querySelector("[data-read-progress]");
  var article = document.querySelector(".article-wrap .entry-content, .initiative-page .entry-content, .hero-page .entry-content");
  if (progress && article) {
    progress.hidden = false;
    var bar = progress.querySelector("span");
    var update = function () {
      var doc = document.documentElement;
      var pageTotal = doc.scrollHeight - window.innerHeight;
      var pageValue = pageTotal > 0 ? window.scrollY / pageTotal : 0;
      var rect = article.getBoundingClientRect();
      var articleTotal = article.offsetHeight - window.innerHeight * 0.35;
      var passed = Math.min(Math.max(-rect.top, 0), Math.max(articleTotal, 1));
      var articleValue = articleTotal > 0 ? passed / articleTotal : 0;
      bar.style.transform = "scaleX(" + Math.max(pageValue, articleValue) + ")";
    };
    window.addEventListener("scroll", update, { passive: true });
    update();
  }

  var targets = document.querySelectorAll(".reveal");
  if ("IntersectionObserver" in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    targets.forEach(function (target, index) {
      target.style.transitionDelay = Math.min(index % 5, 4) * 60 + "ms";
      observer.observe(target);
    });
  } else {
    targets.forEach(function (target) { target.classList.add("is-visible"); });
  }

  hydrateVotes();

  document.addEventListener("click", function (event) {
    var button = event.target.closest("[data-vote]");
    if (!button || button.disabled) return;
    event.preventDefault();
    var id = button.getAttribute("data-vote");
    vote(id, button);
  });

  function voteStore() {
    try { return JSON.parse(localStorage.getItem("rubezh-votes") || "{}"); } catch (e) { return {}; }
  }
  function saveVoteStore(data) {
    try { localStorage.setItem("rubezh-votes", JSON.stringify(data)); } catch (e) {}
  }
  function votedLabel() {
    return (window.rubezhTheme && window.rubezhTheme.i18n && window.rubezhTheme.i18n.voted) || "Ваш голос учтён";
  }
  function hydrateVotes() {
    var data = voteStore();
    document.querySelectorAll("[data-initiative]").forEach(function (card) {
      var id = card.getAttribute("data-initiative");
      var legacy = false;
      try { legacy = localStorage.getItem("rubezh-vote-" + id) === "1"; } catch (e) {}
      if (!data[id] && !legacy) return;
      var record = data[id] || { voted: legacy };
      var countNode = card.querySelector("[data-vote-count]");
      if (countNode && typeof record.votes === "number") {
        countNode.textContent = String(record.votes);
      }
      if (record.voted) {
        var button = card.querySelector("[data-vote]");
        if (button) {
          button.disabled = true;
          button.textContent = votedLabel();
          button.classList.add("is-voted");
        }
      }
    });
    updateBars();
  }

  function vote(id, button) {
    button.disabled = true;
    var cfg = window.rubezhTheme;
    if (cfg && cfg.rest) {
      fetch(cfg.rest, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-WP-Nonce": cfg.nonce || ""
        },
        credentials: "same-origin",
        body: JSON.stringify({ id: Number(id) })
      })
        .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
        .then(function (result) {
          if (result.data && typeof result.data.votes !== "undefined") {
            applyVote(button, result.data.votes, result.data.duplicate);
          } else {
            button.disabled = false;
            announce(button, (cfg.i18n && cfg.i18n.error) || "Ошибка");
          }
        })
        .catch(function () {
          button.disabled = false;
          announce(button, (cfg.i18n && cfg.i18n.error) || "Ошибка");
        });
      return;
    }
    demoVote(id, button);
  }

  function demoVote(id, button) {
    var data = voteStore();
    var card = button.closest("[data-initiative]");
    var countNode = card ? card.querySelector("[data-vote-count]") : document.querySelector("[data-vote-count]");
    var current = countNode ? parseInt(String(countNode.textContent).replace(/\s/g, ""), 10) || 0 : 0;
    if (data[id] && data[id].voted) {
      applyVote(button, data[id].votes || current, true);
      return;
    }
    current += 1;
    data[id] = { voted: true, votes: current };
    saveVoteStore(data);
    try { localStorage.setItem("rubezh-vote-" + id, "1"); } catch (e) {}
    applyVote(button, current, false);
  }

  function applyVote(button, votes, duplicate) {
    var card = button.closest("[data-initiative]");
    var scope = card || document;
    var countNode = scope.querySelector("[data-vote-count]");
    if (countNode) countNode.textContent = String(votes);
    updateBars();
    button.disabled = true;
    button.textContent = votedLabel();
    button.classList.add("is-voted");
    if (duplicate) {
      announce(button, (window.rubezhTheme && window.rubezhTheme.i18n && window.rubezhTheme.i18n.duplicate) || "Вы уже поддержали эту инициативу.");
    }
  }

  function updateBars() {
    var max = 1;
    document.querySelectorAll("[data-vote-count]").forEach(function (node) {
      max = Math.max(max, parseInt(String(node.textContent).replace(/\s/g, ""), 10) || 0);
    });
    document.querySelectorAll("[data-initiative]").forEach(function (card) {
      var countNode = card.querySelector("[data-vote-count]");
      var bar = card.querySelector(".vote-bar span");
      if (!countNode || !bar) return;
      var votes = parseInt(String(countNode.textContent).replace(/\s/g, ""), 10) || 0;
      bar.style.width = Math.round((votes / max) * 100) + "%";
    });
  }

  function announce(node, text) {
    var live = document.getElementById("rubezh-live");
    if (!live) {
      live = document.createElement("div");
      live.id = "rubezh-live";
      live.className = "screen-reader-text";
      live.setAttribute("aria-live", "polite");
      document.body.appendChild(live);
    }
    live.textContent = text;
    node.setAttribute("title", text);
  }
}());
