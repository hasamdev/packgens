<?php
/**
 * Product summary column: title, rating strip, offer, price, actions and the
 * quote panel.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;

if ( ! $product instanceof WC_Product ) {
	return;
}

$product_id  = $product->get_id();
$details     = (array) packgens_field( 'product_details', $product_id, array() );
$price_label = $details['price_label'] ?? '';
$unit        = $details['price_unit'] ?? '';
$delivery    = $details['delivery_label'] ?? '';
$coupon      = $details['coupon_title'] ?? '';
$rating      = (float) ( $details['rating_value'] ?? 0 );
?>
<div class="pg-product__summary">

	<h1 class="pg-product__title"><?php the_title(); ?></h1>

	<?php if ( $rating > 0 || ! empty( $details['rating_label'] ) ) : ?>
		<p class="pg-product__rating"<?php echo ! empty( $details['rating_platform'] ) ? ' data-pg-platform="' . esc_attr( $details['rating_platform'] ) . '"' : ''; ?>>
			<?php if ( ! empty( $details['rating_label'] ) ) : ?>
				<span class="pg-product__rating-label"><?php echo esc_html( $details['rating_label'] ); ?></span>
			<?php endif; ?>

			<?php if ( $rating > 0 ) : ?>
				<?php packgens_stars( $rating ); ?>
				<span class="pg-product__rating-score"><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $details['rating_count'] ) ) : ?>
				<span class="pg-product__rating-count"><?php echo esc_html( $details['rating_count'] ); ?></span>
			<?php endif; ?>

			<?php if ( ! empty( $details['rating_platform'] ) ) : ?>
				<span class="pg-product__rating-platform">
					<?php packgens_icon( $details['rating_platform'] ); ?>
					<?php if ( ! empty( $details['rating_platform_name'] ) ) : ?>
						<?php echo esc_html( $details['rating_platform_name'] ); ?>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( $coupon ) : ?>
		<div class="pg-product__offer">
			<?php packgens_icon( 'ticket-percent', 'pg-product__offer-mark' ); ?>

			<span class="pg-product__offer-copy">
				<span class="pg-product__offer-title"><?php echo esc_html( $coupon ); ?></span>
				<?php if ( ! empty( $details['coupon_note'] ) ) : ?>
					<span class="pg-product__offer-note"><?php echo esc_html( $details['coupon_note'] ); ?></span>
				<?php endif; ?>
			</span>

			<?php if ( ! empty( $details['coupon_code'] ) ) : ?>
				<button type="button" class="pg-product__offer-copy-btn"
					data-pg-copy="<?php echo esc_attr( $details['coupon_code'] ); ?>">
					<?php packgens_icon( 'copy' ); ?>
					<span class="pg-sr-only">
						<?php
						printf(
							/* translators: %s: discount code. */
							esc_html__( 'Copy code %s', 'packgens' ),
							esc_html( $details['coupon_code'] )
						);
						?>
					</span>
				</button>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $product->get_short_description() ) : ?>
		<div class="pg-product__excerpt"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
	<?php endif; ?>

	<div class="pg-product__buy">

		<?php if ( '' !== $product->get_price() ) : ?>
		<p class="pg-product__price">
				<?php if ( $price_label ) : ?>
					<span class="pg-product__price-label"><?php echo esc_html( $price_label ); ?></span>
				<?php endif; ?>
				<span class="pg-product__price-amount"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
				<?php if ( $unit ) : ?>
					<span class="pg-product__price-unit"><?php echo esc_html( $unit ); ?></span>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<div class="pg-product__actions">
		<?php
		/**
		 * Add to cart form, plus anything plugins add to the summary.
		 */
		do_action( 'woocommerce_single_product_summary' );
		?>

		<?php if ( $delivery ) : ?>
			<span class="pg-btn pg-btn--outline pg-product__delivery"><?php echo esc_html( $delivery ); ?></span>
		<?php endif; ?>

		<?php if ( function_exists( 'packgens_wishlist_button' ) ) : ?>
			<?php echo packgens_wishlist_button( get_the_ID(), 'pg-wishlist-btn--inline' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
		</div>

	</div>

	<div class="pg-product__quote">
		<?php
		get_template_part(
			'template-parts/forms/quote',
			null,
			array(
				'variant' => 'panel',
				// The design labels every field here, unlike the category hero.
				'labels'  => true,
				'title'   => packgens_global( 'quote_form', 'title', __( 'Get Instant Quote', 'packgens' ) ),
			)
		);
		?>
	</div>

</div>
