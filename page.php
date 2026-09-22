<?php
/**
 * Default page template.
 *
 * Renders the hero, then the page's own editor content, then any sections the
 * editor has configured.
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

if ( trim( (string) get_post_field( 'post_content', $page_id ) ) ) :
	?>
	<section class="pg-section pg-section--page-content">
		<div class="pg-container pg-container--narrow">
			<div class="pg-prose">
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
		</div>
	</section>
	<?php
endif;

packgens_render_sections( 'page', $page_id, array(), array( 'hero' ) );

get_footer();
