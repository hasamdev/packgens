<?php
/**
 * Content model: Secure Custom Fields local field groups.
 *
 * Registering the groups in PHP keeps the model in version control, so the
 * theme brings its own content structure to any install. Every group follows
 * the same shape:
 *
 *   {name}_section  (group)
 *     enable        (true_false)
 *     title         (text)
 *     description   (textarea)
 *     ...section specific fields
 *     button        (link)
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * The options screen holding the defaults every product category inherits.
 */
define( 'PACKGENS_CAT_DEFAULTS_PAGE', 'packgens-category-defaults' );
define( 'PACKGENS_CAT_DEFAULTS_ID', 'packgens_cat_defaults' );

/**
 * Locations where page-level sections may be edited.
 *
 * @return array
 */
function packgens_section_locations() {
	return array(
		array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ),
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
		array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'product_cat' ) ),
		array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ),
		// Every section is editable once for all product categories here; a term
		// only stores what it wants to differ. See packgens_section_defaults().
		array( array( 'param' => 'options_page', 'operator' => '==', 'value' => PACKGENS_CAT_DEFAULTS_PAGE ) ),
	);
}

/**
 * Build the standard opening fields of a section group.
 *
 * @param string $prefix   Unique key prefix.
 * @param array  $args     Which common fields to include.
 * @return array
 */
function packgens_section_base_fields( $prefix, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'eyebrow'     => false,
			'title'       => true,
			'description' => true,
		)
	);

	$fields = array(
		array(
			'key'           => $prefix . '_enable',
			'label'         => __( 'Show this section', 'packgens' ),
			'name'          => 'enable',
			'type'          => 'true_false',
			'ui'            => 1,
			'default_value' => 1,
		),
	);

	if ( $args['eyebrow'] ) {
		$fields[] = array(
			'key'   => $prefix . '_eyebrow',
			'label' => __( 'Eyebrow', 'packgens' ),
			'name'  => 'eyebrow',
			'type'  => 'text',
		);
	}

	if ( $args['title'] ) {
		$fields[] = array(
			'key'   => $prefix . '_title',
			'label' => __( 'Title', 'packgens' ),
			'name'  => 'title',
			'type'  => 'text',
		);
	}

	if ( $args['description'] ) {
		$fields[] = array(
			'key'   => $prefix . '_description',
			'label' => __( 'Description', 'packgens' ),
			'name'  => 'description',
			'type'  => 'textarea',
			'rows'  => 3,
		);
	}

	return $fields;
}

/**
 * Build a link (button) field.
 *
 * @param string $key   Field key.
 * @param string $name  Field name.
 * @param string $label Field label.
 * @return array
 */
function packgens_link_field( $key, $name = 'button', $label = null ) {
	return array(
		'key'   => $key,
		'label' => null === $label ? __( 'Button', 'packgens' ) : $label,
		'name'  => $name,
		'type'  => 'link',
	);
}

/**
 * Wrap section fields into a named group field.
 *
 * @param string $key    Group field key.
 * @param string $name   Group field name.
 * @param string $label  Group label.
 * @param array  $fields Sub fields.
 * @return array
 */
function packgens_group_wrapper( $key, $name, $label, $fields ) {
	return array(
		'key'        => $key,
		'label'      => $label,
		'name'       => $name,
		'type'       => 'group',
		'layout'     => 'block',
		'sub_fields' => $fields,
	);
}

/**
 * Build a run of plain text fields from a `name => array( label, placeholder )`
 * map.
 *
 * The placeholder carries the wording the theme falls back to, so an editor can
 * see what a blank field will render before they type anything.
 *
 * @param string $prefix Key prefix, unique per group.
 * @param array  $fields Field map.
 * @param string $type   Field type: text or textarea.
 * @return array
 */
function packgens_label_fields( $prefix, $fields, $type = 'text' ) {
	$out = array();

	foreach ( $fields as $name => $spec ) {
		$field = array(
			'key'         => 'field_pg_lbl_' . $prefix . '_' . $name,
			'label'       => $spec[0],
			'name'        => $name,
			'type'        => $type,
			'placeholder' => $spec[1],
		);

		if ( 'textarea' === $type ) {
			$field['rows'] = 2;
		}

		$out[] = $field;
	}

	return $out;
}

/**
 * Turn the site option schema into SCF tabs.
 *
 * The schema in theme-options.php stays the single source of truth for keys,
 * types and defaults; this only renders it. Nothing carries a `default_value`,
 * so an untouched field reads as empty and `packgens_get_option()` can fall
 * back to the value the Customizer used to hold.
 *
 * @return array
 */
function packgens_site_option_fields() {
	$sections = array(
		'packgens_topbar'  => __( 'Announcement bar', 'packgens' ),
		'packgens_header'  => __( 'Header', 'packgens' ),
		'packgens_contact' => __( 'Contact details', 'packgens' ),
		'packgens_social'  => __( 'Social profiles', 'packgens' ),
		'packgens_cta'     => __( 'Global links', 'packgens' ),
		'packgens_forms'   => __( 'Form handling', 'packgens' ),
	);

	$types = array(
		'checkbox' => 'true_false',
		'url'      => 'url',
		'email'    => 'email',
		'textarea' => 'textarea',
		'text'     => 'text',
	);

	$schema = packgens_option_schema();
	$fields = array();

	foreach ( $sections as $section => $title ) {
		$fields[] = array(
			'key'   => 'field_pg_site_tab_' . $section,
			'label' => $title,
			'type'  => 'tab',
		);

		foreach ( $schema as $key => $args ) {
			if ( $args['section'] !== $section ) {
				continue;
			}

			$field = array(
				'key'   => 'field_pg_site_' . $key,
				'label' => $args['label'],
				'name'  => $key,
				'type'  => isset( $types[ $args['type'] ] ) ? $types[ $args['type'] ] : 'text',
			);

			if ( ! empty( $args['description'] ) ) {
				$field['instructions'] = $args['description'];
			}

			if ( 'true_false' === $field['type'] ) {
				$field['ui']            = 1;
				$field['default_value'] = (bool) $args['default'];
			}

			if ( 'textarea' === $field['type'] ) {
				$field['rows'] = 3;
			}

			$fields[] = $field;
		}
	}

	return $fields;
}

/**
 * Register a field group.
 *
 * @param string $key       Group key.
 * @param string $title     Group title.
 * @param array  $fields    Fields.
 * @param array  $location  Location rules.
 * @param int    $order     Menu order.
 * @return void
 */
function packgens_add_group( $key, $title, $fields, $location, $order = 0 ) {
	acf_add_local_field_group(
		array(
			'key'                   => $key,
			'title'                 => $title,
			'fields'                => $fields,
			'location'              => $location,
			'menu_order'            => $order,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'active'                => true,
			'hide_on_screen'        => array(),
		)
	);
}

/**
 * Register every local field group.
 *
 * @return void
 */
