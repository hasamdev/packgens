<?php
/**
 * Single blog post.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$post_id    = get_the_ID();
	$categories = get_the_category();
	$author_id  = (int) get_post_field( 'post_author', $post_id );
	$toc        = packgens_table_of_contents( get_post_field( 'post_content', $post_id ) );
	?>

	<article id="post-<?php the_ID(); ?>" <?php post_class( 'pg-post' ); ?>>

		<header class="pg-post__hero">
			<div class="pg-container pg-post__hero-inner">
				<div class="pg-post__hero-copy">
					<div class="pg-post__meta-top">
						<?php if ( $categories ) : ?>
							<a class="pg-badge" href="<?php echo esc_url( get_category_link( $categories[0] ) ); ?>">
								<?php echo esc_html( $categories[0]->name ); ?>
							</a>
						<?php endif; ?>

						<time class="pg-badge pg-badge--outline" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
							<?php echo esc_html( get_the_date() ); ?>
						</time>
					</div>

					<h1 class="pg-post__title"><?php the_title(); ?></h1>

					<div class="pg-post__byline">
						<?php echo packgens_author_avatar( $author_id, 48, 'pg-post__avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span>
							<span class="pg-post__author"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></span>
							<span class="pg-post__reading">
								<?php
								printf(
									/* translators: %d: number of minutes. */
									esc_html( _n( '%d min read', '%d min read', packgens_reading_time( $post_id ), 'packgens' ) ),
									(int) packgens_reading_time( $post_id )
								);
								?>
							</span>
						</span>
					</div>
				</div>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="pg-post__hero-media">
						<?php the_post_thumbnail( 'packgens-wide', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
					</div>
				<?php endif; ?>
			</div>
		</header>

		<div class="pg-section pg-post__body">
			<div class="pg-container pg-post__layout">

				<div class="pg-post__content">
					<div class="pg-prose">
						<?php
						the_content();

						wp_link_pages(
							array(
								'before' => '<div class="pg-page-links">' . esc_html__( 'Pages:', 'packgens' ),
								'after'  => '</div>',
							)
						);
						?>
					</div>

					<?php if ( has_tag() ) : ?>
						<div class="pg-post__tags">
							<?php the_tags( '<span class="pg-post__tags-label">' . esc_html__( 'Tags:', 'packgens' ) . '</span>', '', '' ); ?>
						</div>
					<?php endif; ?>
				</div>

				<aside class="pg-post__sidebar">
					<?php get_template_part( 'template-parts/post/aside', null, array( 'toc' => $toc, 'post_id' => $post_id ) ); ?>
				</aside>

			</div>
		</div>

		<?php get_template_part( 'template-parts/post/related', null, array( 'post_id' => $post_id ) ); ?>

	</article>

	<?php
endwhile;

get_footer();
