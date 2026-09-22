<?php
/**
 * Latest blog posts, as a carousel.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$posts = packgens_get_section_posts( $data );

if ( empty( $posts ) ) {
	return;
}

$carousel_id = packgens_unique_id( 'pg-blogs' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'blogs' ) ); ?>">
	<div class="pg-container">

		<?php packgens_section_header( $data, array( 'align' => 'center', 'button' => false ) ); ?>

		<?php
		packgens_carousel_open(
			$carousel_id,
			array(
				'class'    => 'pg-blogs__track',
				'label'    => $data['title'] ?? __( 'Latest posts', 'packgens' ),
				'per_view' => array( 0 => 1.05, 576 => 1.6, 768 => 2.2, 992 => 3 ),
			)
		);

		foreach ( $posts as $post_id ) {
			packgens_carousel_slide( 'template-parts/cards/post', null, array( 'id' => $post_id ) );
		}

		packgens_carousel_close();
		?>

		<div class="pg-reviews__foot">
			<?php packgens_carousel_nav( $carousel_id ); ?>

			<?php if ( ! empty( $data['button'] ) ) : ?>
				<?php packgens_button( $data['button'], 'outline', __( 'Visit our blog', 'packgens' ), 'arrow-right' ); ?>
			<?php endif; ?>
		</div>

	</div>
</section>
