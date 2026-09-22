<?php
/**
 * Theme supports, navigation menus, image sizes and widget areas.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register theme supports and menus.
 *
 * @return void
 */
function packgens_setup() {
	load_theme_textdomain( 'packgens', PACKGENS_DIR . 'languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 48,
			'width'       => 190,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);

	register_nav_menus(
		array(
			'primary'         => __( 'Primary Menu', 'packgens' ),
			'topbar'          => __( 'Top Bar Menu', 'packgens' ),
			'footer_info'     => __( 'Footer: Information', 'packgens' ),
			'footer_industry' => __( 'Footer: Industries', 'packgens' ),
			'footer_products' => __( 'Footer: Products', 'packgens' ),
			'footer_legal'    => __( 'Footer: Legal', 'packgens' ),
		)
	);

	// Figma content column is 1320px inside a 1920px canvas.
	if ( ! isset( $GLOBALS['content_width'] ) ) {
		$GLOBALS['content_width'] = 1320;
	}
}
add_action( 'after_setup_theme', 'packgens_setup' );

/**
 * Register the image sizes the Figma layouts actually use.
 *
 * @return void
 */
function packgens_image_sizes() {
	add_image_size( 'packgens-card', 640, 480, true );        // Product / category / blog cards.
	add_image_size( 'packgens-card-tall', 640, 760, true );   // Category style cards.
	add_image_size( 'packgens-thumb', 160, 160, true );       // Gallery thumbs, menu cards, avatars.
	add_image_size( 'packgens-wide', 1320, 660, true );       // Hero and feature bands.
	add_image_size( 'packgens-media', 900, 900, false );      // Product gallery main image.
}
add_action( 'after_setup_theme', 'packgens_image_sizes' );

/**
 * Expose the custom sizes in the media picker.
 *
 * @param array $sizes Existing size choices.
 * @return array
 */
function packgens_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'packgens-card'      => __( 'Packgens Card', 'packgens' ),
			'packgens-card-tall' => __( 'Packgens Card (tall)', 'packgens' ),
			'packgens-wide'      => __( 'Packgens Wide', 'packgens' ),
			'packgens-media'     => __( 'Packgens Media', 'packgens' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'packgens_image_size_names' );

/**
 * Register widget areas.
 *
 * @return void
 */
function packgens_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'packgens' ),
			'id'            => 'sidebar-blog',
			'description'   => __( 'Shown beside blog archives and single posts.', 'packgens' ),
			'before_widget' => '<section id="%1$s" class="pg-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="pg-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'packgens_widgets_init' );

/**
 * Flush rewrite rules once after activation so the theme's rules apply.
 *
 * @return void
 */
function packgens_after_switch_theme() {
	packgens_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'packgens_after_switch_theme' );
