<?php
/**
 * Product gallery: main stage, arrows, zoom and a thumbnail rail.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;
$badge   = $args['badge'] ?? '';

if ( ! $product instanceof WC_Product ) {
	return;
}

$image_ids = array();

if ( $product->get_image_id() ) {
	$image_ids[] = (int) $product->get_image_id();
}

foreach ( $product->get_gallery_image_ids() as $gallery_id ) {
	$image_ids[] = (int) $gallery_id;
}

$image_ids = array_values( array_unique( array_filter( $image_ids ) ) );
?>
<div class="pg-gallery" data-pg-gallery data-index="0">

	<div class="pg-gallery__stage" data-pg-gallery-stage>
		<?php if ( $image_ids ) : ?>
			<?php
			echo wp_get_attachment_image(
				$image_ids[0],
				'packgens-media',
				false,
				array(
					'class'         => 'pg-gallery__image',
					'loading'       => 'eager',
					'fetchpriority' => 'high',
					'alt'           => esc_attr( $product->get_name() ),
				)
			);
			?>
		<?php else : ?>
			<div class="pg-gallery__placeholder"><?php packgens_icon( 'package' ); ?></div>
		<?php endif; ?>

		<?php if ( $badge ) : ?>
			<span class="pg-badge pg-badge--floating"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>

		<?php if ( count( $image_ids ) > 1 ) : ?>
			<button type="button" class="pg-icon-btn pg-gallery__nav pg-gallery__nav--prev" data-pg-gallery-prev>
				<?php packgens_icon( 'chevron-left' ); ?>
				<span class="pg-sr-only"><?php esc_html_e( 'Previous image', 'packgens' ); ?></span>
			</button>
			<button type="button" class="pg-icon-btn pg-gallery__nav pg-gallery__nav--next" data-pg-gallery-next>
				<?php packgens_icon( 'chevron-right' ); ?>
				<span class="pg-sr-only"><?php esc_html_e( 'Next image', 'packgens' ); ?></span>
			</button>
		<?php endif; ?>

		<?php if ( $image_ids ) : ?>
			<button type="button" class="pg-icon-btn pg-gallery__zoom" data-pg-gallery-zoom>
				<?php packgens_icon( 'expand' ); ?>
				<span class="pg-sr-only"><?php esc_html_e( 'View larger image', 'packgens' ); ?></span>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( count( $image_ids ) > 1 ) : ?>
		<div class="pg-gallery__thumbs pg-scroller">
			<?php
			foreach ( $image_ids as $index => $image_id ) :
				$full = wp_get_attachment_image_url( $image_id, 'packgens-media' );
				$alt  = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
				?>
				<button type="button"
					class="pg-gallery__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-pg-gallery-thumb
					data-full="<?php echo esc_url( $full ); ?>"
					data-srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $image_id, 'packgens-media' ) ); ?>"
					data-alt="<?php echo esc_attr( $alt ? $alt : $product->get_name() ); ?>"
					aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>">
					<?php echo wp_get_attachment_image( $image_id, 'packgens-thumb', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?>
					<span class="pg-sr-only">
						<?php
						printf(
							/* translators: %d: image number. */
							esc_html__( 'Show image %d', 'packgens' ),
							(int) $index + 1
						);
						?>
					</span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

</div>