function packgens_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$page = packgens_section_locations();

	/* ------------------------------------------------------------------
	 * Hero
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_hero',
		__( 'Hero', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_hero',
				'hero_section',
				__( 'Hero', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_hero', array( 'eyebrow' => true ) ),
					array(
						array(
							'key'           => 'field_pg_hero_layout',
							'label'         => __( 'Layout', 'packgens' ),
							'name'          => 'layout',
							'type'          => 'select',
							'choices'       => array(
								'center' => __( 'Centred over a background image', 'packgens' ),
								'split'  => __( 'Copy left, media right', 'packgens' ),
								'form'   => __( 'Copy left, quote form right', 'packgens' ),
								'compact' => __( 'Compact band (inner pages)', 'packgens' ),
							),
							'default_value' => 'center',
						),
						array(
							'key'          => 'field_pg_hero_image',
							'label'        => __( 'Background / side image', 'packgens' ),
							'name'         => 'image',
							'type'         => 'image',
							'return_format' => 'id',
							'preview_size' => 'medium',
						),
						array(
							'key'   => 'field_pg_hero_rating',
							'label' => __( 'Rating badge text', 'packgens' ),
							'name'  => 'rating_text',
							'type'  => 'text',
							'instructions' => __( 'For example: 4.9  12,400+ reviews. Leave empty to hide.', 'packgens' ),
						),
						array(
							'key'           => 'field_pg_hero_rating_value',
							'label'         => __( 'Rating value', 'packgens' ),
							'name'          => 'rating_value',
							'type'          => 'number',
							'min'           => 0,
							'max'           => 5,
							'step'          => 0.1,
							'default_value' => 5,
						),
						packgens_link_field( 'field_pg_hero_btn1', 'button_1', __( 'Primary button', 'packgens' ) ),
						packgens_link_field( 'field_pg_hero_btn2', 'button_2', __( 'Secondary button', 'packgens' ) ),
						array(
							'key'          => 'field_pg_hero_slides',
							'label'        => __( 'Slides', 'packgens' ),
							'name'         => 'slides',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add slide', 'packgens' ),
							'instructions' => __( 'Leave empty to show the single heading above. Two or more slides turn the hero into a slider.', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_hero_slide_eyebrow', 'label' => __( 'Eyebrow', 'packgens' ), 'name' => 'eyebrow', 'type' => 'text' ),
								array( 'key' => 'field_pg_hero_slide_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_hero_slide_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 3 ),
								packgens_link_field( 'field_pg_hero_slide_btn1', 'button_1' ),
								packgens_link_field( 'field_pg_hero_slide_btn2', 'button_2' ),
							),
						),
						array(
							'key'          => 'field_pg_hero_tabs',
							'label'        => __( 'Foot tabs', 'packgens' ),
							'name'         => 'tabs',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add tab', 'packgens' ),
							'instructions' => __( 'Shown as a row of tabs across the bottom of the band. The first is the active one.', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_hero_tab_label', 'label' => __( 'Label', 'packgens' ), 'name' => 'label', 'type' => 'text' ),
								packgens_link_field( 'field_pg_hero_tab_link', 'link', __( 'Link', 'packgens' ) ),
							),
						),
						array(
							'key'          => 'field_pg_hero_features',
							'label'        => __( 'Trust strip', 'packgens' ),
							'name'         => 'features',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add item', 'packgens' ),
							'instructions' => __( 'Shown as a bar overlapping the bottom of the hero.', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_hero_feature_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
								array( 'key' => 'field_pg_hero_feature_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_hero_feature_text', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
							),
						),
					)
				)
			),
		),
		$page,
		1
	);

	/* ------------------------------------------------------------------
	 * About / media + text
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_about',
		__( 'About Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_about',
				'about_section',
				__( 'About', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_about', array( 'eyebrow' => true ) ),
					array(
						array(
							'key'          => 'field_pg_about_body',
							'label'        => __( 'Body copy', 'packgens' ),
							'name'         => 'body',
							'type'         => 'wysiwyg',
							'media_upload' => 0,
							'toolbar'      => 'basic',
						),
						array(
							'key'           => 'field_pg_about_image_left',
							'label'         => __( 'Left image', 'packgens' ),
							'name'          => 'image_left',
							'type'          => 'image',
							'return_format' => 'id',
						),
						array(
							'key'           => 'field_pg_about_image_right',
							'label'         => __( 'Right image', 'packgens' ),
							'name'          => 'image_right',
							'type'          => 'image',
							'return_format' => 'id',
						),
						array(
							'key'           => 'field_pg_about_rating_logo',
							'label'         => __( 'Rating logo', 'packgens' ),
							'name'          => 'rating_logo',
							'type'          => 'image',
							'return_format' => 'id',
							'instructions'  => __( 'Review platform badge shown above the left image.', 'packgens' ),
						),
						array(
							'key'   => 'field_pg_about_rating_score',
							'label' => __( 'Rating score', 'packgens' ),
							'name'  => 'rating_score',
							'type'  => 'text',
						),
						packgens_link_field( 'field_pg_about_rating_link', 'rating_link', __( 'Reviews link', 'packgens' ) ),
						array(
							'key'          => 'field_pg_about_proof_avatars',
							'label'        => __( 'Reviewer avatars', 'packgens' ),
							'name'         => 'proof_avatars',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add avatar', 'packgens' ),
							'instructions' => __( 'Shown in the badge under the right image.', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_about_proof_avatar', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
							),
						),
						array(
							'key'           => 'field_pg_about_proof_rating',
							'label'         => __( 'Reviewer rating', 'packgens' ),
							'name'          => 'proof_rating',
							'type'          => 'number',
							'min'           => 0,
							'max'           => 5,
							'step'          => 0.5,
							'default_value' => 5,
						),
						array(
							'key'   => 'field_pg_about_proof_text',
							'label' => __( 'Reviewer text', 'packgens' ),
							'name'  => 'proof_text',
							'type'  => 'text',
						),
						array(
							'key'        => 'field_pg_about_points',
							'label'      => __( 'Checklist', 'packgens' ),
							'name'       => 'points',
							'type'       => 'repeater',
							'layout'     => 'table',
							'button_label' => __( 'Add point', 'packgens' ),
							'sub_fields' => array(
								array( 'key' => 'field_pg_about_point_text', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
							),
						),
						packgens_link_field( 'field_pg_about_btn', 'button' ),
						array( 'key' => 'field_pg_about_contact_label', 'label' => __( 'Direct call label', 'packgens' ), 'name' => 'contact_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_about_contact_phone', 'label' => __( 'Direct call number', 'packgens' ), 'name' => 'contact_phone', 'type' => 'text' ),
						array( 'key' => 'field_pg_about_contact_photo', 'label' => __( 'Direct call photo', 'packgens' ), 'name' => 'contact_photo', 'type' => 'image', 'return_format' => 'id' ),
					)
				)
			),
		),
		$page,
		2
	);

	/* ------------------------------------------------------------------
	 * Contact page: section head, channel notes and the trust band
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_contact',
		__( 'Contact Page', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_contact',
				'contact_section',
				__( 'Contact', 'packgens' ),
				array(
					array( 'key' => 'field_pg_contact_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text', 'default_value' => 'Still Have Questions?' ),
					array( 'key' => 'field_pg_contact_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_pg_contact_call_note', 'label' => __( 'Call note', 'packgens' ), 'name' => 'call_note', 'type' => 'text' ),
					array( 'key' => 'field_pg_contact_email_note', 'label' => __( 'Email note', 'packgens' ), 'name' => 'email_note', 'type' => 'text' ),
					array( 'key' => 'field_pg_contact_chat_note', 'label' => __( 'Chat note', 'packgens' ), 'name' => 'chat_note', 'type' => 'text' ),
					array( 'key' => 'field_pg_contact_chat_label', 'label' => __( 'Chat button', 'packgens' ), 'name' => 'chat_label', 'type' => 'text', 'default_value' => 'Live Chat' ),
					array( 'key' => 'field_pg_contact_follow', 'label' => __( 'Follow label', 'packgens' ), 'name' => 'follow_label', 'type' => 'text', 'default_value' => 'Follow Us' ),
					packgens_group_wrapper(
						'field_pg_contact_trust',
						'trust',
						__( 'Trust band', 'packgens' ),
						array(
							array( 'key' => 'field_pg_contact_trust_enable', 'label' => __( 'Show the band', 'packgens' ), 'name' => 'enable', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1 ),
							array( 'key' => 'field_pg_contact_trust_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
							array( 'key' => 'field_pg_contact_trust_score', 'label' => __( 'Rating score', 'packgens' ), 'name' => 'rating_value', 'type' => 'text' ),
							array( 'key' => 'field_pg_contact_trust_count', 'label' => __( 'Rating count', 'packgens' ), 'name' => 'rating_count', 'type' => 'text' ),
							array( 'key' => 'field_pg_contact_trust_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
							array(
								'key'          => 'field_pg_contact_trust_points',
								'label'        => __( 'Points', 'packgens' ),
								'name'         => 'points',
								'type'         => 'repeater',
								'layout'       => 'table',
								'button_label' => __( 'Add point', 'packgens' ),
								'sub_fields'   => array(
									array( 'key' => 'field_pg_contact_trust_point', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
								),
							),
						)
					),
				)
			),
		),
		array(
			array(
				array( 'param' => 'page_template', 'operator' => '==', 'value' => 'templates/contact.php' ),
			),
		),
		3
	);

	/* ------------------------------------------------------------------
	 * Mission / vision / values tabs
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_values',
		__( 'Mission, Vision, Values', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_values',
				'values_section',
				__( 'Mission, vision, values', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_values', array( 'title' => false, 'description' => false ) ),
					array(
						array(
							'key'          => 'field_pg_values_items',
							'label'        => __( 'Tabs', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add tab', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_values_tab', 'label' => __( 'Tab label', 'packgens' ), 'name' => 'label', 'type' => 'text' ),
								array( 'key' => 'field_pg_values_icon', 'label' => __( 'Tab icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
								array( 'key' => 'field_pg_values_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_values_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 4 ),
								array(
									'key'          => 'field_pg_values_points',
									'label'        => __( 'Checklist', 'packgens' ),
									'name'         => 'points',
									'type'         => 'repeater',
									'layout'       => 'table',
									'button_label' => __( 'Add point', 'packgens' ),
									'sub_fields'   => array(
										array( 'key' => 'field_pg_values_point', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
									),
								),
								array( 'key' => 'field_pg_values_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
							),
						),
					)
				)
			),
		),
		$page,
		3
	);

	/* ------------------------------------------------------------------
	 * Quote band
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_quote_band',
		__( 'Quote Band', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_quote_band',
				'quote_band_section',
				__( 'Quote band', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_quote_band' ),
					array(
						array( 'key' => 'field_pg_quote_band_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
						array(
							'key'          => 'field_pg_quote_band_items',
							'label'        => __( 'Assurances', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add assurance', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_quote_band_item_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
								array( 'key' => 'field_pg_quote_band_item_text', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
							),
						),
						array( 'key' => 'field_pg_quote_band_phone_label', 'label' => __( 'Phone label', 'packgens' ), 'name' => 'phone_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_quote_band_phone', 'label' => __( 'Phone', 'packgens' ), 'name' => 'phone', 'type' => 'text' ),
						packgens_link_field( 'field_pg_quote_band_btn', 'button' ),
						array( 'key' => 'field_pg_quote_band_note', 'label' => __( 'Note under the button', 'packgens' ), 'name' => 'note', 'type' => 'text' ),
					)
				)
			),
		),
		$page,
		4
	);

	/* ------------------------------------------------------------------
	 * Categories carousel
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_categories',
		__( 'Categories Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_categories',
				'categories_section',
				__( 'Categories', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_categories' ),
					array(
						array(
							'key'           => 'field_pg_categories_source',
							'label'         => __( 'Source', 'packgens' ),
							'name'          => 'source',
							'type'          => 'select',
							'choices'       => array(
								'auto'   => __( 'All product categories', 'packgens' ),
								'manual' => __( 'Choose categories', 'packgens' ),
							),
							'default_value' => 'auto',
						),
						array(
							'key'           => 'field_pg_categories_items',
							'label'         => __( 'Categories', 'packgens' ),
							'name'          => 'items',
							'type'          => 'taxonomy',
							'taxonomy'      => 'product_cat',
							'field_type'    => 'multi_select',
							'return_format' => 'id',
							'add_term'      => 0,
							'conditional_logic' => array(
								array( array( 'field' => 'field_pg_categories_source', 'operator' => '==', 'value' => 'manual' ) ),
							),
						),
						array(
							'key'           => 'field_pg_categories_limit',
							'label'         => __( 'Maximum items', 'packgens' ),
							'name'          => 'limit',
							'type'          => 'number',
							'default_value' => 12,
							'min'           => 1,
							'max'           => 40,
						),
						packgens_link_field( 'field_pg_categories_btn', 'button' ),
					)
				)
			),
		),
		$page,
		3
	);

	/* ------------------------------------------------------------------
	 * Products grid
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_products',
		__( 'Products Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_products',
				'products_section',
				__( 'Products', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_products' ),
					array(
						array(
							'key'           => 'field_pg_products_source',
							'label'         => __( 'Source', 'packgens' ),
							'name'          => 'source',
							'type'          => 'select',
							'choices'       => array(
								'featured' => __( 'Featured products', 'packgens' ),
								'latest'   => __( 'Latest products', 'packgens' ),
								'category' => __( 'Products in this category', 'packgens' ),
								'manual'   => __( 'Choose products', 'packgens' ),
							),
							'default_value' => 'featured',
						),
						array(
							'key'           => 'field_pg_products_items',
							'label'         => __( 'Products', 'packgens' ),
							'name'          => 'products',
							'type'          => 'relationship',
							'post_type'     => array( 'product' ),
							'return_format' => 'id',
							'filters'       => array( 'search', 'taxonomy' ),
							'conditional_logic' => array(
								array( array( 'field' => 'field_pg_products_source', 'operator' => '==', 'value' => 'manual' ) ),
							),
						),
						array(
							'key'           => 'field_pg_products_limit',
							'label'         => __( 'Maximum items', 'packgens' ),
							'name'          => 'limit',
							'type'          => 'number',
							'default_value' => 8,
							'min'           => 1,
							'max'           => 48,
						),
						array(
							'key'           => 'field_pg_products_layout',
							'label'         => __( 'Layout', 'packgens' ),
							'name'          => 'layout',
							'type'          => 'select',
							'choices'       => array(
								'grid'     => __( 'Grid', 'packgens' ),
								'carousel' => __( 'Carousel', 'packgens' ),
							),
							'default_value' => 'grid',
						),
						packgens_link_field( 'field_pg_products_btn', 'button' ),
					)
				)
			),
		),
		$page,
		4
	);

	/* ------------------------------------------------------------------
	 * Process steps
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_process',
		__( 'Process Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_process',
				'process_section',
				__( 'Process', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_process' ),
					array(
						array(
							'key'           => 'field_pg_process_image',
							'label'         => __( 'Illustration', 'packgens' ),
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'id',
						),
						array( 'key' => 'field_pg_process_cta_title', 'label' => __( 'CTA title', 'packgens' ), 'name' => 'cta_title', 'type' => 'text' ),
						array( 'key' => 'field_pg_process_cta_desc', 'label' => __( 'CTA description', 'packgens' ), 'name' => 'cta_description', 'type' => 'textarea', 'rows' => 2 ),
						array( 'key' => 'field_pg_process_cta_phone_label', 'label' => __( 'CTA phone label', 'packgens' ), 'name' => 'cta_phone_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_process_cta_phone', 'label' => __( 'CTA phone', 'packgens' ), 'name' => 'cta_phone', 'type' => 'text' ),
						packgens_link_field( 'field_pg_process_cta_btn', 'cta_button', __( 'CTA button', 'packgens' ) ),
						array(
							'key'          => 'field_pg_process_items',
							'label'        => __( 'Steps', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add step', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_process_item_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_process_item_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
								array( 'key' => 'field_pg_process_item_icon', 'label' => __( 'Icon image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id', 'instructions' => __( 'Optional. Leave empty to use the built-in step icon.', 'packgens' ) ),
								array( 'key' => 'field_pg_process_item_photo', 'label' => __( 'Illustration', 'packgens' ), 'name' => 'photo', 'type' => 'image', 'return_format' => 'id', 'instructions' => __( 'Shown beside the list while this step is selected.', 'packgens' ) ),
							),
						),
					)
				)
			),
		),
		$page,
		5
	);

	/* ------------------------------------------------------------------
	 * Why choose us
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_why',
		__( 'Why Choose Us Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_why',
				'why_section',
				__( 'Why Choose Us', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_why', array( 'eyebrow' => true ) ),
					array(
						array(
							'key'          => 'field_pg_why_items',
							'label'        => __( 'Features', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add feature', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_why_item_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
								array( 'key' => 'field_pg_why_item_image', 'label' => __( 'Custom icon image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
								array( 'key' => 'field_pg_why_item_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_why_item_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
							),
						),
						array(
							'key'           => 'field_pg_why_media',
							'label'         => __( 'Side images', 'packgens' ),
							'name'          => 'media',
							'type'          => 'gallery',
							'return_format' => 'id',
							'max'           => 2,
						),
					)
				)
			),
		),
		$page,
		6
	);

	/* ------------------------------------------------------------------
	 * Comparison table
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_comparison',
		__( 'Comparison Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_comparison',
				'comparison_section',
				__( 'Comparison', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_comparison', array( 'eyebrow' => true ) ),
					array(
						array( 'key' => 'field_pg_comparison_heading', 'label' => __( 'Table heading', 'packgens' ), 'name' => 'table_heading', 'type' => 'text' ),
						array( 'key' => 'field_pg_comparison_us', 'label' => __( 'Our column label', 'packgens' ), 'name' => 'us_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_comparison_them', 'label' => __( 'Their column label', 'packgens' ), 'name' => 'them_label', 'type' => 'text' ),
						array(
							'key'          => 'field_pg_comparison_rows',
							'label'        => __( 'Rows', 'packgens' ),
							'name'         => 'rows',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add row', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_comparison_row_text', 'label' => __( 'Feature', 'packgens' ), 'name' => 'text', 'type' => 'textarea', 'rows' => 2 ),
								array( 'key' => 'field_pg_comparison_row_us', 'label' => __( 'We offer it', 'packgens' ), 'name' => 'us', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1 ),
								array( 'key' => 'field_pg_comparison_row_them', 'label' => __( 'They offer it', 'packgens' ), 'name' => 'them', 'type' => 'true_false', 'ui' => 1 ),
							),
						),
						array(
							'key'          => 'field_pg_comparison_points',
							'label'        => __( 'Points below the table', 'packgens' ),
							'name'         => 'points',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add point', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_comparison_point_text', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
							),
						),
						array(
							'key'        => 'field_pg_comparison_promo',
							'label'      => __( 'Promo card', 'packgens' ),
							'name'       => 'promo',
							'type'       => 'group',
							'layout'     => 'block',
							'sub_fields' => array(
								array( 'key' => 'field_pg_comparison_promo_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
								array( 'key' => 'field_pg_comparison_promo_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								packgens_link_field( 'field_pg_comparison_promo_btn', 'button' ),
							),
						),
					)
				)
			),
		),
		$page,
		7
	);

	/* ------------------------------------------------------------------
	 * Materials
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_materials',
		__( 'Materials Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_materials',
				'materials_section',
				__( 'Materials', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_materials' ),
					array(
						array(
							'key'           => 'field_pg_materials_terms',
							'label'         => __( 'Material groups (tabs)', 'packgens' ),
							'name'          => 'items',
							'type'          => 'taxonomy',
							'taxonomy'      => 'category_materials',
							'field_type'    => 'multi_select',
							'return_format' => 'id',
							'add_term'      => 0,
						),
						packgens_link_field( 'field_pg_materials_btn', 'button' ),
					)
				)
			),
		),
		$page,
		8
	);

	/* ------------------------------------------------------------------
	 * Industries
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_industries',
		__( 'Industries Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_industries',
				'industries_section',
				__( 'Industries', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_industries' ),
					array(
						array(
							'key'          => 'field_pg_industries_items',
							'label'        => __( 'Industries', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add industry', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_industries_item_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
								array( 'key' => 'field_pg_industries_item_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_industries_item_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
								packgens_link_field( 'field_pg_industries_item_btn', 'button' ),
							),
						),
						packgens_link_field( 'field_pg_industries_btn', 'button' ),
					)
				)
			),
		),
		$page,
		9
	);

	/* ------------------------------------------------------------------
	 * FAQs
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_faqs',
		__( 'FAQ Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_faqs',
				'faqs_section',
				__( 'FAQs', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_faqs' ),
					array(
						array( 'key' => 'field_pg_faqs_nav_label', 'label' => __( 'Sidebar label', 'packgens' ), 'name' => 'nav_label', 'type' => 'text', 'instructions' => __( 'Heading above the group links on the FAQ page.', 'packgens' ), 'default_value' => 'Friendly ask question' ),
						array(
							'key'          => 'field_pg_faqs_groups',
							'label'        => __( 'FAQ groups', 'packgens' ),
							'name'         => 'groups',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add group', 'packgens' ),
							'instructions' => __( 'Use one group for a simple list, or several to split the FAQs under headings.', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_faqs_group_title', 'label' => __( 'Group title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_faqs_group_sub', 'label' => __( 'Group subtitle', 'packgens' ), 'name' => 'subtitle', 'type' => 'text' ),
								array(
									'key'          => 'field_pg_faqs_items',
									'label'        => __( 'Questions', 'packgens' ),
									'name'         => 'items',
									'type'         => 'repeater',
									'layout'       => 'block',
									'button_label' => __( 'Add question', 'packgens' ),
									'sub_fields'   => array(
										array( 'key' => 'field_pg_faqs_item_q', 'label' => __( 'Question', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
										array( 'key' => 'field_pg_faqs_item_a', 'label' => __( 'Answer', 'packgens' ), 'name' => 'description', 'type' => 'wysiwyg', 'media_upload' => 0, 'toolbar' => 'basic', 'rows' => 4 ),
									),
								),
							),
						),
						packgens_link_field( 'field_pg_faqs_btn', 'button' ),
					)
				)
			),
		),
		$page,
		10
	);

	/* ------------------------------------------------------------------
	 * Reviews
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_reviews',
		__( 'Reviews Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_reviews',
				'reviews_section',
				__( 'Reviews', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_reviews' ),
					array(
						array(
							'key'           => 'field_pg_reviews_items',
							'label'         => __( 'Testimonials', 'packgens' ),
							'name'          => 'items',
							'type'          => 'relationship',
							'post_type'     => array( 'customer' ),
							'return_format' => 'id',
							'instructions'  => __( 'Leave empty to show the most recent testimonials.', 'packgens' ),
						),
						array(
							'key'          => 'field_pg_reviews_badges',
							'label'        => __( 'Rating badges', 'packgens' ),
							'name'         => 'badges',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add badge', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_reviews_badge_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
								array( 'key' => 'field_pg_reviews_badge_name', 'label' => __( 'Name', 'packgens' ), 'name' => 'name', 'type' => 'text' ),
								array( 'key' => 'field_pg_reviews_badge_score', 'label' => __( 'Score', 'packgens' ), 'name' => 'score', 'type' => 'text' ),
								array( 'key' => 'field_pg_reviews_badge_count', 'label' => __( 'Review count', 'packgens' ), 'name' => 'count', 'type' => 'text' ),
								packgens_link_field( 'field_pg_reviews_badge_link', 'link', __( 'Link', 'packgens' ) ),
							),
						),
						packgens_link_field( 'field_pg_reviews_btn', 'button' ),
					)
				)
			),
		),
		$page,
		11
	);

	/* ------------------------------------------------------------------
	 * Blog
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_blogs',
		__( 'Blog Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_blogs',
				'blogs_section',
				__( 'Blog', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_blogs' ),
					array(
						array(
							'key'           => 'field_pg_blogs_limit',
							'label'         => __( 'Number of posts', 'packgens' ),
							'name'          => 'limit',
							'type'          => 'number',
							'default_value' => 3,
							'min'           => 1,
							'max'           => 12,
						),
						packgens_link_field( 'field_pg_blogs_btn', 'button' ),
					)
				)
			),
		),
		$page,
		12
	);

	/* ------------------------------------------------------------------
	 * Brand logos
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_brands',
		__( 'Trusted Brands Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_brands',
				'brands_section',
				__( 'Trusted Brands', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_brands' ),
					array(
						array(
							'key'           => 'field_pg_brands_logos',
							'label'         => __( 'Logos', 'packgens' ),
							'name'          => 'logos',
							'type'          => 'gallery',
							'return_format' => 'id',
						),
					)
				)
			),
		),
		$page,
		13
	);

	/* ------------------------------------------------------------------
	 * Stats
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_stats',
		__( 'Statistics Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_stats',
				'stats_section',
				__( 'Statistics', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_stats' ),
					array(
						array(
							'key'           => 'field_pg_stats_image',
							'label'         => __( 'Image', 'packgens' ),
							'name'          => 'image',
							'type'          => 'image',
							'return_format' => 'id',
						),
						array(
							'key'          => 'field_pg_stats_items',
							'label'        => __( 'Statistics', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add statistic', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_stats_item_value', 'label' => __( 'Value', 'packgens' ), 'name' => 'value', 'type' => 'text' ),
								array( 'key' => 'field_pg_stats_item_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_stats_item_desc', 'label' => __( 'Description', 'packgens' ), 'name' => 'description', 'type' => 'textarea', 'rows' => 2 ),
								array( 'key' => 'field_pg_stats_item_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
								array( 'key' => 'field_pg_stats_item_star', 'label' => __( 'Star after the value', 'packgens' ), 'name' => 'star', 'type' => 'true_false', 'ui' => 1, 'instructions' => __( 'For ratings.', 'packgens' ) ),
							),
						),
					)
				)
			),
		),
		$page,
		14
	);

	/* ------------------------------------------------------------------
	 * Free-form content
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_content',
		__( 'SEO Content Section', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_content',
				'content_section',
				__( 'SEO Content', 'packgens' ),
				array(
					array(
						'key'           => 'field_pg_content_enable',
						'label'         => __( 'Show this section', 'packgens' ),
						'name'          => 'enable',
						'type'          => 'true_false',
						'ui'            => 1,
						'default_value' => 1,
					),
					array(
						'key'           => 'field_pg_content_columns',
						'label'         => __( 'Columns', 'packgens' ),
						'name'          => 'columns',
						'type'          => 'select',
						'choices'       => array( '1' => __( 'One column', 'packgens' ), '2' => __( 'Two columns', 'packgens' ) ),
						'default_value' => '2',
					),
					array(
						'key'          => 'field_pg_content_left',
						'label'        => __( 'Content', 'packgens' ),
						'name'         => 'content',
						'type'         => 'wysiwyg',
						'media_upload' => 1,
					),
					array(
						'key'          => 'field_pg_content_right',
						'label'        => __( 'Second column', 'packgens' ),
						'name'         => 'content_secondary',
						'type'         => 'wysiwyg',
						'media_upload' => 1,
						'conditional_logic' => array(
							array( array( 'field' => 'field_pg_content_columns', 'operator' => '==', 'value' => '2' ) ),
						),
					),
				)
			),
		),
		$page,
		15
	);

	/* ------------------------------------------------------------------
	 * CTA band
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_cta',
		__( 'Call to Action Band', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_cta',
				'cta_section',
				__( 'Call to Action', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_cta' ),
					array(
						array(
							'key'           => 'field_pg_cta_style',
							'label'         => __( 'Colour', 'packgens' ),
							'name'          => 'style',
							'type'          => 'select',
							'choices'       => array(
								'blue'  => __( 'Blue', 'packgens' ),
								'green' => __( 'Green', 'packgens' ),
								'dark'  => __( 'Dark', 'packgens' ),
								'black' => __( 'Black', 'packgens' ),
							),
							'default_value' => 'blue',
						),
						array( 'key' => 'field_pg_cta_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id', 'instructions' => __( 'Optional. Sits flush against the left edge of the band.', 'packgens' ) ),
						array( 'key' => 'field_pg_cta_phone_label', 'label' => __( 'Phone label', 'packgens' ), 'name' => 'phone_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_cta_phone', 'label' => __( 'Phone number', 'packgens' ), 'name' => 'phone', 'type' => 'text' ),
						packgens_link_field( 'field_pg_cta_btn', 'button' ),
					)
				)
			),
		),
		$page,
		16
	);

	/* ------------------------------------------------------------------
	 * Call to action band, second instance
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_cta_alt',
		__( 'Call to Action Band (second)', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_ctaalt',
				'cta_alt_section',
				__( 'Call to Action', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_ctaalt' ),
					array(
						array(
							'key'           => 'field_pg_ctaalt_style',
							'label'         => __( 'Colour', 'packgens' ),
							'name'          => 'style',
							'type'          => 'select',
							'choices'       => array(
								'blue'  => __( 'Blue', 'packgens' ),
								'green' => __( 'Green', 'packgens' ),
								'dark'  => __( 'Dark', 'packgens' ),
								'black' => __( 'Black', 'packgens' ),
							),
							'default_value' => 'green',
						),
						array( 'key' => 'field_pg_ctaalt_phone_label', 'label' => __( 'Phone label', 'packgens' ), 'name' => 'phone_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_ctaalt_phone', 'label' => __( 'Phone number', 'packgens' ), 'name' => 'phone', 'type' => 'text' ),
						packgens_link_field( 'field_pg_ctaalt_btn', 'button' ),
					)
				)
			),
		),
		$page,
		17
	);

	/* ------------------------------------------------------------------
	 * Showcase row
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_showcase',
		__( 'Showcase Row', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_showcase',
				'showcase_section',
				__( 'Showcase', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_showcase' ),
					array(
						array(
							'key'          => 'field_pg_showcase_items',
							'label'        => __( 'Items', 'packgens' ),
							'name'         => 'items',
							'type'         => 'repeater',
							'layout'       => 'block',
							'button_label' => __( 'Add item', 'packgens' ),
							'sub_fields'   => array(
								array( 'key' => 'field_pg_showcase_item_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id', 'preview_size' => 'medium' ),
								array( 'key' => 'field_pg_showcase_item_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
								array( 'key' => 'field_pg_showcase_item_text', 'label' => __( 'Hover text', 'packgens' ), 'name' => 'text', 'type' => 'textarea', 'rows' => 3, 'instructions' => __( 'Shown on the card that lifts over the item on hover.', 'packgens' ) ),
								packgens_link_field( 'field_pg_showcase_item_link', 'link' ),
							),
						),
					)
				)
			),
		),
		$page,
		9
	);

	/* ------------------------------------------------------------------
	 * Chat bar
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_chat',
		__( 'Chat Bar', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_chat',
				'chat_section',
				__( 'Chat Bar', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_chat' ),
					array(
						array( 'key' => 'field_pg_chat_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
						packgens_link_field( 'field_pg_chat_btn', 'button' ),
					)
				)
			),
		),
		$page,
		24
	);

	/* ------------------------------------------------------------------
	 * Promo banner
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_promo',
		__( 'Promo Banner', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_promo',
				'promo_section',
				__( 'Promo Banner', 'packgens' ),
				array_merge(
					packgens_section_base_fields( 'field_pg_promo', array( 'eyebrow' => true ) ),
					array(
						array( 'key' => 'field_pg_promo_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
						array( 'key' => 'field_pg_promo_phone_label', 'label' => __( 'Phone label', 'packgens' ), 'name' => 'phone_label', 'type' => 'text' ),
						array( 'key' => 'field_pg_promo_phone', 'label' => __( 'Phone number', 'packgens' ), 'name' => 'phone', 'type' => 'text' ),
						packgens_link_field( 'field_pg_promo_btn', 'button' ),
					)
				)
			),
		),
		$page,
		17
	);

	/* ------------------------------------------------------------------
	 * Section order (front page and landing pages)
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_layout',
		__( 'Section Order', 'packgens' ),
		array(
			array(
				'key'           => 'field_pg_layout_order',
				'label'         => __( 'Sections', 'packgens' ),
				'name'          => 'section_order',
				'type'          => 'checkbox',
				'instructions'  => __( 'Tick the sections to show and drag to reorder. Leave untouched to use the default order.', 'packgens' ),
				'choices'       => packgens_section_choices(),
				'allow_custom'  => 0,
				'layout'        => 'vertical',
				'return_format' => 'value',
			),
		),
		array(
			array( array( 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ) ),
			array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ) ),
			array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'product_cat' ) ),
		),
		0
	);

	/* ------------------------------------------------------------------
	 * Material group (taxonomy term) fields
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_material_term',
		__( 'Material Group', 'packgens' ),
		array(
			array(
				'key'          => 'field_pg_material_term_icon',
				'label'        => __( 'Tab icon', 'packgens' ),
				'name'         => 'icon',
				'type'         => 'select',
				'choices'      => packgens_icon_choices(),
				'allow_null'   => 1,
				'ui'           => 1,
				'instructions' => __( 'Shown before the group name in the materials tabs.', 'packgens' ),
			),
		),
		array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'category_materials' ) ) ),
		0
	);

	/* ------------------------------------------------------------------
	 * Testimonial (customer) fields
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_customer',
		__( 'Testimonial Details', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_customer',
				'testimonial',
				__( 'Testimonial', 'packgens' ),
				array(
					array( 'key' => 'field_pg_customer_name', 'label' => __( 'Name', 'packgens' ), 'name' => 'name', 'type' => 'text' ),
					array( 'key' => 'field_pg_customer_role', 'label' => __( 'Role / company', 'packgens' ), 'name' => 'designation', 'type' => 'text' ),
					array( 'key' => 'field_pg_customer_text', 'label' => __( 'Review', 'packgens' ), 'name' => 'review', 'type' => 'textarea', 'rows' => 4 ),
					array( 'key' => 'field_pg_customer_rating', 'label' => __( 'Rating', 'packgens' ), 'name' => 'rating', 'type' => 'number', 'min' => 0, 'max' => 5, 'step' => 0.5, 'default_value' => 5 ),
					array( 'key' => 'field_pg_customer_date', 'label' => __( 'Date', 'packgens' ), 'name' => 'date', 'type' => 'date_picker', 'return_format' => 'Ymd' ),
					array( 'key' => 'field_pg_customer_source', 'label' => __( 'Source', 'packgens' ), 'name' => 'source', 'type' => 'select', 'choices' => array( '' => __( 'None', 'packgens' ), 'trustpilot' => 'Trustpilot', 'google' => 'Google' ), 'allow_null' => 1 ),
				)
			),
		),
		array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'customer' ) ) ),
		0
	);

	/* ------------------------------------------------------------------
	 * Material fields
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_material',
		__( 'Material Details', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_material',
				'material_section',
				__( 'Material', 'packgens' ),
				array(
					array( 'key' => 'field_pg_material_summary', 'label' => __( 'Summary', 'packgens' ), 'name' => 'summary', 'type' => 'textarea', 'rows' => 3 ),
					array(
						'key'          => 'field_pg_material_specs',
						'label'        => __( 'Specifications', 'packgens' ),
						'name'         => 'specs',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add specification', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_material_spec_label', 'label' => __( 'Label', 'packgens' ), 'name' => 'label', 'type' => 'text' ),
							array( 'key' => 'field_pg_material_spec_value', 'label' => __( 'Value', 'packgens' ), 'name' => 'value', 'type' => 'text' ),
						),
					),
					packgens_link_field( 'field_pg_material_btn', 'button' ),
				)
			),
		),
		array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'material' ) ) ),
		0
	);

	/* ------------------------------------------------------------------
	 * Product extras
	 * ---------------------------------------------------------------- */
	packgens_add_group(
		'group_pg_product',
		__( 'Product Details', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_product',
				'product_details',
				__( 'Product Details', 'packgens' ),
				array(
					array( 'key' => 'field_pg_product_badge', 'label' => __( 'Badge', 'packgens' ), 'name' => 'badge', 'type' => 'text', 'instructions' => __( 'Shown on the gallery and product cards, e.g. Best Seller.', 'packgens' ) ),
					array( 'key' => 'field_pg_product_subtitle', 'label' => __( 'Subtitle above the title', 'packgens' ), 'name' => 'subtitle', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_price_label', 'label' => __( 'Price label', 'packgens' ), 'name' => 'price_label', 'type' => 'text', 'default_value' => 'Starting From /' ),
					array( 'key' => 'field_pg_product_unit', 'label' => __( 'Price unit label', 'packgens' ), 'name' => 'price_unit', 'type' => 'text', 'default_value' => '/ unit' ),
					array( 'key' => 'field_pg_product_price_note', 'label' => __( 'Price note', 'packgens' ), 'name' => 'price_note', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_delivery', 'label' => __( 'Delivery button label', 'packgens' ), 'name' => 'delivery_label', 'type' => 'text', 'instructions' => __( 'Second button beside Add to Basket.', 'packgens' ) ),

					array( 'key' => 'field_pg_product_tab_rating', 'label' => __( 'Rating strip', 'packgens' ), 'type' => 'tab' ),
					array( 'key' => 'field_pg_product_rating_label', 'label' => __( 'Wording', 'packgens' ), 'name' => 'rating_label', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_rating_value', 'label' => __( 'Score out of five', 'packgens' ), 'name' => 'rating_value', 'type' => 'number', 'min' => 0, 'max' => 5, 'step' => 0.1 ),
					array( 'key' => 'field_pg_product_rating_count', 'label' => __( 'Review count', 'packgens' ), 'name' => 'rating_count', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_rating_platform', 'label' => __( 'Platform', 'packgens' ), 'name' => 'rating_platform', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1, 'ui' => 1 ),
					array( 'key' => 'field_pg_product_rating_name', 'label' => __( 'Platform name', 'packgens' ), 'name' => 'rating_platform_name', 'type' => 'text' ),

					array( 'key' => 'field_pg_product_tab_coupon', 'label' => __( 'Offer strip', 'packgens' ), 'type' => 'tab' ),
					array( 'key' => 'field_pg_product_coupon_title', 'label' => __( 'Offer', 'packgens' ), 'name' => 'coupon_title', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_coupon_note', 'label' => __( 'Small print', 'packgens' ), 'name' => 'coupon_note', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_coupon_code', 'label' => __( 'Code to copy', 'packgens' ), 'name' => 'coupon_code', 'type' => 'text' ),

					array( 'key' => 'field_pg_product_tab_support', 'label' => __( 'Help card', 'packgens' ), 'type' => 'tab' ),
					array( 'key' => 'field_pg_product_help_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'help_title', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_help_text', 'label' => __( 'Text', 'packgens' ), 'name' => 'help_text', 'type' => 'text' ),
					array( 'key' => 'field_pg_product_help_photo', 'label' => __( 'Adviser photo', 'packgens' ), 'name' => 'help_photo', 'type' => 'image', 'return_format' => 'id' ),
					packgens_link_field( 'field_pg_product_help_chat', 'help_chat' ),
					packgens_link_field( 'field_pg_product_help_call', 'help_call' ),

					array( 'key' => 'field_pg_product_tab_features', 'label' => __( 'Feature list', 'packgens' ), 'type' => 'tab' ),
					array(
						'key'          => 'field_pg_product_features',
						'label'        => __( 'Features', 'packgens' ),
						'name'         => 'features',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add feature', 'packgens' ),
						'instructions' => __( 'Two-column list below the gallery.', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_product_feature_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1, 'ui' => 1 ),
							array( 'key' => 'field_pg_product_feature_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
						),
					),

					array( 'key' => 'field_pg_product_tab_detail', 'label' => __( 'Details', 'packgens' ), 'type' => 'tab' ),
					array( 'key' => 'field_pg_product_tabs_title', 'label' => __( 'Heading above the detail tabs', 'packgens' ), 'name' => 'tabs_title', 'type' => 'text', 'default_value' => 'Product Packages Details' ),
					array(
						'key'          => 'field_pg_product_tiers',
						'label'        => __( 'Quantity tiers', 'packgens' ),
						'name'         => 'tiers',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add tier', 'packgens' ),
						'instructions' => __( 'Quantity options shown above Add to Basket.', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_product_tier_qty', 'label' => __( 'Quantity', 'packgens' ), 'name' => 'quantity', 'type' => 'number', 'min' => 1 ),
							array( 'key' => 'field_pg_product_tier_price', 'label' => __( 'Unit price', 'packgens' ), 'name' => 'price', 'type' => 'text' ),
							array( 'key' => 'field_pg_product_tier_note', 'label' => __( 'Saving note', 'packgens' ), 'name' => 'note', 'type' => 'text' ),
							array( 'key' => 'field_pg_product_tier_default', 'label' => __( 'Default', 'packgens' ), 'name' => 'is_default', 'type' => 'true_false', 'ui' => 1 ),
						),
					),
					array(
						'key'          => 'field_pg_product_specs',
						'label'        => __( 'Specifications table', 'packgens' ),
						'name'         => 'specs',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add row', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_product_spec_label', 'label' => __( 'Label', 'packgens' ), 'name' => 'label', 'type' => 'text' ),
							array( 'key' => 'field_pg_product_spec_value', 'label' => __( 'Value', 'packgens' ), 'name' => 'value', 'type' => 'text' ),
						),
					),
					array(
						'key'          => 'field_pg_product_tabs',
						'label'        => __( 'Extra tabs', 'packgens' ),
						'name'         => 'tabs',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add tab', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_product_tab_title', 'label' => __( 'Tab title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
							array( 'key' => 'field_pg_product_tab_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
							array( 'key' => 'field_pg_product_tab_content', 'label' => __( 'Content', 'packgens' ), 'name' => 'content', 'type' => 'wysiwyg', 'media_upload' => 1 ),
						),
					),
				)
			),
		),
		array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ) ) ),
		0
	);

	packgens_register_options_fields();
}
add_action( 'acf/init', 'packgens_register_fields' );

