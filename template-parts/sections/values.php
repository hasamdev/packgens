<?php
/**
 * Mission / vision / values: a tabbed block with copy beside an image.
 *
 * Uses the shared ARIA tabs module, so no section-specific script.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$items = array_values(
	array_filter(
		(array) ( $data['items'] ?? array() ),
		static function ( $item ) {
			return ! empty( $item['label'] ) || ! empty( $item['title'] );
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$base = packgens_unique_id( 'pg-values' );
?>
<section class="<?php echo esc_attr( packgens_section_class( 'values' ) ); ?>">
	<div class="pg-container">

		<div class="pg-values__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Mission, vision and values', 'packgens' ); ?>">
			<?php foreach ( $items as $index => $item ) : ?>
				<button
					type="button"
					class="pg-values__tab"
					id="<?php echo esc_attr( $base . '-tab-' . $index ); ?>"
					role="tab"
					aria-controls="<?php echo esc_attr( $base . '-panel-' . $index ); ?>"
					aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
				>
					<?php if ( ! empty( $item['icon'] ) ) : ?>
						<?php packgens_icon( $item['icon'] ); ?>
					<?php endif; ?>
					<?php echo esc_html( $item['label'] ?? $item['title'] ); ?>
				</button>
			<?php endforeach; ?>
		</div>

		<?php foreach ( $items as $index => $item ) : ?>
			<?php
			$points = array_filter( (array) ( $item['points'] ?? array() ), static function ( $point ) {
				return ! empty( $point['text'] );
			} );
			?>
			<div
				class="pg-values__panel"
				id="<?php echo esc_attr( $base . '-panel-' . $index ); ?>"
				role="tabpanel"
				aria-labelledby="<?php echo esc_attr( $base . '-tab-' . $index ); ?>"
				<?php echo 0 === $index ? '' : 'hidden'; ?>
			>
				<div class="pg-values__copy">
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<h2 class="pg-values__title"><?php echo esc_html( $item['title'] ); ?></h2>
					<?php endif; ?>

					<?php if ( ! empty( $item['description'] ) ) : ?>
						<p class="pg-values__desc"><?php echo esc_html( $item['description'] ); ?></p>
					<?php endif; ?>

					<?php if ( $points ) : ?>
						<ul class="pg-checklist">
							<?php foreach ( $points as $point ) : ?>
								<li>
									<?php packgens_icon( 'check-circle' ); ?>
									<span><?php echo esc_html( $point['text'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $item['image'] ) ) : ?>
					<figure class="pg-values__media">
						<?php packgens_image( $item['image'], 'packgens-card', array(), $item['title'] ?? '' ); ?>
					</figure>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>

	</div>
</section>
