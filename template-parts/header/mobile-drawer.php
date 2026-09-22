<?php
/**
 * Off-canvas navigation drawer for small screens.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$phone   = packgens_get_option( 'phone' );
$email   = packgens_get_option( 'email' );
$cta_txt = packgens_get_option( 'header_cta_text' );
$cta_url = packgens_get_option( 'header_cta_url' );
$socials = packgens_social_links();
?>
<div class="pg-drawer-backdrop" data-pg-drawer-close hidden></div>

<div id="pg-drawer" class="pg-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Site menu', 'packgens' ); ?>" hidden>

	<div class="pg-drawer__head">
		<div class="pg-drawer__brand"><?php get_template_part( 'template-parts/header/branding' ); ?></div>
		<button type="button" class="pg-drawer__close" data-pg-drawer-close>
			<?php packgens_icon( 'close' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Close menu', 'packgens' ); ?></span>
		</button>
	</div>

	<div class="pg-drawer__body">
		<nav class="pg-mnav" aria-label="<?php esc_attr_e( 'Mobile', 'packgens' ); ?>">
			<?php packgens_mobile_menu(); ?>
		</nav>

		<?php if ( $cta_txt && $cta_url ) : ?>
			<a class="pg-btn pg-btn--primary pg-btn--block" href="<?php echo esc_url( $cta_url ); ?>">
				<?php echo esc_html( $cta_txt ); ?>
			</a>
		<?php endif; ?>

		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<ul class="pg-drawer__links">
				<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php packgens_icon( 'user' ); ?><?php esc_html_e( 'My account', 'packgens' ); ?></a></li>
				<li><a href="<?php echo esc_url( packgens_wishlist_url() ); ?>"><?php packgens_icon( 'heart' ); ?><?php esc_html_e( 'Saved items', 'packgens' ); ?></a></li>
				<li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php packgens_icon( 'cart' ); ?><?php esc_html_e( 'Basket', 'packgens' ); ?></a></li>
			</ul>
		<?php endif; ?>

		<div class="pg-drawer__contact">
			<?php if ( $phone ) : ?>
				<a href="<?php echo esc_url( packgens_tel_href( $phone ) ); ?>"><?php packgens_icon( 'phone' ); ?><?php echo esc_html( $phone ); ?></a>
			<?php endif; ?>
			<?php if ( $email ) : ?>
				<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php packgens_icon( 'mail' ); ?><?php echo esc_html( $email ); ?></a>
			<?php endif; ?>
		</div>

		<?php if ( $socials ) : ?>
			<ul class="pg-social">
				<?php foreach ( $socials as $icon => $url ) : ?>
					<li>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
							<?php packgens_icon( $icon ); ?>
							<span class="pg-sr-only"><?php echo esc_html( ucfirst( $icon ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

</div>
