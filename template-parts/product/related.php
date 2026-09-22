<?php
/**
 * Related products carousel.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$product_id = absint( $args['product_id'] ?? 0 );
$related    = packgens_get_related_products( $product_id, 8 );

if ( empty( $related ) ) {
	return;
}

$carousel_id = packgens_unique_id( 'pg-related' );
?>
<section class="pg-section pg-section--gray pg-section--related-products">
	<div class="pg-container">

		<div class="pg-section-head pg-section-head--center">
			<div class="pg-section-head__copy">
				<h2 class="pg-section-head__title"><?php esc_html_e( 'Related Products', 'packgens' ); ?></h2>
			</div>
		</div>

		<?php
		packgens_carousel_open(
			$carousel_id,
			array(
				'label'    => __( 'Related products', 'packgens' ),
				'per_view' => array( 0 => 1.2, 576 => 2.2, 768 => 3, 1200 => 4 ),
			)
		);

		foreach ( $related as $related_id ) {
			packgens_carousel_slide( 'template-parts/cards/product', null, array( 'id' => $related_id ) );
		}

		packgens_carousel_close();
		?>

		<div class="pg-carousel__nav pg-carousel__nav--center">
			<?php packgens_carousel_nav( $carousel_id ); ?>
		</div>

	</div>
</section>
