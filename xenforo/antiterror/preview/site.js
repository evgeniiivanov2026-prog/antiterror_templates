(function () {
	var trigger = document.querySelector(".p-nav-menuTrigger");
	if (!trigger) return;
	var list = document.getElementById("navList");
	trigger.addEventListener("click", function () {
		var open = document.body.classList.toggle("is-menuOpen");
		trigger.setAttribute("aria-expanded", open ? "true" : "false");
	});
	if (!list) return;
	list.addEventListener("click", function (event) {
		if (event.target.closest("a")) {
			document.body.classList.remove("is-menuOpen");
			trigger.setAttribute("aria-expanded", "false");
		}
	});
})();
