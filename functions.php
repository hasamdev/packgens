<?php
/**
 * Packgens theme bootstrap.
 *
 * This file stays deliberately small: it defines the theme constants and loads
 * the modules in inc/. Add new behaviour to a module, not to this file.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

define( 'PACKGENS_VERSION', wp_get_theme( 'packgens' )->get( 'Version' ) ?: '1.0.0' );
define( 'PACKGENS_DIR', trailingslashit( get_template_directory() ) );
define( 'PACKGENS_SWIPER_VERSION', '11.2.10' );
define( 'PACKGENS_URI', trailingslashit( get_template_directory_uri() ) );

/**
 * Load a theme module from inc/.
 *
 * @param string $module Module file name without extension.
 */
function packgens_load_module( $module ) {
	$path = PACKGENS_DIR . 'inc/' . $module . '.php';

	if ( is_readable( $path ) ) {
		require_once $path;
	}
}

$packgens_modules = array(
	'helpers',            // Field accessors and small view helpers. Load first.
	'setup',              // Theme supports, menus, image sizes.
	'enqueue',            // Conditional CSS/JS registration.
	'post-types',         // Customer (testimonial) and material post types.
	'fields',             // Secure Custom Fields / ACF local field groups.
	'theme-options',      // Customizer: contact details, social, top bar, CTAs.
	'nav-walkers',        // Mega menu and mobile navigation walkers.
	'menu-fields',        // Per-menu-item image + description admin fields.
	'template-functions', // Body classes, breadcrumbs, pagination, excerpts.
	'queries',            // Query helpers for the section templates.
	'sections',           // Reusable section renderer used by page templates.
	'forms',              // Native AJAX forms + enquiry storage.
	'ajax',               // Load more and predictive search endpoints.
	'security',           // Hardening, upload rules, header policy.
	'seo',                // Schema.org output and SEO plugin compatibility.
	'performance',        // Asset trimming, preloads, resource hints.
);

foreach ( $packgens_modules as $packgens_module ) {
	packgens_load_module( $packgens_module );
}
unset( $packgens_module );

if ( class_exists( 'WooCommerce' ) ) {
	packgens_load_module( 'integrations/woocommerce' );
	packgens_load_module( 'integrations/wishlist' );
}
