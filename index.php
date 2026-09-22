<?php
/**
 * Fallback template and blog index.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$blog_page_id = (int) get_option( 'page_for_posts' );

if ( is_home() && $blog_page_id ) {
	packgens_render_section( 'hero', $blog_page_id, 'page' );
}

if ( ! packgens_view_has_hero() || ! is_home() ) {
	get_template_part( 'template-parts/components/page-header' );
}
?>

<section class="pg-section pg-section--blog-archive">
	<div class="pg-container">

		<div class="pg-blog-layout">

			<div class="pg-blog-main">
				<?php if ( have_posts() ) : ?>

					<h2 class="pg-sr-only"><?php esc_html_e( 'Articles', 'packgens' ); ?></h2>

					<div class="pg-post-grid" id="pg-post-grid">
						<?php
						$pg_index = 0;

						while ( have_posts() ) :
							the_post();

							/*
							 * The design breaks the two-up rhythm with a full-width
							 * card at the top and again mid-way down, so every fifth
							 * card from the first is laid out wide.
							 */
							get_template_part(
								'template-parts/cards/post',
								null,
								array(
									'id'   => get_the_ID(),
									'wide' => 0 === $pg_index % 5,
								)
							);

							++$pg_index;
						endwhile;
						?>
					</div>

					<?php
					global $wp_query;
					packgens_load_more_button( $wp_query, 'pg-post-grid', 'post', __( 'Show More Blogs', 'packgens' ) );
					?>

				<?php else : ?>
					<?php get_template_part( 'template-parts/components/no-results' ); ?>
				<?php endif; ?>
			</div>

			<?php get_template_part( 'template-parts/post/sidebar' ); ?>

		</div>

	</div>
</section>

<?php
if ( is_home() && $blog_page_id ) {
	packgens_render_sections( 'page', $blog_page_id, array(), array( 'hero', 'blogs' ) );
}

get_footer();
