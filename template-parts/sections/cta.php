<?php
/**
 * Call to action band.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$style = $data['style'] ?? 'blue';
$phone = $data['phone'] ?? packgens_get_option( 'phone' );
$chat  = packgens_whatsapp_href( $phone );
$href  = $chat ? $chat : packgens_tel_href( $phone );

$image = (int) ( $data['image'] ?? 0 );

$surface = array(
	'blue'  => 'blue',
	'green' => 'green',
	'dark'  => 'navy',
	'black' => 'black',
);

if ( empty( $data['title'] ) && empty( $data['button'] ) ) {
	return;
}

// With artwork the band runs edge to edge and sets its own height, so the
// section's vertical padding would only fight it.
$extra = $image ? 'pg-section--has-media' : 'pg-section--tight';
?>
<section class="<?php echo esc_attr( packgens_section_class( 'cta', $surface[ $style ] ?? 'blue', $extra ) ); ?>">
	<div class="pg-container pg-cta<?php echo $image ? ' pg-cta--media' : ''; ?>">

		<?php if ( $image ) : ?>
			<figure class="pg-cta__media">
				<?php packgens_image( $image, 'packgens-card', array( 'loading' => 'lazy' ), '' ); ?>
			</figure>
		<?php endif; ?>

		<?php if ( $image ) : ?><div class="pg-cta__panel"><?php endif; ?>

		<div class="pg-cta__copy">
			<?php if ( ! empty( $data['title'] ) ) : ?>
				<h2 class="pg-cta__title"><?php echo esc_html( $data['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="pg-cta__desc"><?php echo esc_html( $data['description'] ); ?></p>
			<?php endif; ?>
		</div>

		<div class="pg-cta__actions">
			<?php if ( $phone && $href ) : ?>
				<a class="pg-cta__phone" href="<?php echo esc_url( $href ); ?>"<?php echo $chat ? ' target="_blank" rel="noopener"' : ''; ?>>
					<?php if ( ! empty( $data['phone_label'] ) ) : ?>
						<span class="pg-cta__phone-label"><?php echo esc_html( $data['phone_label'] ); ?></span>
					<?php endif; ?>
					<span class="pg-cta__phone-number">
						<?php if ( $chat ) : ?>
							<?php packgens_icon( 'whatsapp' ); ?>
						<?php endif; ?>
						<?php echo esc_html( $phone ); ?>
						<?php if ( $chat ) : ?>
							<span class="pg-sr-only"><?php esc_html_e( '(chat on WhatsApp, opens in a new tab)', 'packgens' ); ?></span>
						<?php endif; ?>
					</span>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $data['button'] ) ) : ?>
				<?php packgens_button( $data['button'], $image ? 'dark' : 'white' ); ?>
			<?php endif; ?>
		</div>

		<?php if ( $image ) : ?></div><?php endif; ?>

	</div>
</section>