/**
 * Icon choices offered to editors, derived from assets/icons.
 *
 * @return array<string,string>
 */
function packgens_icon_choices() {
	static $choices = null;

	if ( null !== $choices ) {
		return $choices;
	}

	$choices = array();
	$files   = glob( PACKGENS_DIR . 'assets/icons/*.svg' );

	foreach ( (array) $files as $file ) {
		$slug             = basename( $file, '.svg' );
		$choices[ $slug ] = ucwords( str_replace( '-', ' ', $slug ) );
	}

	return $choices;
}

/**
 * The sections a page may render, in default order.
 *
 * @return array<string,string>
 */
function packgens_section_choices() {
	return array(
		'hero'       => __( 'Hero', 'packgens' ),
		'categories' => __( 'Categories', 'packgens' ),
		'about'      => __( 'About', 'packgens' ),
		'values'     => __( 'Mission, vision, values', 'packgens' ),
		'quote_band' => __( 'Quote band', 'packgens' ),
		'showcase'   => __( 'Showcase row', 'packgens' ),
		'promo'      => __( 'Promo banner', 'packgens' ),
		'cta'        => __( 'Call to action band', 'packgens' ),
		'cta_alt'    => __( 'Call to action band (second)', 'packgens' ),
		'chat'       => __( 'Chat bar', 'packgens' ),
		'products'   => __( 'Products', 'packgens' ),
		'process'    => __( 'Process steps', 'packgens' ),
		'why'        => __( 'Why choose us', 'packgens' ),
		'comparison' => __( 'Comparison', 'packgens' ),
		'materials'  => __( 'Materials', 'packgens' ),
		'stats'      => __( 'Statistics', 'packgens' ),
		'industries' => __( 'Industries', 'packgens' ),
		'faqs'       => __( 'FAQs', 'packgens' ),
		'brands'     => __( 'Trusted brands', 'packgens' ),
		'reviews'    => __( 'Reviews', 'packgens' ),
		'content'    => __( 'SEO content', 'packgens' ),
		'blogs'      => __( 'Blog', 'packgens' ),
	);
}

