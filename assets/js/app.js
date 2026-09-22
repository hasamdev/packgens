/**
 * Packgens shared runtime.
 *
 * Exposes a very small namespace the other modules build on. No globals beyond
 * window.Packgens, no dependencies.
 */
window.Packgens = (function () {
	'use strict';

	var data = window.packgensData || {};
	var i18n = data.i18n || {};

	/**
	 * Query helpers.
	 */
	function $(selector, scope) {
		return (scope || document).querySelector(selector);
	}

	function $$(selector, scope) {
		return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
	}

	/**
	 * Run a callback once the DOM is parsed.
	 */
	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn, { once: true });
		} else {
			fn();
		}
	}

	/**
	 * Trailing debounce.
	 */
	function debounce(fn, wait) {
		var timer = null;

		return function () {
			var context = this;
			var args = arguments;

			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(context, args);
			}, wait || 200);
		};
	}

	/**
	 * POST to admin-ajax with the public nonce attached.
	 *
	 * @param {string} action  admin-ajax action name.
	 * @param {Object} payload Key/value pairs.
	 * @param {Object} options { signal } for aborting.
	 * @returns {Promise<Object>} Parsed response data.
	 */
	function post(action, payload, options) {
		var body = new FormData();

		body.append('action', action);
		body.append('nonce', data.nonce || '');

		Object.keys(payload || {}).forEach(function (key) {
			body.append(key, payload[key]);
		});

		return request(body, options);
	}

	/**
	 * POST a prepared FormData (used by the form module for file uploads).
	 */
	function postForm(formData, options) {
		return request(formData, options);
	}

	function request(body, options) {
		options = options || {};

		return fetch(data.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body,
			signal: options.signal
		})
			.then(function (response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}

				return response.json();
			})
			.then(function (json) {
				if (!json || typeof json.success === 'undefined') {
					throw new Error('Malformed response');
				}

				if (!json.success) {
					var error = new Error((json.data && json.data.message) || i18n.genericError);
					error.payload = json.data;
					throw error;
				}

				return json.data;
			});
	}

	/**
	 * Trap focus inside a container while it is open.
	 */
	function trapFocus(container) {
		var selector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

		function onKeydown(event) {
			if (event.key !== 'Tab') {
				return;
			}

			var items = $$(selector, container).filter(function (el) {
				return el.offsetParent !== null;
			});

			if (!items.length) {
				return;
			}

			var first = items[0];
			var last = items[items.length - 1];

			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		}

		container.addEventListener('keydown', onKeydown);

		return function release() {
			container.removeEventListener('keydown', onKeydown);
		};
	}

	/**
	 * Escape a string for safe insertion as text.
	 */
	function escapeHtml(value) {
		var div = document.createElement('div');
		div.textContent = value == null ? '' : String(value);

		return div.innerHTML;
	}

	return {
		$: $,
		$$: $$,
		ready: ready,
		debounce: debounce,
		post: post,
		postForm: postForm,
		trapFocus: trapFocus,
		escapeHtml: escapeHtml,
		data: data,
		i18n: i18n
	};
})();
