<?php
/**
 * Generic archive template (categories, tags, dates, authors).
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();
get_template_part( 'template-parts/components/page-header' );
?>

<section class="pg-section pg-section--blog-archive">
	<div class="pg-container">

		<?php get_template_part( 'template-parts/post/filters' ); ?>

		<?php if ( have_posts() ) : ?>

			<h2 class="pg-sr-only"><?php esc_html_e( 'Articles', 'packgens' ); ?></h2>

			<div class="pg-grid pg-post-grid" id="pg-post-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:3">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/cards/post', null, array( 'id' => get_the_ID() ) );
				endwhile;
				?>
			</div>

			<?php
			global $wp_query;
			packgens_load_more_button( $wp_query, 'pg-post-grid', 'post' );
			?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/components/no-results' ); ?>
		<?php endif; ?>

	</div>
</section>

<?php
get_footer();
