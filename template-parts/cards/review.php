<?php
/**
 * Testimonial card.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$review_id = absint( $args['id'] ?? 0 );

if ( ! $review_id ) {
	return;
}

$name   = packgens_group_field( 'testimonial', 'name', $review_id, get_the_title( $review_id ) );
$role   = packgens_group_field( 'testimonial', 'designation', $review_id );
$text   = packgens_group_field( 'testimonial', 'review', $review_id, get_post_field( 'post_content', $review_id ) );
$rating = (float) packgens_group_field( 'testimonial', 'rating', $review_id, 5 );
$source = packgens_group_field( 'testimonial', 'source', $review_id );
?>
<article class="pg-review-card"<?php echo $source ? ' data-pg-platform="' . esc_attr( $source ) . '"' : ''; ?>>

	<header class="pg-review-card__head">
		<?php if ( $rating > 0 ) : ?>
			<?php packgens_stars( $rating ); ?>
		<?php endif; ?>

		<span class="pg-review-card__source">
			<?php packgens_icon( $source ? $source : 'thumbs-up' ); ?>
			<span><?php echo esc_html( $source ? ucfirst( $source ) : __( 'Testimonial', 'packgens' ) ); ?></span>
		</span>
	</header>

	<?php if ( $text ) : ?>
		<blockquote class="pg-review-card__text"><?php echo esc_html( wp_strip_all_tags( $text ) ); ?></blockquote>
	<?php endif; ?>

	<footer class="pg-review-card__foot">
		<?php if ( has_post_thumbnail( $review_id ) ) : ?>
			<span class="pg-review-card__avatar">
				<?php echo get_the_post_thumbnail( $review_id, 'packgens-thumb', array( 'loading' => 'lazy', 'alt' => esc_attr( $name ) ) ); ?>
			</span>
		<?php endif; ?>

		<span class="pg-review-card__meta">
			<span class="pg-review-card__name"><?php echo esc_html( $name ); ?></span>
			<?php if ( $role ) : ?>
				<span class="pg-review-card__role"><?php echo esc_html( $role ); ?></span>
			<?php endif; ?>
		</span>
	</footer>

</article>
