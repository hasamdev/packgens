/**
 * Load-more behaviour for the blog, catalogue and reviews archives.
 *
 * The button is rendered by PHP only when there are more pages, and it wraps a
 * real link to page 2, so pagination still works without JavaScript.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		PG.$$('[data-pg-load-more]').forEach(setup);
	});

	function setup(button) {
		var targetId = button.dataset.target;
		var container = targetId ? document.getElementById(targetId) : null;

		if (!container) {
			return;
		}

		var page = parseInt(button.dataset.page || '1', 10);
		var maxPages = parseInt(button.dataset.maxPages || '1', 10);
		var busy = false;

		button.addEventListener('click', function (event) {
			event.preventDefault();

			if (busy || page >= maxPages) {
				return;
			}

			busy = true;
			button.classList.add('is-loading');
			button.disabled = true;

			var label = PG.$('.pg-btn__label', button) || button;
			var original = label.textContent;
			label.textContent = PG.i18n.loading;

			PG.post('packgens_load_more', {
				page: page + 1,
				type: button.dataset.type || 'post',
				query: button.dataset.query || ''
			})
				.then(function (data) {
					if (data.html) {
						container.insertAdjacentHTML('beforeend', data.html);
					}

					page = data.page || page + 1;
					maxPages = data.maxPages || maxPages;
					button.dataset.page = String(page);

					if (page >= maxPages) {
						button.remove();
						return;
					}

					label.textContent = original;
				})
				.catch(function () {
					label.textContent = PG.i18n.genericError;
				})
				.finally(function () {
					busy = false;
					button.classList.remove('is-loading');
					button.disabled = false;
				});
		});
	}
})(window.Packgens);
