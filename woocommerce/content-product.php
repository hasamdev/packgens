<?php
/**
 * Product loop item.
 *
 * Delegates to the theme's card so shortcodes and blocks that run the loop
 * (related products, [products], the shop block) all match the design.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

get_template_part( 'template-parts/cards/product', null, array( 'id' => $product->get_id() ) );
