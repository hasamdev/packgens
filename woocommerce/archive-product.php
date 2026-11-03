<?php
/**
 * Product catalogue: shop, product categories and product tags.
 *
 * Every section comes from Packgens -> Category Defaults; a category stores
 * only what it changes. The hero heading and text default to the category's
 * name and description.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$context   = packgens_field_context();
$is_term   = is_product_category() || is_product_tag();
$view      = $is_term ? 'product_cat' : 'page';
// Merged with Category Defaults: a category that inherits its hero has no
// row of its own, which used to read as "no hero" and print a second band.
$hero      = packgens_section_data( 'hero', $context, $view );
$has_hero  = $hero && ( ! array_key_exists( 'enable', $hero ) || $hero['enable'] );
$term_name = $is_term ? single_term_title( '', false ) : woocommerce_page_title( false );

/*
 * The grid is the view's own product section: its heading, blurb and footer
 * button are edited on the term rather than hard-coded here.
 */
$catalog       = packgens_section_data( 'products', $context, $view );
$catalog_title = ! empty( $catalog['title'] ) ? $catalog['title'] : $term_name;
$catalog_desc  = $catalog['description'] ?? '';
$catalog_btn   = $catalog['button'] ?? null;

// On a category the heading is "{Category} Styles" unless the category typed
// its own. A heading set on Category Defaults would read the same on every
// category, so only the category's own value replaces it.
if ( is_product_category() ) {
	$own_title     = packgens_stored_section_keys( 'products_section', $context, array( 'title' ) )
		? packgens_group_field( 'products_section', 'title', $context )
		: '';
	$catalog_title = $own_title ? $own_title : sprintf(
		/* translators: %s: product category name. */
		__( '%s Styles', 'packgens' ),
		$term_name
	);
}

/*
 * An empty category shows the four newest products rather than a dead end;
 * only a shop with no products at all gets the empty state.
 */
$fallback_ids = array();

if ( $is_term && ! woocommerce_product_loop() ) {
	$fallback_ids = wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'limit'      => 4,
			'orderby'    => 'date',
			'order'      => 'DESC',
			'return'     => 'ids',
		)
	);
}

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

		<?php elseif ( $fallback_ids ) : ?>

			<div class="pg-grid pg-product-grid" style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:4">
				<?php foreach ( $fallback_ids as $fallback_id ) : ?>
					<?php get_template_part( 'template-parts/cards/product', null, array( 'id' => $fallback_id ) ); ?>
				<?php endforeach; ?>
			</div>

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
