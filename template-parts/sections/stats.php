<?php
/**
 * Statistics section: media beside a list of numbers.
 *
 * With an image the heading moves into the media column, as the design draws
 * it; without one the section keeps its ordinary centred header.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$items = array_filter( (array) ( $data['items'] ?? array() ), static function ( $item ) {
	return ! empty( $item['title'] ) || ! empty( $item['value'] );
} );

if ( empty( $items ) ) {
	return;
}

$has_media = ! empty( $data['image'] );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'stats' ) ); ?>">
	<div class="pg-container">

		<?php
		if ( ! $has_media ) {
			packgens_section_header( $data, array( 'button' => false ) );
		}
		?>

		<div class="pg-stats<?php echo $has_media ? ' pg-stats--split' : ''; ?>">

			<?php if ( $has_media ) : ?>
				<?php if ( ! empty( $data['title'] ) || ! empty( $data['description'] ) ) : ?>
					<div class="pg-stats__intro">
						<?php if ( ! empty( $data['title'] ) ) : ?>
							<h2 class="pg-stats__heading"><?php echo esc_html( $data['title'] ); ?></h2>
						<?php endif; ?>

						<?php if ( ! empty( $data['description'] ) ) : ?>
							<p class="pg-stats__lead"><?php echo esc_html( $data['description'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="pg-stats__media">
					<?php packgens_image( $data['image'], 'packgens-card', array(), '' ); ?>
				</div>
			<?php endif; ?>

			<ul class="pg-stats__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="pg-stats__item">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<span class="pg-stats__icon"><?php packgens_icon( $item['icon'] ); ?></span>
						<?php endif; ?>

						<div class="pg-stats__copy">
							<?php if ( ! empty( $item['value'] ) ) : ?>
								<span class="pg-stats__value">
									<?php echo esc_html( $item['value'] ); ?>
									<?php if ( ! empty( $item['star'] ) ) : ?>
										<?php packgens_icon( 'star-gold', 'pg-stats__star' ); ?>
									<?php endif; ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $item['title'] ) ) : ?>
								<span class="pg-stats__title"><?php echo esc_html( $item['title'] ); ?></span>
							<?php endif; ?>

							<?php if ( ! empty( $item['description'] ) ) : ?>
								<span class="pg-stats__desc"><?php echo esc_html( $item['description'] ); ?></span>
							<?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>

		</div>
	</div>
</section>
