<?php
/**
 * Saved items ("wishlist").
 *
 * Kept in the theme rather than delegated to a plugin: the list is a handful of
 * product IDs, and a plugin would bring its own markup and stylesheet to fight
 * with. Signed-in shoppers keep the list on their account, guests keep it in a
 * cookie, and signing in merges the two.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

const PACKGENS_WISHLIST_KEY    = 'packgens_wishlist';
const PACKGENS_WISHLIST_COOKIE = 'packgens_wishlist';
const PACKGENS_WISHLIST_DAYS   = 30;

/**
 * The saved product IDs for the current visitor.
 *
 * @return int[]
 */
function packgens_wishlist_items() {
	if ( is_user_logged_in() ) {
		$stored = get_user_meta( get_current_user_id(), PACKGENS_WISHLIST_KEY, true );
	} else {
		$raw    = isset( $_COOKIE[ PACKGENS_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ PACKGENS_WISHLIST_COOKIE ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$stored = explode( ',', (string) $raw );
	}

	$items = array_filter( array_map( 'absint', (array) $stored ) );

	return array_values( array_unique( $items ) );
}

/**
 * Persist the list for the current visitor.
 *
 * @param int[] $items Product IDs.
 * @return void
 */
function packgens_wishlist_save( $items ) {
	$items = array_values( array_unique( array_filter( array_map( 'absint', (array) $items ) ) ) );

	if ( is_user_logged_in() ) {
		update_user_meta( get_current_user_id(), PACKGENS_WISHLIST_KEY, $items );

		return;
	}

	$value = implode( ',', $items );

	// Headers are already out during AJAX responses only after output starts,
	// so guard rather than warn.
	if ( ! headers_sent() ) {
		setcookie(
			PACKGENS_WISHLIST_COOKIE,
			$value,
			array(
				'expires'  => time() + ( DAY_IN_SECONDS * PACKGENS_WISHLIST_DAYS ),
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				'secure'   => is_ssl(),
				'httponly' => false,
				'samesite' => 'Lax',
			)
		);
	}

	$_COOKIE[ PACKGENS_WISHLIST_COOKIE ] = $value;
}

/**
 * Whether a product is on the list.
 *
 * @param int $product_id Product.
 * @return bool
 */
function packgens_wishlist_has( $product_id ) {
	return in_array( absint( $product_id ), packgens_wishlist_items(), true );
}

/**
 * How many products are saved.
 *
 * @return int
 */
function packgens_wishlist_count() {
	return count( packgens_wishlist_items() );
}

/**
 * Toggle a product and return the resulting state.
 *
 * @param int $product_id Product.
 * @return array{saved:bool,count:int}
 */
function packgens_wishlist_toggle( $product_id ) {
	$product_id = absint( $product_id );
	$items      = packgens_wishlist_items();
	$position   = array_search( $product_id, $items, true );

	if ( false === $position ) {
		$items[] = $product_id;
		$saved   = true;
	} else {
		unset( $items[ $position ] );
		$saved = false;
	}

	packgens_wishlist_save( $items );

	return array(
		'saved' => $saved,
		'count' => count( $items ),
	);
}

/**
 * Move a guest list onto the account it just signed in to.
 *
 * @param string  $login User login.
 * @param WP_User $user  User.
 * @return void
 */
function packgens_wishlist_merge_on_login( $login, $user ) {
	$raw = isset( $_COOKIE[ PACKGENS_WISHLIST_COOKIE ] ) ? wp_unslash( $_COOKIE[ PACKGENS_WISHLIST_COOKIE ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

	$guest = array_filter( array_map( 'absint', explode( ',', (string) $raw ) ) );

	if ( ! $guest ) {
		return;
	}

	$stored = array_filter( array_map( 'absint', (array) get_user_meta( $user->ID, PACKGENS_WISHLIST_KEY, true ) ) );

	update_user_meta( $user->ID, PACKGENS_WISHLIST_KEY, array_values( array_unique( array_merge( $stored, $guest ) ) ) );

	if ( ! headers_sent() ) {
		setcookie( PACKGENS_WISHLIST_COOKIE, '', time() - HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
	}
}
add_action( 'wp_login', 'packgens_wishlist_merge_on_login', 10, 2 );

/**
 * Toggle endpoint.
 *
 * @return void
 */
function packgens_ajax_wishlist() {
	check_ajax_referer( 'packgens_public', 'nonce' );

	$product_id = isset( $_POST['product'] ) ? absint( wp_unslash( $_POST['product'] ) ) : 0;

	if ( ! $product_id || 'product' !== get_post_type( $product_id ) || 'publish' !== get_post_status( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'That product could not be saved.', 'packgens' ) ), 400 );
	}

	wp_send_json_success( packgens_wishlist_toggle( $product_id ) );
}
add_action( 'wp_ajax_packgens_wishlist', 'packgens_ajax_wishlist' );
add_action( 'wp_ajax_nopriv_packgens_wishlist', 'packgens_ajax_wishlist' );

/**
 * The save button shown on product cards and on the product page.
 *
 * @param int    $product_id Product.
 * @param string $class      Extra class names.
 * @return string
 */
function packgens_wishlist_button( $product_id, $class = '' ) {
	$product_id = absint( $product_id );

	if ( ! $product_id ) {
		return '';
	}

	$saved = packgens_wishlist_has( $product_id );

	return sprintf(
		'<button type="button" class="pg-wishlist-btn %1$s%2$s" data-pg-wishlist="%3$d" aria-pressed="%4$s"><span class="pg-wishlist-btn__icon">%5$s</span><span class="pg-wishlist-btn__label">%6$s</span></button>',
		esc_attr( $class ),
		$saved ? ' is-saved' : '',
		$product_id,
		$saved ? 'true' : 'false',
		packgens_get_icon( 'heart' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $saved ? __( 'Saved', 'packgens' ) : __( 'Save', 'packgens' ) )
	);
}

/**
 * Keep the header count in step when the page is served from a cache.
 *
 * @param array $fragments Cart fragments.
 * @return array
 */
function packgens_wishlist_fragment( $fragments ) {
	ob_start();
	packgens_wishlist_badge();
	$fragments['.pg-header__count--wishlist'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'packgens_wishlist_fragment' );

/**
 * The count bubble on the header heart.
 *
 * @return void
 */
function packgens_wishlist_badge() {
	$count = packgens_wishlist_count();

	printf(
		'<span class="pg-header__count pg-header__count--wishlist%1$s">%2$s</span>',
		$count ? '' : ' is-empty',
		esc_html( (string) $count )
	);
}
