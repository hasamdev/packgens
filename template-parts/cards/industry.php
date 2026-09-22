<?php
/**
 * Industry card, used by the industries carousel.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$item = $args['item'] ?? array();

if ( empty( $item['title'] ) && empty( $item['image'] ) ) {
	return;
}

$link = packgens_link( $item['button'] ?? null, __( 'Learn More', 'packgens' ) );
?>
<article class="pg-card pg-industry-card">

	<?php if ( ! empty( $item['image'] ) ) : ?>
		<div class="pg-card__media">
			<?php packgens_image( $item['image'], 'packgens-card', array(), $item['title'] ?? '' ); ?>
		</div>
	<?php endif; ?>

	<div class="pg-card__body">
		<?php if ( ! empty( $item['title'] ) ) : ?>
			<h3 class="pg-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
		<?php endif; ?>

		<?php if ( ! empty( $item['description'] ) ) : ?>
			<p class="pg-card__excerpt"><?php echo esc_html( $item['description'] ); ?></p>
		<?php endif; ?>

		<?php if ( $link ) : ?>
			<div class="pg-card__footer">
				<a class="pg-btn pg-btn--ghost" href="<?php echo esc_url( $link['url'] ); ?>">
					<?php echo esc_html( $link['title'] ); ?>
					<?php packgens_icon( 'plus', 'pg-btn__icon' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

</article>
