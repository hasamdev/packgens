<?php
/**
 * The help card and the feature cards that sit under the gallery.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$product_id = absint( $args['product_id'] ?? 0 );

if ( ! $product_id ) {
	return;
}

$details  = (array) packgens_field( 'product_details', $product_id, array() );
$chat     = packgens_link( $details['help_chat'] ?? null );
$call     = packgens_link( $details['help_call'] ?? null );
$features = array_values(
	array_filter(
		(array) ( $details['features'] ?? array() ),
		static function ( $feature ) {
			return ! empty( $feature['title'] );
		}
	)
);

if ( empty( $details['help_title'] ) && empty( $features ) ) {
	return;
}

// The design reads across the two cards rather than down the first, so the
// items alternate between the columns in source order.
$columns = array( array(), array() );

foreach ( $features as $index => $feature ) {
	$columns[ $index % 2 ][] = $feature;
}

$columns = array_filter( $columns );
?>
<div class="pg-product-support">
<?php if ( ! empty( $details['help_title'] ) ) : ?>
	<div class="pg-product-help">
		<?php if ( ! empty( $details['help_photo'] ) ) : ?>
			<span class="pg-product-help__photo">
				<?php packgens_image( $details['help_photo'], 'packgens-thumb', array(), '' ); ?>
			</span>
		<?php endif; ?>

		<span class="pg-product-help__copy">
			<span class="pg-product-help__title"><?php echo esc_html( $details['help_title'] ); ?></span>
			<?php if ( ! empty( $details['help_text'] ) ) : ?>
				<span class="pg-product-help__text"><?php echo esc_html( $details['help_text'] ); ?></span>
			<?php endif; ?>
		</span>

		<?php if ( $chat || $call ) : ?>
			<span class="pg-product-help__actions">
				<?php if ( $chat ) : ?>
					<a class="pg-product-help__action pg-product-help__action--chat" href="<?php echo esc_url( $chat['url'] ); ?>">
						<span class="pg-product-help__mark"><?php packgens_icon( 'message-circle' ); ?></span>
						<span><?php echo esc_html( $chat['title'] ); ?></span>
					</a>
				<?php endif; ?>

				<?php if ( $call ) : ?>
					<a class="pg-product-help__action pg-product-help__action--call" href="<?php echo esc_url( $call['url'] ); ?>">
						<span class="pg-product-help__mark"><?php packgens_icon( 'phone' ); ?></span>
						<span><?php echo esc_html( $call['title'] ); ?></span>
					</a>
				<?php endif; ?>
			</span>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( $columns ) : ?>
	<div class="pg-product-features">
		<?php foreach ( $columns as $column ) : ?>
			<ul class="pg-product-features__card">
				<?php foreach ( $column as $feature ) : ?>
					<li>
						<?php packgens_icon( ! empty( $feature['icon'] ) ? $feature['icon'] : 'check-circle' ); ?>
						<span><?php echo esc_html( $feature['title'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
</div>
