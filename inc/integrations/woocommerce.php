<?php
/**
 * WooCommerce integration.
 *
 * Layout is rewired with hooks wherever possible so the theme carries as few
 * plugin template overrides as possible and stays upgrade safe.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Declare theme support.
 *
 * @return void
 */
function packgens_woocommerce_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 640,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);

	// The theme ships its own gallery, so none of the plugin's gallery
	// features (zoom, lightbox, slider) are declared here.
}
add_action( 'after_setup_theme', 'packgens_woocommerce_setup' );

/**
 * Replace the plugin's page wrappers with the theme's own markup.
 *
 * @return void
 */
function packgens_wc_wrapper_start() {
	echo '<div class="pg-wc">';
}

/**
 * Close the wrapper.
 *
 * @return void
 */
function packgens_wc_wrapper_end() {
	echo '</div>';
}

/**
 * Print any queued WooCommerce notices at the top of the content.
 *
 * The theme's own archive and single-product templates never fire
 * `woocommerce_before_shop_loop` or `woocommerce_before_single_product`, which
 * is where WooCommerce normally prints them, so "added to your cart" and every
 * error went unseen on the shop, the category pages and the product pages.
 *
 * @return void
 */
function packgens_wc_notices() {
	if ( ! function_exists( 'woocommerce_output_all_notices' ) || ! wc_notice_count() ) {
		return;
	}

	echo '<div class="pg-container pg-wc-notices">';
	woocommerce_output_all_notices();
	echo '</div>';
}
add_action( 'woocommerce_before_main_content', 'packgens_wc_notices', 5 );

/**
 * Rewire the default WooCommerce hooks.
 *
 * @return void
 */
function packgens_wc_rewire_hooks() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper_start', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	add_action( 'woocommerce_before_main_content', 'packgens_wc_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'packgens_wc_wrapper_end', 10 );

	// The theme prints its own breadcrumb and page header.
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
	remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

	// Sidebar and default result count / ordering are not in the design.
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );

	// Single product: rebuilt in the theme templates.
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}
add_action( 'init', 'packgens_wc_rewire_hooks' );

/**
 * Products per page on catalogue views.
 *
 * @param int $per_page Current value.
 * @return int
 */
function packgens_wc_products_per_page( $per_page ) {
	return 12;
}
add_filter( 'loop_shop_per_page', 'packgens_wc_products_per_page', 20 );

/**
 * Four columns, matching the Figma grid.
 *
 * @return int
 */
function packgens_wc_loop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'packgens_wc_loop_columns', 20 );

/**
 * Append the unit label to prices, e.g. "£0.47 / unit".
 *
 * @param string     $price   Formatted price HTML.
 * @param WC_Product $product Product.
 * @return string
 */
function packgens_wc_price_unit( $price, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $price;
	}

	$unit = packgens_group_field( 'product_details', 'price_unit', $product->get_id() );

	if ( ! $unit ) {
		return $price;
	}

	return $price . ' <span class="pg-price__unit">' . esc_html( $unit ) . '</span>';
}
add_filter( 'woocommerce_get_price_html', 'packgens_wc_price_unit', 20, 2 );

/**
 * Keep the header cart count in sync after an AJAX add to cart.
 *
 * @param array $fragments Cart fragments.
 * @return array
 */
