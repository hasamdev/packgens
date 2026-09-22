<?php
/**
 * Blog post card.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$post_id = absint( $args['id'] ?? get_the_ID() );

if ( ! $post_id ) {
	return;
}

$permalink  = get_permalink( $post_id );
$categories = get_the_category( $post_id );
$author_id  = (int) get_post_field( 'post_author', $post_id );

// The wide card lays its media beside the copy and carries the date up top.
$wide = ! empty( $args['wide'] );
?>
<article class="pg-post-card<?php echo $wide ? ' pg-post-card--wide' : ''; ?>" id="post-<?php echo esc_attr( $post_id ); ?>">

	<a class="pg-post-card__media" href="<?php echo esc_url( $permalink ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( has_post_thumbnail( $post_id ) ) {
			echo get_the_post_thumbnail( $post_id, 'packgens-card', array( 'loading' => 'lazy', 'decoding' => 'async' ) );
		} else {
			echo '<span class="pg-card__placeholder">' . packgens_get_icon( 'file-text' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</a>

	<div class="pg-post-card__body">

		<?php if ( $categories || $wide ) : ?>
			<p class="pg-post-card__terms">
				<?php
				$labels = array();

				foreach ( array_slice( $categories, 0, $wide ? 1 : 2 ) as $category ) {
					$labels[] = sprintf(
						'<a href="%s">%s</a>',
						esc_url( get_category_link( $category ) ),
						esc_html( $category->name )
					);
				}

				echo wp_kses_post( implode( ' <span aria-hidden="true">&middot;</span> ', $labels ) );
				?>

				<?php if ( $wide ) : ?>
					<time class="pg-post-card__posted" datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>">
						<?php echo esc_html( get_the_date( 'F j, Y', $post_id ) ); ?>
					</time>
				<?php endif; ?>
			</p>
		<?php endif; ?>

		<h3 class="pg-post-card__title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
		</h3>

		<p class="pg-post-card__excerpt"><?php echo esc_html( packgens_trim_words( get_the_excerpt( $post_id ), 14 ) ); ?></p>

		<div class="pg-post-card__footer">
			<div class="pg-post-card__author">
				<?php echo packgens_author_avatar( $author_id, 32, 'pg-post-card__avatar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span>
					<span class="pg-post-card__author-name"><?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?></span>
					<time class="pg-post-card__date" datetime="<?php echo esc_attr( get_the_date( 'c', $post_id ) ); ?>">
						<?php echo esc_html( get_the_date( 'j M Y', $post_id ) ); ?>
					</time>
				</span>
			</div>

			<a class="pg-btn <?php echo $wide ? 'pg-btn--primary' : 'pg-btn--ghost'; ?>" href="<?php echo esc_url( $permalink ); ?>">
				<?php packgens_the_label( 'blog', 'read_more', __( 'Read More', 'packgens' ) ); ?>
				<?php packgens_icon( 'arrow-up-right', 'pg-btn__icon' ); ?>
			</a>
		</div>

	</div>
</article>
