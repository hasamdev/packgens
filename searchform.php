<?php
/**
 * Default search form.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$field_id = packgens_unique_id( 'pg-searchform' );
?>
<form role="search" method="get" class="pg-search pg-search--inline" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="pg-sr-only" for="<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Search', 'packgens' ); ?>
	</label>
	<input type="search"
		id="<?php echo esc_attr( $field_id ); ?>"
		class="pg-search__input"
		name="s"
		value="<?php echo esc_attr( get_search_query() ); ?>"
		placeholder="<?php echo esc_attr( packgens_label( 'header', 'site_search_placeholder', __( 'Search&hellip;', 'packgens' ) ) ); ?>">
	<button type="submit" class="pg-search__submit">
		<?php packgens_icon( 'search' ); ?>
		<span class="pg-sr-only"><?php esc_html_e( 'Search', 'packgens' ); ?></span>
	</button>
</form>
