<?php
/**
 * The products grid under a product's summary: a shared heading and either the
 * products picked on this product or, failing that, six from its category.
 *
 * Renders through the Products section template so it keeps that section's
 * look; only where the data comes from differs.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

$product_id = absint( $args['product_id'] ?? 0 );

if ( ! $product_id ) {
	return;
}

$picked   = array_filter( array_map( 'absint', (array) packgens_group_field( 'product_details', 'grid_products', $product_id, array() ) ) );
$picked   = array_values( array_diff( $picked, array( $product_id ) ) );
$products = $picked ? $picked : packgens_get_related_products( $product_id, 6 );

if ( empty( $products ) ) {
	return;
}

get_template_part(
	'template-parts/sections/products',
	null,
	array(
		'view' => 'product',
		'data' => array(
			'title'    => packgens_product_detail( $product_id, 'products_title' ),
			'source'   => 'manual',
			'products' => $products,
			'limit'    => count( $products ),
			'layout'   => 'grid',
		),
	)
);
