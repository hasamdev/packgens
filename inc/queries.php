<?php
/**
 * Query helpers used by the section templates.
 *
 * Each helper turns a section's saved settings into the objects the template
 * needs, and returns an empty array rather than throwing when a dependency
 * (WooCommerce, a taxonomy) is missing.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product categories for the categories section.
 *
 * @param array $data Section data.
 * @return WP_Term[]
 */
function packgens_get_section_categories( $data ) {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$limit  = max( 1, (int) ( $data['limit'] ?? 12 ) );
	$source = $data['source'] ?? 'auto';

	if ( 'manual' === $source && ! empty( $data['items'] ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'include'    => array_map( 'absint', (array) $data['items'] ),
				'orderby'    => 'include',
				'hide_empty' => false,
			)
		);
	} else {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'number'     => $limit,
				'orderby'    => 'menu_order',
				'hide_empty' => true,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
				'parent'     => 0,
			)
		);
	}

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return array_slice( $terms, 0, $limit );
}

/**
 * Product IDs for the products section.
 *
 * @param array           $data    Section data.
 * @param int|string|null $context Field context.
 * @return int[]
 */
function packgens_get_section_products( $data, $context = null ) {
	if ( ! post_type_exists( 'product' ) ) {
		return array();
	}

	$limit  = max( 1, (int) ( $data['limit'] ?? 8 ) );
	$source = $data['source'] ?? 'featured';

	if ( 'manual' === $source ) {
		$ids = array_map( 'absint', (array) ( $data['products'] ?? array() ) );

		return array_slice( array_filter( $ids ), 0, $limit );
	}

	$query_args = array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'fields'              => 'ids',
		'tax_query'           => array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
	);

	if ( 'featured' === $source ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'product_visibility',
			'field'    => 'name',
			'terms'    => 'featured',
			'operator' => 'IN',
		);
	}

	if ( 'category' === $source ) {
		$term_id = 0;

		if ( is_string( $context ) && 0 === strpos( $context, 'term_' ) ) {
			$term_id = (int) substr( $context, 5 );
		} elseif ( is_tax( 'product_cat' ) ) {
			$term_id = (int) get_queried_object_id();
		}

		if ( $term_id ) {
			$query_args['tax_query'][] = array(
				'taxonomy'         => 'product_cat',
				'field'            => 'term_id',
				'terms'            => $term_id,
				'include_children' => true,
			);
		}
	}

	$query = new WP_Query( $query_args );
	$ids   = $query->posts;

	// Featured is a curated list; fall back to latest when nothing is flagged.
	if ( empty( $ids ) && 'featured' === $source ) {
		$data['source'] = 'latest';

		return packgens_get_section_products( $data, $context );
	}

	return array_map( 'absint', (array) $ids );
}

/**
 * Testimonial post IDs for the reviews section.
 *
 * @param array $data  Section data.
 * @param int   $limit Fallback number of testimonials.
 * @return int[]
 */
function packgens_get_section_reviews( $data, $limit = 9 ) {
	if ( ! post_type_exists( 'customer' ) ) {
		return array();
	}

	$ids = array_filter( array_map( 'absint', (array) ( $data['items'] ?? array() ) ) );

	if ( ! empty( $ids ) ) {
		return $ids;
	}

	$query = new WP_Query( packgens_reviews_query_args( 1, $limit ) );

	return array_map( 'absint', (array) $query->posts );
}

/**
 * Shared query arguments for testimonial listings.
 *
 * @param int $page     Page number.
 * @param int $per_page Items per page.
 * @return array
 */
function packgens_reviews_query_args( $page = 1, $per_page = 9 ) {
	return array(
		'post_type'      => 'customer',
		'post_status'    => 'publish',
		'posts_per_page' => max( 1, (int) $per_page ),
		'paged'          => max( 1, (int) $page ),
		'fields'         => 'ids',
		'meta_key'       => 'testimonial_date', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'orderby'        => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ),
	);
}

/**
 * Material posts grouped by their taxonomy terms.
 *
 * @param array $data Section data.
 * @return array<int,array{term:WP_Term,posts:int[]}>
 */
function packgens_get_section_materials( $data ) {
	if ( ! post_type_exists( 'material' ) || ! taxonomy_exists( 'category_materials' ) ) {
		return array();
	}

	$term_ids = array_filter( array_map( 'absint', (array) ( $data['items'] ?? array() ) ) );

	$terms = get_terms(
		array(
			'taxonomy'   => 'category_materials',
			'include'    => ! empty( $term_ids ) ? $term_ids : array(),
			'orderby'    => ! empty( $term_ids ) ? 'include' : 'menu_order',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$groups = array();

	foreach ( $terms as $term ) {
		$query = new WP_Query(
			array(
				'post_type'      => 'material',
				'post_status'    => 'publish',
				'posts_per_page' => 12,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'category_materials',
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					),
				),
			)
		);

		if ( ! empty( $query->posts ) ) {
			$groups[] = array(
				'term'  => $term,
				'posts' => array_map( 'absint', $query->posts ),
			);
		}
	}

	return $groups;
}

/**
 * Latest blog post IDs.
 *
 * @param array $data Section data.
 * @return int[]
 */
function packgens_get_section_posts( $data ) {
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) ( $data['limit'] ?? 3 ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'fields'              => 'ids',
		)
	);

	return array_map( 'absint', (array) $query->posts );
}

/**
 * Related products for a single product, falling back to same-category items.
 *
 * @param int $product_id Product ID.
 * @param int $limit      Maximum results.
 * @return int[]
 */
function packgens_get_related_products( $product_id, $limit = 8 ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$terms = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'post__not_in'   => array( $product_id ),
			'no_found_rows'  => true,
			'fields'         => 'ids',
			'orderby'        => 'rand',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $terms,
				),
			),
		)
	);

	return array_map( 'absint', (array) $query->posts );
}
