/**
 * Trusted brands row: a carousel only when it has to be.
 *
 * The logos lay out as a wrapping row. When that row breaks onto a second line
 * (a narrow screen, or more logos than fit) it becomes a Swiper carousel; when
 * it fits again it goes back to the plain row. Without Swiper on the page the
 * row falls back to a native horizontal scroller.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		PG.$$('[data-pg-brands]').forEach(setup);
	});

	/**
	 * Whether the plain row has broken onto more than one line.
	 *
	 * @param {HTMLElement} list The logo list.
	 * @return {boolean} True when the last logo sits on a line below the first.
	 */
	function wraps(list) {
		var items = list.children;

		if (items.length < 2) {
			return false;
		}

		var first = items[0].getBoundingClientRect();
		var last = items[items.length - 1].getBoundingClientRect();

		// Fully below, not merely lower: logos of different heights are
		// centred on the line, so their tops differ without any wrapping.
		return last.top >= first.bottom - 1;
	}

	function reduceMotion() {
		return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
	}

	function setup(el) {
		var list = PG.$('.pg-brands__list', el);
		var swiper = null;
		var width = window.innerWidth;

		if (!list) {
			return;
		}

		function disable() {
			if (swiper) {
				// Clean styles, so the plain row is measured without Swiper's
				// inline widths and margins.
				swiper.destroy(true, true);
				swiper = null;
			}

			el.classList.remove('is-scroller');
		}

		function enable() {
			// The row's own gap, read while it is still a plain row, so the
			// carousel spacing stays token-driven.
			var gap = parseFloat(window.getComputedStyle(list).columnGap) || 24;
			var i18n = PG.i18n || {};

			if (typeof window.Swiper !== 'function') {
				el.classList.add('is-scroller');

				return;
			}

			var config = {
				slidesPerView: 'auto',
				spaceBetween: gap,
				rewind: true,
				grabCursor: true,
				threshold: 5,
				a11y: {
					enabled: true,
					containerRoleDescriptionMessage: i18n.carousel || 'carousel',
					itemRoleDescriptionMessage: i18n.slide || 'slide',
					slideLabelMessage: i18n.slideLabel || 'Slide {{index}} of {{slidesLength}}'
				}
			};

			if (!reduceMotion()) {
				config.autoplay = {
					delay: 2500,
					disableOnInteraction: false,
					pauseOnMouseEnter: true
				};
			}

			try {
				swiper = new window.Swiper(el, config);
			} catch (error) {
				swiper = null;
				el.classList.add('is-scroller');
			}
		}

		function update() {
			disable();

			if (wraps(list)) {
				enable();
			}
		}

		update();

		// The logos are lazy-loaded and measure 0px wide until they arrive,
		// which is after the page's own load event, so each one re-checks the
		// row as it lands.
		var settle = PG.debounce(update, 100);

		PG.$$('img', list).forEach(function (img) {
			if (!img.complete) {
				img.addEventListener('load', settle);
			}
		});

		// Width only: mobile browsers fire resize as the address bar hides.
		window.addEventListener(
			'resize',
			PG.debounce(function () {
				if (window.innerWidth === width) {
					return;
				}

				width = window.innerWidth;
				update();
			}, 200)
		);
	}
})(window.Packgens);
