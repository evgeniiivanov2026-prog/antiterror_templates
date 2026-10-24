(function () {
  var toggle = document.querySelector("[data-nav-toggle]");
  var header = document.querySelector(".site-header");
  if (!toggle || !header) {
    return;
  }

  toggle.addEventListener("click", function () {
    var open = header.classList.toggle("nav-open");
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
  });

  header.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && header.classList.contains("nav-open")) {
      header.classList.remove("nav-open");
      toggle.setAttribute("aria-expanded", "false");
      toggle.focus();
    }
  });
})();
