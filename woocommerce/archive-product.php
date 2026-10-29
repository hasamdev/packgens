<?php
/**
 * Product catalogue: shop, product categories and product tags.
 *
 * Every section comes from Packgens -> Category Defaults; a category adds only
 * its hero heading, text and image. See packgens_section_data().
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$context   = packgens_field_context();
$is_term   = is_product_category() || is_product_tag();
$view      = $is_term ? 'product_cat' : 'page';
$hero      = packgens_section_data( 'hero', $context, $view );
$has_hero  = ! empty( $hero ) && ( ! array_key_exists( 'enable', $hero ) || $hero['enable'] );
$term_name = $is_term ? single_term_title( '', false ) : woocommerce_page_title( false );

/*
 * The grid is the view's own product section. On a category its heading is
 * the category name; the blurb and footer button are shared.
 */
$catalog       = packgens_section_data( 'products', $context, $view );
$catalog_title = ( $is_term || empty( $catalog['title'] ) ) ? $term_name : $catalog['title'];
$catalog_desc  = $catalog['description'] ?? '';
$catalog_btn   = $catalog['button'] ?? null;

packgens_render_section( 'hero', $context, $view );

if ( ! $has_hero ) {
	get_template_part(
		'template-parts/components/page-header',
		null,
		array(
			'title'    => $term_name,
			'subtitle' => $is_term ? wp_strip_all_tags( term_description() ) : '',
		)
	);
}

do_action( 'woocommerce_before_main_content' );
?>

<section class="pg-section pg-section--catalog">
	<div class="pg-container">

		<div class="pg-catalog__head">
			<div class="pg-section-head__copy">
				<h2 class="pg-section-head__title"><?php echo esc_html( $catalog_title ); ?></h2>

				<?php if ( $catalog_desc ) : ?>
					<p class="pg-section-head__desc"><?php echo esc_html( $catalog_desc ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( woocommerce_product_loop() ) : ?>

			<div class="pg-grid pg-product-grid" id="pg-product-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:3">
				<?php
				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();

						do_action( 'woocommerce_shop_loop' );

						get_template_part( 'template-parts/cards/product', null, array( 'id' => get_the_ID() ) );
					}
				}
				?>
			</div>

			<?php
			global $wp_query;
			packgens_load_more_button( $wp_query, 'pg-product-grid', 'product' );
			?>

			<?php if ( $catalog_btn ) : ?>
				<div class="pg-section__footer">
					<?php packgens_button( $catalog_btn, 'dark' ); ?>
				</div>
			<?php endif; ?>

		<?php else : ?>
			<?php
			get_template_part(
				'template-parts/components/no-results',
				null,
				array(
					'title' => __( 'No products found', 'packgens' ),
					'text'  => __( 'Nothing matches this view yet. Try another category or ask us for a custom quote.', 'packgens' ),
				)
			);
			?>
		<?php endif; ?>

	</div>
</section>

<?php
do_action( 'woocommerce_after_main_content' );

packgens_render_sections( $view, $context, array(), array( 'hero', 'products' ) );

get_footer();
