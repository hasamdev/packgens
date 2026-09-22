<?php
/**
 * Site-wide options, edited under Packgens -> Site Settings.
 *
 * Everything an editor may reasonably want to change without touching PHP lives
 * here: contact details, social profiles, the announcement bar and the header
 * call to action.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Option defaults. Also the single source of truth for sanitisation type.
 *
 * @return array<string,array{default:mixed,type:string,label:string,section:string,description?:string}>
 */
function packgens_option_schema() {
	return array(
		// Announcement bar.
		'topbar_enabled'      => array( 'default' => true, 'type' => 'checkbox', 'section' => 'packgens_topbar', 'label' => __( 'Show announcement bar', 'packgens' ) ),
		'topbar_message'      => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_topbar', 'label' => __( 'Announcement text', 'packgens' ) ),
		'topbar_chat_label'   => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_topbar', 'label' => __( 'Live chat label', 'packgens' ) ),
		'topbar_chat_url'     => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_topbar', 'label' => __( 'Live chat link', 'packgens' ) ),

		// Header.
		'header_cta_text'     => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_header', 'label' => __( 'Header button text', 'packgens' ) ),
		'header_cta_url'      => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_header', 'label' => __( 'Header button link', 'packgens' ) ),
		'header_shipping_title' => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_header', 'label' => __( 'Shipping note title', 'packgens' ) ),
		'header_shipping_text'  => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_header', 'label' => __( 'Shipping note subtitle', 'packgens' ) ),

		// Contact details.
		'phone'               => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_contact', 'label' => __( 'Primary phone', 'packgens' ) ),
		'phone_note'          => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_contact', 'label' => __( 'Phone caption', 'packgens' ), 'description' => __( 'For example: Advice &amp; Sales', 'packgens' ) ),
		'phone_secondary'     => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_contact', 'label' => __( 'Secondary phone', 'packgens' ) ),
		'whatsapp'            => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_contact', 'label' => __( 'WhatsApp number', 'packgens' ) ),
		'email'               => array( 'default' => '', 'type' => 'email', 'section' => 'packgens_contact', 'label' => __( 'Contact email', 'packgens' ) ),
		'address'             => array( 'default' => '', 'type' => 'textarea', 'section' => 'packgens_contact', 'label' => __( 'Postal address', 'packgens' ) ),
		'opening_hours'       => array( 'default' => '', 'type' => 'textarea', 'section' => 'packgens_contact', 'label' => __( 'Opening hours', 'packgens' ) ),

		// Social.
		'social_facebook'     => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_social', 'label' => __( 'Facebook', 'packgens' ) ),
		'social_instagram'    => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_social', 'label' => __( 'Instagram', 'packgens' ) ),
		'social_linkedin'     => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_social', 'label' => __( 'LinkedIn', 'packgens' ) ),
		'social_youtube'      => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_social', 'label' => __( 'YouTube', 'packgens' ) ),
		'social_x'            => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_social', 'label' => __( 'X / Twitter', 'packgens' ) ),

		// Global CTA targets.
		'quote_page_url'      => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_cta', 'label' => __( 'Quote page URL', 'packgens' ) ),
		'reviews_page_url'    => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_cta', 'label' => __( 'Reviews page URL', 'packgens' ) ),
		'shop_page_url'       => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_cta', 'label' => __( 'All products URL', 'packgens' ) ),

		// Forms.
		'form_recipient'      => array( 'default' => '', 'type' => 'email', 'section' => 'packgens_forms', 'label' => __( 'Enquiry notification email', 'packgens' ), 'description' => __( 'Defaults to the site admin email when empty.', 'packgens' ) ),
		'form_thankyou_url'   => array( 'default' => '', 'type' => 'url', 'section' => 'packgens_forms', 'label' => __( 'Thank you page URL', 'packgens' ) ),
		'form_success_text'   => array( 'default' => '', 'type' => 'text', 'section' => 'packgens_forms', 'label' => __( 'Success message', 'packgens' ) ),
	);
}

/**
 * Read a theme option with its schema default.
 *
 * @param string $key      Option key without prefix.
 * @param mixed  $fallback Value returned when the option and default are empty.
 * @return mixed
 */
function packgens_get_option( $key, $fallback = '' ) {
	$schema  = packgens_option_schema();
	$default = isset( $schema[ $key ]['default'] ) ? $schema[ $key ]['default'] : '';

	// Settings live on Packgens -> Site Settings. Anything still only in a
	// theme_mod from before the move is read from there, so nothing was lost.
	$value = function_exists( 'packgens_group_field' )
		? packgens_group_field( 'site_options', $key, 'option', null )
		: null;

	if ( null === $value || '' === $value ) {
		$value = get_theme_mod( 'packgens_' . $key, $default );
	}

	if ( '' === $value || null === $value ) {
		return $fallback;
	}

	return $value;
}

/**
 * Echo a theme option, escaped for HTML text.
 *
 * @param string $key      Option key.
 * @param string $fallback Fallback text.
 * @return void
 */
function packgens_option( $key, $fallback = '' ) {
	echo esc_html( packgens_get_option( $key, $fallback ) );
}

/**
 * Social profiles that have a URL saved.
 *
 * @return array<string,string> Icon key => URL.
 */
function packgens_social_links() {
	$map = array(
		'facebook'  => 'social_facebook',
		'instagram' => 'social_instagram',
		'linkedin'  => 'social_linkedin',
		'youtube'   => 'social_youtube',
		'x'         => 'social_x',
	);

	$links = array();

	foreach ( $map as $icon => $key ) {
		$url = packgens_get_option( $key );

		if ( $url ) {
			$links[ $icon ] = $url;
		}
	}

	return $links;
}
