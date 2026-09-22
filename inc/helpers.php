<?php
/**
 * Small, dependency-free helpers used across templates.
 *
 * Every custom-field read in this theme goes through packgens_field() so the
 * templates keep working when Secure Custom Fields is deactivated.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a custom fields plugin exposing the ACF API is active.
 *
 * @return bool
 */
function packgens_has_fields() {
	return function_exists( 'get_field' );
}

/**
 * Read a custom field with a safe fallback.
 *
 * @param string     $selector Field name.
 * @param int|string $post_id  Post ID or term/option identifier.
 * @param mixed      $default  Value returned when the field is missing.
 * @return mixed
 */
function packgens_field( $selector, $post_id = false, $default = null ) {
	if ( ! packgens_has_fields() ) {
		return $default;
	}

	$value = get_field( $selector, $post_id );

	return ( null === $value || '' === $value || false === $value ) ? $default : $value;
}

/**
 * Read a sub-key from a grouped field.
 *
 * @param string     $group   Group field name.
 * @param string     $key     Key inside the group.
 * @param int|string $post_id Post ID or term/option identifier.
 * @param mixed      $default Fallback value.
 * @return mixed
 */
function packgens_group_field( $group, $key, $post_id = false, $default = null ) {
	$data = packgens_field( $group, $post_id, array() );

	if ( ! is_array( $data ) || ! array_key_exists( $key, $data ) ) {
		return $default;
	}

	$value = $data[ $key ];

	return ( null === $value || '' === $value ) ? $default : $value;
}

/**
 * A piece of interface text the client can override from the admin.
 *
 * Every visible label in the theme reads through here. The English string stays
 * in the template as the argument, so an empty field -- or SCF being switched
 * off entirely -- still renders the page rather than a blank space.
 *
 * @param string $group   Label group without the `labels_` prefix, e.g. "forms".
 * @param string $key     Field name inside that group.
 * @param string $default Text to use when the field is empty.
 * @return string
 */
function packgens_label( $group, $key, $default = '' ) {
	return (string) packgens_group_field( 'labels_' . $group, $key, 'option', $default );
}

/**
 * Echo a label, escaped for HTML.
 *
 * @param string $group   Label group.
 * @param string $key     Field name.
 * @param string $default Fallback text.
 * @return void
 */
function packgens_the_label( $group, $key, $default = '' ) {
	echo esc_html( packgens_label( $group, $key, $default ) );
}

/**
 * Whether a section group exists and its enable toggle is on.
 *
 * Sections default to enabled when the toggle has never been saved, so a fresh
 * install still renders something instead of a blank page.
 *
 * @param string     $group      Section group name, e.g. "faqs_section".
 * @param string     $enable_key Toggle key inside the group.
 * @param int|string $post_id    Post ID or term/option identifier.
 * @return bool
 */
function packgens_section_enabled( $group, $enable_key, $post_id = false ) {
	$data = packgens_field( $group, $post_id, null );

	if ( ! is_array( $data ) ) {
		return false;
	}

	if ( ! array_key_exists( $enable_key, $data ) ) {
		return true;
	}

	return (bool) $data[ $enable_key ];
}

/**
 * Normalise an ACF-style link value.
 *
 * Accepts the array form ( url / title / target ) or a bare URL string.
 *
 * @param mixed  $link       Raw field value.
 * @param string $default_label Label used when the field has none.
 * @return array{url:string,title:string,target:string}|null
 */
function packgens_link( $link, $default_label = '' ) {
	if ( empty( $link ) ) {
		return null;
	}

	if ( is_string( $link ) ) {
		$link = array( 'url' => $link );
	}

	if ( ! is_array( $link ) || empty( $link['url'] ) ) {
		return null;
	}

	$title = isset( $link['title'] ) ? trim( (string) $link['title'] ) : '';

	return array(
		'url'    => esc_url( $link['url'] ),
		'title'  => $title ? $title : $default_label,
		'target' => ! empty( $link['target'] ) ? $link['target'] : '',
	);
}

/**
 * Render a link as a themed button.
 *
 * @param mixed  $link          Link field value or URL string.
 * @param string $variant       Button variant suffix, e.g. "primary".
 * @param string $default_label Label used when the field has none.
 * @param string $icon          Optional icon key appended after the label.
 * @return void
 */
