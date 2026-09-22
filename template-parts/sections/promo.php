<?php
/**
 * Promo banner: split panel with copy on the brand colour and an image beside.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$phone = $data['phone'] ?? '';

if ( empty( $data['title'] ) && empty( $data['image'] ) ) {
	return;
}
?>
<section class="<?php echo esc_attr( packgens_section_class( 'promo' ) ); ?>">
	<div class="pg-container">
		<div class="pg-promo">

			<div class="pg-promo__copy">
				<?php if ( ! empty( $data['eyebrow'] ) ) : ?>
					<p class="pg-promo__eyebrow"><?php echo esc_html( $data['eyebrow'] ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $data['title'] ) ) : ?>
					<h2 class="pg-promo__title"><?php echo esc_html( $data['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $data['description'] ) ) : ?>
					<p class="pg-promo__desc"><?php echo esc_html( $data['description'] ); ?></p>
				<?php endif; ?>

				<div class="pg-promo__actions">
					<?php if ( $phone ) : ?>
						<a class="pg-promo__phone" href="<?php echo esc_url( packgens_tel_href( $phone ) ); ?>">
							<?php packgens_icon( 'phone-signal' ); ?>
							<span class="pg-promo__phone-text">
								<?php if ( ! empty( $data['phone_label'] ) ) : ?>
									<span class="pg-promo__phone-label"><?php echo esc_html( $data['phone_label'] ); ?></span>
								<?php endif; ?>
								<span class="pg-promo__phone-number"><?php echo esc_html( $phone ); ?></span>
							</span>
						</a>
					<?php endif; ?>

					<?php if ( ! empty( $data['button'] ) ) : ?>
						<?php packgens_button( $data['button'], 'white' ); ?>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( ! empty( $data['image'] ) ) : ?>
				<div class="pg-promo__media">
					<?php packgens_image( $data['image'], 'packgens-wide', array(), '' ); ?>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
