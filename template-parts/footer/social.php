<?php
/**
 * Footer social profiles.
 *
 * Sits in its own grid cell so it can share a row with the payments strip.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$socials = packgens_social_links();

if ( empty( $socials ) ) {
	return;
}
?>
<div class="pg-footer__social">
	<h2 class="pg-footer__heading"><?php packgens_the_label( 'footer', 'follow', __( 'Follow us', 'packgens' ) ); ?></h2>
	<ul class="pg-social pg-social--light">
		<?php foreach ( $socials as $icon => $url ) : ?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
					<?php packgens_icon( $icon ); ?>
					<span class="pg-sr-only"><?php echo esc_html( ucfirst( $icon ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
