<?php
/**
 * Products section: grid or carousel of product cards.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data     = $args['data'] ?? array();
$context  = $args['context'] ?? null;
$products = packgens_get_section_products( $data, $context );

if ( empty( $products ) ) {
	return;
}

$is_carousel = 'carousel' === ( $data['layout'] ?? 'grid' );
$carousel_id = packgens_unique_id( 'pg-products' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'products' ) ); ?>">
	<div class="pg-container">

		<?php
		packgens_section_header(
			$data,
			array(
				// The product page centres this heading, as the design draws it.
				'align'  => 'product' === ( $args['view'] ?? '' ) ? 'center' : 'left',
				'icon'   => 'chevrons-right',
				'nav'    => $is_carousel,
				'nav_id' => $carousel_id,
			)
		);
		?>

		<?php if ( $is_carousel ) : ?>
			<?php
			packgens_carousel_open(
				$carousel_id,
				array(
					'class'    => 'pg-products__track',
					'label'    => $data['title'] ?? __( 'Products', 'packgens' ),
					'per_view' => array( 0 => 1.2, 576 => 2.2, 768 => 3, 1200 => 4 ),
				)
			);

			foreach ( $products as $product_id ) {
				packgens_carousel_slide( 'template-parts/cards/product', null, array( 'id' => $product_id ) );
			}

			packgens_carousel_close();
			?>
		<?php else : ?>
			<div class="pg-products__grid">
				<?php foreach ( $products as $product_id ) : ?>
					<?php
					get_template_part(
						'template-parts/cards/product',
						null,
						array(
							'id'         => $product_id,
							'show_price' => false,
							'show_badge' => false,
						)
					);
					?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $data['button'] ) ) : ?>
			<div class="pg-section__footer">
				<?php packgens_button( $data['button'], 'primary' ); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
