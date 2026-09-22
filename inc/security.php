<?php
/**
 * Hardening that belongs to the theme layer.
 *
 * Anything that changes site-wide behaviour is kept conservative: this file
 * should never break a plugin or the REST API for logged-in users.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Send a small set of safe security headers.
 *
 * A Content-Security-Policy is deliberately not set here: it depends on which
 * plugins a site runs, so it belongs in server configuration.
 *
 * @return void
 */
function packgens_security_headers() {
	if ( is_admin() || headers_sent() ) {
		return;
	}

	header( 'X-Content-Type-Options: nosniff' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
}
add_action( 'send_headers', 'packgens_security_headers' );

/**
 * Remove the WordPress version from the head and from asset URLs.
 *
 * @return void
 */
function packgens_remove_version() {
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'packgens_remove_version' );

/**
 * Strip the ver query argument that leaks the core version.
 *
 * @param string $src Asset URL.
 * @return string
 */
function packgens_strip_core_version( $src ) {
	if ( strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) !== false ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
add_filter( 'style_loader_src', 'packgens_strip_core_version', 9999 );
add_filter( 'script_loader_src', 'packgens_strip_core_version', 9999 );

/**
 * Never reveal whether a username exists on the login form.
 *
 * @return string
 */
function packgens_generic_login_error() {
	return __( 'The username or password you entered is not correct.', 'packgens' );
}
add_filter( 'login_errors', 'packgens_generic_login_error' );

/**
 * Disable XML-RPC, which this site does not use.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Remove the pingback header advertised by XML-RPC.
 *
 * @param string[] $headers Response headers.
 * @return string[]
 */
function packgens_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );

	return $headers;
}
add_filter( 'wp_headers', 'packgens_remove_pingback_header' );

/**
 * Block user enumeration through ?author=N on the front end.
 *
 * @return void
 */
function packgens_block_author_enumeration() {
	if ( is_admin() || is_user_logged_in() ) {
		return;
	}

	if ( ! isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'packgens_block_author_enumeration', 0 );

/**
 * Hide the users REST route from anonymous requests.
 *
 * Everything else in the REST API is left alone so the block editor and
 * plugins keep working.
 *
 * @param mixed $result Current authentication result.
 * @return mixed
 */
function packgens_restrict_users_endpoint( $result ) {
	if ( ! empty( $result ) || is_user_logged_in() ) {
		return $result;
	}

	$route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? (string) $GLOBALS['wp']->query_vars['rest_route'] : '';

	if ( $route && 0 === strpos( ltrim( $route, '/' ), 'wp/v2/users' ) ) {
		return new WP_Error(
			'rest_forbidden',
			__( 'Sorry, you are not allowed to do that.', 'packgens' ),
			array( 'status' => 401 )
		);
	}

	return $result;
}
add_filter( 'rest_authentication_errors', 'packgens_restrict_users_endpoint' );

/**
 * Keep SVG uploads restricted to users who can already run unfiltered HTML.
 *
 * SVG is not enabled by default. Sites that need it should sanitise on upload,
 * so this only widens the door for trusted roles.
 *
 * @param array $mimes Allowed mime types.
 * @return array
 */
function packgens_upload_mimes( $mimes ) {
	if ( ! current_user_can( 'unfiltered_html' ) ) {
		unset( $mimes['svg'], $mimes['svgz'] );
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'packgens_upload_mimes', 99 );

/**
 * Escape the comment author URL classes WordPress prints unfiltered.
 *
 * @param string $comment_text Comment text.
 * @return string
 */
function packgens_kses_comment( $comment_text ) {
	return wp_kses_post( $comment_text );
}
add_filter( 'comment_text', 'packgens_kses_comment', 20 );
