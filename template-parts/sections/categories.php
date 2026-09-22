<?php
/**
 * Product category carousel.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$terms = packgens_get_section_categories( $data );

if ( empty( $terms ) ) {
	return;
}

$carousel_id = packgens_unique_id( 'pg-categories' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'categories' ) ); ?>">
	<div class="pg-container">

		<?php
		packgens_section_header(
			$data,
			array(
				'nav'    => true,
				'nav_id' => $carousel_id,
			)
		);
		?>

		<?php
		packgens_carousel_open(
			$carousel_id,
			array(
				'class'    => 'pg-categories__track',
				'label'    => __( 'Product categories', 'packgens' ),
				'loop'     => true,
				'per_view' => array( 0 => 2.2, 480 => 3.2, 768 => 4.5, 992 => 6, 1200 => 7 ),
			)
		);

		foreach ( $terms as $term ) {
			packgens_carousel_slide( 'template-parts/cards/category', null, array( 'term' => $term ) );
		}

		packgens_carousel_close();
		?>

	</div>
</section>
