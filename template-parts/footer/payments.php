<?php
/**
 * Accepted payment methods strip.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$title = packgens_global( 'footer', 'payment_title' );
$logos = array_filter( array_map( 'absint', (array) packgens_global( 'footer', 'payment_logos', array() ) ) );

if ( empty( $logos ) && ! $title ) {
	return;
}
?>
<div class="pg-footer__payments">
	<?php if ( $title ) : ?>
		<h2 class="pg-footer__payments-title"><?php echo esc_html( $title ); ?></h2>
	<?php endif; ?>

	<?php if ( $logos ) : ?>
		<ul class="pg-logo-row">
			<?php foreach ( $logos as $logo_id ) : ?>
				<li><?php packgens_image( $logo_id, 'packgens-thumb', array(), '' ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
