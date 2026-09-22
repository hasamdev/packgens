<?php
/**
 * Site logo.
 *
 * Order of preference: the Customizer custom logo, then the brand mark shipped
 * with the theme, then the site title as text.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

if ( has_custom_logo() ) {
	the_custom_logo();

	return;
}

$brand = packgens_get_brand_mark();

if ( $brand ) :
	?>
	<a class="pg-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"
		aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<?php echo $brand; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-authored SVG. ?>
	</a>
	<?php
	return;
endif;
?>
<p class="pg-brand-text">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php bloginfo( 'name' ); ?>
	</a>
</p>
