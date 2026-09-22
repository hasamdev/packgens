<?php
/**
 * Industries carousel on a green band.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$items = array_filter( (array) ( $data['items'] ?? array() ), static function ( $item ) {
	return ! empty( $item['title'] ) || ! empty( $item['image'] );
} );

if ( empty( $items ) ) {
	return;
}

$carousel_id = packgens_unique_id( 'pg-industries' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'industries', 'green' ) ); ?>">
	<div class="pg-container">

		<?php packgens_section_header( $data, array( 'align' => 'center', 'button' => false ) ); ?>

		<?php
		packgens_carousel_open(
			$carousel_id,
			array(
				'class'    => 'pg-industries__track',
				'bleed'    => true,
				'label'    => $data['title'] ?? __( 'Industries', 'packgens' ),
				'per_view' => array( 0 => 1.15, 576 => 2.1, 768 => 3.1, 992 => 3.8, 1200 => 4.5 ),
			)
		);

		foreach ( $items as $item ) {
			packgens_carousel_slide( 'template-parts/cards/industry', null, array( 'item' => $item ) );
		}

		packgens_carousel_close();
		?>

		<div class="pg-reviews__foot">
			<?php packgens_carousel_nav( $carousel_id ); ?>

			<?php if ( ! empty( $data['button'] ) ) : ?>
				<?php packgens_button( $data['button'], 'white', __( 'View all', 'packgens' ), 'arrow-right' ); ?>
			<?php endif; ?>
		</div>

	</div>
</section>
