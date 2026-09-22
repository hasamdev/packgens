<?php
/**
 * Predictive product search used in the header.
 *
 * Falls back to a plain GET search form when JavaScript is unavailable.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$context   = isset( $args['context'] ) ? sanitize_key( $args['context'] ) : 'header';
$field_id  = 'pg-search-' . $context;
$panel_id  = 'pg-search-results-' . $context;
$post_type = class_exists( 'WooCommerce' ) ? 'product' : 'post';
?>
<form role="search"
	method="get"
	class="pg-search pg-search--<?php echo esc_attr( $context ); ?>"
	action="<?php echo esc_url( home_url( '/' ) ); ?>"
	data-pg-search
	data-post-type="<?php echo esc_attr( $post_type ); ?>">

	<label class="pg-sr-only" for="<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Search products', 'packgens' ); ?>
	</label>

	<input type="search"
		id="<?php echo esc_attr( $field_id ); ?>"
		class="pg-search__input"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php echo esc_attr( packgens_label( 'header', 'search_placeholder', __( 'Search for Products', 'packgens' ) ) ); ?>"
		autocomplete="off"
		role="combobox"
		aria-expanded="false"
		aria-autocomplete="list"
		aria-controls="<?php echo esc_attr( $panel_id ); ?>">

	<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">

	<button type="submit" class="pg-search__submit">
		<?php packgens_icon( 'search' ); ?>
		<span class="pg-sr-only"><?php esc_html_e( 'Search', 'packgens' ); ?></span>
	</button>

	<div class="pg-search__panel" id="<?php echo esc_attr( $panel_id ); ?>" role="listbox" hidden></div>
</form>
