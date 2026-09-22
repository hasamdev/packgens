/**
 * Swiper carousels.
 *
 * Markup comes from packgens_carousel_open() in inc/sections.php. Until this
 * runs the track is a native scroll-snap row, so the content is usable with
 * touch, trackpad and keyboard before — and without — Swiper.
 *
 * Per-carousel options ride on data-pg-swiper as JSON:
 *   perView  map of min-width breakpoint to slides per view
 *
 * Tracks sharing data-pg-carousel-group scroll together and answer to one pair
 * of arrows, which is how the reviews section runs its two rows.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		var tracks = PG.$$('[data-pg-carousel]');

		if (!tracks.length || typeof window.Swiper !== 'function') {
			// No Swiper: the scroll-snap fallback stays in place.
			return;
		}

		// Within a group only the first row owns the arrows; the rest follow it
		// through the controller, so one click moves the group by one step.
		var leaders = {};

		tracks.forEach(function (track) {
			var key = track.dataset.pgCarouselGroup;

			if (key && !leaders[key]) {
				leaders[key] = track;
			}
		});

		var groups = {};

		tracks.forEach(function (track) {
			var key = track.dataset.pgCarouselGroup;
			var instance = build(track, !key || leaders[key] === track);

			if (!instance) {
				return;
			}

			if (key) {
				(groups[key] = groups[key] || []).push(instance);
			}
		});

		// Rows in a group can run in opposite directions, so mapping one
		// translate onto the other is meaningless. The arrows simply step every
		// member instead.
		Object.keys(groups).forEach(function (key) {
			var members = groups[key];

			if (members.length < 2) {
				return;
			}

			bindGroupNav(key, members);
		});
	});

	/**
	 * Step every row in a group from one pair of arrows.
	 *
	 * @param {string} key     Group id.
	 * @param {Array}  members Swiper instances.
	 * @return {void}
	 */
	function bindGroupNav(key, members) {
		[['prev', 'slidePrev'], ['next', 'slideNext']].forEach(function (pair) {
			PG.$$('[data-pg-carousel-' + pair[0] + '="' + key + '"]').forEach(function (button) {
				button.addEventListener('click', function () {
					members.forEach(function (swiper) {
						swiper[pair[1]]();
					});
				});
			});
		});
	}

	/**
	 * Read the gap the fallback grid is using, so spacing stays token-driven.
	 *
	 * @param {HTMLElement} track Carousel container.
	 * @return {number} Gap in pixels.
	 */
	/**
	 * Whether the visitor has asked for less motion.
	 *
	 * @return {boolean} True when reduced motion is preferred.
	 */
	function reduceMotion() {
		return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
	}

	function gapOf(track) {
		var gap = parseFloat(window.getComputedStyle(track).columnGap);

		return isNaN(gap) ? 24 : gap;
	}

	/**
	 * Expand { 0: 1.2, 768: 3 } into Swiper's breakpoints option.
	 *
	 * @param {Object} perView      Slides per view by min-width.
	 * @param {number} spaceBetween Gap in pixels.
	 * @return {Object} { base, breakpoints }
	 */
	function expand(perView, spaceBetween) {
		var breakpoints = {};
		var base = 1;

		Object.keys(perView)
			.map(Number)
			.sort(function (a, b) {
				return a - b;
			})
			.forEach(function (width) {
				var value = perView[width];

				if (width === 0) {
					base = value;

					return;
				}

				breakpoints[width] = { slidesPerView: value, spaceBetween: spaceBetween };
			});

		return { base: base, breakpoints: breakpoints };
	}

	function build(track, ownsNav) {
		var options = {};

		if (track.dataset.pgSwiper) {
			try {
				options = JSON.parse(track.dataset.pgSwiper) || {};
			} catch (error) {
				options = {};
			}
		}

		var spaceBetween = gapOf(track);
		var view = expand(options.perView || { 0: 1 }, spaceBetween);
		var i18n = PG.i18n || {};

		var config = {
			slidesPerView: view.base,
			breakpoints: view.breakpoints,
			spaceBetween: spaceBetween,
			watchOverflow: true,
			threshold: 5,
			grabCursor: true,
			slidesPerGroupAuto: true,
			keyboard: { enabled: true, onlyInViewport: true },
			a11y: {
				enabled: true,
				prevSlideMessage: i18n.prevSlide || 'Previous slide',
				nextSlideMessage: i18n.nextSlide || 'Next slide',
				firstSlideMessage: i18n.firstSlide || 'This is the first slide',
				lastSlideMessage: i18n.lastSlide || 'This is the last slide',
				slideLabelMessage: i18n.slideLabel || 'Slide {{index}} of {{slidesLength}}',
				containerRoleDescriptionMessage: i18n.carousel || 'carousel',
				itemRoleDescriptionMessage: i18n.slide || 'slide'
			},
			on: {
				afterInit: function (swiper) {
					// The gap now belongs to Swiper; drop the CSS one.
					swiper.el.style.columnGap = '0px';
				}
			}
		};

		if (options.loop) {
			config.loop = true;
		}

		if ('fade' === options.effect) {
			// Slides stack and cross-fade rather than sliding across.
			config.effect = 'fade';
			config.fadeEffect = { crossFade: true };
			config.loop = true;
			config.slidesPerView = 1;
			config.breakpoints = {};
			config.spaceBetween = 0;
			config.grabCursor = false;
			config.slidesPerGroupAuto = false;
		}

		if (options.autoplay && !reduceMotion()) {
			config.autoplay = {
				delay: options.autoplay,
				disableOnInteraction: false,
				pauseOnMouseEnter: true
			};
		}

		// Arrows address the group when there is one, otherwise the track.
		var target = track.dataset.pgCarouselGroup || track.id;

		// Grouped rows get their arrows wired by hand in bindGroupNav().
		if (target && ownsNav && !track.dataset.pgCarouselGroup) {
			var prev = PG.$$('[data-pg-carousel-prev="' + target + '"]');
			var next = PG.$$('[data-pg-carousel-next="' + target + '"]');

			if (prev.length && next.length) {
				config.navigation = { prevEl: prev, nextEl: next };
			}
		}

		try {
			return new window.Swiper(track, config);
		} catch (error) {
			if (window.console && window.console.warn) {
				window.console.warn('Packgens: carousel failed to initialise', error);
			}

			return null;
		}
	}
})(window.Packgens);
