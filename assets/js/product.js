/**
 * Single product interactions: gallery, lightbox and quantity tiers.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		gallery();
		quantityTiers();
		copyCodes();
		quantitySteppers();
	});

	/* ---------------------------------------------------------------- */

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
	 * Copy a discount code to the clipboard, confirming in the button itself.
	 */
	function copyCodes() {
		PG.$$('[data-pg-copy]').forEach(function (button) {
			button.addEventListener('click', function () {
				var code = button.dataset.pgCopy;

				if (!code || !navigator.clipboard) {
					return;
				}

				navigator.clipboard.writeText(code).then(function () {
					button.classList.add('is-copied');
					window.setTimeout(function () {
						button.classList.remove('is-copied');
					}, 1600);
				});
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
