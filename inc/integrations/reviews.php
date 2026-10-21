<?php
/**
 * Product reviews: the data behind woocommerce/single-product-reviews.php and
 * the two optional fields (title, location) the theme adds to the review form.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Approved reviews for a product, newest first, with their store replies.
 *
 * Every review is rendered at once and product.js pages, filters and sorts
 * them in the browser, so the list never depends on WordPress comment paging.
 * The visitor's own review awaiting moderation is included, flagged pending.
 *
 * @param int $product_id Product ID.
 * @return array<int,array<string,mixed>>
 */
function packgens_product_reviews( $product_id ) {
	$commenter = wp_get_current_commenter();
	// The cookie covers returning visitors; the moderation hash in the URL
	// covers the redirect straight after posting without cookie consent.
	$own = array_filter(
		array(
			get_current_user_id(),
			$commenter['comment_author_email'] ?? '',
			function_exists( 'wp_get_unapproved_comment_author_email' ) ? wp_get_unapproved_comment_author_email() : '',
		)
	);

	$comments = get_comments(
		array(
			'post_id'            => $product_id,
			'status'             => 'approve',
			'type'               => 'review',
			'parent'             => 0,
			'orderby'            => 'comment_date_gmt',
			'order'              => 'DESC',
			'include_unapproved' => $own,
		)
	);

	$reviews = array();

	foreach ( $comments as $comment ) {
		$replies = get_comments(
			array(
				'post_id' => $product_id,
				'status'  => 'approve',
				'parent'  => $comment->comment_ID,
				'orderby' => 'comment_date_gmt',
				'order'   => 'ASC',
			)
		);

		$reviews[] = array(
			'id'       => (int) $comment->comment_ID,
			'rating'   => (int) get_comment_meta( $comment->comment_ID, 'rating', true ),
			'title'    => (string) get_comment_meta( $comment->comment_ID, 'pg_review_title', true ),
			'location' => (string) get_comment_meta( $comment->comment_ID, 'pg_review_location', true ),
			'author'   => packgens_review_author_name( $comment->comment_author ),
			'time'     => strtotime( $comment->comment_date_gmt ),
			'date'     => get_comment_date( 'M j, Y', $comment ),
			'text'     => $comment->comment_content,
			'verified' => function_exists( 'wc_review_is_from_verified_owner' ) && wc_review_is_from_verified_owner( $comment->comment_ID ),
			'pending'  => '0' === (string) $comment->comment_approved,
			'replies'  => $replies,
		);
	}

	return $reviews;
}

/**
 * A reviewer's name as first name plus last initial, e.g. "Ann P.".
 *
 * @param string $name Name as entered.
 * @return string
 */
function packgens_review_author_name( $name ) {
	$parts = preg_split( '/\s+/', trim( (string) $name ) );

	if ( count( $parts ) < 2 ) {
		return (string) $name;
	}

	return $parts[0] . ' ' . mb_strtoupper( mb_substr( end( $parts ), 0, 1 ) ) . '.';
}

/**
 * Arguments for the review form, in the theme's field markup.
 *
 * The rating stays a select with id "rating": WooCommerce's single-product
 * script turns that into the clickable stars and checks it on submit.
 *
 * @param WC_Product $product Product being reviewed.
 * @param bool       $has_reviews Whether the product already has reviews.
 * @return array
 */
