<?php
/**
 * Demo content seeder.
 *
 * Creates the pages, menus, taxonomies, products, testimonials, materials,
 * posts and section content needed to see the theme fully populated. Intended
 * for a fresh install or a staging site.
 *
 * Usage (from the WordPress root):
 *
 *   php wp-content/themes/packgens/tools/seed-demo.php --images=/path/to/images
 *   php wp-content/themes/packgens/tools/seed-demo.php --reset
 *
 * Everything it creates is tagged with the meta key _packgens_demo, so --reset
 * removes exactly what it made and nothing else.
 *
 * @package Packgens
 */

if ( PHP_SAPI !== 'cli' ) {
	exit( 1 );
}

$options = getopt( '', array( 'images::', 'reset', 'url::' ) );

// --- Bootstrap WordPress ---------------------------------------------------
$root = dirname( __DIR__, 4 );

if ( ! file_exists( $root . '/wp-load.php' ) ) {
	fwrite( STDERR, "Could not locate wp-load.php from {$root}\n" );
	exit( 1 );
}

$_SERVER['HTTP_HOST']   = $options['url'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'];

require_once $root . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

if ( ! function_exists( 'packgens_get_option' ) ) {
	fwrite( STDERR, "The Packgens theme is not active.\n" );
	exit( 1 );
}

const PG_DEMO_FLAG = '_packgens_demo';

/**
 * Print a progress line.
 *
 * @param string $message Message.
 * @return void
 */
function pg_say( $message ) {
	fwrite( STDOUT, $message . "\n" );
}

/* =========================================================================
   Reset
   ========================================================================= */

if ( isset( $options['reset'] ) ) {
	$ids = get_posts(
		array(
			'post_type'      => 'any',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => PG_DEMO_FLAG,
		)
	);

	foreach ( $ids as $id ) {
		wp_delete_post( $id, true );
	}

	foreach ( array( 'product_cat', 'category', 'category_materials', 'category_customers' ) as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		foreach ( get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) ) as $term ) {
			if ( get_term_meta( $term->term_id, PG_DEMO_FLAG, true ) ) {
				wp_delete_term( $term->term_id, $taxonomy );
			}
		}
	}

	foreach ( wp_get_nav_menus() as $menu ) {
		if ( 0 === strpos( $menu->name, 'Packgens ' ) ) {
			wp_delete_nav_menu( $menu->term_id );
		}
	}

	pg_say( 'Removed ' . count( $ids ) . ' demo posts and their terms and menus.' );
	exit( 0 );
}

/* =========================================================================
   Media
   ========================================================================= */

$image_dir = $options['images'] ?? '';

if ( ! $image_dir || ! is_dir( $image_dir ) ) {
	fwrite( STDERR, "Pass --images=/path/to/image/folder\n" );
	exit( 1 );
}

/**
 * Import an image once and return its attachment ID.
 *
 * @param string $prefix Filename prefix to match inside the image folder.
 * @param string $title  Attachment title and alt text.
 * @return int
 */
function pg_image( $prefix, $title = '' ) {
	global $image_dir;
	static $cache = array();

	if ( isset( $cache[ $prefix ] ) ) {
		return $cache[ $prefix ];
	}

	$matches = glob( trailingslashit( $image_dir ) . $prefix . '*' );

	if ( empty( $matches ) ) {
		pg_say( "  ! missing image {$prefix}" );
		$cache[ $prefix ] = 0;

		return 0;
	}

	$source = $matches[0];
	$name   = sanitize_title( $title ? $title : $prefix ) . '.' . pathinfo( $source, PATHINFO_EXTENSION );

	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => PG_DEMO_FLAG,
			'meta_value'     => $prefix,
		)
	);

	if ( $existing ) {
		$cache[ $prefix ] = (int) $existing[0];

		return $cache[ $prefix ];
	}

	$uploads = wp_upload_dir();
	$target  = trailingslashit( $uploads['path'] ) . wp_unique_filename( $uploads['path'], $name );

	if ( ! copy( $source, $target ) ) {
		pg_say( "  ! could not copy {$source}" );
		$cache[ $prefix ] = 0;

		return 0;
	}

	$filetype = wp_check_filetype( $target );

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $filetype['type'],
			'post_title'     => $title ? $title : pathinfo( $target, PATHINFO_FILENAME ),
			'post_status'    => 'inherit',
		),
		$target
	);

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $target ) );
	update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
	update_post_meta( $attachment_id, PG_DEMO_FLAG, $prefix );

	$cache[ $prefix ] = (int) $attachment_id;

	return $cache[ $prefix ];
}