function packgens_wc_cart_fragment( $fragments ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

	ob_start();
	printf(
		'<span class="pg-header__cart-count pg-cart-count">%s</span>',
		esc_html( number_format_i18n( $count ) )
	);
	$fragments['span.pg-cart-count'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'packgens_wc_cart_fragment' );

/**
 * Quantity tiers configured on the product.
 *
 * @param int $product_id Product ID.
 * @return array<int,array{quantity:int,price:string,note:string,is_default:bool}>
 */
function packgens_product_tiers( $product_id ) {
	$rows  = (array) packgens_group_field( 'product_details', 'tiers', $product_id, array() );
	$tiers = array();

	foreach ( $rows as $row ) {
		$quantity = absint( $row['quantity'] ?? 0 );

		if ( ! $quantity ) {
			continue;
		}

		$tiers[] = array(
			'quantity'   => $quantity,
			'price'      => (string) ( $row['price'] ?? '' ),
			'note'       => (string) ( $row['note'] ?? '' ),
			'is_default' => ! empty( $row['is_default'] ),
		);
	}

	return $tiers;
}

/**
 * Product detail tabs, built from the design's fixed tabs plus editor extras.
 *
 * @param int $product_id Product ID.
 * @return array<int,array{key:string,title:string,icon:string,content:string}>
 */
function packgens_product_tabs( $product_id ) {
	$tabs  = array();
	$specs = (array) packgens_group_field( 'product_details', 'specs', $product_id, array() );

	if ( $specs ) {
		ob_start();
		?>
		<div class="pg-scroll-x">
			<table class="pg-spec-table">
				<tbody>
					<?php foreach ( $specs as $row ) : ?>
						<?php if ( empty( $row['label'] ) ) : ?>
							<?php continue; ?>
						<?php endif; ?>
						<tr>
							<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
							<td><?php echo esc_html( $row['value'] ?? '' ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
		$tabs[] = array(
			'key'     => 'specifications',
			'title'   => __( 'Product Specification', 'packgens' ),
			'icon'    => 'package',
			'content' => ob_get_clean(),
		);
	}

	$description = get_post_field( 'post_content', $product_id );

	if ( trim( (string) $description ) ) {
		$tabs[] = array(
			'key'     => 'description',
			'title'   => __( 'Product Descriptions', 'packgens' ),
			// Lines of copy: the document glyph belongs to Artwork Guidelines.
			'icon'    => 'align-left',
			'content' => apply_filters( 'the_content', $description ),
		);
	}

	foreach ( (array) packgens_group_field( 'product_details', 'tabs', $product_id, array() ) as $index => $tab ) {
		if ( empty( $tab['title'] ) ) {
			continue;
		}

		$tabs[] = array(
			'key'     => 'custom-' . $index,
			'title'   => $tab['title'],
			'icon'    => $tab['icon'] ?? '',
			'content' => (string) ( $tab['content'] ?? '' ),
		);
	}

	// FAQs configured on the product join the tab strip.
	$faqs = packgens_field( 'faqs_section', $product_id, array() );

	if ( is_array( $faqs ) && ! empty( $faqs['groups'] ) ) {
		ob_start();
		echo '<div class="pg-accordion" data-pg-accordion>';

		foreach ( (array) $faqs['groups'] as $group ) {
			foreach ( (array) ( $group['items'] ?? array() ) as $item ) {
				if ( empty( $item['title'] ) ) {
					continue;
				}

				$panel_id = packgens_unique_id( 'pg-ptab-faq' );
				?>
				<div class="pg-accordion__item">
					<h3 class="pg-accordion__heading">
						<button type="button" class="pg-accordion__trigger" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
							<span><?php echo esc_html( $item['title'] ); ?></span>
							<span class="pg-accordion__icon"><?php packgens_icon( 'plus' ); ?></span>
						</button>
					</h3>
					<div class="pg-accordion__panel" id="<?php echo esc_attr( $panel_id ); ?>" role="region">
						<div><div class="pg-accordion__content"><?php echo wp_kses_post( wpautop( $item['description'] ?? '' ) ); ?></div></div>
					</div>
				</div>
				<?php
			}
		}

		echo '</div>';

		$tabs[] = array(
			'key'     => 'faqs',
			'title'   => __( 'FAQs', 'packgens' ),
			'icon'    => 'help-circle',
			'content' => ob_get_clean(),
		);
	}

	return $tabs;
}

/**
 * Product schema is emitted by WooCommerce; add the theme's spec data to it.
 *
 * @param array      $markup  Structured data.
 * @param WC_Product $product Product.
 * @return array
 */
function packgens_wc_product_schema( $markup, $product ) {
	if ( ! $product instanceof WC_Product ) {
		return $markup;
	}

	$specs = (array) packgens_group_field( 'product_details', 'specs', $product->get_id(), array() );

	if ( empty( $specs ) ) {
		return $markup;
	}

	$properties = array();

	foreach ( $specs as $row ) {
		if ( empty( $row['label'] ) || empty( $row['value'] ) ) {
			continue;
		}

		$properties[] = array(
			'@type' => 'PropertyValue',
			'name'  => wp_strip_all_tags( $row['label'] ),
			'value' => wp_strip_all_tags( $row['value'] ),
		);
	}

	if ( $properties ) {
		$markup['additionalProperty'] = $properties;
	}

	return $markup;
}
add_filter( 'woocommerce_structured_data_product', 'packgens_wc_product_schema', 10, 2 );

/**
 * The design labels the primary product action "Add to Basket".
 *
 * @return string
 */
function packgens_add_to_cart_text() {
	return __( 'Add to Basket', 'packgens' );
}
add_filter( 'woocommerce_product_single_add_to_cart_text', 'packgens_add_to_cart_text' );

/**
 * Payment method logos shown under the buy box.
 *
 * @return int[]
 */
function packgens_payment_logos() {
	return array_filter( array_map( 'absint', (array) packgens_global( 'footer', 'payment_logos', array() ) ) );
}

/**
 * Print one half of the quantity stepper.
 *
 * WooCommerce's quantity template opens these two slots around the input, which
 * is why the buttons go in here rather than the template being overridden.
 *
 * @param int $step -1 to take one off, 1 to add one.
 * @return void
 */
function packgens_quantity_step_button( $step ) {
	$down = $step < 0;
	?>
	<button type="button"
		class="pg-qty-step pg-qty-step--<?php echo $down ? 'down' : 'up'; ?>"
		data-pg-qty="<?php echo (int) $step; ?>"
		tabindex="-1">
		<?php packgens_icon( $down ? 'minus' : 'plus' ); ?>
		<span class="pg-sr-only">
			<?php echo $down ? esc_html__( 'Reduce quantity', 'packgens' ) : esc_html__( 'Increase quantity', 'packgens' ); ?>
		</span>
	</button>
	<?php
}

/**
 * Wire the stepper onto the single product form only; the cart page keeps the
 * plain field it lays out around.
 *
 * @return void
 */
function packgens_quantity_steppers() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	add_action( 'woocommerce_before_quantity_input_field', function () {
		packgens_quantity_step_button( -1 );
	} );

	add_action( 'woocommerce_after_quantity_input_field', function () {
		packgens_quantity_step_button( 1 );
	} );
}
add_action( 'wp', 'packgens_quantity_steppers' );