/**
 * Global options page and its fields.
 *
 * @return void
 */
function packgens_register_options_fields() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => __( 'Packgens Content', 'packgens' ),
			'menu_title'  => __( 'Packgens', 'packgens' ),
			'menu_slug'   => 'packgens-settings',
			'capability'  => 'edit_theme_options',
			'icon_url'    => 'dashicons-archive',
			'position'    => 3,
			'redirect'    => false,
			'update_button' => __( 'Save', 'packgens' ),
		)
	);

	// Sub-page of the same menu: one screen that every product category falls
	// back to, so a change here reaches all of them at once.
	acf_add_options_sub_page(
		array(
			'page_title'    => __( 'Product Category Defaults', 'packgens' ),
			'menu_title'    => __( 'Category Defaults', 'packgens' ),
			'menu_slug'     => PACKGENS_CAT_DEFAULTS_PAGE,
			'parent_slug'   => 'packgens-settings',
			'capability'    => 'edit_theme_options',
			'post_id'       => PACKGENS_CAT_DEFAULTS_ID,
			'redirect'      => false,
			'update_button' => __( 'Save', 'packgens' ),
		)
	);

	// Contact details, social profiles and the announcement bar used to live in
	// the Customizer, which meant two admin screens for one job.
	acf_add_options_sub_page(
		array(
			'page_title'    => __( 'Packgens Site Settings', 'packgens' ),
			'menu_title'    => __( 'Site Settings', 'packgens' ),
			'menu_slug'     => 'packgens-site-settings',
			'parent_slug'   => 'packgens-settings',
			'capability'    => 'edit_theme_options',
			'redirect'      => false,
			'update_button' => __( 'Save', 'packgens' ),
		)
	);

	packgens_add_group(
		'group_pg_site',
		__( 'Site Settings', 'packgens' ),
		array(
			packgens_group_wrapper(
				'field_pg_site',
				'site_options',
				__( 'Site settings', 'packgens' ),
				packgens_site_option_fields()
			),
		),
		array(
			array(
				array( 'param' => 'options_page', 'operator' => '==', 'value' => 'packgens-site-settings' ),
			),
		),
		1
	);

	packgens_add_group(
		'group_pg_options',
		__( 'Global Content', 'packgens' ),
		array(
			array( 'key' => 'field_pg_opt_tab_aside', 'label' => __( 'Article sidebar', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_opt_aside',
				'post_aside',
				__( 'Article sidebar card', 'packgens' ),
				array(
					array( 'key' => 'field_pg_opt_aside_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_aside_rating', 'label' => __( 'Score', 'packgens' ), 'name' => 'rating', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_aside_reviews', 'label' => __( 'Reviews note', 'packgens' ), 'name' => 'rating_note', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_aside_image', 'label' => __( 'Image', 'packgens' ), 'name' => 'image', 'type' => 'image', 'return_format' => 'id' ),
					array(
						'key'          => 'field_pg_opt_aside_points',
						'label'        => __( 'Points', 'packgens' ),
						'name'         => 'points',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add point', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_opt_aside_point', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'text' ),
						),
					),
					packgens_link_field( 'field_pg_opt_aside_btn', 'button' ),
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_forms', 'label' => __( 'Text: forms', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_forms',
				'labels_forms',
				__( 'Form labels', 'packgens' ),
				packgens_label_fields(
					'forms',
					array(
						'name_label'          => array( __( 'Name label', 'packgens' ), 'Full Name' ),
						'name_placeholder'    => array( __( 'Name placeholder', 'packgens' ), 'Enter Full Name' ),
						'email_label'         => array( __( 'Email label', 'packgens' ), 'Email' ),
						'email_placeholder'   => array( __( 'Email placeholder', 'packgens' ), 'Enter your Email' ),
						'phone_label'         => array( __( 'Phone label', 'packgens' ), 'Phone Number' ),
						'phone_placeholder'   => array( __( 'Phone placeholder', 'packgens' ), 'Enter your Phone' ),
						'category_label'      => array( __( 'Category label', 'packgens' ), 'Category' ),
						'category_empty'      => array( __( 'Category empty option', 'packgens' ), 'Select Category' ),
						'quantity_label'      => array( __( 'Quantity label', 'packgens' ), 'Quantity' ),
						'quantity_placeholder' => array( __( 'Quantity placeholder', 'packgens' ), 'Quantity' ),
						'zip_label'           => array( __( 'Zip label', 'packgens' ), 'Zip Code' ),
						'zip_placeholder'     => array( __( 'Zip placeholder', 'packgens' ), 'Enter zip code' ),
						'size_label'          => array( __( 'Box size label', 'packgens' ), 'Box size' ),
						'size_length'         => array( __( 'Length placeholder', 'packgens' ), 'Length' ),
						'size_width'          => array( __( 'Width placeholder', 'packgens' ), 'Width' ),
						'size_height'         => array( __( 'Height placeholder', 'packgens' ), 'Height' ),
						'message_label'       => array( __( 'Message label', 'packgens' ), 'Project Details' ),
						'message_placeholder' => array( __( 'Message placeholder', 'packgens' ), 'Enter your project details...' ),
						'file_label'          => array( __( 'File upload label', 'packgens' ), 'Attach or Browse Files' ),
						'contact_title'       => array( __( 'Contact form title', 'packgens' ), 'Contact Our Support Team' ),
						'contact_submit'      => array( __( 'Contact form button', 'packgens' ), 'Send Free Quote' ),
						'newsletter_placeholder' => array( __( 'Newsletter placeholder', 'packgens' ), 'type: your@email.com' ),
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_header', 'label' => __( 'Text: header', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_header',
				'labels_header',
				__( 'Header text', 'packgens' ),
				packgens_label_fields(
					'header',
					array(
						'search_placeholder' => array( __( 'Product search placeholder', 'packgens' ), 'Search for Products' ),
						'site_search_placeholder' => array( __( 'Site search placeholder', 'packgens' ), 'Search&hellip;' ),
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_footer', 'label' => __( 'Text: footer', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_footer',
				'labels_footer',
				__( 'Footer text', 'packgens' ),
				packgens_label_fields(
					'footer',
					array(
						'instant_info' => array( __( 'Instant information heading', 'packgens' ), 'Instant Information' ),
						'send_email'   => array( __( 'Email heading', 'packgens' ), 'Send Email' ),
						'call_info'    => array( __( 'Call heading', 'packgens' ), 'Call Information' ),
						'address'      => array( __( 'Address heading', 'packgens' ), 'Address' ),
						'follow'       => array( __( 'Follow heading', 'packgens' ), 'Follow us' ),
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_blog', 'label' => __( 'Text: blog', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_blog',
				'labels_blog',
				__( 'Blog text', 'packgens' ),
				packgens_label_fields(
					'blog',
					array(
						'filter_label'       => array( __( 'Category filter label', 'packgens' ), 'Filter by category' ),
						'filter_empty'       => array( __( 'Category filter empty option', 'packgens' ), 'Select Blog Category' ),
						'filter_button'      => array( __( 'Filter button', 'packgens' ), 'Filter' ),
						'search_label'       => array( __( 'Blog search label', 'packgens' ), 'Search the blog' ),
						'search_placeholder' => array( __( 'Blog search placeholder', 'packgens' ), 'Search blog' ),
						'sidebar_search'     => array( __( 'Sidebar search heading', 'packgens' ), 'Search Posts' ),
						'sidebar_popular'    => array( __( 'Sidebar popular heading', 'packgens' ), 'Most Popular' ),
						'related_title'      => array( __( 'Related row heading', 'packgens' ), 'Related Blogs' ),
						'related_button'     => array( __( 'Related row button', 'packgens' ), 'Visit Our Blog' ),
						'toc_title'          => array( __( 'Contents heading', 'packgens' ), 'Table of Contents' ),
						'share_title'        => array( __( 'Share heading', 'packgens' ), 'Share This Article' ),
						'read_more'          => array( __( 'Card link', 'packgens' ), 'Read More' ),
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_contact', 'label' => __( 'Text: contact channels', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_contact',
				'labels_contact',
				__( 'Contact channel headings', 'packgens' ),
				packgens_label_fields(
					'contact',
					array(
						'call_title'    => array( __( 'Call heading', 'packgens' ), 'Call Us' ),
						'email_title'   => array( __( 'Email heading', 'packgens' ), 'Send Email' ),
						'chat_title'    => array( __( 'Chat heading', 'packgens' ), 'Message Us' ),
						'address_title' => array( __( 'Address heading', 'packgens' ), 'Office Address' ),
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_quote_card', 'label' => __( 'Text: quote card', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_quote_card',
				'labels_quote_card',
				__( 'Sidebar quote card', 'packgens' ),
				array_merge(
					packgens_label_fields(
						'quote_card',
						array(
							'title' => array( __( 'Title', 'packgens' ), 'Ready to box? Let&rsquo;s get started!' ),
						)
					),
					packgens_label_fields(
						'quote_card_text',
						array(
							'text' => array( __( 'Text', 'packgens' ), 'Get in touch with a Packgens custom packaging specialist for an instant price quote.' ),
						),
						'textarea'
					),
					packgens_label_fields(
						'quote_card',
						array(
							'phone_label' => array( __( 'Phone label', 'packgens' ), 'Call Us Toll Free' ),
							'button'      => array( __( 'Button', 'packgens' ), 'Get a Free Quote' ),
						)
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_wishlist', 'label' => __( 'Text: saved items', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_wishlist',
				'labels_wishlist',
				__( 'Saved items', 'packgens' ),
				array_merge(
					packgens_label_fields(
						'wishlist',
						array(
							'empty_title' => array( __( 'Empty state title', 'packgens' ), 'Nothing saved yet' ),
						)
					),
					packgens_label_fields(
						'wishlist_text',
						array(
							'empty_text' => array( __( 'Empty state text', 'packgens' ), 'Tap the heart on any product to keep it here while you decide.' ),
						),
						'textarea'
					),
					packgens_label_fields(
						'wishlist',
						array(
							'empty_button' => array( __( 'Empty state button', 'packgens' ), 'Browse packaging' ),
						)
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_lbl_error', 'label' => __( 'Text: 404 and search', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_lbl_error',
				'labels_error',
				__( 'Not found and search', 'packgens' ),
				array_merge(
					packgens_label_fields(
						'error',
						array(
							'title' => array( __( '404 title', 'packgens' ), 'We could not find that page' ),
						)
					),
					packgens_label_fields(
						'error_text',
						array(
							'text' => array( __( '404 text', 'packgens' ), 'The page may have moved or the link may be out of date. Try a search, or head back to the homepage.' ),
						),
						'textarea'
					),
					packgens_label_fields(
						'error',
						array(
							'home_button' => array( __( 'Home button', 'packgens' ), 'Back to home' ),
							'shop_button' => array( __( 'Shop button', 'packgens' ), 'Browse products' ),
						)
					)
				)
			),

			array( 'key' => 'field_pg_opt_tab_hstrip', 'label' => __( 'Header strip', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_opt_hstrip',
				'header_strip',
				__( 'Header strip', 'packgens' ),
				array(
					array( 'key' => 'field_pg_opt_hstrip_enable', 'label' => __( 'Show under the header', 'packgens' ), 'name' => 'enable', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'instructions' => __( 'Appears on pages without a hero, where the header paints its own band.', 'packgens' ) ),
					array(
						'key'          => 'field_pg_opt_hstrip_items',
						'label'        => __( 'Items', 'packgens' ),
						'name'         => 'items',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add item', 'packgens' ),
						'max'          => 5,
						'sub_fields'   => array(
							array( 'key' => 'field_pg_opt_hstrip_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
							array( 'key' => 'field_pg_opt_hstrip_title', 'label' => __( 'Label', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
							packgens_link_field( 'field_pg_opt_hstrip_link', 'link', __( 'Link', 'packgens' ) ),
						),
					),
				)
			),

			array( 'key' => 'field_pg_opt_tab_usp', 'label' => __( 'Trust strip', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_opt_usp',
				'usp_strip',
				__( 'Trust strip', 'packgens' ),
				array(
					array( 'key' => 'field_pg_opt_usp_enable', 'label' => __( 'Show above the footer', 'packgens' ), 'name' => 'enable', 'type' => 'true_false', 'ui' => 1, 'default_value' => 1 ),
					array(
						'key'          => 'field_pg_opt_usp_items',
						'label'        => __( 'Items', 'packgens' ),
						'name'         => 'items',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => __( 'Add item', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_opt_usp_icon', 'label' => __( 'Icon', 'packgens' ), 'name' => 'icon', 'type' => 'select', 'choices' => packgens_icon_choices(), 'allow_null' => 1 ),
							array( 'key' => 'field_pg_opt_usp_title', 'label' => __( 'Title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
							array( 'key' => 'field_pg_opt_usp_text', 'label' => __( 'Text', 'packgens' ), 'name' => 'text', 'type' => 'textarea', 'rows' => 2 ),
							packgens_link_field( 'field_pg_opt_usp_link', 'link', __( 'Link', 'packgens' ) ),
						),
					),
				)
			),

			array( 'key' => 'field_pg_opt_tab_footer', 'label' => __( 'Footer', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_opt_footer',
				'footer',
				__( 'Footer', 'packgens' ),
				array(
					array( 'key' => 'field_pg_opt_footer_about', 'label' => __( 'About text', 'packgens' ), 'name' => 'about', 'type' => 'textarea', 'rows' => 3 ),
					array( 'key' => 'field_pg_opt_footer_news_title', 'label' => __( 'Newsletter title', 'packgens' ), 'name' => 'newsletter_title', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_footer_news_text', 'label' => __( 'Newsletter text', 'packgens' ), 'name' => 'newsletter_text', 'type' => 'textarea', 'rows' => 2 ),
					packgens_link_field( 'field_pg_opt_footer_btn1', 'button_1', __( 'Footer button 1', 'packgens' ) ),
					packgens_link_field( 'field_pg_opt_footer_btn2', 'button_2', __( 'Footer button 2', 'packgens' ) ),
					array( 'key' => 'field_pg_opt_footer_pay_title', 'label' => __( 'Payment strip title', 'packgens' ), 'name' => 'payment_title', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_footer_pay', 'label' => __( 'Payment logos', 'packgens' ), 'name' => 'payment_logos', 'type' => 'gallery', 'return_format' => 'id' ),
					array( 'key' => 'field_pg_opt_footer_del_title', 'label' => __( 'Delivery strip title', 'packgens' ), 'name' => 'delivery_title', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_footer_del', 'label' => __( 'Delivery logos', 'packgens' ), 'name' => 'delivery_logos', 'type' => 'gallery', 'return_format' => 'id' ),
					array( 'key' => 'field_pg_opt_footer_copy', 'label' => __( 'Copyright', 'packgens' ), 'name' => 'copyright', 'type' => 'text', 'instructions' => __( 'Use %year% for the current year.', 'packgens' ) ),
				)
			),

			array( 'key' => 'field_pg_opt_tab_forms', 'label' => __( 'Quote form', 'packgens' ), 'type' => 'tab' ),
			packgens_group_wrapper(
				'field_pg_opt_quote',
				'quote_form',
				__( 'Quote form', 'packgens' ),
				array(
					array( 'key' => 'field_pg_opt_quote_title', 'label' => __( 'Default title', 'packgens' ), 'name' => 'title', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_quote_intro', 'label' => __( 'Intro text', 'packgens' ), 'name' => 'intro', 'type' => 'textarea', 'rows' => 2 ),
					array( 'key' => 'field_pg_opt_quote_submit', 'label' => __( 'Submit button label', 'packgens' ), 'name' => 'submit_label', 'type' => 'text' ),
					array( 'key' => 'field_pg_opt_quote_consent', 'label' => __( 'Consent text', 'packgens' ), 'name' => 'consent', 'type' => 'textarea', 'rows' => 2 ),
					array(
						'key'          => 'field_pg_opt_quote_categories',
						'label'        => __( 'Category options', 'packgens' ),
						'name'         => 'categories',
						'type'         => 'repeater',
						'layout'       => 'table',
						'button_label' => __( 'Add option', 'packgens' ),
						'instructions' => __( 'Leave empty to list the product categories automatically.', 'packgens' ),
						'sub_fields'   => array(
							array( 'key' => 'field_pg_opt_quote_cat_label', 'label' => __( 'Label', 'packgens' ), 'name' => 'label', 'type' => 'text' ),
						),
					),
				)
			),
		),
		array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'packgens-settings' ) ) ),
		0
	);
}

/**
 * Read a global option field.
 *
 * @param string $group   Group name.
 * @param string $key     Key inside the group.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function packgens_global( $group, $key, $default = null ) {
	return packgens_group_field( $group, $key, 'option', $default );
}
