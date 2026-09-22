<?php
/**
 * Trust strip shown above the footer on every page.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

if ( ! packgens_section_enabled( 'usp_strip', 'enable', 'option' ) ) {
	return;
}

$items = array_filter( (array) packgens_global( 'usp_strip', 'items', array() ), static function ( $item ) {
	return ! empty( $item['title'] );
} );

if ( empty( $items ) ) {
	return;
}
?>
<section class="pg-section pg-section--blue pg-section--usp">
	<div class="pg-container">
		<ul class="pg-usp">
			<?php
			foreach ( $items as $item ) :
				$link = packgens_link( $item['link'] ?? null );
				$tag  = $link ? 'a' : 'div';
				?>
				<li class="pg-usp__item">
					<<?php echo esc_attr( $tag ); ?> class="pg-usp__inner"<?php echo $link ? ' href="' . esc_url( $link['url'] ) . '"' : ''; ?>>
						<span class="pg-usp__icon"><?php packgens_icon( $item['icon'] ? $item['icon'] : 'shield-check' ); ?></span>
						<span class="pg-usp__copy">
							<span class="pg-usp__title"><?php echo esc_html( $item['title'] ); ?></span>
							<?php if ( ! empty( $item['text'] ) ) : ?>
								<span class="pg-usp__text"><?php echo esc_html( $item['text'] ); ?></span>
							<?php endif; ?>
						</span>
					</<?php echo esc_attr( $tag ); ?>>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
