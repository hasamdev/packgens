<?php
/**
 * Trusted brand logos.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data  = $args['data'] ?? array();
$logos = array_filter( array_map( 'absint', (array) ( $data['logos'] ?? array() ) ) );

if ( empty( $logos ) ) {
	return;
}
?>
<section class="<?php echo esc_attr( packgens_section_class( 'brands' ) ); ?> pg-section--tight">
	<div class="pg-container pg-brands">

		<?php if ( ! empty( $data['title'] ) || ! empty( $data['description'] ) ) : ?>
			<div class="pg-brands__copy">
				<?php if ( ! empty( $data['title'] ) ) : ?>
					<h2 class="pg-brands__title"><?php echo esc_html( $data['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $data['description'] ) ) : ?>
					<p class="pg-brands__desc"><?php echo esc_html( $data['description'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<ul class="pg-brands__list">
			<?php foreach ( $logos as $logo_id ) : ?>
				<li><?php packgens_image( $logo_id, 'medium', array(), '' ); ?></li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
