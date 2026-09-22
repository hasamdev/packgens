<?php
/**
 * Custom post types and taxonomies.
 *
 * The reference build registered these through the Secure Custom Fields UI,
 * which puts the content model in the database. Registering them in code keeps
 * the model in version control and means the theme works on a fresh install.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the customer (testimonial) and material post types.
 *
 * @return void
 */
function packgens_register_post_types() {
	register_post_type(
		'customer',
		array(
			'labels'        => array(
				'name'               => __( 'Testimonials', 'packgens' ),
				'singular_name'      => __( 'Testimonial', 'packgens' ),
				'add_new_item'       => __( 'Add Testimonial', 'packgens' ),
				'edit_item'          => __( 'Edit Testimonial', 'packgens' ),
				'new_item'           => __( 'New Testimonial', 'packgens' ),
				'view_item'          => __( 'View Testimonial', 'packgens' ),
				'search_items'       => __( 'Search Testimonials', 'packgens' ),
				'not_found'          => __( 'No testimonials found.', 'packgens' ),
				'not_found_in_trash' => __( 'No testimonials in the bin.', 'packgens' ),
				'menu_name'          => __( 'Testimonials', 'packgens' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'hierarchical'  => false,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-format-quote',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
			'rewrite'       => array( 'slug' => 'testimonial', 'with_front' => false ),
		)
	);

	register_post_type(
		'material',
		array(
			'labels'        => array(
				'name'               => __( 'Materials', 'packgens' ),
				'singular_name'      => __( 'Material', 'packgens' ),
				'add_new_item'       => __( 'Add Material', 'packgens' ),
				'edit_item'          => __( 'Edit Material', 'packgens' ),
				'new_item'           => __( 'New Material', 'packgens' ),
				'view_item'          => __( 'View Material', 'packgens' ),
				'search_items'       => __( 'Search Materials', 'packgens' ),
				'not_found'          => __( 'No materials found.', 'packgens' ),
				'not_found_in_trash' => __( 'No materials in the bin.', 'packgens' ),
				'menu_name'          => __( 'Materials', 'packgens' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'hierarchical'  => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-layout',
			'menu_position' => 23,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields' ),
			'rewrite'       => array( 'slug' => 'material', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'category_customers',
		array( 'customer' ),
		array(
			'labels'            => array(
				'name'          => __( 'Testimonial Categories', 'packgens' ),
				'singular_name' => __( 'Testimonial Category', 'packgens' ),
				'menu_name'     => __( 'Categories', 'packgens' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'testimonial-category', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'category_materials',
		array( 'material' ),
		array(
			'labels'            => array(
				'name'          => __( 'Material Categories', 'packgens' ),
				'singular_name' => __( 'Material Category', 'packgens' ),
				'menu_name'     => __( 'Categories', 'packgens' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'material-category', 'with_front' => true ),
		)
	);
}
add_action( 'init', 'packgens_register_post_types' );

/**
 * Testimonials and materials are building blocks, not standalone pages.
 * Send direct hits to the pages that actually display them.
 *
 * @return void
 */
function packgens_redirect_component_singles() {
	if ( ! is_singular( array( 'customer', 'material' ) ) ) {
		return;
	}

	$target = is_singular( 'customer' )
		? packgens_get_option( 'reviews_page_url', home_url( '/' ) )
		: home_url( '/' );

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'packgens_redirect_component_singles', 1 );
