/**
 * Accordions (FAQ).
 *
 * Markup works without JS: panels are open in CSS until this script marks the
 * group as enhanced, so no content is ever hidden from crawlers.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		PG.$$('[data-pg-accordion]').forEach(setupAccordion);
	});

	function setupAccordion(group) {
		group.addEventListener('click', function (event) {
			var trigger = event.target.closest('.pg-accordion__trigger');

			if (!trigger || !group.contains(trigger)) {
				return;
			}

			var item = trigger.closest('.pg-accordion__item');
			var willOpen = !item.classList.contains('is-open');

			// One panel at a time, matching the design.
			PG.$$('.pg-accordion__item', group).forEach(function (other) {
				if (other === item) {
					return;
				}

				other.classList.remove('is-open');
				var otherTrigger = PG.$('.pg-accordion__trigger', other);

				if (otherTrigger) {
					otherTrigger.setAttribute('aria-expanded', 'false');
					setIcon(otherTrigger, false);
				}
			});

			item.classList.toggle('is-open', willOpen);
			trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
			setIcon(trigger, willOpen);
		});

		group.addEventListener('keydown', function (event) {
			if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') {
				return;
			}

			var triggers = PG.$$('.pg-accordion__trigger', group);
			var index = triggers.indexOf(document.activeElement);

			if (index === -1) {
				return;
			}

			event.preventDefault();
			index += event.key === 'ArrowDown' ? 1 : -1;

			if (index < 0) {
				index = triggers.length - 1;
			}

			if (index >= triggers.length) {
				index = 0;
			}

			triggers[index].focus();
		});
	}

	/**
	 * Swap the plus / minus glyph without re-rendering the whole button.
	 */
	function setIcon(trigger, isOpen) {
		var icon = PG.$('.pg-accordion__icon svg', trigger);

		if (!icon) {
			return;
		}

		icon.innerHTML = isOpen
			? '<path d="M5 12h14"/>'
			: '<path d="M12 5v14"/><path d="M5 12h14"/>';
	}
})(window.Packgens);
