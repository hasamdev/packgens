<?php
/**
 * Blog filter bar: category select and a search field.
 *
 * Both controls are plain GET forms, so filtering works without JavaScript.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$categories = get_categories( array( 'hide_empty' => true, 'number' => 40 ) );
$current    = is_category() ? (int) get_queried_object_id() : 0;
?>
<div class="pg-blog-filters">

	<?php if ( $categories ) : ?>
		<form class="pg-blog-filters__cats" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="pg-sr-only" for="pg-blog-cat"><?php packgens_the_label( 'blog', 'filter_label', __( 'Filter by category', 'packgens' ) ); ?></label>
			<select class="pg-select" id="pg-blog-cat" name="cat" onchange="this.form.submit()">
				<option value=""><?php packgens_the_label( 'blog', 'filter_empty', __( 'Select Blog Category', 'packgens' ) ); ?></option>
				<?php foreach ( $categories as $category ) : ?>
					<option value="<?php echo esc_attr( $category->term_id ); ?>" <?php selected( $current, $category->term_id ); ?>>
						<?php echo esc_html( $category->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<noscript><button type="submit" class="pg-btn pg-btn--sm pg-btn--primary"><?php packgens_the_label( 'blog', 'filter_button', __( 'Filter', 'packgens' ) ); ?></button></noscript>
		</form>
	<?php endif; ?>

	<form class="pg-blog-filters__search" method="get" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="pg-sr-only" for="pg-blog-search"><?php packgens_the_label( 'blog', 'search_label', __( 'Search the blog', 'packgens' ) ); ?></label>
		<input type="search" id="pg-blog-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php echo esc_attr( packgens_label( 'blog', 'search_placeholder', __( 'Search blog', 'packgens' ) ) ); ?>">
		<input type="hidden" name="post_type" value="post">
		<button type="submit" class="pg-search__submit">
			<?php packgens_icon( 'search' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Search', 'packgens' ); ?></span>
		</button>
	</form>

</div>
