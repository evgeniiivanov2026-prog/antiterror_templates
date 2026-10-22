(function () {
  var key = "kordon-theme";
  var root = document.documentElement;
  var query = new URLSearchParams(location.search).get("theme");
  var saved = query || localStorage.getItem(key);
  if (saved === "dark") root.classList.add("kordon-dark");

  function paint() {
    var dark = root.classList.contains("kordon-dark");
    document.querySelectorAll("[data-kordon-theme]").forEach(function (link) {
      var on = (link.getAttribute("data-kordon-theme") === "dark") === dark;
      link.classList.toggle("is-active", on);
      if (on) link.setAttribute("aria-current", "true");
      else link.removeAttribute("aria-current");
    });
  }

  document.addEventListener("click", function (event) {
    var themeLink = event.target.closest("[data-kordon-theme]");
    if (themeLink) {
      event.preventDefault();
      var mode = themeLink.getAttribute("data-kordon-theme");
      root.classList.toggle("kordon-dark", mode === "dark");
      localStorage.setItem(key, mode);
      paint();
      return;
    }
    var trigger = event.target.closest(".p-nav-menuTrigger");
    if (trigger) {
      event.preventDefault();
      var list = document.querySelector(".p-nav-list");
      var open = list.classList.toggle("is-active");
      trigger.setAttribute("aria-expanded", open ? "true" : "false");
    }
  });

  paint();
})();
