<?php
/**
 * Related posts below a single post.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$post_id    = absint( $args['post_id'] ?? get_the_ID() );
$categories = wp_get_post_categories( $post_id );

$query = new WP_Query(
	array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		// Three show at a time; the rest give the arrows somewhere to page to.
		'posts_per_page' => 9,
		'post__not_in'   => array( $post_id ),
		'no_found_rows'  => true,
		'fields'         => 'ids',
		'category__in'   => $categories ? $categories : array(),
	)
);

if ( empty( $query->posts ) ) {
	return;
}
?>
<?php
$carousel_id = packgens_unique_id( 'pg-related-posts' );
$blog_url    = get_permalink( (int) get_option( 'page_for_posts' ) );
?>
<section class="pg-section pg-section--cream pg-section--related">
	<div class="pg-container">

		<div class="pg-section-head pg-section-head--center">
			<div class="pg-section-head__copy">
				<h2 class="pg-section-head__title"><?php packgens_the_label( 'blog', 'related_title', __( 'Related Blogs', 'packgens' ) ); ?></h2>
			</div>
		</div>

		<?php
		packgens_carousel_open(
			$carousel_id,
			array(
				'class'    => 'pg-related__track',
				'label'    => __( 'Related blogs', 'packgens' ),
				'per_view' => array( 0 => 1.1, 576 => 2, 992 => 3 ),
			)
		);

		foreach ( $query->posts as $related_id ) {
			packgens_carousel_slide( 'template-parts/cards/post', null, array( 'id' => (int) $related_id ) );
		}

		packgens_carousel_close();
		?>

		<?php // The same foot the homepage blog row uses: arrows centred, link right. ?>
		<div class="pg-reviews__foot">
			<?php packgens_carousel_nav( $carousel_id ); ?>

			<?php if ( $blog_url ) : ?>
				<a class="pg-btn pg-btn--outline" href="<?php echo esc_url( $blog_url ); ?>">
					<?php packgens_the_label( 'blog', 'related_button', __( 'Visit Our Blog', 'packgens' ) ); ?>
					<?php packgens_icon( 'arrow-right', 'pg-btn__icon' ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>
