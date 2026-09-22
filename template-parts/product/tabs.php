<?php
/**
 * Product detail tabs.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$tabs = $args['tabs'] ?? array();

if ( empty( $tabs ) ) {
	return;
}

$base = packgens_unique_id( 'pg-ptabs' );
?>
<div class="pg-tabs pg-product-tabs" data-pg-tabs>

	<div class="pg-tabs__list" role="tablist" aria-label="<?php esc_attr_e( 'Product details', 'packgens' ); ?>">
		<?php foreach ( array_values( $tabs ) as $index => $tab ) : ?>
			<button type="button"
				class="pg-tabs__tab"
				role="tab"
				id="<?php echo esc_attr( $base . '-tab-' . $index ); ?>"
				aria-controls="<?php echo esc_attr( $base . '-panel-' . $index ); ?>"
				aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
				tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>">
				<?php if ( ! empty( $tab['icon'] ) ) : ?>
					<?php packgens_icon( $tab['icon'] ); ?>
				<?php endif; ?>
				<?php echo esc_html( $tab['title'] ); ?>
			</button>
		<?php endforeach; ?>
	</div>

	<?php foreach ( array_values( $tabs ) as $index => $tab ) : ?>
		<div class="pg-tabs__panel"
			role="tabpanel"
			id="<?php echo esc_attr( $base . '-panel-' . $index ); ?>"
			aria-labelledby="<?php echo esc_attr( $base . '-tab-' . $index ); ?>"
			tabindex="0"
			<?php echo 0 === $index ? '' : 'hidden'; ?>>
			<div class="pg-prose"><?php echo wp_kses_post( $tab['content'] ); ?></div>
		</div>
	<?php endforeach; ?>

</div>
