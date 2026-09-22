<?php
/**
 * Blog sidebar: search with category shortcuts, and the most-read posts.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$categories = get_categories(
	array(
		'hide_empty' => true,
		'number'     => 8,
		'orderby'    => 'count',
		'order'      => 'DESC',
	)
);

$popular = get_posts(
	array(
		'post_type'        => 'post',
		'numberposts'      => 3,
		'orderby'          => 'comment_count',
		'order'            => 'DESC',
		'ignore_sticky_posts' => true,
		'fields'           => 'ids',
	)
);
?>
<aside class="pg-blog-side">

	<section class="pg-blog-side__panel">
		<h2 class="pg-blog-side__title"><?php packgens_the_label( 'blog', 'sidebar_search', __( 'Search Posts', 'packgens' ) ); ?></h2>

		<form class="pg-blog-search" method="get" role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="pg-sr-only" for="pg-blog-search"><?php esc_html_e( 'Search the blog', 'packgens' ); ?></label>
			<input type="search" id="pg-blog-search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php echo esc_attr( packgens_label( 'blog', 'search_placeholder', __( 'Search blog', 'packgens' ) ) ); ?>">
			<input type="hidden" name="post_type" value="post">
			<button type="submit" class="pg-blog-search__submit">
				<?php packgens_icon( 'search' ); ?>
				<span class="pg-sr-only"><?php esc_html_e( 'Search', 'packgens' ); ?></span>
			</button>
		</form>

		<?php if ( $categories ) : ?>
			<ul class="pg-blog-side__tags">
				<?php foreach ( $categories as $category ) : ?>
					<li>
						<a href="<?php echo esc_url( get_category_link( $category ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</section>

	<?php if ( $popular ) : ?>
		<section class="pg-blog-side__popular">
			<h2 class="pg-blog-side__title"><?php packgens_the_label( 'blog', 'sidebar_popular', __( 'Most Popular', 'packgens' ) ); ?></h2>

			<ul class="pg-blog-popular">
				<?php
				foreach ( $popular as $popular_id ) :
					$terms = get_the_category( $popular_id );
					?>
					<li class="pg-blog-popular__item">
						<a class="pg-blog-popular__media" href="<?php echo esc_url( get_permalink( $popular_id ) ); ?>" tabindex="-1" aria-hidden="true">
							<?php
							if ( has_post_thumbnail( $popular_id ) ) {
								echo get_the_post_thumbnail( $popular_id, 'packgens-thumb', array( 'loading' => 'lazy' ) );
							} else {
								echo '<span class="pg-card__placeholder">' . packgens_get_icon( 'file-text' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</a>

						<span class="pg-blog-popular__copy">
							<?php if ( $terms ) : ?>
								<span class="pg-blog-popular__term"><?php echo esc_html( $terms[0]->name ); ?></span>
							<?php endif; ?>

							<a class="pg-blog-popular__title" href="<?php echo esc_url( get_permalink( $popular_id ) ); ?>">
								<?php echo esc_html( packgens_trim_words( get_the_title( $popular_id ), 6 ) ); ?>
							</a>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
	<?php endif; ?>

</aside>
