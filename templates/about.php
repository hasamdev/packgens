<?php
/**
 * Template Name: About
 *
 * The hero and the statistics band render as one brand-coloured block, as the
 * design draws them; everything below is the shared section library.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id  = get_the_ID();
$hero     = packgens_field( 'hero_section', $page_id );
$has_hero = (bool) $hero;

/*
 * The band carries the hero artwork itself rather than letting the hero own it,
 * so the one brand wash covers the headline, the statistics and the tab row
 * together instead of stopping where the hero ends.
 */
$band_image = ! empty( $hero['image'] ) ? packgens_image_url( $hero['image'], 'full' ) : '';
?>

<div class="pg-about-band"<?php echo $band_image ? ' style="--pg-band-image: url(' . esc_url( $band_image ) . ');"' : ''; ?>>
	<?php
	packgens_render_section( 'hero', $page_id, 'page' );

	if ( ! $has_hero ) {
		get_template_part( 'template-parts/components/page-header' );
	}

	packgens_render_section( 'stats', $page_id, 'page' );

	get_template_part( 'template-parts/sections/hero-tabs', null, array( 'context' => $page_id ) );
	?>
</div>

<?php
if ( trim( (string) get_post_field( 'post_content', $page_id ) ) ) :
	?>
	<section class="pg-section pg-section--page-content">
		<div class="pg-container pg-container--narrow">
			<div class="pg-prose">
				<?php
				while ( have_posts() ) :
					the_post();
					the_content();
				endwhile;
				?>
			</div>
		</div>
	</section>
	<?php
endif;

packgens_render_sections( 'page', $page_id, array(), array( 'hero', 'stats' ) );

get_footer();
