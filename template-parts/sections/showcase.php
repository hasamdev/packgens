<?php
/**
 * Showcase row.
 *
 * A drifting row of product images with their labels. Hovering or focusing an
 * item lifts a card over it carrying the copy and a button, which is the state
 * the design shows on the second slide.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$items = array_values( array_filter( (array) ( $data['items'] ?? array() ), static function ( $item ) {
	return ! empty( $item['image'] ) || ! empty( $item['title'] );
} ) );

if ( empty( $items ) ) {
	return;
}

$carousel_id = packgens_unique_id( 'pg-showcase' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'showcase', 'product' === ( $args['view'] ?? '' ) ? '' : 'gray' ) ); ?>">
	<div class="pg-container">

		<?php packgens_section_header( $data, array( 'align' => 'center', 'button' => false ) ); ?>

		<?php
		packgens_carousel_open(
			$carousel_id,
			array(
				'class'    => 'pg-showcase__track',
				'label'    => $data['title'] ?? __( 'Showcase', 'packgens' ),
				'auto'     => 'forward',
				'per_view' => array( 0 => 1.4, 576 => 2.4, 768 => 3.4, 992 => 4.4, 1200 => 5 ),
			)
		);

		foreach ( $items as $item ) :
			$link = packgens_link( $item['link'] ?? null );
			$tag  = $link ? 'a' : 'div';
			?>
			<div class="swiper-slide">
				<<?php echo esc_attr( $tag ); ?> class="pg-showcase__item"<?php echo $link ? ' href="' . esc_url( $link['url'] ) . '"' : ''; ?>>
					<span class="pg-showcase__face">
						<?php if ( ! empty( $item['image'] ) ) : ?>
							<span class="pg-showcase__media">
								<?php echo wp_get_attachment_image( (int) $item['image'], 'packgens-card', false, array( 'loading' => 'lazy' ) ); ?>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $item['title'] ) ) : ?>
							<span class="pg-showcase__title"><?php echo esc_html( $item['title'] ); ?></span>
						<?php endif; ?>
					</span>

					<?php if ( ! empty( $item['text'] ) || $link ) : ?>
						<span class="pg-showcase__reveal" aria-hidden="true">
							<?php if ( ! empty( $item['text'] ) ) : ?>
								<span class="pg-showcase__reveal-text"><?php echo esc_html( $item['text'] ); ?></span>
							<?php endif; ?>

							<?php if ( $link ) : ?>
								<span class="pg-btn pg-btn--primary">
									<?php echo esc_html( $link['title'] ); ?>
									<?php packgens_icon( 'chevrons-right', 'pg-btn__icon' ); ?>
								</span>
							<?php endif; ?>
						</span>
					<?php endif; ?>
				</<?php echo esc_attr( $tag ); ?>>
			</div>
			<?php
		endforeach;

		packgens_carousel_close( $carousel_id, 'pg-carousel__nav--sides' );
		?>

	</div>
</section>
