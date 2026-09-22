<?php
/**
 * Quote band: copy and assurances, artwork, and a contact block on the brand
 * colour.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data   = $args['data'] ?? array();
$button = packgens_link( $data['button'] ?? null );
$phone  = $data['phone'] ?? '';
$items  = array_filter( (array) ( $data['items'] ?? array() ), static function ( $item ) {
	return ! empty( $item['text'] );
} );

if ( empty( $data['title'] ) && empty( $data['image'] ) ) {
	return;
}
?>
<section class="<?php echo esc_attr( packgens_section_class( 'quote-band', 'blue' ) ); ?>">
	<div class="pg-container pg-quote-band">

		<div class="pg-quote-band__copy">
			<?php if ( ! empty( $data['title'] ) ) : ?>
				<h2 class="pg-quote-band__title"><?php echo esc_html( $data['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="pg-quote-band__desc"><?php echo esc_html( $data['description'] ); ?></p>
			<?php endif; ?>

			<?php if ( $items ) : ?>
				<ul class="pg-quote-band__points">
					<?php foreach ( $items as $item ) : ?>
						<li>
							<span class="pg-quote-band__point-icon">
								<?php packgens_icon( $item['icon'] ? $item['icon'] : 'check-circle' ); ?>
							</span>
							<span><?php echo esc_html( $item['text'] ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $data['image'] ) ) : ?>
			<div class="pg-quote-band__media">
				<?php packgens_image( $data['image'], 'packgens-card', array(), '' ); ?>
			</div>
		<?php endif; ?>

		<div class="pg-quote-band__actions">
			<?php if ( $phone ) : ?>
				<span class="pg-quote-band__mark"><?php packgens_icon( 'phone-signal' ); ?></span>

				<?php if ( ! empty( $data['phone_label'] ) ) : ?>
					<span class="pg-quote-band__phone-label"><?php echo esc_html( $data['phone_label'] ); ?></span>
				<?php endif; ?>

				<a class="pg-quote-band__phone" href="<?php echo esc_url( packgens_tel_href( $phone ) ); ?>">
					<?php echo esc_html( $phone ); ?>
				</a>
			<?php endif; ?>

			<?php if ( $button ) : ?>
				<?php packgens_button( $button, 'white', '', 'file-text' ); ?>
			<?php endif; ?>

			<?php if ( ! empty( $data['note'] ) ) : ?>
				<p class="pg-quote-band__note"><?php echo esc_html( $data['note'] ); ?></p>
			<?php endif; ?>
		</div>

	</div>
</section>
