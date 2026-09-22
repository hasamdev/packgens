/**
 * Header behaviour: sticky bar, mega menu, mobile drawer, mobile search.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		stickyHeader();
		desktopMenu();
		mobileDrawer();
		mobileSearchToggle();
	});

	/* ---------------------------------------------------------------- */

	function stickyHeader() {
		var header = document.getElementById('pg-header');

		if (!header) {
			return;
		}

		// Reserve the header height so the hero can pad itself correctly.
		function measure() {
			if (header.classList.contains('is-stuck')) {
				return;
			}

			document.documentElement.style.setProperty('--pg-header-h', header.offsetHeight + 'px');
		}

		var threshold = 220;
		var ticking = false;

		function onScroll() {
			if (ticking) {
				return;
			}

			ticking = true;

			window.requestAnimationFrame(function () {
				var y = window.scrollY || window.pageYOffset;
				header.classList.toggle('is-stuck', y > threshold);
				ticking = false;
			});
		}

		measure();
		window.addEventListener('resize', PG.debounce(measure, 200));
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	/* ---------------------------------------------------------------- */

	function desktopMenu() {
		var items = PG.$$('.pg-nav__item--has-children');

		if (!items.length) {
			return;
		}

		function closeAll(except) {
			items.forEach(function (item) {
				if (item === except) {
					return;
				}

				item.classList.remove('is-open');
				var toggle = PG.$('.pg-nav__toggle', item);

				if (toggle) {
					toggle.setAttribute('aria-expanded', 'false');
				}
			});
		}

		items.forEach(function (item) {
			var toggle = PG.$('.pg-nav__toggle', item);
			var link = PG.$('.pg-nav__link', item);

			if (toggle) {
				// The toggle is only reachable by keyboard; pointer users hover.
				toggle.addEventListener('click', function (event) {
					event.preventDefault();
					var open = item.classList.toggle('is-open');
					toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
					closeAll(open ? item : null);
				});
			}

			if (link) {
				link.addEventListener('focus', function () {
					closeAll(item);
				});
			}

			item.addEventListener('mouseenter', function () {
				closeAll(item);
			});
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				closeAll(null);
			}
		});

		document.addEventListener('click', function (event) {
			if (!event.target.closest('.pg-nav__item--has-children')) {
				closeAll(null);
			}
		});
	}

	/* ---------------------------------------------------------------- */

	function mobileDrawer() {
		var drawer = document.getElementById('pg-drawer');
		var backdrop = PG.$('.pg-drawer-backdrop');

		if (!drawer || !backdrop) {
			return;
		}

		var releaseFocus = null;
		var lastFocused = null;

		function open() {
			lastFocused = document.activeElement;

			drawer.hidden = false;
			backdrop.hidden = false;

			// Next frame so the transition runs from the closed state.
			window.requestAnimationFrame(function () {
				drawer.classList.add('is-open');
				backdrop.classList.add('is-open');
			});

			document.body.classList.add('pg-no-scroll');
			releaseFocus = PG.trapFocus(drawer);

			PG.$$('[data-pg-drawer-open]').forEach(function (button) {
				button.setAttribute('aria-expanded', 'true');
			});

			var close = PG.$('.pg-drawer__close', drawer);

			if (close) {
				close.focus();
			}
		}

		function close() {
			drawer.classList.remove('is-open');
			backdrop.classList.remove('is-open');
			document.body.classList.remove('pg-no-scroll');

			if (releaseFocus) {
				releaseFocus();
				releaseFocus = null;
			}

			PG.$$('[data-pg-drawer-open]').forEach(function (button) {
				button.setAttribute('aria-expanded', 'false');
			});

			window.setTimeout(function () {
				drawer.hidden = true;
				backdrop.hidden = true;
			}, 280);

			if (lastFocused) {
				lastFocused.focus();
			}
		}

		document.addEventListener('click', function (event) {
			if (event.target.closest('[data-pg-drawer-open]')) {
				event.preventDefault();
				open();
				return;
			}

			if (event.target.closest('[data-pg-drawer-close]')) {
				event.preventDefault();
				close();
			}
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
				close();
			}
		});

		// Accordion inside the drawer.
		drawer.addEventListener('click', function (event) {
			var toggle = event.target.closest('.pg-mnav__toggle');

			if (!toggle) {
				return;
			}

			event.preventDefault();

			var item = toggle.closest('.pg-mnav__item');
			var open = item.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	/* ---------------------------------------------------------------- */

	function mobileSearchToggle() {
		var panel = document.getElementById('pg-mobile-search');

		if (!panel) {
			return;
		}

		document.addEventListener('click', function (event) {
			var toggle = event.target.closest('[data-pg-search-toggle]');

			if (!toggle) {
				return;
			}

			event.preventDefault();

			var open = panel.hidden;
			panel.hidden = !open;
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

			if (open) {
				var input = PG.$('input[type="search"]', panel);

				if (input) {
					input.focus();
				}
			}
		});
	}
})(window.Packgens);
