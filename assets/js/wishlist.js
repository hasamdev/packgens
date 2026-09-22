/**
 * Saved items.
 *
 * One delegated listener: every heart on the page posts a toggle and updates
 * itself, the header count, and — on the saved-items page — removes the card.
 */
(function () {
	'use strict';

	var data = window.packgensData || {};

	function setCount(count) {
		var badges = document.querySelectorAll('.pg-header__count--wishlist');

		Array.prototype.forEach.call(badges, function (badge) {
			badge.textContent = String(count);
			badge.classList.toggle('is-empty', count === 0);
		});
	}

	function setState(button, saved) {
		var label = button.querySelector('.pg-wishlist-btn__label');

		button.classList.toggle('is-saved', saved);
		button.setAttribute('aria-pressed', saved ? 'true' : 'false');

		if (label) {
			label.textContent = saved ? button.dataset.pgSavedLabel || 'Saved' : button.dataset.pgSaveLabel || 'Save';
		}
	}

	// The guest list lives in a cookie, so two toggles in flight at once would
	// both read the pre-click value and the second would undo the first.
	var queue = Promise.resolve();

	document.addEventListener('click', function (event) {
		var button = event.target.closest('[data-pg-wishlist]');

		if (!button || button.disabled) {
			return;
		}

		event.preventDefault();

		var body = new FormData();

		body.append('action', 'packgens_wishlist');
		body.append('nonce', data.nonce || '');
		body.append('product', button.getAttribute('data-pg-wishlist'));

		button.disabled = true;

		queue = queue
			.then(function () {
				return fetch(data.ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' });
			})
			.then(function (response) {
				return response.json();
			})
			.then(function (payload) {
				if (!payload || !payload.success) {
					return;
				}

				setState(button, payload.data.saved);
				setCount(payload.data.count);

				// On the saved-items page an unsave means the card is gone.
				var card = button.closest('[data-pg-wishlist-item]');

				if (card && !payload.data.saved) {
					card.remove();

					var grid = document.querySelector('[data-pg-wishlist-grid]');
					var empty = document.querySelector('[data-pg-wishlist-empty]');

					if (grid && !grid.children.length) {
						grid.hidden = true;

						if (empty) {
							empty.hidden = false;
						}
					}
				}
			})
			.catch(function () {})
			.then(function () {
				button.disabled = false;
			});
	});
})();
