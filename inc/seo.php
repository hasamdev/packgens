<?php
/**
 * Structured data and SEO plugin compatibility.
 *
 * The theme never outputs titles, descriptions or canonicals: those belong to
 * an SEO plugin. It only adds schema that a plugin cannot derive on its own,
 * and steps aside when the plugin already provides it.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a major SEO plugin is handling schema output.
 *
 * @return bool
 */
function packgens_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' );
}

/**
 * Print the JSON-LD graph.
 *
 * @return void
 */
function packgens_schema() {
	$graph = array();

	if ( ! packgens_seo_plugin_active() ) {
		$graph[] = packgens_organization_schema();
	}

	$faq = packgens_faq_schema();

	if ( $faq ) {
		$graph[] = $faq;
	}

	$breadcrumb = packgens_breadcrumb_schema();

	if ( $breadcrumb ) {
		$graph[] = $breadcrumb;
	}

	$graph = array_values( array_filter( $graph ) );

	if ( empty( $graph ) ) {
		return;
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_head', 'packgens_schema', 20 );

/**
 * Organisation node built from the Customizer contact details.
 *
 * @return array
 */
function packgens_organization_schema() {
	$schema = array(
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$logo_id = (int) get_theme_mod( 'custom_logo' );

	if ( $logo_id ) {
		$src = wp_get_attachment_image_src( $logo_id, 'full' );

		if ( $src ) {
			$schema['logo'] = array(
				'@type'  => 'ImageObject',
				'url'    => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
			);
		}
	}

	$phone = packgens_get_option( 'phone' );
	$email = packgens_get_option( 'email' );

	if ( $phone ) {
		$schema['contactPoint'] = array(
			array(
				'@type'       => 'ContactPoint',
				'telephone'   => $phone,
				'contactType' => 'sales',
			),
		);
	}

	if ( $email ) {
		$schema['email'] = $email;
	}

	$address = packgens_get_option( 'address' );

	if ( $address ) {
		$schema['address'] = array(
			'@type'         => 'PostalAddress',
			'streetAddress' => str_replace( array( "\r\n", "\n" ), ', ', $address ),
		);
	}

	$socials = array_values( packgens_social_links() );

	if ( $socials ) {
		$schema['sameAs'] = $socials;
	}

	return $schema;
}

/**
 * FAQPage node built from the FAQ section fields.
 *
 * @return array|null
 */
function packgens_faq_schema() {
	$context = packgens_field_context();
	$data    = packgens_field( 'faqs_section', $context, array() );

	if ( ! is_array( $data ) || empty( $data['groups'] ) ) {
		return null;
	}

	if ( array_key_exists( 'enable', $data ) && ! $data['enable'] ) {
		return null;
	}

	$entities = array();

	foreach ( (array) $data['groups'] as $group ) {
		foreach ( (array) ( $group['items'] ?? array() ) as $item ) {
			if ( empty( $item['title'] ) || empty( $item['description'] ) ) {
				continue;
			}

			$entities[] = array(
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $item['title'] ),
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $item['description'] ),
				),
			);
		}
	}

	if ( empty( $entities ) ) {
		return null;
	}

	return array(
		'@type'      => 'FAQPage',
		'@id'        => get_permalink() . '#faq',
		'mainEntity' => $entities,
	);
}

/**
 * BreadcrumbList node.
 *
 * @return array|null
 */
function packgens_breadcrumb_schema() {
	if ( packgens_seo_plugin_active() || is_front_page() ) {
		return null;
	}

	$items = packgens_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return null;
	}

	$list = array();

	foreach ( $items as $index => $item ) {
		$entry = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => $item['label'],
		);

		if ( ! empty( $item['url'] ) ) {
			$entry['item'] = $item['url'];
		}

		$list[] = $entry;
	}

	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list,
	);
}

/**
 * Tell an SEO plugin that testimonials and materials are not indexable pages.
 *
 * @param array $args Post type arguments.
 * @return array
 */
function packgens_noindex_components() {
	if ( is_singular( array( 'customer', 'material', PACKGENS_ENQUIRY_CPT ) ) ) {
		echo '<meta name="robots" content="noindex, follow">' . "\n";
	}
}
add_action( 'wp_head', 'packgens_noindex_components', 1 );
