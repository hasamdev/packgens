<?php
/**
 * The hero's foot tabs, rendered separately so a template can place them below
 * whatever else shares the band.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$context = $args['context'] ?? get_the_ID();
$hero    = packgens_field( 'hero_section', $context );
$tabs    = array_values(
	array_filter(
		(array) ( $hero['tabs'] ?? array() ),
		static function ( $tab ) {
			return ! empty( $tab['label'] );
		}
	)
);

if ( empty( $tabs ) ) {
	return;
}
?>
<div class="pg-container">
	<ul class="pg-hero__tabs">
		<?php
		foreach ( $tabs as $index => $tab ) :
			$tab_link = packgens_link( $tab['link'] ?? null );
			$tag      = $tab_link ? 'a' : 'span';
			?>
			<li>
				<<?php echo esc_attr( $tag ); ?>
					class="pg-hero__tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
					<?php echo $tab_link ? ' href="' . esc_url( $tab_link['url'] ) . '"' : ''; ?>
				><?php echo esc_html( $tab['label'] ); ?><?php echo 0 === $index ? packgens_get_icon( 'tab-caret', 'pg-hero__tab-caret' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></<?php echo esc_attr( $tag ); ?>>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
