<?php
/**
 * Search results.
 *
 * Product searches get the catalogue grid, everything else the blog grid.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$searched_type = get_query_var( 'post_type' );
$is_product    = ( 'product' === $searched_type ) && post_type_exists( 'product' );

get_template_part( 'template-parts/components/page-header' );
?>

<section class="pg-section pg-section--search">
	<div class="pg-container">

		<div class="pg-search-summary">
			<p>
				<?php
				printf(
					/* translators: %s: number of results. */
					esc_html( _n( '%s result found', '%s results found', (int) $GLOBALS['wp_query']->found_posts, 'packgens' ) ),
					esc_html( number_format_i18n( (int) $GLOBALS['wp_query']->found_posts ) )
				);
				?>
			</p>
			<?php get_search_form(); ?>
		</div>

		<?php if ( have_posts() ) : ?>

			<div class="pg-grid" id="pg-search-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:<?php echo $is_product ? '4' : '3'; ?>">
				<?php
				while ( have_posts() ) :
					the_post();

					if ( $is_product || 'product' === get_post_type() ) {
						get_template_part( 'template-parts/cards/product', null, array( 'id' => get_the_ID() ) );
					} else {
						get_template_part( 'template-parts/cards/post', null, array( 'id' => get_the_ID() ) );
					}
				endwhile;
				?>
			</div>

			<?php packgens_pagination(); ?>

		<?php else : ?>
			<?php get_template_part( 'template-parts/components/no-results' ); ?>
		<?php endif; ?>

	</div>
</section>

<?php
get_footer();
