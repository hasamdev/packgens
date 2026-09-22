/**
 * AJAX submission for the theme's native forms.
 *
 * Without JavaScript the same forms post to admin-post.php and redirect, so
 * this module only upgrades the experience.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		PG.$$('[data-pg-form]').forEach(setup);
		PG.$$('[data-pg-file]').forEach(showFileName);
		reportRedirectStatus();
	});

	function setup(form) {
		var message = PG.$('[data-pg-form-message]', form);
		var submit = form.querySelector('[type="submit"]');
		var busy = false;

		form.addEventListener('submit', function (event) {
			if (busy) {
				event.preventDefault();
				return;
			}

			// Let the browser show its own validation UI first.
			if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
				return;
			}

			event.preventDefault();
			clearErrors(form);

			busy = true;
			setBusy(submit, true);
			setMessage(message, PG.i18n.sending, '');

			var payload = new FormData(form);
			payload.set('action', 'packgens_submit_form');

			PG.postForm(payload)
				.then(function (data) {
					form.reset();
					PG.$$('[data-pg-file-name]', form).forEach(function (node) {
						node.textContent = '';
					});

					setMessage(message, data.message, 'is-success');

					if (data.redirect) {
						window.setTimeout(function () {
							window.location.href = data.redirect;
						}, 900);
					}
				})
				.catch(function (error) {
					var payloadErrors = (error.payload && error.payload.errors) || {};

					applyErrors(form, payloadErrors);
					setMessage(message, error.message || PG.i18n.genericError, 'is-error');

					var firstKey = Object.keys(payloadErrors)[0];

					if (firstKey) {
						var field = form.querySelector('[name="' + firstKey + '"]');

						if (field) {
							field.focus();
						}
					}
				})
				.finally(function () {
					busy = false;
					setBusy(submit, false);
				});
		});
	}

	function setBusy(button, isBusy) {
		if (!button) {
			return;
		}

		button.disabled = isBusy;
		button.classList.toggle('is-loading', isBusy);
	}

	function setMessage(node, text, state) {
		if (!node) {
			return;
		}

		node.textContent = text || '';
		node.className = 'pg-form__message' + (text ? ' is-visible' : '') + (state ? ' ' + state : '');
	}

	function clearErrors(form) {
		PG.$$('.pg-field.has-error', form).forEach(function (field) {
			field.classList.remove('has-error');
			var note = PG.$('.pg-field__error', field);

			if (note) {
				note.remove();
			}
		});

		PG.$$('[aria-invalid="true"]', form).forEach(function (input) {
			input.removeAttribute('aria-invalid');
		});
	}

	function applyErrors(form, errors) {
		Object.keys(errors || {}).forEach(function (key) {
			var input = form.querySelector('[name="' + key + '"]');

			if (!input) {
				return;
			}

			input.setAttribute('aria-invalid', 'true');

			var field = input.closest('.pg-field');

			if (!field) {
				return;
			}

			field.classList.add('has-error');

			var note = document.createElement('span');
			note.className = 'pg-field__error';
			note.textContent = errors[key];
			field.appendChild(note);
		});
	}

	function showFileName(input) {
		var label = input.closest('.pg-file');
		var target = label ? PG.$('[data-pg-file-name]', label) : null;

		if (!target) {
			return;
		}

		input.addEventListener('change', function () {
			target.textContent = input.files && input.files.length ? input.files[0].name : '';
		});
	}

	/**
	 * Surface the result of a no-JS submission after the redirect.
	 */
	function reportRedirectStatus() {
		var params = new URLSearchParams(window.location.search);
		var status = params.get('pg_form_status');

		if (!status) {
			return;
		}

		var node = PG.$('[data-pg-form-message]');

		if (!node) {
			return;
		}

		setMessage(
			node,
			status === 'success'
				? node.dataset.successText || 'Thank you. Your request has been sent.'
				: PG.i18n.genericError,
			status === 'success' ? 'is-success' : 'is-error'
		);
	}
})(window.Packgens);
