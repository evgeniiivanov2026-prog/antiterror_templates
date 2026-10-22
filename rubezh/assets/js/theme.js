(function () {
  var root = document.documentElement;
  var toggle = document.querySelector("[data-theme-toggle]");
  var navButton = document.querySelector("[data-nav-toggle]");
  var nav = document.getElementById("rz-nav");
  var searchButton = document.querySelector("[data-search-open]");
  var dialog = document.getElementById("rz-search");
  var progress = document.querySelector("[data-progress]");

  function paintTheme(theme) {
    root.setAttribute("data-theme", theme);
    var dark = theme === "dark";
    if (toggle) {
      toggle.setAttribute("aria-pressed", dark ? "true" : "false");
      toggle.setAttribute("aria-label", dark ? toggle.getAttribute("data-label-light") : toggle.getAttribute("data-label-dark"));
    }
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute("content", dark ? "#12151c" : "#f3efe6");
  }

  paintTheme(root.getAttribute("data-theme") === "dark" ? "dark" : "light");

  if (toggle) {
    toggle.addEventListener("click", function () {
      var next = root.getAttribute("data-theme") === "dark" ? "light" : "dark";
      paintTheme(next);
      try { localStorage.setItem("rubezh-theme", next); } catch (error) {}
    });
  }

  if (navButton && nav) {
    navButton.addEventListener("click", function () {
      var open = navButton.getAttribute("aria-expanded") === "true";
      navButton.setAttribute("aria-expanded", String(!open));
      navButton.setAttribute("aria-label", open ? "Открыть меню" : "Закрыть меню");
      nav.classList.toggle("is-open", !open);
      document.body.classList.toggle("rz-nav-open", !open);
    });
    nav.addEventListener("click", function (event) {
      if (event.target.closest("a")) {
        navButton.setAttribute("aria-expanded", "false");
        nav.classList.remove("is-open");
        document.body.classList.remove("rz-nav-open");
      }
    });
  }

  if (searchButton && dialog && typeof dialog.showModal === "function") {
    searchButton.addEventListener("click", function () {
      dialog.showModal();
      var input = dialog.querySelector("input[type='search']");
      if (input) input.focus();
    });
    dialog.addEventListener("click", function (event) {
      if (event.target === dialog || event.target.closest("[data-search-close]")) dialog.close();
    });
  }

  if (progress) {
    var updateProgress = function () {
      var scrollable = document.documentElement.scrollHeight - window.innerHeight;
      var value = scrollable > 0 ? Math.min(1, window.scrollY / scrollable) : 0;
      progress.style.transform = "scaleX(" + value + ")";
    };
    updateProgress();
    window.addEventListener("scroll", updateProgress, { passive: true });
  }

  function refreshBars(list) {
    var nodes = list.querySelectorAll("[data-vote-root]");
    var max = 1;
    nodes.forEach(function (node) {
      max = Math.max(max, parseInt(node.getAttribute("data-count") || "0", 10));
    });
    nodes.forEach(function (node) {
      var bar = node.querySelector("[data-vote-bar]");
      var count = parseInt(node.getAttribute("data-count") || "0", 10);
      if (bar) bar.style.setProperty("--share", (count / max) * 100 + "%");
    });
  }

  document.addEventListener("click", function (event) {
    var button = event.target.closest("[data-vote]");
    if (!button || button.disabled || !window.rubezhData) return;
    var box = button.closest("[data-vote-root]");
    if (!box) return;
    var error = box.querySelector("[data-vote-error]");
    button.disabled = true;
    var body = new URLSearchParams();
    body.set("action", "rubezh_vote");
    body.set("nonce", window.rubezhData.nonce);
    body.set("post_id", box.getAttribute("data-id") || "");
    fetch(window.rubezhData.ajaxUrl, {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: body.toString()
    }).then(function (response) {
      return response.json();
    }).then(function (payload) {
      if (!payload || !payload.success) {
        var message = payload && payload.data && payload.data.message ? payload.data.message : window.rubezhData.error;
        throw new Error(message);
      }
      var pressed = !!payload.data.voted;
      button.setAttribute("aria-pressed", pressed ? "true" : "false");
      button.textContent = pressed ? window.rubezhData.votedLabel : window.rubezhData.voteLabel;
      box.setAttribute("data-count", String(payload.data.count));
      var count = box.querySelector("[data-vote-count]");
      if (count) count.textContent = payload.data.label;
      var list = box.closest("[data-vote-list]");
      if (list) refreshBars(list);
      if (error) error.textContent = "";
    }).catch(function (err) {
      if (error) error.textContent = err.message || window.rubezhData.error;
    }).finally(function () {
      button.disabled = false;
    });
  });

  var more = document.querySelector("[data-load-more]");
  if (more && window.rubezhData) {
    var pager = document.querySelector("[data-pager]");
    if (pager) pager.hidden = true;
    more.hidden = false;
    more.addEventListener("click", function () {
      var page = parseInt(more.getAttribute("data-page") || "2", 10);
      more.disabled = true;
      var body = new URLSearchParams();
      body.set("action", "rubezh_posts");
      body.set("nonce", window.rubezhData.postsNonce);
      body.set("page", String(page));
      body.set("cat", more.getAttribute("data-cat") || "0");
      fetch(window.rubezhData.ajaxUrl, {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
        body: body.toString()
      }).then(function (response) {
        return response.json();
      }).then(function (payload) {
        if (!payload || !payload.success) throw new Error();
        var list = document.querySelector("[data-post-list]");
        if (list) list.insertAdjacentHTML("beforeend", payload.data.html);
        if (payload.data.more) {
          more.setAttribute("data-page", String(page + 1));
          more.disabled = false;
        } else {
          more.remove();
        }
      }).catch(function () {
        more.disabled = false;
        if (pager) pager.hidden = false;
      });
    });
  }

  var copy = document.querySelector("[data-copy-link]");
  if (copy && navigator.clipboard) {
    copy.addEventListener("click", function () {
      var original = copy.textContent;
      navigator.clipboard.writeText(window.location.href).then(function () {
        copy.textContent = copy.getAttribute("data-copied") || original;
        window.setTimeout(function () { copy.textContent = original; }, 1600);
      });
    });
  }
}());
