<?php
/**
 * Empty state for archives and searches.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$title = $args['title'] ?? __( 'Nothing found', 'packgens' );
$text  = $args['text'] ?? '';

if ( '' === $text ) {
	$text = is_search()
		? __( 'Try a different search term, or browse our packaging categories.', 'packgens' )
		: __( 'There is nothing here yet. Please check back soon.', 'packgens' );
}
?>
<div class="pg-empty">
	<p class="pg-empty__icon"><?php packgens_icon( 'search' ); ?></p>
	<h2 class="pg-empty__title"><?php echo esc_html( $title ); ?></h2>
	<p class="pg-empty__text"><?php echo esc_html( $text ); ?></p>

	<?php if ( is_search() ) : ?>
		<div class="pg-empty__search"><?php get_search_form(); ?></div>
	<?php endif; ?>

	<a class="pg-btn pg-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php packgens_the_label( 'error', 'home_button', __( 'Back to home', 'packgens' ) ); ?>
	</a>
</div>
