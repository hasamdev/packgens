/**
 * Menu item image picker (Appearance > Menus).
 */
(function () {
	'use strict';

	var strings = window.packgensMenuFields || {};

	function fieldFor(el) {
		return el.closest('.packgens-menu-image');
	}

	document.addEventListener('click', function (event) {
		var select = event.target.closest('.packgens-menu-image__select');
		var clear = event.target.closest('.packgens-menu-image__clear');

		if (select) {
			event.preventDefault();
			openPicker(fieldFor(select));
			return;
		}

		if (clear) {
			event.preventDefault();
			var wrap = fieldFor(clear);
			wrap.querySelector('.packgens-menu-image__input').value = '';
			wrap.querySelector('.packgens-menu-image__preview').innerHTML = '';
			clear.hidden = true;
		}
	});

	function openPicker(wrap) {
		if (!wrap || !window.wp || !window.wp.media) {
			return;
		}

		var frame = window.wp.media({
			title: strings.title || 'Select image',
			button: { text: strings.button || 'Use this image' },
			library: { type: 'image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			var url = (attachment.sizes && attachment.sizes.thumbnail)
				? attachment.sizes.thumbnail.url
				: attachment.url;

			wrap.querySelector('.packgens-menu-image__input').value = attachment.id;
			wrap.querySelector('.packgens-menu-image__preview').innerHTML =
				'<img src="' + url + '" alt="" width="60" height="60">';

			var clear = wrap.querySelector('.packgens-menu-image__clear');
			if (clear) {
				clear.hidden = false;
			}
		});

		frame.open();
	}
})();
