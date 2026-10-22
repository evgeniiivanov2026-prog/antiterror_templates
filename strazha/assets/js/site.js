(function () {
	var root = document.documentElement;
	var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
	var i18n = (window.Strazha && window.Strazha.i18n) || {};

	function themeName() {
		return root.getAttribute("data-theme") === "dark" ? "dark" : "light";
	}

	function paintChrome(theme) {
		var meta = document.querySelector('meta[name="theme-color"]');
		if (meta) {
			meta.setAttribute("content", theme === "dark" ? "#121820" : "#f3efe6");
		}
	}

	function applyTheme(theme, persist) {
		root.setAttribute("data-theme", theme);
		paintChrome(theme);
		if (persist) {
			try {
				localStorage.setItem("strazha-theme", theme);
			} catch (error) {}
		}
		var toggle = document.querySelector("[data-theme-toggle]");
		if (toggle) {
			toggle.setAttribute("aria-label", theme === "dark" ? (i18n.light || "Включить светлую тему") : (i18n.dark || "Включить тёмную тему"));
		}
	}

	paintChrome(themeName());

	var toggle = document.querySelector("[data-theme-toggle]");
	if (toggle) {
		toggle.addEventListener("click", function () {
			applyTheme(themeName() === "dark" ? "light" : "dark", true);
		});
	}

	var media = window.matchMedia("(prefers-color-scheme: dark)");
	if (media.addEventListener) {
		media.addEventListener("change", function (event) {
			try {
				if (localStorage.getItem("strazha-theme")) {
					return;
				}
			} catch (error) {
				return;
			}
			applyTheme(event.matches ? "dark" : "light", false);
		});
	}

	function openDialog(dialog) {
		if (!dialog) {
			return;
		}
		if (typeof dialog.showModal === "function") {
			if (!dialog.open) {
				dialog.showModal();
			}
		} else {
			dialog.setAttribute("open", "");
		}
	}

	function closeDialog(dialog) {
		if (!dialog) {
			return;
		}
		if (typeof dialog.close === "function") {
			dialog.close();
		} else {
			dialog.removeAttribute("open");
		}
	}

	var searchDialog = document.getElementById("search-dialog");
	var navDialog = document.getElementById("nav-dialog");
	var searchOpen = document.querySelector("[data-search-open]");
	var navOpen = document.querySelector("[data-nav-open]");

	if (searchOpen && searchDialog) {
		searchOpen.addEventListener("click", function () {
			openDialog(searchDialog);
			var input = searchDialog.querySelector('input[type="search"]');
			if (input) {
				input.focus();
			}
		});
	}

	if (navOpen && navDialog) {
		navOpen.addEventListener("click", function () {
			openDialog(navDialog);
			navOpen.setAttribute("aria-expanded", "true");
		});
		navDialog.addEventListener("close", function () {
			navOpen.setAttribute("aria-expanded", "false");
		});
		navDialog.addEventListener("click", function (event) {
			if (event.target.closest("a")) {
				closeDialog(navDialog);
			}
		});
	}

	document.querySelectorAll("[data-dialog-close]").forEach(function (button) {
		button.addEventListener("click", function () {
			closeDialog(button.closest("dialog"));
		});
	});

	[searchDialog, navDialog].forEach(function (dialog) {
		if (!dialog) {
			return;
		}
		dialog.addEventListener("click", function (event) {
			var rect = dialog.getBoundingClientRect();
			var inside = event.clientX >= rect.left && event.clientX <= rect.right && event.clientY >= rect.top && event.clientY <= rect.bottom;
			if (event.target === dialog && !inside) {
				closeDialog(dialog);
			}
		});
	});

	document.addEventListener("keydown", function (event) {
		if (event.key !== "/" || event.metaKey || event.ctrlKey || event.altKey) {
			return;
		}
		var el = document.activeElement;
		if (!el) {
			return;
		}
		var tag = el.tagName;
		if (tag === "INPUT" || tag === "TEXTAREA" || el.isContentEditable) {
			return;
		}
		if (!searchDialog) {
			return;
		}
		event.preventDefault();
		openDialog(searchDialog);
		var input = searchDialog.querySelector('input[type="search"]');
		if (input) {
			input.focus();
		}
	});

	var frame = 0;
	function onScroll() {
		if (frame) {
			return;
		}
		frame = window.requestAnimationFrame(function () {
			document.body.classList.toggle("is-compact", window.scrollY > 28);
			var bar = document.querySelector(".progress span");
			var article = document.querySelector(".entry--dropcap .entry__content");
			if (bar && article && document.body.classList.contains("has-progress")) {
				var rect = article.getBoundingClientRect();
				var total = Math.max(article.offsetHeight - window.innerHeight * 0.4, 1);
				var seen = Math.min(Math.max(-rect.top, 0), total);
				bar.style.transform = "scaleX(" + (seen / total) + ")";
			}
			frame = 0;
		});
	}
	onScroll();
	window.addEventListener("scroll", onScroll, { passive: true });

	document.querySelectorAll(".entry--dropcap .entry__content").forEach(function (entry) {
		var first = entry.firstElementChild;
		if (first && first.tagName === "P") {
			first.classList.add("has-dropcap");
		}
	});

	document.querySelectorAll("[data-rail]").forEach(function (button) {
		button.addEventListener("click", function () {
			var rail = document.getElementById(button.getAttribute("data-rail"));
			if (!rail) {
				return;
			}
			var card = rail.querySelector(".portrait");
			var width = card ? card.getBoundingClientRect().width + 16 : 260;
			var dir = Number(button.getAttribute("data-dir")) || 1;
			rail.scrollBy({ left: width * dir, behavior: reduce ? "auto" : "smooth" });
		});
	});

	document.addEventListener("submit", function (event) {
		var form = event.target;
		if (!form || !form.matches || !form.matches("[data-vote-form]") || !window.fetch || !window.Strazha) {
			return;
		}
		event.preventDefault();
		if (form.dataset.busy === "1") {
			return;
		}
		form.dataset.busy = "1";
		var button = form.querySelector(".vote");
		var note = form.querySelector("[data-vote-note]");
		fetch(window.Strazha.ajaxUrl, {
			method: "POST",
			body: new FormData(form),
			credentials: "same-origin",
			headers: { Accept: "application/json" }
		}).then(function (response) {
			return response.json();
		}).then(function (payload) {
			var result = payload.data || {};
			if (!payload.success) {
				if (note) {
					note.textContent = result.message || i18n.error || "Не удалось отправить голос";
				}
				return;
			}
			if (button) {
				button.classList.toggle("is-on", !!result.pressed);
				button.setAttribute("aria-pressed", result.pressed ? "true" : "false");
				var label = button.querySelector("[data-label]");
				var count = button.querySelector("[data-count]");
				if (label && result.label) {
					label.textContent = result.label;
				}
				if (count && result.votes_label) {
					count.textContent = result.votes_label;
				}
			}
			if (note) {
				note.textContent = result.message || "";
			}
		}).catch(function () {
			if (note) {
				note.textContent = i18n.error || "Не удалось отправить голос";
			}
		}).finally(function () {
			form.dataset.busy = "";
		});
	});

	var more = document.querySelector("[data-feed]");
	if (more && window.Strazha) {
		more.hidden = false;
		var pager = more.parentElement ? more.parentElement.querySelector(".pagination") : null;
		if (pager) {
			pager.hidden = true;
		}
		more.addEventListener("click", function () {
			var page = Number(more.getAttribute("data-page") || "1") + 1;
			var max = Number(more.getAttribute("data-max") || "1");
			var cards = document.querySelector("[data-cards]");
			if (!cards || page > max) {
				more.hidden = true;
				return;
			}
			more.disabled = true;
			var previous = more.textContent;
			more.textContent = i18n.loading || "Загрузка…";
			var body = new FormData();
			body.append("action", "strazha_feed");
			body.append("nonce", window.Strazha.feedNonce);
			body.append("page", String(page));
			["cat", "tag", "author", "year", "month", "day", "s"].forEach(function (key) {
				body.append(key, more.getAttribute("data-" + key) || "");
			});
			fetch(window.Strazha.ajaxUrl, {
				method: "POST",
				body: body,
				credentials: "same-origin",
				headers: { Accept: "application/json" }
			}).then(function (response) {
				return response.json();
			}).then(function (payload) {
				if (!payload.success) {
					throw new Error("feed");
				}
				if (payload.data && payload.data.html) {
					cards.insertAdjacentHTML("beforeend", payload.data.html);
				}
				more.setAttribute("data-page", String(page));
				if (!payload.data || !payload.data.more) {
					more.hidden = true;
					var end = document.createElement("p");
					end.className = "fund__fine";
					end.textContent = i18n.end || "Это все материалы";
					more.insertAdjacentElement("afterend", end);
				}
			}).catch(function () {
				more.textContent = i18n.error || "Не удалось загрузить материалы";
				window.setTimeout(function () {
					more.textContent = previous;
				}, 1600);
				if (pager) {
					pager.hidden = false;
				}
			}).finally(function () {
				more.disabled = false;
				if (!more.hidden && more.textContent !== (i18n.error || "Не удалось загрузить материалы")) {
					more.textContent = previous;
				}
			});
		});
	}
}());
