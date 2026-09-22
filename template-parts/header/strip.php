<?php
/**
 * Trust strip under the header.
 *
 * Only for views that have no hero: with a hero, the hero carries its own
 * strip across its bottom edge and two rows would collide.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_page() || packgens_view_has_hero() || ! packgens_section_enabled( 'header_strip', 'enable', 'option' ) ) {
	return;
}

$items = array_filter(
	(array) packgens_global( 'header_strip', 'items', array() ),
	static function ( $item ) {
		return ! empty( $item['title'] );
	}
);

if ( ! $items ) {
	return;
}
?>
<div class="pg-container">
	<ul class="pg-header__strip">
		<?php
		foreach ( $items as $item ) :
			$link = packgens_link( $item['link'] ?? null );
			$tag  = $link ? 'a' : 'span';
			?>
			<li class="pg-header__strip-item">
				<<?php echo esc_attr( $tag ); ?> class="pg-header__strip-link"<?php echo $link ? ' href="' . esc_url( $link['url'] ) . '"' : ''; ?>>
					<span class="pg-usp__icon"><?php packgens_icon( $item['icon'] ? $item['icon'] : 'check-circle' ); ?></span>
					<span class="pg-usp__title"><?php echo esc_html( $item['title'] ); ?></span>
				</<?php echo esc_attr( $tag ); ?>>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