/* =========================================================================
   Helpers
   ========================================================================= */

/**
 * Create or update a page and mark it as demo content.
 *
 * @param string $slug     Page slug.
 * @param string $title    Page title.
 * @param string $template Page template path, or empty.
 * @param string $content  Editor content.
 * @return int
 */
function pg_page( $slug, $title, $template = '', $content = '' ) {
	$existing = get_page_by_path( $slug );

	$args = array(
		'post_type'    => 'page',
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_status'  => 'publish',
		'post_content' => $content,
	);

	if ( $existing ) {
		$args['ID'] = $existing->ID;
	}

	$page_id = wp_insert_post( $args );

	if ( $template ) {
		update_post_meta( $page_id, '_wp_page_template', $template );
	}

	update_post_meta( $page_id, PG_DEMO_FLAG, 1 );

	return (int) $page_id;
}

/**
 * Create or update a term and mark it as demo content.
 *
 * @param string $taxonomy Taxonomy.
 * @param string $name     Term name.
 * @param int    $image_id Optional thumbnail.
 * @param string $description Term description.
 * @return int
 */
function pg_term( $taxonomy, $name, $image_id = 0, $description = '' ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return 0;
	}

	$term = get_term_by( 'name', $name, $taxonomy );

	if ( ! $term ) {
		$result = wp_insert_term( $name, $taxonomy, array( 'description' => $description ) );

		if ( is_wp_error( $result ) ) {
			return 0;
		}

		$term_id = (int) $result['term_id'];
	} else {
		$term_id = (int) $term->term_id;
		wp_update_term( $term_id, $taxonomy, array( 'description' => $description ) );
	}

	if ( $image_id ) {
		update_term_meta( $term_id, 'thumbnail_id', $image_id );
	}

	update_term_meta( $term_id, PG_DEMO_FLAG, 1 );

	return $term_id;
}

/**
 * Create or update a post of any type.
 *
 * @param string $type    Post type.
 * @param string $title   Title.
 * @param array  $args    Extra wp_insert_post arguments.
 * @param int    $image   Featured image ID.
 * @return int
 */
function pg_post( $type, $title, $args = array(), $image = 0 ) {
	$existing = get_posts(
		array(
			'post_type'      => $type,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'title'          => $title,
		)
	);

	$args = wp_parse_args(
		$args,
		array(
			'post_type'   => $type,
			'post_title'  => $title,
			'post_status' => 'publish',
		)
	);

	if ( $existing ) {
		$args['ID'] = (int) $existing[0];
	}

	$post_id = wp_insert_post( $args );

	if ( $image ) {
		set_post_thumbnail( $post_id, $image );
	}

	update_post_meta( $post_id, PG_DEMO_FLAG, 1 );

	return (int) $post_id;
}

/**
 * Save a field group, tolerating a missing custom-fields plugin.
 *
 * @param string     $name    Field name.
 * @param mixed      $value   Value.
 * @param int|string $post_id Target.
 * @return void
 */
function pg_field( $name, $value, $post_id ) {
	if ( ! function_exists( 'update_field' ) ) {
		return;
	}

	/*
	 * ACF stores a hidden `_{name}` row holding the field key a value was last
	 * written with. If a group has since been renamed, that stale key points at
	 * a different field, and the update runs against its sub-fields instead --
	 * silently dropping everything that does not match. Clearing the reference
	 * first makes the write resolve against the current registration.
	 */
	if ( is_numeric( $post_id ) ) {
		delete_post_meta( (int) $post_id, '_' . $name );
	} elseif ( is_string( $post_id ) && 0 === strpos( $post_id, 'term_' ) ) {
		delete_term_meta( (int) substr( $post_id, 5 ), '_' . $name );
	}

	update_field( $name, $value, $post_id );
}

require __DIR__ . '/seed-data.php';
