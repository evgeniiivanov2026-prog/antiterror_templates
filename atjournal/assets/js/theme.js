(function () {
	var root = document.documentElement;
	var labels = (window.atjTheme && atjTheme.labels) || {};

	function prefersReduced() {
		return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	}

	function applyTheme(theme, persist) {
		var change = function () {
			root.setAttribute('data-theme', theme);
			if (persist) {
				try { localStorage.setItem('atj-theme', theme); } catch (error) {}
			}
		};
		if (persist && document.startViewTransition && !prefersReduced()) {
			document.startViewTransition(change);
		} else {
			change();
		}
		var toggle = document.querySelector('[data-theme-toggle]');
		if (toggle) {
			toggle.setAttribute('aria-label', theme === 'dark' ? (labels.themeLight || 'Включить светлую тему') : (labels.themeDark || 'Включить тёмную тему'));
		}
	}

	var themeToggle = document.querySelector('[data-theme-toggle]');
	if (themeToggle) {
		themeToggle.addEventListener('click', function () {
			applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark', true);
		});
	}

	var menuButton = document.querySelector('[data-menu-toggle]');
	var drawer = document.getElementById('atj-drawer');
	function closeMenu() {
		if (!menuButton || !drawer) return;
		menuButton.setAttribute('aria-expanded', 'false');
		var hidden = menuButton.querySelector('.screen-reader-text');
		if (hidden) hidden.textContent = labels.menuOpen || 'Открыть меню';
		drawer.setAttribute('hidden', '');
	}
	if (menuButton && drawer) {
		menuButton.addEventListener('click', function () {
			var open = menuButton.getAttribute('aria-expanded') === 'true';
			menuButton.setAttribute('aria-expanded', open ? 'false' : 'true');
			var hidden = menuButton.querySelector('.screen-reader-text');
			if (hidden) hidden.textContent = open ? (labels.menuOpen || 'Открыть меню') : (labels.menuClose || 'Закрыть меню');
			if (open) drawer.setAttribute('hidden', '');
			else drawer.removeAttribute('hidden');
		});
		drawer.addEventListener('click', function (event) {
			if (event.target.closest('a')) closeMenu();
		});
	}

	var searchLayer = document.getElementById('atj-search-layer');
	var searchOpen = document.querySelector('[data-search-open]');
	var searchField = document.getElementById('atj-search-field');
	function closeSearch() {
		if (!searchLayer) return;
		searchLayer.setAttribute('hidden', '');
		document.body.classList.remove('atj-lock');
		if (searchOpen) {
			searchOpen.setAttribute('aria-expanded', 'false');
			searchOpen.focus();
		}
	}
	if (searchLayer && searchOpen) {
		searchOpen.addEventListener('click', function () {
			searchLayer.removeAttribute('hidden');
			searchOpen.setAttribute('aria-expanded', 'true');
			document.body.classList.add('atj-lock');
			if (searchField) searchField.focus();
		});
		searchLayer.addEventListener('click', function (event) {
			if (event.target.closest('[data-search-close]')) closeSearch();
		});
	}

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeSearch();
			closeMenu();
		}
		if (event.key === '/' && searchOpen && document.activeElement && !/^(INPUT|TEXTAREA|SELECT)$/.test(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
			event.preventDefault();
			searchOpen.click();
		}
	});

	document.querySelectorAll('.atj-railwrap').forEach(function (wrap) {
		var rail = wrap.querySelector('.atj-rail');
		if (!rail) return;
		wrap.querySelectorAll('[data-rail]').forEach(function (button) {
			button.addEventListener('click', function () {
				var amount = rail.clientWidth * 0.8;
				rail.scrollBy({ left: button.getAttribute('data-rail') === 'next' ? amount : -amount, behavior: prefersReduced() ? 'auto' : 'smooth' });
			});
		});
	});

	var progress = document.querySelector('.atj-progress span');
	if (progress && !(window.CSS && CSS.supports('animation-timeline: scroll()'))) {
		var paint = function () {
			var height = document.documentElement.scrollHeight - window.innerHeight;
			var ratio = height > 0 ? window.scrollY / height : 0;
			progress.style.transform = 'scaleX(' + Math.max(0, Math.min(1, ratio)) + ')';
		};
		paint();
		window.addEventListener('scroll', paint, { passive: true });
	}

	function formatCount(value) {
		try {
			return new Intl.NumberFormat(document.documentElement.lang || 'ru').format(value);
		} catch (error) {
			return String(value);
		}
	}

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-vote] .atj-vote__button');
		if (!button || button.disabled || !window.atjTheme) return;
		var vote = button.closest('[data-vote]');
		if (!vote || vote.classList.contains('is-busy') || vote.classList.contains('is-closed')) return;

		var error = vote.querySelector('[data-vote-error]');
		var countNode = vote.querySelector('[data-vote-count]');
		var wordNode = vote.querySelector('[data-vote-word]');
		var label = button.querySelector('.atj-vote__label');
		var previous = {
			pressed: button.getAttribute('aria-pressed'),
			count: countNode ? countNode.textContent : '',
			word: wordNode ? wordNode.textContent : '',
			label: label ? label.textContent : ''
		};

		vote.classList.add('is-busy');
		button.setAttribute('aria-busy', 'true');
		if (error) {
			error.hidden = true;
			error.textContent = '';
		}

		var body = new URLSearchParams();
		body.set('action', 'atj_vote');
		body.set('nonce', atjTheme.nonce);
		body.set('post_id', vote.getAttribute('data-post') || '');

		fetch(atjTheme.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString()
		}).then(function (response) {
			return response.json().then(function (payload) {
				return { ok: response.ok, payload: payload };
			});
		}).then(function (result) {
			var data = result.payload && (result.payload.data || {});
			if (!result.payload || !result.payload.success) {
				throw new Error((data && data.message) || labels.error || 'Не удалось сохранить голос.');
			}
			var voted = !!data.voted;
			vote.classList.toggle('is-voted', voted);
			button.setAttribute('aria-pressed', voted ? 'true' : 'false');
			if (label) label.textContent = voted ? (labels.voted || 'Голос учтён') : (labels.support || 'Поддержать');
			if (countNode) countNode.textContent = formatCount(data.count);
			if (wordNode && data.word) wordNode.textContent = data.word;
			document.querySelectorAll('[data-vote][data-post="' + vote.getAttribute('data-post') + '"]').forEach(function (other) {
				if (other === vote) return;
				other.classList.toggle('is-voted', voted);
				var otherButton = other.querySelector('.atj-vote__button');
				var otherLabel = other.querySelector('.atj-vote__label');
				var otherCount = other.querySelector('[data-vote-count]');
				var otherWord = other.querySelector('[data-vote-word]');
				if (otherButton) otherButton.setAttribute('aria-pressed', voted ? 'true' : 'false');
				if (otherLabel) otherLabel.textContent = voted ? (labels.voted || 'Голос учтён') : (labels.support || 'Поддержать');
				if (otherCount) otherCount.textContent = formatCount(data.count);
				if (otherWord && data.word) otherWord.textContent = data.word;
			});
		}).catch(function (reason) {
			button.setAttribute('aria-pressed', previous.pressed);
			if (countNode) countNode.textContent = previous.count;
			if (wordNode) wordNode.textContent = previous.word;
			if (label) label.textContent = previous.label;
			if (error) {
				error.hidden = false;
				error.textContent = reason && reason.message ? reason.message : (labels.error || 'Не удалось сохранить голос.');
			}
		}).finally(function () {
			vote.classList.remove('is-busy');
			button.removeAttribute('aria-busy');
		});
	});
}());