function packgens_button( $link, $variant = 'primary', $default_label = '', $icon = 'chevrons-right' ) {
	$link = packgens_link( $link, $default_label );

	if ( ! $link ) {
		return;
	}

	printf(
		'<a class="pg-btn pg-btn--%1$s" href="%2$s"%3$s>%4$s%5$s</a>',
		esc_attr( $variant ),
		esc_url( $link['url'] ),
		$link['target'] ? ' target="' . esc_attr( $link['target'] ) . '" rel="noopener"' : '',
		esc_html( $link['title'] ),
		$icon ? packgens_get_icon( $icon, 'pg-btn__icon' ) : ''
	);
}

/**
 * Return an inline SVG icon from assets/icons.
 *
 * Icons are inlined so they inherit currentColor and cost no extra request.
 * The markup in assets/icons is authored by this theme, so it is safe to echo.
 *
 * @param string $name  Icon file name without extension.
 * @param string $class Extra class names for the wrapper.
 * @return string
 */
function packgens_get_icon( $name, $class = '' ) {
	static $cache = array();

	$name = preg_replace( '/[^a-z0-9\-]/', '', strtolower( (string) $name ) );

	if ( '' === $name ) {
		return '';
	}

	if ( ! isset( $cache[ $name ] ) ) {
		$path = PACKGENS_DIR . 'assets/icons/' . $name . '.svg';

		$cache[ $name ] = is_readable( $path ) ? trim( (string) file_get_contents( $path ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	if ( '' === $cache[ $name ] ) {
		return '';
	}

	return sprintf(
		'<span class="pg-icon %s" aria-hidden="true">%s</span>',
		esc_attr( $class ),
		$cache[ $name ]
	);
}

/**
 * Echo an inline SVG icon.
 *
 * @param string $name  Icon file name without extension.
 * @param string $class Extra class names.
 * @return void
 */
function packgens_icon( $name, $class = '' ) {
	echo packgens_get_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-authored SVG.
}

/**
 * Render a responsive image from an attachment ID or ACF image array.
 *
 * @param mixed  $image     Attachment ID, URL or ACF image array.
 * @param string $size      Registered image size.
 * @param array  $attr      Extra HTML attributes.
 * @param string $fallback_alt Alt text used when the attachment has none.
 * @return void
 */
function packgens_image( $image, $size = 'large', $attr = array(), $fallback_alt = '' ) {
	$attr = wp_parse_args( $attr, array( 'loading' => 'lazy', 'decoding' => 'async' ) );

	if ( is_array( $image ) && isset( $image['ID'] ) ) {
		$image = (int) $image['ID'];
	} elseif ( is_array( $image ) && isset( $image['id'] ) ) {
		$image = (int) $image['id'];
	}

	if ( is_numeric( $image ) && (int) $image > 0 ) {
		if ( empty( $attr['alt'] ) ) {
			$alt = get_post_meta( (int) $image, '_wp_attachment_image_alt', true );
			$attr['alt'] = $alt ? $alt : $fallback_alt;
		}

		echo wp_get_attachment_image( (int) $image, $size, false, $attr );
		return;
	}

	$url = '';

	if ( is_string( $image ) && '' !== $image ) {
		$url = $image;
	} elseif ( is_array( $image ) && ! empty( $image['url'] ) ) {
		$url = $image['url'];
	}

	if ( '' === $url ) {
		return;
	}

	$html = '<img src="' . esc_url( $url ) . '"';

	foreach ( $attr as $key => $value ) {
		$html .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
	}

	if ( empty( $attr['alt'] ) ) {
		$html .= ' alt="' . esc_attr( $fallback_alt ) . '"';
	}

	echo $html . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributes escaped above.
}

/**
 * Resolve an image field to a URL.
 *
 * @param mixed  $image Attachment ID, URL or ACF image array.
 * @param string $size  Registered image size.
 * @return string
 */
function packgens_image_url( $image, $size = 'large' ) {
	if ( is_array( $image ) ) {
		if ( isset( $image['sizes'][ $size ] ) ) {
			return (string) $image['sizes'][ $size ];
		}

		if ( isset( $image['ID'] ) ) {
			$image = (int) $image['ID'];
		} elseif ( ! empty( $image['url'] ) ) {
			return (string) $image['url'];
		}
	}

	if ( is_numeric( $image ) && (int) $image > 0 ) {
		$src = wp_get_attachment_image_src( (int) $image, $size );

		return $src ? (string) $src[0] : '';
	}

	return is_string( $image ) ? $image : '';
}

/**
 * Trim text to a word count without breaking entities.
 *
 * @param string $text  Source text.
 * @param int    $words Maximum number of words.
 * @return string
 */
function packgens_trim_words( $text, $words = 20 ) {
	return wp_trim_words( wp_strip_all_tags( (string) $text ), absint( $words ), '&hellip;' );
}

/**
 * Build a telephone href from a display number.
 *
 * @param string $number Human readable phone number.
 * @return string
 */
function packgens_tel_href( $number ) {
	$clean = preg_replace( '/[^0-9\+]/', '', (string) $number );

	return $clean ? 'tel:' . $clean : '';
}

/**
 * Render an author photo.
 *
 * Prefers the attachment chosen on the user's profile, so a byline works on a
 * site that does not use Gravatar, and falls back to get_avatar() otherwise.
 *
 * @param int    $user_id User ID.
 * @param int    $size    Square size in pixels.
 * @param string $class   Extra class for the image.
 * @return string
 */
function packgens_author_avatar( $user_id, $size = 40, $class = '' ) {
	$attachment_id = (int) get_user_meta( (int) $user_id, 'packgens_avatar_id', true );

	if ( $attachment_id ) {
		$image = wp_get_attachment_image(
			$attachment_id,
			'packgens-thumb',
			false,
			array(
				'class'  => trim( 'avatar ' . $class ),
				'alt'    => get_the_author_meta( 'display_name', $user_id ),
				'width'  => $size,
				'height' => $size,
			)
		);

		if ( $image ) {
			return $image;
		}
	}

	return get_avatar( $user_id, $size, '', '', array( 'class' => $class ) );
}

/**
 * Build a WhatsApp click-to-chat URL from a phone number.
 *
 * wa.me expects digits only in full international form, so the leading plus,
 * spaces and punctuation are stripped.
 *
 * @param string $number Phone number in any format.
 * @return string
 */
function packgens_whatsapp_href( $number ) {
	$digits = preg_replace( '/[^0-9]/', '', (string) $number );

	return $digits ? 'https://wa.me/' . $digits : '';
}

/**
 * Render a star rating.
 *
 * @param float $rating Rating out of five.
 * @param bool  $show_value Whether to print the numeric value.
 * @return void
 */
function packgens_stars( $rating, $show_value = false ) {
	$rating = max( 0, min( 5, (float) $rating ) );
	$full   = (int) floor( $rating );

	echo '<span class="pg-stars" role="img" aria-label="' . esc_attr( sprintf( /* translators: %s: rating out of five. */ __( 'Rated %s out of 5', 'packgens' ), number_format_i18n( $rating, 1 ) ) ) . '">';

	for ( $i = 1; $i <= 5; $i++ ) {
		$state = $i <= $full ? 'is-full' : ( ( $i - $rating ) < 1 ? 'is-half' : 'is-empty' );
		echo '<span class="pg-stars__star ' . esc_attr( $state ) . '">' . packgens_get_icon( 'star' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</span>';

	if ( $show_value ) {
		echo '<span class="pg-stars__value">' . esc_html( number_format_i18n( $rating, 1 ) ) . '</span>';
	}
}

/**
 * Return a unique DOM id with a theme prefix.
 *
 * @param string $prefix Prefix for readability.
 * @return string
 */
function packgens_unique_id( $prefix = 'pg' ) {
	static $counter = 0;
	++$counter;

	return sanitize_html_class( $prefix ) . '-' . $counter;
}

/**
 * URL for the saved-items list.
 *
 * Supports the common wishlist plugins by page slug and falls back to the shop
 * so the header icon is never a dead link.
 *
 * @return string
 */
function packgens_wishlist_url() {
	$page = get_page_by_path( 'wishlist' );

	if ( $page instanceof WP_Post ) {
		return get_permalink( $page );
	}

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		return wc_get_page_permalink( 'shop' );
	}

	return home_url( '/' );
}

/**
 * The brand mark shipped with the theme, as inline SVG.
 *
 * Used when no custom logo is set in the Customizer, so a fresh install still
 * shows the brand rather than the site title in plain text.
 *
 * @return string Inline SVG, or an empty string when the file is absent.
 */
function packgens_get_brand_mark() {
	static $svg = null;

	if ( null !== $svg ) {
		return $svg;
	}

	$path = PACKGENS_DIR . 'assets/images/logo.svg';

	if ( ! is_readable( $path ) ) {
		$svg = '';

		return $svg;
	}

	$markup = trim( (string) file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	// Drop the XML prolog and make the mark scale with its container.
	$markup = preg_replace( '/<\?xml.*?\?>/s', '', $markup );
	$markup = preg_replace( '/\s(width|height)="[^"]*"/', '', $markup, 2 );
	$markup = preg_replace( '/<svg\b/', '<svg class="pg-brand__mark" role="img" aria-hidden="true" focusable="false"', $markup, 1 );

	$svg = trim( $markup );

	return $svg;
}