function packgens_review_form_args( $product, $has_reviews ) {
	$commenter = wp_get_current_commenter();
	$required  = (bool) get_option( 'require_name_email', 1 );
	$star      = '<span class="pg-required" aria-hidden="true">*</span>';

	$field = static function ( $id, $name, $label, $input, $is_required = false ) use ( $star ) {
		return '<p class="pg-field pg-product-review-form__field pg-product-review-form__field--' . esc_attr( $name ) . '">'
			. '<label class="pg-field__label" for="' . esc_attr( $id ) . '">' . esc_html( $label ) . ( $is_required ? ' ' . $star : '' ) . '</label>'
			. $input
			. '</p>';
	};

	$text = static function ( $id, $name, $value, $type = 'text', $is_required = false, $extra = '' ) {
		return '<input class="pg-input" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" type="' . esc_attr( $type ) . '" value="' . esc_attr( $value ) . '"' . ( $is_required ? ' required' : '' ) . $extra . ' />';
	};

	$fields = array(
		'author'   => $field( 'author', 'author', packgens_label( 'reviews', 'form_name', __( 'Name', 'packgens' ) ), $text( 'author', 'author', $commenter['comment_author'], 'text', $required, ' autocomplete="name"' ), $required ),
		'email'    => $field( 'email', 'email', packgens_label( 'reviews', 'form_email', __( 'Email', 'packgens' ) ), $text( 'email', 'email', $commenter['comment_author_email'], 'email', $required, ' autocomplete="email"' ), $required ),
		'location' => $field( 'pg-product-review-location', 'location', packgens_label( 'reviews', 'form_location', __( 'Location (optional)', 'packgens' ) ), $text( 'pg-product-review-location', 'pg_review_location', '', 'text', false, ' maxlength="60" autocomplete="country-name"' ) ),
	);

	// Logged-in reviewers never see the name and email fields, so the optional
	// location has to travel with the comment field instead.
	$comment_field = '';

	if ( wc_review_ratings_enabled() ) {
		$rating_required = wc_review_ratings_required();

		$comment_field .= '<div class="pg-field pg-product-review-form__field pg-product-review-form__field--rating comment-form-rating">'
			. '<label class="pg-field__label" for="rating" id="comment-form-rating-label">' . esc_html( packgens_label( 'reviews', 'form_rating', __( 'Your rating', 'packgens' ) ) ) . ( $rating_required ? ' ' . $star : '' ) . '</label>'
			. '<select name="rating" id="rating"' . ( $rating_required ? ' required' : '' ) . '>'
			. '<option value="">' . esc_html__( 'Rate&hellip;', 'packgens' ) . '</option>'
			. '<option value="5">' . esc_html__( 'Perfect', 'packgens' ) . '</option>'
			. '<option value="4">' . esc_html__( 'Good', 'packgens' ) . '</option>'
			. '<option value="3">' . esc_html__( 'Average', 'packgens' ) . '</option>'
			. '<option value="2">' . esc_html__( 'Not that bad', 'packgens' ) . '</option>'
			. '<option value="1">' . esc_html__( 'Very poor', 'packgens' ) . '</option>'
			. '</select></div>';
	}

	$comment_field .= $field( 'pg-product-review-title', 'title', packgens_label( 'reviews', 'form_title_field', __( 'Review title (optional)', 'packgens' ) ), $text( 'pg-product-review-title', 'pg_review_title', '', 'text', false, ' maxlength="100"' ) );
	$comment_field .= $field(
		'comment',
		'comment',
		packgens_label( 'reviews', 'form_review', __( 'Your review', 'packgens' ) ),
		'<textarea class="pg-input" id="comment" name="comment" rows="6" required></textarea>',
		true
	);

	if ( is_user_logged_in() ) {
		$comment_field .= $fields['location'];
	}

	$title = $has_reviews
		? packgens_label( 'reviews', 'form_heading', __( 'Write a review', 'packgens' ) )
		: sprintf(
			/* translators: %s: product name. */
			packgens_label( 'reviews', 'form_heading_first', __( 'Be the first to review %s', 'packgens' ) ),
			$product->get_name()
		);

	$args = array(
		'title_reply'          => esc_html( $title ),
		'title_reply_to'       => esc_html__( 'Leave a Reply to %s', 'packgens' ),
		'title_reply_before'   => '<h3 id="reply-title" class="pg-product-review-form__title">',
		'title_reply_after'    => '</h3>',
		'comment_notes_before' => '',
		'comment_notes_after'  => '',
		'logged_in_as'         => '',
		'class_form'           => 'pg-product-review-form__form',
		'class_submit'         => 'pg-btn pg-btn--primary',
		'label_submit'         => packgens_label( 'reviews', 'form_submit', __( 'Submit review', 'packgens' ) ),
		'submit_field'         => '<p class="pg-product-review-form__submit">%1$s %2$s</p>',
		'fields'               => $fields,
		'comment_field'        => $comment_field,
	);

	$account = wc_get_page_permalink( 'myaccount' );

	if ( $account ) {
		$args['must_log_in'] = '<p class="must-log-in">' . sprintf(
			/* translators: 1: opening link tag, 2: closing link tag. */
			esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'packgens' ),
			'<a href="' . esc_url( $account ) . '">',
			'</a>'
		) . '</p>';
	}

	return $args;
}

/**
 * Store the optional title and location sent with a product review.
 *
 * @param int $comment_id Comment ID.
 * @return void
 */
function packgens_save_review_extras( $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! $comment || 'product' !== get_post_type( $comment->comment_post_ID ) || $comment->comment_parent ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Missing -- core's comment form posts without a nonce; the values are sanitised plain text.
	$fields = array(
		'pg_review_title'    => 100,
		'pg_review_location' => 60,
	);

	foreach ( $fields as $key => $max ) {
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';

		if ( '' !== $value ) {
			add_comment_meta( $comment_id, $key, mb_substr( $value, 0, $max ), true );
		}
	}
	// phpcs:enable
}
add_action( 'comment_post', 'packgens_save_review_extras' );
