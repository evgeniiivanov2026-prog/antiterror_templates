(function () {
  var nav = document.querySelector("[data-nav]");
  var toggle = document.querySelector("[data-nav-toggle]");

  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = nav.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && nav.classList.contains("is-open")) {
        nav.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
      }
    });
  }

  var bar = document.querySelector("[data-progress]");
  if (!bar) {
    return;
  }

  var paint = function () {
    var root = document.documentElement;
    var max = root.scrollHeight - root.clientHeight;
    var value = max > 0 ? (root.scrollTop / max) * 100 : 0;
    bar.style.width = value + "%";
  };

  document.addEventListener("scroll", paint, { passive: true });
  window.addEventListener("resize", paint);
  paint();
})();
