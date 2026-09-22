/**
 * ARIA tabs used by the materials section and the product detail tabs.
 */
(function (PG) {
	'use strict';

	if (!PG) {
		return;
	}

	PG.ready(function () {
		PG.$$('[role="tablist"]').forEach(setup);
	});

	function setup(list) {
		var tabs = PG.$$('[role="tab"]', list);

		if (tabs.length < 2) {
			return;
		}

		function select(tab) {
			tabs.forEach(function (other) {
				var selected = other === tab;
				var panel = document.getElementById(other.getAttribute('aria-controls'));

				other.setAttribute('aria-selected', selected ? 'true' : 'false');
				other.setAttribute('tabindex', selected ? '0' : '-1');

				if (panel) {
					panel.hidden = !selected;
				}
			});
		}

		list.addEventListener('click', function (event) {
			var tab = event.target.closest('[role="tab"]');

			if (!tab || !list.contains(tab)) {
				return;
			}

			event.preventDefault();
			select(tab);
			tab.focus();
		});

		list.addEventListener('keydown', function (event) {
			var index = tabs.indexOf(document.activeElement);

			if (index === -1) {
				return;
			}

			var next = null;

			switch (event.key) {
				case 'ArrowRight':
					next = tabs[(index + 1) % tabs.length];
					break;
				case 'ArrowLeft':
					next = tabs[(index - 1 + tabs.length) % tabs.length];
					break;
				case 'Home':
					next = tabs[0];
					break;
				case 'End':
					next = tabs[tabs.length - 1];
					break;
				default:
					return;
			}

			event.preventDefault();
			select(next);
			next.focus();
		});
	}
})(window.Packgens);
