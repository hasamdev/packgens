<?php
/**
 * 404 template.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="pg-section pg-section--404">
	<div class="pg-container pg-container--narrow pg-text-center">

		<p class="pg-404__code">404</p>

		<h1 class="pg-404__title"><?php packgens_the_label( 'error', 'title', __( 'We could not find that page', 'packgens' ) ); ?></h1>

		<p class="pg-404__text">
			<?php packgens_the_label( 'error_text', 'text', __( 'The page may have moved or the link may be out of date. Try a search, or head back to the homepage.', 'packgens' ) ); ?>
		</p>

		<div class="pg-404__search"><?php get_search_form(); ?></div>

		<div class="pg-404__actions">
			<a class="pg-btn pg-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php packgens_the_label( 'error', 'home_button', __( 'Back to home', 'packgens' ) ); ?>
			</a>

			<?php
			$shop = packgens_get_option( 'shop_page_url' );

			if ( ! $shop && function_exists( 'wc_get_page_permalink' ) ) {
				$shop = wc_get_page_permalink( 'shop' );
			}

			if ( $shop ) :
				?>
				<a class="pg-btn pg-btn--outline" href="<?php echo esc_url( $shop ); ?>">
					<?php packgens_the_label( 'error', 'shop_button', __( 'Browse products', 'packgens' ) ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>

<?php
get_footer();
