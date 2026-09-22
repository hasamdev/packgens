<?php
/**
 * Product card.
 *
 * Works with a WooCommerce product or, when WooCommerce is inactive, with any
 * post ID so the layout still renders during development.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$product_id = absint( $args['id'] ?? 0 );

if ( ! $product_id ) {
	return;
}

$show_price = ! isset( $args['show_price'] ) || $args['show_price'];
$show_badge = ! isset( $args['show_badge'] ) || $args['show_badge'];
$cta_label  = $args['cta'] ?? __( 'Customize Now', 'packgens' );
$permalink  = get_permalink( $product_id );
$title      = get_the_title( $product_id );
$badge      = packgens_group_field( 'product_details', 'badge', $product_id );
$excerpt    = has_excerpt( $product_id )
	? get_the_excerpt( $product_id )
	: packgens_trim_words( get_post_field( 'post_content', $product_id ), 18 );

$price_html = '';

if ( $show_price && function_exists( 'wc_get_product' ) ) {
	$product = wc_get_product( $product_id );

	if ( $product && '' !== $product->get_price() ) {
		$unit       = packgens_group_field( 'product_details', 'price_unit', $product_id, __( '/ unit', 'packgens' ) );
		$price_html = sprintf(
			/* translators: 1: formatted price, 2: unit label. */
			__( 'From %1$s %2$s', 'packgens' ),
			'<strong>' . wp_kses_post( wc_price( $product->get_price() ) ) . '</strong>',
			esc_html( $unit )
		);
	}
}
?>
<article class="pg-card pg-product-card">

	<a class="pg-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail( $product_id ) ) {
			echo get_the_post_thumbnail( $product_id, 'packgens-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		} else {
			echo '<span class="pg-card__placeholder">' . packgens_get_icon( 'package' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<?php if ( $badge && $show_badge ) : ?>
			<span class="pg-badge pg-badge--floating"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
	</a>

	<?php if ( function_exists( 'packgens_wishlist_button' ) ) : ?>
		<?php echo packgens_wishlist_button( $product_id, 'pg-wishlist-btn--card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>

	<div class="pg-card__body">
		<h3 class="pg-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>

		<?php if ( $excerpt ) : ?>
			<p class="pg-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>

		<div class="pg-card__footer">
			<?php if ( $price_html ) : ?>
				<span class="pg-card__price"><?php echo wp_kses_post( $price_html ); ?></span>
			<?php endif; ?>

			<a class="pg-btn pg-btn--ghost" href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( $cta_label ); ?>
				<?php packgens_icon( 'plus', 'pg-btn__icon' ); ?>
			</a>
		</div>
	</div>

</article>
