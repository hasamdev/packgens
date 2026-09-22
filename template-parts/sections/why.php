<?php
/**
 * Why choose us: copy and feature grid on the left, stacked media on the right.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$items = array_filter( (array) ( $data['items'] ?? array() ), static function ( $item ) {
	return ! empty( $item['title'] );
} );
$media = array_filter( array_map( 'absint', (array) ( $data['media'] ?? array() ) ) );

if ( empty( $items ) ) {
	return;
}
?>
<section class="<?php echo esc_attr( packgens_section_class( 'why' ) ); ?>">
	<div class="pg-container pg-why">

		<div class="pg-why__main">
			<?php if ( ! empty( $data['eyebrow'] ) ) : ?>
				<span class="pg-badge pg-badge--brand"><?php echo esc_html( $data['eyebrow'] ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $data['title'] ) ) : ?>
				<h2 class="pg-why__title"><?php echo esc_html( $data['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="pg-why__desc"><?php echo esc_html( $data['description'] ); ?></p>
			<?php endif; ?>

			<ul class="pg-why__grid">
				<?php foreach ( $items as $item ) : ?>
					<li class="pg-why__item">
						<span class="pg-why__icon">
							<?php
							if ( ! empty( $item['image'] ) ) {
								packgens_image( $item['image'], 'packgens-thumb', array(), '' );
							} elseif ( ! empty( $item['icon'] ) ) {
								packgens_icon( $item['icon'] );
							} else {
								packgens_icon( 'check-circle' );
							}
							?>
						</span>
						<span class="pg-why__copy">
							<span class="pg-why__item-title"><?php echo esc_html( $item['title'] ); ?></span>
							<?php if ( ! empty( $item['description'] ) ) : ?>
								<span class="pg-why__item-desc"><?php echo esc_html( $item['description'] ); ?></span>
							<?php endif; ?>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php if ( $media ) : ?>
			<div class="pg-why__media">
				<div class="pg-why__card">
					<?php foreach ( $media as $image_id ) : ?>
						<figure class="pg-why__figure"><?php packgens_image( $image_id, 'packgens-card', array(), '' ); ?></figure>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
