<?php
/**
 * Compact page banner used when a view has no configured hero.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$title    = $args['title'] ?? '';
$subtitle = $args['subtitle'] ?? '';

if ( '' === $title ) {
	if ( is_search() ) {
		$title = sprintf(
			/* translators: %s: search term. */
			__( 'Search results for &ldquo;%s&rdquo;', 'packgens' ),
			get_search_query()
		);
	} elseif ( is_404() ) {
		$title = __( 'Page not found', 'packgens' );
	} elseif ( is_home() ) {
		$title = get_the_title( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_archive() ) {
		$title = get_the_archive_title();
	} elseif ( is_singular() ) {
		$title = get_the_title();
	}
}

if ( '' === $subtitle && is_archive() ) {
	$subtitle = wp_strip_all_tags( get_the_archive_description() );
}
?>
<section class="pg-page-header">
	<div class="pg-container">
		<?php packgens_breadcrumbs(); ?>

		<?php if ( $title ) : ?>
			<h1 class="pg-page-header__title"><?php echo wp_kses_post( $title ); ?></h1>
		<?php endif; ?>

		<?php if ( $subtitle ) : ?>
			<p class="pg-page-header__subtitle"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
	</div>
</section>
