<?php
/**
 * Material card, used inside the materials tabs.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$material_id = absint( $args['id'] ?? 0 );

if ( ! $material_id ) {
	return;
}

$summary = packgens_group_field( 'material_section', 'summary', $material_id );

if ( ! $summary ) {
	$summary = packgens_trim_words( get_post_field( 'post_content', $material_id ), 18 );
}

$link = packgens_link( packgens_group_field( 'material_section', 'button', $material_id ) );
?>
<article class="pg-card pg-material-card">

	<div class="pg-card__media">
		<?php
		if ( has_post_thumbnail( $material_id ) ) {
			echo get_the_post_thumbnail( $material_id, 'packgens-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		} else {
			echo '<span class="pg-card__placeholder">' . packgens_get_icon( 'layers' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>

	<div class="pg-card__body">
		<h3 class="pg-card__title"><?php echo esc_html( get_the_title( $material_id ) ); ?></h3>

		<?php if ( $summary ) : ?>
			<p class="pg-card__excerpt"><?php echo esc_html( $summary ); ?></p>
		<?php endif; ?>

		<?php if ( $link ) : ?>
			<div class="pg-card__footer">
				<a class="pg-btn pg-btn--ghost" href="<?php echo esc_url( $link['url'] ); ?>">
					<?php echo esc_html( $link['title'] ? $link['title'] : __( 'Learn more', 'packgens' ) ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>

</article>
