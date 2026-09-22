<?php
/**
 * Template Name: Saved Items
 *
 * The products this visitor has hearted, in the shop's own card layout.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_the_ID();
$items   = function_exists( 'packgens_wishlist_items' ) ? packgens_wishlist_items() : array();

$products = $items
	? get_posts(
		array(
			'post_type'      => 'product',
			'post__in'       => $items,
			'orderby'        => 'post__in',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		)
	)
	: array();

packgens_render_section( 'hero', $page_id, 'page' );

if ( ! packgens_field( 'hero_section', $page_id ) ) {
	get_template_part( 'template-parts/components/page-header' );
}
?>

<section class="pg-section pg-section--wishlist">
	<div class="pg-container">

		<div class="pg-grid" data-pg-wishlist-grid<?php echo $products ? '' : ' hidden'; ?> style="--pg-cols:1;--pg-cols-sm:2;--pg-cols-lg:3;--pg-cols-xl:4">
			<?php foreach ( $products as $product ) : ?>
				<div data-pg-wishlist-item>
					<?php get_template_part( 'template-parts/cards/product', null, array( 'id' => $product->ID ) ); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="pg-wishlist-empty" data-pg-wishlist-empty<?php echo $products ? ' hidden' : ''; ?>>
			<span class="pg-wishlist-empty__icon"><?php packgens_icon( 'heart' ); ?></span>
			<h2 class="pg-wishlist-empty__title"><?php packgens_the_label( 'wishlist', 'empty_title', __( 'Nothing saved yet', 'packgens' ) ); ?></h2>
			<p class="pg-wishlist-empty__text">
				<?php packgens_the_label( 'wishlist_text', 'empty_text', __( 'Tap the heart on any product to keep it here while you decide.', 'packgens' ) ); ?>
			</p>
			<a class="pg-btn pg-btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
				<?php packgens_the_label( 'wishlist', 'empty_button', __( 'Browse packaging', 'packgens' ) ); ?>
				<?php packgens_icon( 'arrow-right', 'pg-btn__icon' ); ?>
			</a>
		</div>

	</div>
</section>

<?php
packgens_render_sections( 'page', $page_id, array(), array( 'hero' ) );

get_footer();
