<?php
/**
 * Header navigation row with the primary call to action.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$cta_text = packgens_get_option( 'header_cta_text' );
$cta_url  = packgens_get_option( 'header_cta_url' );
?>
<div class="pg-header__nav-row pg-hide-mobile">

	<nav class="pg-nav" aria-label="<?php esc_attr_e( 'Primary', 'packgens' ); ?>">
		<?php packgens_primary_menu(); ?>
	</nav>

	<?php if ( $cta_text && $cta_url ) : ?>
		<a class="pg-btn pg-btn--primary pg-header__cta" href="<?php echo esc_url( $cta_url ); ?>">
			<?php echo esc_html( $cta_text ); ?>
			<?php packgens_icon( 'chevrons-right', 'pg-btn__icon' ); ?>
		</a>
	<?php endif; ?>

</div>
