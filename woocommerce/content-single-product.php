<?php
/**
 * Single product layout.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	return;
}

$product_id = $product->get_id();
$badge      = packgens_group_field( 'product_details', 'badge', $product_id );
$tabs       = packgens_product_tabs( $product_id );
$tabs_title = packgens_group_field( 'product_details', 'tabs_title', $product_id, __( 'Product Packages Details', 'packgens' ) );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'pg-product', $product ); ?>>

	<section class="pg-section pg-section--tight pg-product__top">
		<div class="pg-container">

			<?php packgens_breadcrumbs(); ?>

			<div class="pg-product__layout">

				<div class="pg-product__media-col">
					<?php
					get_template_part(
						'template-parts/product/gallery',
						null,
						array( 'product' => $product, 'badge' => $badge )
					);

					get_template_part(
						'template-parts/product/support',
						null,
						array( 'product_id' => $product_id )
					);
					?>
				</div>

				<?php
				get_template_part(
					'template-parts/product/summary',
					null,
					array( 'product' => $product )
				);
				?>

			</div>
		</div>
	</section>

	<?php packgens_render_sections( 'product', $product_id, array( 'products' ) ); ?>

	<?php if ( $tabs ) : ?>
		<section class="pg-section pg-section--tight pg-product__tabs">
			<div class="pg-container">
				<?php if ( $tabs_title ) : ?>
					<div class="pg-section-head pg-section-head--center">
						<div class="pg-section-head__copy">
							<h2 class="pg-section-head__title"><?php echo esc_html( $tabs_title ); ?></h2>
						</div>
					</div>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/product/tabs', null, array( 'tabs' => $tabs ) ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	packgens_render_sections( 'product', $product_id, array(), array( 'products' ) );

	if ( comments_open() || $product->get_review_count() ) {
		echo '<section class="pg-section pg-section--gray-cool" id="reviews"><div class="pg-container pg-container--narrow">';
		comments_template();
		echo '</div></section>';
	}
	?>

</div>
