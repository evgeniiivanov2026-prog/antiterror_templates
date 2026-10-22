( function () {
	var root = document.documentElement;
	root.classList.add( 'js' );

	var storageKey = 'rubezh-theme';
	var themeButton = document.querySelector( '[data-theme-toggle]' );

	function currentTheme() {
		return root.getAttribute( 'data-theme' ) === 'dark' ? 'dark' : 'light';
	}

	function applyTheme( theme ) {
		root.setAttribute( 'data-theme', theme );
		if ( themeButton ) {
			themeButton.setAttribute(
				'aria-label',
				theme === 'dark' ? 'Включить светлую тему' : 'Включить тёмную тему'
			);
		}
		var meta = document.querySelector( 'meta[name="theme-color"]' );
		if ( meta ) {
			meta.setAttribute( 'content', theme === 'dark' ? '#070b12' : '#101826' );
		}
	}

	applyTheme( currentTheme() );

	if ( themeButton ) {
		themeButton.addEventListener( 'click', function () {
			var next = currentTheme() === 'dark' ? 'light' : 'dark';
			applyTheme( next );
			try {
				localStorage.setItem( storageKey, next );
			} catch ( error ) {
				/* Preference stays for this view only. */
			}
		} );
	}

	var menuButton = document.querySelector( '.rz-burger' );
	var mobileMenu = document.getElementById( 'rz-mobile' );
	if ( menuButton && mobileMenu ) {
		menuButton.addEventListener( 'click', function () {
			var open = menuButton.getAttribute( 'aria-expanded' ) === 'true';
			menuButton.setAttribute( 'aria-expanded', String( ! open ) );
			menuButton.setAttribute( 'aria-label', open ? 'Открыть меню' : 'Закрыть меню' );
			mobileMenu.hidden = open;
		} );
		mobileMenu.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a' ) ) {
				menuButton.setAttribute( 'aria-expanded', 'false' );
				menuButton.setAttribute( 'aria-label', 'Открыть меню' );
				mobileMenu.hidden = true;
			}
		} );
	}

	var searchButton = document.querySelector( '[data-search-open]' );
	var searchPanel = document.getElementById( 'rz-search-panel' );
	if ( searchButton && searchPanel ) {
		searchButton.addEventListener( 'click', function () {
			var open = searchButton.getAttribute( 'aria-expanded' ) === 'true';
			searchButton.setAttribute( 'aria-expanded', String( ! open ) );
			searchPanel.hidden = open;
			if ( ! open ) {
				var field = searchPanel.querySelector( 'input' );
				if ( field ) {
					field.focus();
				}
			}
		} );
		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && searchButton.getAttribute( 'aria-expanded' ) === 'true' ) {
				searchButton.setAttribute( 'aria-expanded', 'false' );
				searchPanel.hidden = true;
				searchButton.focus();
			}
		} );
	}

	var progress = document.querySelector( '[data-reading-progress] span' );
	var progressWrap = document.querySelector( '[data-reading-progress]' );
	if ( progress && progressWrap ) {
		progressWrap.hidden = false;
		var article = document.querySelector( '.rz-prose' );
		var tick = function () {
			if ( ! article ) {
				return;
			}
			var rect = article.getBoundingClientRect();
			var total = article.offsetHeight - window.innerHeight;
			var seen = Math.min( Math.max( -rect.top, 0 ), Math.max( total, 1 ) );
			var ratio = total > 0 ? seen / total : 0;
			progress.style.transform = 'scaleX(' + ratio + ')';
		};
		tick();
		document.addEventListener( 'scroll', tick, { passive: true } );
		window.addEventListener( 'resize', tick );
	}

	document.addEventListener( 'submit', function ( event ) {
		var form = event.target.closest( '[data-vote-form]' );
		if ( ! form || ! window.rubezhData ) {
			return;
		}
		if ( ! window.fetch ) {
			return;
		}
		event.preventDefault();
		var button = form.querySelector( '[data-vote]' );
		if ( ! button || button.disabled ) {
			return;
		}
		var note = form.querySelector( '[data-vote-note]' );
		var id = Number( button.getAttribute( 'data-vote' ) );
		var goal = Number( button.getAttribute( 'data-goal' ) ) || 1;
		var previous = button.textContent;
		button.disabled = true;
		button.textContent = window.rubezhData.wait;
		fetch( window.rubezhData.rest, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.rubezhData.nonce
			},
			body: JSON.stringify( { id: id } )
		} )
			.then( function ( response ) {
				return response.json().then( function ( data ) {
					return { ok: response.ok, data: data };
				} );
			} )
			.then( function ( result ) {
				if ( ! result.ok ) {
					throw new Error( ( result.data && result.data.message ) || window.rubezhData.error );
				}
				var votes = Number( result.data.votes ) || 0;
				paintVote( form, votes, goal );
				button.textContent = window.rubezhData.voted;
				button.setAttribute( 'aria-pressed', 'true' );
				if ( note ) {
					note.textContent = result.data.already ? window.rubezhData.voted : '';
				}
			} )
			.catch( function ( error ) {
				button.disabled = false;
				button.textContent = previous;
				if ( note ) {
					note.textContent = error.message || window.rubezhData.error;
				}
			} );
	} );

	function paintVote( form, votes, goal ) {
		var card = form.closest( '.rz-ballot, .rz-case__vote' ) || form.parentNode;
		var meter = card.querySelector( '.rz-meter' );
		var bar = meter ? meter.querySelector( 'span' ) : null;
		var count = card.querySelector( '[data-vote-count]' );
		var empty = card.querySelector( '[data-vote-empty]' );
		var ratio = Math.max( 0, Math.min( 100, Math.round( ( votes / goal ) * 100 ) ) );
		if ( bar ) {
			bar.style.width = ratio + '%';
		}
		if ( meter ) {
			meter.setAttribute( 'aria-valuenow', String( votes ) );
		}
		if ( count ) {
			count.textContent = String( votes );
			var label = count.parentNode;
			if ( label ) {
				label.hidden = false;
			}
		}
		if ( empty ) {
			empty.hidden = votes > 0;
		}
	}
}() );
