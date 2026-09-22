<?php
/**
 * Template Name: Legal
 *
 * Terms, privacy and returns: one white card of long-form copy on a tinted
 * page, with no hero band above it.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();
?>

<section class="pg-section pg-section--legal">
	<div class="pg-container">
		<article class="pg-legal">
			<h1 class="pg-legal__title"><?php the_title(); ?></h1>

			<div class="pg-prose pg-legal__body">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();

					wp_link_pages(
						array(
							'before' => '<div class="pg-page-links">' . esc_html__( 'Pages:', 'packgens' ),
							'after'  => '</div>',
						)
					);
				endwhile;
				?>
			</div>
		</article>
	</div>
</section>

<?php
packgens_render_sections( 'page', $page_id, array(), array( 'hero' ) );

get_footer();
