/**
 * Predictive header search.
 *
 * Progressive enhancement: the form still submits normally without JS.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	var MIN_CHARS = 3;

	PG.ready(function () {
		PG.$$('[data-pg-search]').forEach(setup);
	});

	function setup(form) {
		var input = PG.$('input[type="search"]', form);
		var panel = PG.$('.pg-search__panel', form);

		if (!input || !panel) {
			return;
		}

		var controller = null;
		var activeIndex = -1;

		function closePanel() {
			panel.hidden = true;
			panel.innerHTML = '';
			input.setAttribute('aria-expanded', 'false');
			activeIndex = -1;
		}

		function renderStatus(message) {
			panel.innerHTML = '<p class="pg-search__status">' + PG.escapeHtml(message) + '</p>';
			panel.hidden = false;
			input.setAttribute('aria-expanded', 'true');
		}

		function renderResults(payload) {
			var results = (payload && payload.results) || [];

			if (!results.length) {
				renderStatus(PG.i18n.noResults);
				return;
			}

			var html = results
				.map(function (item) {
					return (
						'<a class="pg-search__result" href="' + PG.escapeHtml(item.url) + '" role="option">' +
						(item.image ? '<img src="' + PG.escapeHtml(item.image) + '" alt="" width="44" height="44" loading="lazy">' : '') +
						'<span>' +
						'<span class="pg-search__result-title">' + PG.escapeHtml(item.title) + '</span>' +
						(item.meta ? '<span class="pg-search__result-meta">' + PG.escapeHtml(item.meta) + '</span>' : '') +
						'</span></a>'
					);
				})
				.join('');

			if (payload.allUrl) {
				html +=
					'<a class="pg-search__all" href="' + PG.escapeHtml(payload.allUrl) + '">' +
					PG.escapeHtml(payload.allLabel || 'View all results') +
					'</a>';
			}

			panel.innerHTML = html;
			panel.hidden = false;
			input.setAttribute('aria-expanded', 'true');
			activeIndex = -1;
		}

		var search = PG.debounce(function () {
			var term = input.value.trim();

			if (term.length < MIN_CHARS) {
				closePanel();
				return;
			}

			if (controller) {
				controller.abort();
			}

			controller = new AbortController();

			renderStatus(PG.i18n.loading);

			PG.post(
				'packgens_search',
				{ term: term, post_type: form.dataset.postType || 'product' },
				{ signal: controller.signal }
			)
				.then(renderResults)
				.catch(function (error) {
					if (error.name === 'AbortError') {
						return;
					}

					renderStatus(PG.i18n.genericError);
				});
		}, 260);

		input.addEventListener('input', search);

		input.addEventListener('focus', function () {
			if (input.value.trim().length >= MIN_CHARS && panel.innerHTML) {
				panel.hidden = false;
			}
		});

		input.addEventListener('keydown', function (event) {
			var options = PG.$$('.pg-search__result', panel);

			if (event.key === 'Escape') {
				closePanel();
				return;
			}

			if (!options.length) {
				return;
			}

			if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
				event.preventDefault();
				activeIndex += event.key === 'ArrowDown' ? 1 : -1;

				if (activeIndex < 0) {
					activeIndex = options.length - 1;
				}

				if (activeIndex >= options.length) {
					activeIndex = 0;
				}

				options.forEach(function (option, index) {
					option.classList.toggle('is-active', index === activeIndex);
				});

				options[activeIndex].scrollIntoView({ block: 'nearest' });
				return;
			}

			if (event.key === 'Enter' && activeIndex > -1) {
				event.preventDefault();
				options[activeIndex].click();
			}
		});

		document.addEventListener('click', function (event) {
			if (!form.contains(event.target)) {
				closePanel();
			}
		});
	}
})(window.Packgens);
