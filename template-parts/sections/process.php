<?php
/**
 * Process section: a numbered step list that switches the illustration beside it.
 *
 * The steps are an ARIA tablist and the illustrations are its panels, so the
 * shared tabs module drives the whole thing without a section-specific script.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$items = array_values(
	array_filter(
		(array) ( $data['items'] ?? array() ),
		static function ( $item ) {
			return ! empty( $item['title'] );
		}
	)
);

if ( empty( $items ) ) {
	return;
}

$base     = packgens_unique_id( 'pg-process' );
$fallback = $data['image'] ?? 0;

// Built-in step icons, used when an item has no icon image of its own.
$icons = array( 'step-style', 'step-artwork', 'step-print' );

$cta_button = packgens_link( $data['cta_button'] ?? null );
$cta_phone  = $data['cta_phone'] ?? '';
$has_cta    = ! empty( $data['cta_title'] ) || $cta_button || $cta_phone;
// On a category page the steps sit on white, as the why-choose-us block does.
$surface = ( 'product_cat' === ( $args['view'] ?? '' ) ) ? '' : 'cream';
?>
<section class="<?php echo esc_attr( packgens_section_class( 'process', $surface ) ); ?>">
	<div class="pg-container">

		<?php packgens_section_header( $data, array( 'align' => 'center', 'button' => false ) ); ?>

		<div class="pg-process-wrap">
			<div class="pg-process">
				<div class="pg-process__media">
					<?php foreach ( $items as $index => $item ) : ?>
						<?php $photo = ! empty( $item['photo'] ) ? $item['photo'] : $fallback; ?>
						<div
							class="pg-process__panel"
							id="<?php echo esc_attr( $base . '-panel-' . $index ); ?>"
							role="tabpanel"
							aria-labelledby="<?php echo esc_attr( $base . '-tab-' . $index ); ?>"
							<?php echo 0 === $index ? '' : 'hidden'; ?>
						>
							<?php if ( $photo ) : ?>
								<?php packgens_image( $photo, 'packgens-card', array(), '' ); ?>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="pg-process__steps" role="tablist" aria-orientation="vertical" aria-label="<?php echo esc_attr( $data['title'] ?? __( 'Steps', 'packgens' ) ); ?>">
					<?php foreach ( $items as $index => $item ) : ?>
						<button
							type="button"
							class="pg-process__step"
							id="<?php echo esc_attr( $base . '-tab-' . $index ); ?>"
							role="tab"
							aria-controls="<?php echo esc_attr( $base . '-panel-' . $index ); ?>"
							aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
							tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
						>
							<span class="pg-process__number"><?php echo esc_html( number_format_i18n( $index + 1 ) ); ?></span>

							<span class="pg-process__icon" data-pg-step="<?php echo esc_attr( $index % 3 ); ?>">
								<?php
								if ( ! empty( $item['image'] ) ) {
									packgens_image( $item['image'], 'packgens-thumb', array(), '' );
								} else {
									packgens_icon( $icons[ $index % 3 ] );
								}
								?>
							</span>

							<span class="pg-process__copy">
								<span class="pg-process__title"><?php echo esc_html( $item['title'] ); ?></span>
								<?php if ( ! empty( $item['description'] ) ) : ?>
									<span class="pg-process__desc"><?php echo esc_html( $item['description'] ); ?></span>
								<?php endif; ?>
							</span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $has_cta ) : ?>
				<div class="pg-process__cta">
					<div class="pg-process__cta-copy">
						<?php if ( ! empty( $data['cta_title'] ) ) : ?>
							<p class="pg-process__cta-title"><?php echo esc_html( $data['cta_title'] ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $data['cta_description'] ) ) : ?>
							<p class="pg-process__cta-desc"><?php echo esc_html( $data['cta_description'] ); ?></p>
						<?php endif; ?>
					</div>

					<div class="pg-process__cta-actions">
						<?php if ( $cta_phone ) : ?>
							<a class="pg-promo__phone" href="<?php echo esc_url( packgens_tel_href( $cta_phone ) ); ?>">
								<?php packgens_icon( 'phone-signal' ); ?>
								<span class="pg-promo__phone-text">
									<?php if ( ! empty( $data['cta_phone_label'] ) ) : ?>
										<span class="pg-promo__phone-label"><?php echo esc_html( $data['cta_phone_label'] ); ?></span>
									<?php endif; ?>
									<span class="pg-promo__phone-number"><?php echo esc_html( $cta_phone ); ?></span>
								</span>
							</a>
						<?php endif; ?>

						<?php if ( $cta_button ) : ?>
							<?php packgens_button( $cta_button, 'white' ); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>

	</div>
</section>
