<?php
/**
 * Performance: trim assets WordPress loads by default and hint at critical media.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Drop core assets this theme does not use on the front end.
 *
 * @return void
 */
function packgens_dequeue_unused() {
	if ( is_admin() ) {
		return;
	}

	// Emoji support costs a script, a stylesheet and a DNS lookup.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

	// Block library styles are only needed where blocks actually render.
	if ( ! packgens_uses_blocks() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'packgens_dequeue_unused', 100 );

/**
 * Whether the current view renders block content.
 *
 * @return bool
 */
function packgens_uses_blocks() {
	if ( ! is_singular() ) {
		return false;
	}

	$post = get_post();

	return $post instanceof WP_Post && has_blocks( $post );
}

/**
 * Preload the hero image so it is not discovered late.
 *
 * @return void
 */
function packgens_preload_hero() {
	$context = packgens_field_context();
	$image   = packgens_group_field( 'hero_section', 'image', $context );

	if ( ! $image ) {
		return;
	}

	$src = wp_get_attachment_image_src( (int) $image, 'full' );

	if ( ! $src ) {
		return;
	}

	$srcset = wp_get_attachment_image_srcset( (int) $image, 'full' );
	$sizes  = wp_get_attachment_image_sizes( (int) $image, 'full' );

	printf(
		'<link rel="preload" as="image" href="%s"%s%s fetchpriority="high">' . "\n",
		esc_url( $src[0] ),
		$srcset ? ' imagesrcset="' . esc_attr( $srcset ) . '"' : '',
		$sizes ? ' imagesizes="' . esc_attr( $sizes ) . '"' : ''
	);
}
add_action( 'wp_head', 'packgens_preload_hero', 3 );

/**
 * Never lazy-load the first image on a page: it is usually the LCP element.
 *
 * @param string|bool $value   Current loading attribute.
 * @param string      $image   Image markup.
 * @param string      $context Where the image is rendered.
 * @return string|bool
 */
function packgens_skip_first_image_lazy( $value, $image, $context ) {
	static $seen = false;

	if ( is_admin() || 'the_content' !== $context ) {
		return $value;
	}

	if ( ! $seen ) {
		$seen = true;

		return false;
	}

	return $value;
}
add_filter( 'wp_img_tag_add_loading_attr', 'packgens_skip_first_image_lazy', 10, 3 );

/**
 * Limit revisions so the posts table stays small on a content-heavy site.
 *
 * @param int     $num  Revisions to keep.
 * @param WP_Post $post Post being saved.
 * @return int
 */
function packgens_limit_revisions( $num, $post ) {
	return in_array( $post->post_type, array( 'page', 'post', 'product' ), true ) ? 8 : $num;
}
add_filter( 'wp_revisions_to_keep', 'packgens_limit_revisions', 10, 2 );

/**
 * Preconnect to the uploads host when it is on a different origin (CDN).
 *
 * @param string[] $urls          URLs to hint.
 * @param string   $relation_type Hint type.
 * @return string[]
 */
function packgens_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type ) {
		return $urls;
	}

	$uploads = wp_get_upload_dir();
	$host    = wp_parse_url( $uploads['baseurl'], PHP_URL_HOST );
	$site    = wp_parse_url( home_url(), PHP_URL_HOST );

	if ( $host && $site && $host !== $site ) {
		$urls[] = array(
			'href'        => '//' . $host,
			'crossorigin' => '',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'packgens_resource_hints', 10, 2 );
