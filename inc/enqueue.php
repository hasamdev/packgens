<?php
/**
 * Conditional asset loading.
 *
 * Only base.css, fonts.css, components.css, header.css and footer.css load on
 * every request. Everything else is attached to the templates that need it.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Versioned asset URL.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function packgens_asset( $relative ) {
	return PACKGENS_URI . ltrim( $relative, '/' );
}

/**
 * Asset version string.
 *
 * @param string $relative Path relative to the theme root.
 * @return string
 */
function packgens_asset_version( $relative ) {
	// The file's modified time, not the theme version: with a fixed version a
	// browser keeps serving its cached copy after a stylesheet changes.
	$path = PACKGENS_DIR . ltrim( $relative, '/' );

	if ( file_exists( $path ) ) {
		return PACKGENS_VERSION . '.' . filemtime( $path );
	}

	return PACKGENS_VERSION;
}

/**
 * Register a theme stylesheet.
 *
 * @param string   $handle Handle.
 * @param string   $file   File name inside assets/css.
 * @param string[] $deps   Dependencies.
 * @return void
 */
function packgens_register_style( $handle, $file, $deps = array() ) {
	$relative = 'assets/css/' . $file;

	wp_register_style( $handle, packgens_asset( $relative ), $deps, packgens_asset_version( $relative ) );
}

/**
 * Register a theme script. All theme scripts are ES modules-free, deferred.
 *
 * @param string   $handle Handle.
 * @param string   $file   File name inside assets/js.
 * @param string[] $deps   Dependencies.
 * @return void
 */
