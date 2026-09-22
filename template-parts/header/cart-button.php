<?php
/**
 * Cart button with a live item count.
 *
 * The count node carries the WooCommerce fragment class so the number updates
 * after an AJAX add to cart without a page reload.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_cart_url' ) ) {
	return;
}

$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<a class="pg-header__cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
	<?php packgens_icon( 'cart' ); ?>
	<span class="pg-header__cart-count pg-cart-count"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	<span class="pg-sr-only"><?php esc_html_e( 'View basket', 'packgens' ); ?></span>
</a>
