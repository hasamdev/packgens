<?php
/**
 * Header utility row: brand, search, contact blocks and account actions.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$phone          = packgens_get_option( 'phone' );
$phone_note     = packgens_get_option( 'phone_note' );
$ship_title     = packgens_get_option( 'header_shipping_title' );
$ship_text      = packgens_get_option( 'header_shipping_text' );
$has_woocommerce = class_exists( 'WooCommerce' );
?>
<div class="pg-header__utility">

	<div class="pg-header__brand">
		<?php get_template_part( 'template-parts/header/branding' ); ?>
	</div>

	<div class="pg-header__search pg-hide-mobile">
		<?php get_template_part( 'template-parts/forms/product-search' ); ?>
	</div>

	<?php if ( $phone ) : ?>
		<div class="pg-header__contact pg-hide-mobile">
			<?php packgens_icon( 'phone', 'pg-header__contact-icon' ); ?>
			<span class="pg-header__contact-copy">
				<a class="pg-header__contact-title" href="<?php echo esc_url( packgens_tel_href( $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
				<?php if ( $phone_note ) : ?>
					<span class="pg-header__contact-note"><?php echo esc_html( $phone_note ); ?></span>
				<?php endif; ?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( $ship_title ) : ?>
		<div class="pg-header__contact pg-header__contact--shipping pg-hide-mobile">
			<?php packgens_icon( 'truck', 'pg-header__contact-icon' ); ?>
			<span class="pg-header__contact-copy">
				<span class="pg-header__contact-title"><?php echo esc_html( $ship_title ); ?></span>
				<?php if ( $ship_text ) : ?>
					<span class="pg-header__contact-note"><?php echo esc_html( $ship_text ); ?></span>
				<?php endif; ?>
			</span>
		</div>
	<?php endif; ?>

	<div class="pg-header__actions">

		<button type="button" class="pg-header__action pg-hide-desktop" data-pg-search-toggle aria-expanded="false" aria-controls="pg-mobile-search">
			<?php packgens_icon( 'search' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Search products', 'packgens' ); ?></span>
		</button>

		<?php if ( $has_woocommerce ) : ?>

			<a class="pg-header__action pg-header__action--wishlist pg-hide-mobile" href="<?php echo esc_url( packgens_wishlist_url() ); ?>">
				<?php packgens_icon( 'heart' ); ?>
				<?php if ( function_exists( 'packgens_wishlist_badge' ) ) : ?>
					<?php packgens_wishlist_badge(); ?>
				<?php endif; ?>
				<span class="pg-sr-only"><?php esc_html_e( 'Saved items', 'packgens' ); ?></span>
			</a>

			<a class="pg-header__action pg-hide-mobile" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
				<?php packgens_icon( 'user' ); ?>
				<span class="pg-sr-only"><?php esc_html_e( 'My account', 'packgens' ); ?></span>
			</a>

			<?php get_template_part( 'template-parts/header/cart-button' ); ?>

		<?php endif; ?>

		<button type="button" class="pg-header__burger pg-hide-desktop" data-pg-drawer-open aria-expanded="false" aria-controls="pg-drawer">
			<?php packgens_icon( 'menu' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Open menu', 'packgens' ); ?></span>
		</button>

	</div>
</div>

<div class="pg-header__mobile-search pg-hide-desktop" id="pg-mobile-search" hidden>
	<?php get_template_part( 'template-parts/forms/product-search', null, array( 'context' => 'mobile' ) ); ?>
</div>