function packgens_register_script( $handle, $file, $deps = array() ) {
	$relative = 'assets/js/' . $file;

	wp_register_script(
		$handle,
		packgens_asset( $relative ),
		$deps,
		packgens_asset_version( $relative ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

/**
 * Describe the current request once so the enqueue logic stays readable.
 *
 * @return array<string,bool>
 */
function packgens_view_context() {
	static $context = null;

	if ( null !== $context ) {
		return $context;
	}

	$has_wc = function_exists( 'is_woocommerce' );

	$context = array(
		'front'        => is_front_page(),
		'page'         => is_page(),
		'blog_archive' => is_home() || is_category() || is_tag() || is_author() || is_date(),
		'blog_single'  => is_singular( 'post' ),
		'search'       => is_search(),
		'error'        => is_404(),
		'shop_archive' => $has_wc && ( is_shop() || is_product_category() || is_product_tag() ),
		'product'      => $has_wc && is_product(),
		'wc_pages'     => $has_wc && ( is_cart() || is_checkout() || is_account_page() ),
		'contact'      => is_page_template( 'templates/contact.php' ),
		'about'        => is_page_template( 'templates/about.php' ),
		'faq'          => is_page_template( 'templates/faq.php' ),
		'reviews'      => is_page_template( 'templates/reviews.php' ),
		'industries'   => is_page_template( 'templates/industries.php' ),
	);

	$context['woocommerce'] = $context['shop_archive'] || $context['product'] || $context['wc_pages'];

	return $context;
}

/**
 * Register and enqueue front-end assets.
 *
 * @return void
 */
function packgens_enqueue_assets() {
	$ctx = packgens_view_context();

	// --- Register everything first so dependencies resolve cleanly. --------
	packgens_register_style( 'packgens-fonts', 'fonts.css' );
	packgens_register_style( 'packgens-base', 'base.css', array( 'packgens-fonts' ) );
	packgens_register_style( 'packgens-components', 'components.css', array( 'packgens-base' ) );
	packgens_register_style( 'packgens-header', 'header.css', array( 'packgens-components' ) );
	packgens_register_style( 'packgens-footer', 'footer.css', array( 'packgens-components' ) );
	packgens_register_style( 'packgens-sections', 'sections.css', array( 'packgens-components' ) );
	packgens_register_style( 'packgens-home', 'home.css', array( 'packgens-sections' ) );
	packgens_register_style( 'packgens-catalog', 'catalog.css', array( 'packgens-sections' ) );
	packgens_register_style( 'packgens-product', 'product.css', array( 'packgens-sections' ) );
	packgens_register_style( 'packgens-blog', 'blog.css', array( 'packgens-sections' ) );
	packgens_register_style( 'packgens-page', 'page.css', array( 'packgens-sections' ) );
	packgens_register_style( 'packgens-woocommerce', 'woocommerce.css', array( 'packgens-sections' ) );

	// Swiper is vendored in assets/vendor so the theme stays self-contained.
	wp_register_style(
		'swiper',
		packgens_asset( 'assets/vendor/swiper/swiper-bundle.min.css' ),
		array(),
		PACKGENS_SWIPER_VERSION
	);

	wp_register_script(
		'swiper',
		packgens_asset( 'assets/vendor/swiper/swiper-bundle.min.js' ),
		array(),
		PACKGENS_SWIPER_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	packgens_register_script( 'packgens-app', 'app.js' );
	packgens_register_script( 'packgens-navigation', 'navigation.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-search', 'search.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-accordion', 'accordion.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-tabs', 'tabs.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-carousels', 'carousels.js', array( 'packgens-app', 'swiper' ) );
	packgens_register_script( 'packgens-marquee', 'marquee.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-forms', 'forms.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-archive', 'archive.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-product', 'product.js', array( 'packgens-app' ) );
	packgens_register_script( 'packgens-wishlist', 'wishlist.js', array( 'packgens-app' ) );

	// --- Always on. -------------------------------------------------------
	// Swiper first, so theme rules win on equal specificity.
	$needs_carousel = $ctx['front'] || $ctx['page'] || $ctx['shop_archive'] || $ctx['product']
		|| $ctx['blog_single'] || $ctx['reviews'] || $ctx['industries'];

	if ( $needs_carousel ) {
		wp_enqueue_style( 'swiper' );
	}

	// The brands row upgrades to a Swiper carousel where Swiper is on the page
	// and falls back to a native scroller elsewhere, so it loads everywhere.
	packgens_register_script(
		'packgens-brands',
		'brands.js',
		$needs_carousel ? array( 'packgens-app', 'swiper' ) : array( 'packgens-app' )
	);

	wp_enqueue_style( 'packgens-base' );
	wp_enqueue_style( 'packgens-components' );
	wp_enqueue_style( 'packgens-header' );
	wp_enqueue_style( 'packgens-footer' );
	wp_enqueue_style( 'packgens-sections' );

	wp_enqueue_script( 'packgens-app' );
	wp_enqueue_script( 'packgens-navigation' );
	wp_enqueue_script( 'packgens-search' );
	wp_enqueue_script( 'packgens-accordion' );
	wp_enqueue_script( 'packgens-forms' );
	wp_enqueue_script( 'packgens-brands' );

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'packgens-wishlist' );
	}

	wp_localize_script(
		'packgens-app',
		'packgensData',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'packgens_public' ),
			'homeUrl'  => home_url( '/' ),
			'i18n'     => array(
				'loading'      => __( 'Loading&hellip;', 'packgens' ),
				'loadMore'     => __( 'Load more', 'packgens' ),
				'noResults'    => __( 'No results found.', 'packgens' ),
				'searchHint'   => __( 'Type at least 3 characters.', 'packgens' ),
				'genericError' => __( 'Something went wrong. Please try again.', 'packgens' ),
				'sending'      => __( 'Sending&hellip;', 'packgens' ),
				'prevSlide'    => __( 'Previous slide', 'packgens' ),
				'nextSlide'    => __( 'Next slide', 'packgens' ),
				'firstSlide'   => __( 'This is the first slide', 'packgens' ),
				'lastSlide'    => __( 'This is the last slide', 'packgens' ),
				'slideLabel'   => __( 'Slide {{index}} of {{slidesLength}}', 'packgens' ),
				'carousel'     => __( 'carousel', 'packgens' ),
				'slide'        => __( 'slide', 'packgens' ),
			),
		)
	);

	// --- Carousels: any template that renders a slider. -------------------
	if ( $needs_carousel ) {
		wp_enqueue_script( 'packgens-carousels' );
		wp_enqueue_script( 'packgens-marquee' );
	}

	// --- Template specific. -----------------------------------------------
	if ( $ctx['front'] ) {
		wp_enqueue_style( 'packgens-home' );
		wp_enqueue_script( 'packgens-tabs' );
	}

	if ( $ctx['page'] || $ctx['error'] ) {
		wp_enqueue_style( 'packgens-page' );
	}

	if ( $ctx['about'] || $ctx['faq'] || $ctx['contact'] || $ctx['industries'] ) {
		wp_enqueue_script( 'packgens-tabs' );
	}

	if ( $ctx['shop_archive'] || $ctx['search'] ) {
		wp_enqueue_style( 'packgens-catalog' );
		wp_enqueue_script( 'packgens-archive' );
		wp_enqueue_script( 'packgens-tabs' );
	}

	if ( $ctx['product'] ) {
		wp_enqueue_style( 'packgens-product' );
		wp_enqueue_script( 'packgens-product' );
		wp_enqueue_script( 'packgens-tabs' );
	}

	if ( $ctx['blog_archive'] || $ctx['blog_single'] || $ctx['search'] ) {
		wp_enqueue_style( 'packgens-blog' );
		wp_enqueue_script( 'packgens-archive' );
	}

	if ( $ctx['reviews'] ) {
		wp_enqueue_script( 'packgens-archive' );
	}

	// The header heart and the save button on product cards appear on pages that
	// are not WooCommerce views, so this sheet follows WooCommerce being active
	// rather than the view being one of its own.
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style( 'packgens-woocommerce' );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'packgens_enqueue_assets' );

/**
 * Preload the fonts that render above the fold.
 *
 * @return void
 */
function packgens_preload_fonts() {
	$fonts = array(
		'assets/fonts/plus-jakarta-sans-variable-latin.woff2',
		'assets/fonts/inter-variable-latin.woff2',
	);

	foreach ( $fonts as $font ) {
		if ( ! file_exists( PACKGENS_DIR . $font ) ) {
			continue;
		}

		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( packgens_asset( $font ) )
		);
	}
}
add_action( 'wp_head', 'packgens_preload_fonts', 2 );

/**
 * Editor styles need the tokens too.
 *
 * @return void
 */
function packgens_admin_assets() {
	packgens_register_style( 'packgens-admin', 'admin.css' );
	wp_enqueue_style( 'packgens-admin' );
}
add_action( 'admin_enqueue_scripts', 'packgens_admin_assets' );
