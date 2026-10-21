<?php
/**
 * Product summary column: title, review average, price, actions and the
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
$details     = packgens_product_details( $product_id );
$price_label = $details['price_label'] ?? '';
$unit        = $details['price_unit'] ?? '';
$delivery    = $details['delivery_label'] ?? '';
// Worked out from the product's approved reviews, so it needs no editing.
$review_count = wc_review_ratings_enabled() ? (int) $product->get_review_count() : 0;
$average      = (float) $product->get_average_rating();
?>
<div class="pg-product__summary">

	<h1 class="pg-product__title"><?php the_title(); ?></h1>

	<?php if ( $review_count ) : ?>
		<p class="pg-product__rating">
			<?php packgens_stars( round( $average * 2 ) / 2 ); // Nearest half, so 4.1 reads as four stars. ?>
			<span class="pg-product__rating-score"><?php echo esc_html( number_format_i18n( $average, 1 ) ); ?></span>
			<a class="pg-product__rating-count" href="#reviews"
				aria-label="<?php echo esc_attr( sprintf( /* translators: %s: number of reviews. */ _n( 'Read %s review', 'Read all %s reviews', $review_count, 'packgens' ), number_format_i18n( $review_count ) ) ); ?>">(<?php echo esc_html( number_format_i18n( $review_count ) ); ?>)</a>
		</p>
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
