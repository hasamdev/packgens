<?php
/**
 * Product category card.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$term = $args['term'] ?? null;

if ( ! $term instanceof WP_Term ) {
	return;
}

$thumb_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
$link     = get_term_link( $term );

if ( is_wp_error( $link ) ) {
	return;
}
?>
<article class="pg-cat-card">
	<a class="pg-cat-card__link" href="<?php echo esc_url( $link ); ?>">
		<span class="pg-cat-card__media">
			<?php if ( $thumb_id ) : ?>
				<?php packgens_image( $thumb_id, 'packgens-card-tall', array(), $term->name ); ?>
			<?php else : ?>
				<span class="pg-cat-card__placeholder" aria-hidden="true"><?php packgens_icon( 'package' ); ?></span>
			<?php endif; ?>
		</span>
		<span class="pg-cat-card__title"><?php echo esc_html( $term->name ); ?></span>
	</a>
</article>
