/**
 * Single product interactions: gallery, lightbox, quantity tiers and reviews.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		gallery();
		quantityTiers();
		quantitySteppers();
		reviews();
	});

	/* ---------------------------------------------------------------- */

	/**
	 * Review list: search, rating filter, sort, numbered pages and the
	 * write-a-review toggle. The server prints every review, so with scripts
	 * off the whole list and the form simply stay visible.
	 */
	function reviews() {
		var root = PG.$('[data-pg-product-reviews]');

		if (!root) {
			return;
		}

		var list = PG.$('[data-pg-product-reviews-list]', root);
		var items = PG.$$('[data-pg-product-review]', root);
		var toolbar = PG.$('[data-pg-product-reviews-toolbar]', root);
		var search = PG.$('[data-pg-product-reviews-search]', root);
		var filter = PG.$('[data-pg-product-reviews-filter]', root);
		var sort = PG.$('[data-pg-product-reviews-sort]', root);
		var none = PG.$('[data-pg-product-reviews-none]', root);
		var foot = PG.$('[data-pg-product-reviews-foot]', root);
		var range = PG.$('[data-pg-product-reviews-range]', root);
		var pages = PG.$('[data-pg-product-reviews-pages]', root);
		var perPage = parseInt(root.dataset.perPage, 10) || 5;
		var page = 1;

		reviewForm(root);

		if (!list || !items.length) {
			return;
		}

		if (toolbar) {
			toolbar.hidden = false;
		}

		var format = function (template, values) {
			var i = 0;

			return template.replace(/%(\d+\$)?s/g, function (match, position) {
				var index = position ? parseInt(position, 10) - 1 : i++;

				return values[index];
			});
		};

		var render = function () {
			var query = search ? search.value.trim().toLowerCase() : '';
			var stars = filter ? filter.value : '';
			var order = sort ? sort.value : 'recent';

			var matches = items.filter(function (item) {
				return (!stars || item.dataset.rating === stars)
					&& (!query || item.textContent.toLowerCase().indexOf(query) !== -1);
			});

			matches.sort(function (a, b) {
				var byTime = b.dataset.time - a.dataset.time;

				if ('high' === order) {
					return (b.dataset.rating - a.dataset.rating) || byTime;
				}

				if ('low' === order) {
					return (a.dataset.rating - b.dataset.rating) || byTime;
				}

				return byTime;
			});

			var total = matches.length;
			var count = Math.max(1, Math.ceil(total / perPage));

			page = Math.min(page, count);

			var start = (page - 1) * perPage;
			var shown = matches.slice(start, start + perPage);

			items.forEach(function (item) {
				item.hidden = true;
			});

			// Reorder in place so the visible page reads in the chosen order.
			matches.forEach(function (item) {
				list.appendChild(item);
			});

			shown.forEach(function (item) {
				item.hidden = false;
			});

			if (none) {
				none.hidden = total > 0;
			}

			if (foot) {
				foot.hidden = total === 0;
			}

			if (range && total) {
				range.textContent = format(root.dataset.range || '%1$s–%2$s of %3$s', [start + 1, start + shown.length, total]);
			}

			if (pages) {
				pages.innerHTML = '';
				pages.hidden = count < 2;

				for (var n = 1; n <= count; n++) {
					var button = document.createElement('button');

					button.type = 'button';
					button.className = 'pg-product-reviews__page';
					button.textContent = n;
					button.dataset.page = n;
					button.setAttribute('aria-label', format(root.dataset.pageLabel || '%s', [n]));

					if (n === page) {
						button.setAttribute('aria-current', 'page');
					}

					pages.appendChild(button);
				}
			}
		};

		if (pages) {
			pages.addEventListener('click', function (event) {
				var button = event.target.closest('[data-page]');

				if (!button) {
					return;
				}

				page = parseInt(button.dataset.page, 10);
				render();

				var top = root.getBoundingClientRect().top;

				if (top < 0) {
					root.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		}

		var reset = function () {
			page = 1;
			render();
		};

		if (search) {
			search.addEventListener('input', reset);
		}

		[filter, sort].forEach(function (control) {
			if (control) {
				control.addEventListener('change', reset);
			}
		});

		// A link to one review (#comment-123) opens the page that holds it.
		var target = window.location.hash.match(/^#comment-\d+$/) && PG.$(window.location.hash, root);

		if (target && items.indexOf(target) !== -1) {
			page = Math.floor(items.indexOf(target) / perPage) + 1;
		}

		render();
	}

	/**
	 * Fold the review form behind its button once there are reviews to read,
	 * unless the visitor came for the form or is replying to a review.
	 */
	function reviewForm(root) {
		var toggle = PG.$('[data-pg-product-review-toggle]', root);
		var form = PG.$('[data-pg-product-review-form]', root);

		if (!toggle || !form) {
			return;
		}

		var wanted = /^#(respond|review_form|reviews)/.test(window.location.hash)
			|| PG.$('#comment_parent', form) && '0' !== PG.$('#comment_parent', form).value;

		var set = function (open) {
			form.hidden = !open;
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
		};

		toggle.hidden = false;
		set(wanted);

		toggle.addEventListener('click', function () {
			var open = form.hidden;

			set(open);

			if (open) {
				form.scrollIntoView({ behavior: 'smooth', block: 'start' });

				var first = PG.$('#rating, .stars a, #comment', form);

				if (first) {
					first.focus({ preventScroll: true });
				}
			}
		});
	}

	function gallery() {
		var root = PG.$('[data-pg-gallery]');

		if (!root) {
			return;
		}

		var stage = PG.$('[data-pg-gallery-stage]', root);
		var thumbs = PG.$$('[data-pg-gallery-thumb]', root);

		if (!stage || !thumbs.length) {
			return;
		}

		function show(index) {
			var thumb = thumbs[index];

			if (!thumb) {
				return;
			}

			var full = thumb.dataset.full;
			var image = PG.$('img', stage);

			if (image && full) {
				image.src = full;
				image.srcset = thumb.dataset.srcset || '';
				image.alt = thumb.dataset.alt || '';
			}

			thumbs.forEach(function (other, i) {
				other.classList.toggle('is-active', i === index);
				other.setAttribute('aria-current', i === index ? 'true' : 'false');
			});

			root.dataset.index = String(index);
		}

		thumbs.forEach(function (thumb, index) {
			thumb.addEventListener('click', function (event) {
				event.preventDefault();
				show(index);
			});
		});

		PG.$$('[data-pg-gallery-prev]', root).forEach(function (button) {
			button.addEventListener('click', function () {
				var current = parseInt(root.dataset.index || '0', 10);
				show((current - 1 + thumbs.length) % thumbs.length);
			});
		});

		PG.$$('[data-pg-gallery-next]', root).forEach(function (button) {
			button.addEventListener('click', function () {
				var current = parseInt(root.dataset.index || '0', 10);
				show((current + 1) % thumbs.length);
			});
		});

		lightbox(root, stage);
		show(0);
	}

	function lightbox(root, stage) {
		var trigger = PG.$('[data-pg-gallery-zoom]', root);

		if (!trigger) {
			return;
		}

		var dialog = null;
		var release = null;

		function close() {
			if (!dialog) {
				return;
			}

			if (release) {
				release();
				release = null;
			}

			dialog.remove();
			dialog = null;
			document.body.classList.remove('pg-no-scroll');
			trigger.focus();
		}

		trigger.addEventListener('click', function (event) {
			event.preventDefault();

			var image = PG.$('img', stage);

			if (!image) {
				return;
			}

			dialog = document.createElement('div');
			dialog.className = 'pg-lightbox';
			dialog.setAttribute('role', 'dialog');
			dialog.setAttribute('aria-modal', 'true');
			dialog.innerHTML =
				'<button type="button" class="pg-lightbox__close" aria-label="Close">&times;</button>' +
				'<img src="' + PG.escapeHtml(image.currentSrc || image.src) + '" alt="' + PG.escapeHtml(image.alt) + '">';

			document.body.appendChild(dialog);
			document.body.classList.add('pg-no-scroll');
			release = PG.trapFocus(dialog);
			PG.$('.pg-lightbox__close', dialog).focus();

			dialog.addEventListener('click', function (clickEvent) {
				if (clickEvent.target === dialog || clickEvent.target.closest('.pg-lightbox__close')) {
					close();
				}
			});
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') {
				close();
			}
		});
	}

	/* ---------------------------------------------------------------- */

	function quantityTiers() {
		var group = PG.$('[data-pg-tiers]');

		if (!group) {
			return;
		}

		var buttons = PG.$$('[data-pg-tier]', group);
		var quantityInput = document.querySelector('form.cart input.qty, form.cart input[name="quantity"]');
		var priceNode = PG.$('[data-pg-tier-price]');

		buttons.forEach(function (button) {
			button.addEventListener('click', function () {
				buttons.forEach(function (other) {
					other.classList.toggle('is-active', other === button);
					other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
				});

				if (quantityInput && button.dataset.quantity) {
					quantityInput.value = button.dataset.quantity;
					quantityInput.dispatchEvent(new Event('change', { bubbles: true }));
				}

				if (priceNode && button.dataset.price) {
					priceNode.textContent = button.dataset.price;
				}
			});
		});
	}

	/**
	 * Step the quantity field, honouring whatever min, max and step the product
	 * declares rather than assuming whole numbers from one.
	 */
	function quantitySteppers() {
		PG.$$('[data-pg-qty]').forEach(function (button) {
			button.addEventListener('click', function () {
				var field = button.parentNode.querySelector('input.qty, input[name="quantity"]');

				if (!field) {
					return;
				}

				var step = parseFloat(field.step) || 1;
				var min = field.min === '' ? 0 : parseFloat(field.min);
				var max = field.max === '' ? Infinity : parseFloat(field.max);
				var current = parseFloat(field.value);

				if (isNaN(current)) {
					current = min;
				}

				var next = current + step * parseFloat(button.dataset.pgQty);

				field.value = Math.min(max, Math.max(min, next));
				field.dispatchEvent(new Event('change', { bubbles: true }));
			});
		});
	}
})(window.Packgens);
