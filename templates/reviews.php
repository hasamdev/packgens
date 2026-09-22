<?php
/**
 * Template Name: Reviews
 *
 * Paginated grid of testimonials with a load-more button.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();

packgens_render_section( 'hero', $page_id, 'page' );

if ( ! packgens_field( 'hero_section', $page_id ) ) {
	get_template_part( 'template-parts/components/page-header' );
}

$paged = max( 1, (int) get_query_var( 'paged' ) );
$query = new WP_Query( packgens_reviews_query_args( $paged, 9 ) );
?>

<section class="pg-section pg-section--reviews-page">
	<div class="pg-container">

		<?php if ( ! empty( $query->posts ) ) : ?>

			<div class="pg-grid pg-review-grid" id="pg-review-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:3">
				<?php foreach ( $query->posts as $review_id ) : ?>
					<?php get_template_part( 'template-parts/cards/review', null, array( 'id' => (int) $review_id ) ); ?>
				<?php endforeach; ?>
			</div>

			<?php packgens_load_more_button( $query, 'pg-review-grid', 'review' ); ?>

		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/components/no-results',
				null,
				array(
					'title' => __( 'No reviews yet', 'packgens' ),
					'text'  => __( 'Customer reviews will appear here once they are published.', 'packgens' ),
				)
			);
			?>
		<?php endif; ?>

	</div>
</section>

<?php
wp_reset_postdata();

packgens_render_sections( 'page', $page_id, array(), array( 'hero', 'reviews' ) );

get_footer();
