/**
 * Drifting rows.
 *
 * The motion itself is a CSS animation on a duplicated track, so it runs on the
 * compositor with no seam and nothing to restart between cards. This script
 * only does the two things CSS cannot: set a duration that gives every row the
 * same speed whatever its content, and nudge a row when an arrow is pressed.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	// Pixels per second. One rate for every row, in both directions.
	var SPEED = 42;

	PG.ready(function () {
		var rows = PG.$$('[data-pg-marquee]');

		if (!rows.length) {
			return;
		}

		rows.forEach(setup);

		// One pair of arrows can drive several rows, as the reviews section does.
		var groups = {};

		rows.forEach(function (row) {
			var key = row.dataset.pgCarouselGroup || row.id;

			(groups[key] = groups[key] || []).push(row);
		});

		Object.keys(groups).forEach(function (key) {
			bindNav(key, groups[key]);
		});
	});

	function setup(row) {
		var track = row.querySelector('.pg-marquee__track');
		var clone = row.querySelector('.pg-marquee__clone');

		if (!track) {
			return;
		}

		// The duplicate is decoration; keep it off the keyboard path.
		if (clone) {
			clone.querySelectorAll('a, button, input, select, textarea').forEach(function (el) {
				el.setAttribute('tabindex', '-1');
			});
		}

		measure(row, track);

		// Card widths shift at breakpoints, so the duration is recalculated.
		// The animation is a transform and changes no layout box, so reading the
		// width back here cannot feed into itself.
		window.addEventListener('resize', PG.debounce(function () {
			measure(row, track);
		}, 200));
	}

	function measure(row, track) {
		var half = track.scrollWidth / 2;

		if (!half) {
			return;
		}

		track.style.animationDuration = (half / SPEED).toFixed(2) + 's';
		row.pgHalf = half;
	}

	function bindNav(key, rows) {
		var pairs = [['prev', -1], ['next', 1]];

		pairs.forEach(function (pair) {
			PG.$$('[data-pg-carousel-' + pair[0] + '="' + key + '"]').forEach(function (button) {
				button.addEventListener('click', function () {
					rows.forEach(function (row) {
						nudge(row, pair[1]);
					});
				});
			});
		});
	}

	/**
	 * Shift a row by one card, on a layer of its own so the drift underneath
	 * keeps running.
	 *
	 * @param {HTMLElement} row       Marquee viewport.
	 * @param {number}      direction -1 for previous, 1 for next.
	 * @return {void}
	 */
	function nudge(row, direction) {
		var shift = row.querySelector('.pg-marquee__shift');
		var slide = row.querySelector('.pg-marquee__track > *');

		if (!shift || !slide) {
			return;
		}

		var styles = window.getComputedStyle(row.querySelector('.pg-marquee__track'));
		var gap = parseFloat(styles.columnGap || styles.gap || '0') || 0;
		var step = slide.getBoundingClientRect().width + gap;

		row.pgShift = (row.pgShift || 0) - direction * step;
		shift.style.transform = 'translateX(' + row.pgShift + 'px)';

		// The track repeats every half its width, so winding the offset back by
		// that much lands on an identical frame. Done without a transition, it
		// is invisible, and it stops the offset growing without bound.
		var half = row.pgHalf || 0;

		if (half && Math.abs(row.pgShift) >= half) {
			window.setTimeout(function () {
				shift.style.transition = 'none';
				row.pgShift += row.pgShift > 0 ? -half : half;
				shift.style.transform = 'translateX(' + row.pgShift + 'px)';

				// Next frame, put the transition back for the following press.
				window.requestAnimationFrame(function () {
					window.requestAnimationFrame(function () {
						shift.style.transition = '';
					});
				});
			}, 600);
		}
	}
})(window.Packgens);
