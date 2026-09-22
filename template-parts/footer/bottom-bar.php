<?php
/**
 * Footer bottom bar: copyright, delivery partners and legal links.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$copyright = packgens_global( 'footer', 'copyright' );
$copyright = $copyright
	? str_replace( '%year%', gmdate( 'Y' ), $copyright )
	: sprintf(
		/* translators: 1: year, 2: site name. */
		__( '&copy; %1$s %2$s. All rights reserved.', 'packgens' ),
		gmdate( 'Y' ),
		get_bloginfo( 'name' )
	);

$delivery_title = packgens_global( 'footer', 'delivery_title' );
$delivery_logos = array_filter( array_map( 'absint', (array) packgens_global( 'footer', 'delivery_logos', array() ) ) );
?>
<div class="pg-footer__bottom">

	<p class="pg-footer__copyright"><?php echo wp_kses_post( $copyright ); ?></p>

	<?php if ( $delivery_logos || $delivery_title ) : ?>
		<div class="pg-footer__delivery">
			<?php if ( $delivery_title ) : ?>
				<span class="pg-footer__delivery-title"><?php echo esc_html( $delivery_title ); ?></span>
			<?php endif; ?>

			<?php if ( $delivery_logos ) : ?>
				<ul class="pg-logo-row pg-logo-row--sm">
					<?php foreach ( $delivery_logos as $logo_id ) : ?>
						<li><?php packgens_image( $logo_id, 'packgens-thumb', array(), '' ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( has_nav_menu( 'footer_legal' ) ) : ?>
		<nav class="pg-footer__legal" aria-label="<?php esc_attr_e( 'Legal', 'packgens' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_legal',
					'container'      => false,
					'menu_class'     => 'pg-footer__legal-menu',
					'depth'          => 1,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	<?php endif; ?>

</div>
