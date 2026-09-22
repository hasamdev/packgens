<?php
/**
 * Long-form SEO content, in one or two columns on a raised card.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$data      = $args['data'] ?? array();
$primary   = $data['content'] ?? '';
$secondary = $data['content_secondary'] ?? '';
$columns   = ( '2' === (string) ( $data['columns'] ?? '2' ) && $secondary ) ? 2 : 1;

if ( ! $primary && ! $secondary ) {
	return;
}
?>
<section class="<?php echo esc_attr( packgens_section_class( 'content' ) ); ?>">
	<div class="pg-container">

		<div class="pg-longform">
			<div class="pg-longform__inner pg-longform__inner--cols-<?php echo esc_attr( $columns ); ?>">
				<?php if ( $primary ) : ?>
					<div class="pg-prose"><?php echo wp_kses_post( $primary ); ?></div>
				<?php endif; ?>

				<?php if ( 2 === $columns ) : ?>
					<div class="pg-prose"><?php echo wp_kses_post( $secondary ); ?></div>
				<?php endif; ?>
			</div>
		</div>

	</div>
</section>
