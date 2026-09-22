<?php
/**
 * Native form handling: quote requests, contact messages and newsletter sign-ups.
 *
 * Every submission is nonce checked, rate limited, sanitised field by field,
 * stored as a private enquiry post so nothing is lost if email fails, and then
 * emailed to the configured recipient.
 *
 * Both an AJAX endpoint and an admin-post endpoint are registered so the forms
 * keep working with JavaScript disabled.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post type used to archive submissions.
 */
const PACKGENS_ENQUIRY_CPT = 'pg_enquiry';

/**
 * Maximum upload size for artwork attachments, in bytes.
 */
const PACKGENS_MAX_UPLOAD = 10485760; // 10 MB.

/**
 * Register the enquiry archive post type.
 *
 * @return void
 */
function packgens_register_enquiry_cpt() {
	register_post_type(
		PACKGENS_ENQUIRY_CPT,
		array(
			'labels'          => array(
				'name'          => __( 'Enquiries', 'packgens' ),
				'singular_name' => __( 'Enquiry', 'packgens' ),
				'menu_name'     => __( 'Enquiries', 'packgens' ),
				'edit_item'     => __( 'View Enquiry', 'packgens' ),
				'not_found'     => __( 'No enquiries yet.', 'packgens' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title' ),
			'capabilities'    => array(
				'create_posts' => 'do_not_allow',
			),
			'map_meta_cap'    => true,
			'has_archive'     => false,
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'packgens_register_enquiry_cpt' );

/**
 * Field definitions per form type.
 *
 * type       - sanitiser to apply
 * required   - whether the field must be present
 * label      - used in the notification email
 *
 * @param string $form Form key.
 * @return array<string,array{type:string,required:bool,label:string}>
 */
function packgens_form_schema( $form ) {
	$schemas = array(
		'quote'      => array(
			'name'     => array( 'type' => 'text', 'required' => true, 'label' => __( 'Full name', 'packgens' ) ),
			'email'    => array( 'type' => 'email', 'required' => true, 'label' => __( 'Email', 'packgens' ) ),
			'phone'    => array( 'type' => 'text', 'required' => false, 'label' => __( 'Phone', 'packgens' ) ),
			'category' => array( 'type' => 'text', 'required' => false, 'label' => __( 'Category', 'packgens' ) ),
			'quantity' => array( 'type' => 'text', 'required' => false, 'label' => __( 'Quantity', 'packgens' ) ),
			'zip'      => array( 'type' => 'text', 'required' => false, 'label' => __( 'Zip code', 'packgens' ) ),
			'length'   => array( 'type' => 'text', 'required' => false, 'label' => __( 'Length', 'packgens' ) ),
			'width'    => array( 'type' => 'text', 'required' => false, 'label' => __( 'Width', 'packgens' ) ),
			'height'   => array( 'type' => 'text', 'required' => false, 'label' => __( 'Height', 'packgens' ) ),
			'message'  => array( 'type' => 'textarea', 'required' => true, 'label' => __( 'Project details', 'packgens' ) ),
			'product'  => array( 'type' => 'text', 'required' => false, 'label' => __( 'Product', 'packgens' ) ),
		),
		'contact'    => array(
			'name'     => array( 'type' => 'text', 'required' => true, 'label' => __( 'Full name', 'packgens' ) ),
			'email'    => array( 'type' => 'email', 'required' => true, 'label' => __( 'Email', 'packgens' ) ),
			'phone'    => array( 'type' => 'text', 'required' => false, 'label' => __( 'Phone', 'packgens' ) ),
			'category' => array( 'type' => 'text', 'required' => false, 'label' => __( 'Category', 'packgens' ) ),
			'quantity' => array( 'type' => 'text', 'required' => false, 'label' => __( 'Quantity', 'packgens' ) ),
			'zip'      => array( 'type' => 'text', 'required' => false, 'label' => __( 'Zip code', 'packgens' ) ),
			'message'  => array( 'type' => 'textarea', 'required' => true, 'label' => __( 'Message', 'packgens' ) ),
		),
		'newsletter' => array(
			'email' => array( 'type' => 'email', 'required' => true, 'label' => __( 'Email', 'packgens' ) ),
		),
		'callback'   => array(
			'name'  => array( 'type' => 'text', 'required' => true, 'label' => __( 'Full name', 'packgens' ) ),
			'phone' => array( 'type' => 'text', 'required' => true, 'label' => __( 'Phone', 'packgens' ) ),
		),
	);

	/**
	 * Filter the field schema for a form.
	 *
	 * @param array  $schema Field definitions.
	 * @param string $form   Form key.
	 */
	return apply_filters( 'packgens_form_schema', $schemas[ $form ] ?? array(), $form );
}

/**
 * Sanitise a single value by schema type.
 *
 * @param string $type  Schema type.
 * @param mixed  $value Raw value.
 * @return string
 */
function packgens_sanitize_by_type( $type, $value ) {
	$value = wp_unslash( $value );

	switch ( $type ) {
		case 'email':
			return sanitize_email( (string) $value );
		case 'textarea':
			return sanitize_textarea_field( (string) $value );
		case 'url':
			return esc_url_raw( (string) $value );
		default:
			return sanitize_text_field( (string) $value );
	}
}

/**
 * Whether this client has submitted too recently.
 *
 * @return bool
 */
function packgens_form_rate_limited() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

	if ( ! $ip ) {
		return false;
	}

	$key = 'pg_form_' . md5( $ip );

	if ( get_transient( $key ) ) {
		return true;
	}

	set_transient( $key, 1, 20 );

	return false;
}

/**
 * Validate and process a submission.
 *
 * @param array $request Raw request data ($_POST).
 * @param array $files   Raw file data ($_FILES).
 * @return array{success:bool,message:string,errors:array<string,string>,redirect:string}
 */
function packgens_process_form( $request, $files = array() ) {
	$result = array(
		'success'  => false,
		'message'  => '',
		'errors'   => array(),
		'redirect' => '',
	);

	$form   = isset( $request['pg_form'] ) ? sanitize_key( wp_unslash( $request['pg_form'] ) ) : '';
	$schema = packgens_form_schema( $form );

	if ( empty( $schema ) ) {
		$result['message'] = __( 'Unknown form.', 'packgens' );

		return $result;
	}

	// Honeypot: a real person never fills this in.
	if ( ! empty( $request['pg_website'] ) ) {
		$result['success'] = true;
		$result['message'] = packgens_form_success_message();

		return $result;
	}

	if ( packgens_form_rate_limited() ) {
		$result['message'] = __( 'Please wait a moment before sending another message.', 'packgens' );

		return $result;
	}

	$values = array();

	foreach ( $schema as $key => $def ) {
		$raw = $request[ $key ] ?? '';

		// Emails are checked before sanitising: sanitize_email() empties an
		// invalid address, which would otherwise read as "missing".
		if ( 'email' === $def['type'] ) {
			$typed = trim( sanitize_text_field( wp_unslash( (string) $raw ) ) );

			if ( '' === $typed ) {
				if ( $def['required'] ) {
					$result['errors'][ $key ] = sprintf(
						/* translators: %s: field label. */
						__( '%s is required.', 'packgens' ),
						$def['label']
					);
				}

				continue;
			}

			if ( ! is_email( $typed ) ) {
				$result['errors'][ $key ] = __( 'Please enter a valid email address.', 'packgens' );
				continue;
			}

			$values[ $key ] = sanitize_email( $typed );
			continue;
		}

		$value = packgens_sanitize_by_type( $def['type'], $raw );

		if ( $def['required'] && '' === $value ) {
			$result['errors'][ $key ] = sprintf(
				/* translators: %s: field label. */
				__( '%s is required.', 'packgens' ),
				$def['label']
			);
			continue;
		}

		$values[ $key ] = $value;
	}

	if ( ! empty( $result['errors'] ) ) {
		$result['message'] = __( 'Please check the highlighted fields.', 'packgens' );

		return $result;
	}

	$attachment = packgens_handle_form_upload( $files );

	if ( is_wp_error( $attachment ) ) {
		$result['errors']['artwork'] = $attachment->get_error_message();
		$result['message']           = $attachment->get_error_message();

		return $result;
	}

	$source = isset( $request['pg_source'] ) ? esc_url_raw( wp_unslash( $request['pg_source'] ) ) : '';

	$enquiry_id = packgens_store_enquiry( $form, $values, $attachment, $source );
	packgens_notify_enquiry( $form, $values, $attachment, $source, $enquiry_id );

	$result['success']  = true;
	$result['message']  = packgens_form_success_message();
	$result['redirect'] = packgens_get_option( 'form_thankyou_url' );

	return $result;
}

/**
 * Default success message.
 *
 * @return string
 */
function packgens_form_success_message() {
	$custom = packgens_get_option( 'form_success_text' );

	return $custom ? $custom : __( 'Thank you. Your request has been sent and we will be in touch shortly.', 'packgens' );
}

/**
 * Validate and store an uploaded artwork file.
 *
 * @param array $files Raw $_FILES array.
 * @return array{id:int,url:string,name:string}|null|WP_Error
 */
function packgens_handle_form_upload( $files ) {
	if ( empty( $files['artwork'] ) || ! isset( $files['artwork']['error'] ) ) {
		return null;
	}

	$file = $files['artwork'];

	if ( UPLOAD_ERR_NO_FILE === (int) $file['error'] ) {
		return null;
	}

	if ( UPLOAD_ERR_OK !== (int) $file['error'] ) {
		return new WP_Error( 'upload_error', __( 'The file could not be uploaded. Please try again.', 'packgens' ) );
	}

	if ( (int) $file['size'] > PACKGENS_MAX_UPLOAD ) {
		return new WP_Error( 'upload_too_large', __( 'The file is larger than 10 MB.', 'packgens' ) );
	}

	$allowed = array(
		'jpg|jpeg' => 'image/jpeg',
		'png'      => 'image/png',
		'gif'      => 'image/gif',
		'webp'     => 'image/webp',
		'pdf'      => 'application/pdf',
		'ai'       => 'application/postscript',
		'eps'      => 'application/postscript',
		'psd'      => 'image/vnd.adobe.photoshop',
		'zip'      => 'application/zip',
	);

	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], $allowed );

	if ( empty( $check['ext'] ) || empty( $check['type'] ) ) {
		return new WP_Error( 'upload_type', __( 'That file type is not accepted. Please upload a JPG, PNG, WEBP, PDF, AI, EPS, PSD or ZIP.', 'packgens' ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$overrides = array(
		'test_form' => false,
		'mimes'     => $allowed,
	);

	$moved = wp_handle_upload( $file, $overrides );

	if ( isset( $moved['error'] ) ) {
		return new WP_Error( 'upload_failed', $moved['error'] );
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $moved['type'],
			'post_title'     => sanitize_file_name( pathinfo( $moved['file'], PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'private',
		),
		$moved['file']
	);

	if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return new WP_Error( 'upload_failed', __( 'The file could not be saved.', 'packgens' ) );
	}

	wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $moved['file'] ) );

	return array(
		'id'   => (int) $attachment_id,
		'url'  => $moved['url'],
		'name' => basename( $moved['file'] ),
		'path' => $moved['file'],
	);
}

/**
 * Save the submission as a private post.
 *
 * @param string     $form       Form key.
 * @param array      $values     Sanitised values.
 * @param array|null $attachment Uploaded file data.
 * @param string     $source     Page the form was submitted from.
 * @return int Enquiry post ID, or 0 on failure.
 */
function packgens_store_enquiry( $form, $values, $attachment, $source ) {
	$title = sprintf(
		/* translators: 1: form name, 2: submitter name or email. */
		__( '%1$s from %2$s', 'packgens' ),
		ucfirst( $form ),
		$values['name'] ?? ( $values['email'] ?? __( 'website visitor', 'packgens' ) )
	);

	$post_id = wp_insert_post(
		array(
			'post_type'   => PACKGENS_ENQUIRY_CPT,
			'post_status' => 'private',
			'post_title'  => $title,
		),
		true
	);

	if ( is_wp_error( $post_id ) ) {
		return 0;
	}

	update_post_meta( $post_id, '_pg_form', $form );
	update_post_meta( $post_id, '_pg_values', $values );
	update_post_meta( $post_id, '_pg_source', $source );

	if ( $attachment ) {
		update_post_meta( $post_id, '_pg_attachment', (int) $attachment['id'] );
	}

	return (int) $post_id;
}

/**
 * Email the submission to the configured recipient.
 *
 * @param string     $form       Form key.
 * @param array      $values     Sanitised values.
 * @param array|null $attachment Uploaded file data.
 * @param string     $source     Source URL.
 * @param int        $enquiry_id Stored enquiry ID.
 * @return void
 */
function packgens_notify_enquiry( $form, $values, $attachment, $source, $enquiry_id ) {
	$to = packgens_get_option( 'form_recipient' );

	if ( ! $to || ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}

	$schema  = packgens_form_schema( $form );
	$subject = sprintf(
		/* translators: 1: site name, 2: form name. */
		__( '[%1$s] New %2$s enquiry', 'packgens' ),
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$form
	);

	$lines = array();

	foreach ( $values as $key => $value ) {
		if ( '' === $value ) {
			continue;
		}

		$label   = $schema[ $key ]['label'] ?? ucfirst( $key );
		$lines[] = $label . ': ' . $value;
	}

	if ( $source ) {
		$lines[] = __( 'Submitted from', 'packgens' ) . ': ' . $source;
	}

	if ( $enquiry_id ) {
		$lines[] = __( 'Manage', 'packgens' ) . ': ' . get_edit_post_link( $enquiry_id, 'raw' );
	}

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( ! empty( $values['email'] ) ) {
		$headers[] = 'Reply-To: ' . $values['email'];
	}

	$attachments = ( $attachment && ! empty( $attachment['path'] ) ) ? array( $attachment['path'] ) : array();

	wp_mail( $to, $subject, implode( "\n", $lines ), $headers, $attachments );
}

/**
 * AJAX endpoint.
 *
 * @return void
 */
function packgens_ajax_submit_form() {
	check_ajax_referer( 'packgens_public', 'nonce' );

	$result = packgens_process_form( $_POST, $_FILES ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified above.

	if ( $result['success'] ) {
		wp_send_json_success( $result );
	}

	wp_send_json_error( $result );
}
add_action( 'wp_ajax_packgens_submit_form', 'packgens_ajax_submit_form' );
add_action( 'wp_ajax_nopriv_packgens_submit_form', 'packgens_ajax_submit_form' );

/**
 * Non-JavaScript endpoint.
 *
 * @return void
 */
function packgens_post_submit_form() {
	$referer = wp_get_referer();
	$referer = $referer ? $referer : home_url( '/' );

	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'packgens_public' ) ) {
		wp_safe_redirect( add_query_arg( 'pg_form_status', 'error', $referer ) );
		exit;
	}

	$result = packgens_process_form( $_POST, $_FILES );

	if ( $result['success'] && $result['redirect'] ) {
		wp_safe_redirect( $result['redirect'] );
		exit;
	}

	wp_safe_redirect(
		add_query_arg( 'pg_form_status', $result['success'] ? 'success' : 'error', $referer ) . '#pg-form-status'
	);
	exit;
}
add_action( 'admin_post_packgens_submit_form', 'packgens_post_submit_form' );
add_action( 'admin_post_nopriv_packgens_submit_form', 'packgens_post_submit_form' );

/**
 * Show the stored submission on the enquiry edit screen.
 *
 * @return void
 */
function packgens_enquiry_meta_box() {
	add_meta_box(
		'packgens_enquiry',
		__( 'Submission', 'packgens' ),
		'packgens_render_enquiry_meta_box',
		PACKGENS_ENQUIRY_CPT,
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'packgens_enquiry_meta_box' );

/**
 * Render the submission details.
 *
 * @param WP_Post $post Enquiry post.
 * @return void
 */
function packgens_render_enquiry_meta_box( $post ) {
	$form   = get_post_meta( $post->ID, '_pg_form', true );
	$values = get_post_meta( $post->ID, '_pg_values', true );
	$source = get_post_meta( $post->ID, '_pg_source', true );
	$file   = (int) get_post_meta( $post->ID, '_pg_attachment', true );
	$schema = packgens_form_schema( $form );

	if ( ! is_array( $values ) ) {
		echo '<p>' . esc_html__( 'No data stored.', 'packgens' ) . '</p>';

		return;
	}

	echo '<table class="widefat striped"><tbody>';

	foreach ( $values as $key => $value ) {
		if ( '' === $value ) {
			continue;
		}

		printf(
			'<tr><th style="width:180px">%s</th><td>%s</td></tr>',
			esc_html( $schema[ $key ]['label'] ?? ucfirst( $key ) ),
			esc_html( $value )
		);
	}

	if ( $source ) {
		printf(
			'<tr><th>%s</th><td><a href="%s">%s</a></td></tr>',
			esc_html__( 'Submitted from', 'packgens' ),
			esc_url( $source ),
			esc_html( $source )
		);
	}

	if ( $file ) {
		printf(
			'<tr><th>%s</th><td><a href="%s" download>%s</a></td></tr>',
			esc_html__( 'Attachment', 'packgens' ),
			esc_url( wp_get_attachment_url( $file ) ),
			esc_html( get_the_title( $file ) )
		);
	}

	echo '</tbody></table>';
}

/**
 * Category options offered by the quote form.
 *
 * @return string[]
 */
function packgens_quote_categories() {
	$custom = (array) packgens_global( 'quote_form', 'categories', array() );
	$labels = array();

	foreach ( $custom as $row ) {
		if ( ! empty( $row['label'] ) ) {
			$labels[] = $row['label'];
		}
	}

	if ( ! empty( $labels ) ) {
		return $labels;
	}

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'number'     => 40,
			'parent'     => 0,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return wp_list_pluck( $terms, 'name' );
}
