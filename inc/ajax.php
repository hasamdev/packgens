<?php
/**
 * Public AJAX endpoints: predictive search and load-more listings.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Predictive search across products or posts.
 *
 * @return void
 */
function packgens_ajax_search() {
	check_ajax_referer( 'packgens_public', 'nonce' );

	$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';

	if ( mb_strlen( $term ) < 3 ) {
		wp_send_json_success( array( 'results' => array() ) );
	}

	$requested = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : 'product';
	$post_type = post_type_exists( $requested ) ? $requested : 'post';

	$query = new WP_Query(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			's'              => $term,
			'posts_per_page' => 6,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		)
	);

	$results = array();

	foreach ( $query->posts as $post_id ) {
		$meta = '';

		if ( 'product' === $post_type && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post_id );

			if ( $product ) {
				// Decoded: the script escapes the text itself, so entities such
				// as &pound; would otherwise show literally.
				$meta = html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES, 'UTF-8' );
			}
		} else {
			$meta = get_the_date( '', $post_id );
		}

		$results[] = array(
			'title' => html_entity_decode( get_the_title( $post_id ), ENT_QUOTES, 'UTF-8' ),
			'url'   => get_permalink( $post_id ),
			'image' => get_the_post_thumbnail_url( $post_id, 'packgens-thumb' ),
			'meta'  => $meta,
		);
	}

	wp_send_json_success(
		array(
			'results'  => $results,
			'allUrl'   => add_query_arg(
				array(
					's'         => rawurlencode( $term ),
					'post_type' => $post_type,
				),
				home_url( '/' )
			),
			'allLabel' => html_entity_decode(
				sprintf(
					/* translators: %s: search term. */
					__( 'View all results for &ldquo;%s&rdquo;', 'packgens' ),
					$term
				),
				ENT_QUOTES,
				'UTF-8'
			),
		)
	);
}
add_action( 'wp_ajax_packgens_search', 'packgens_ajax_search' );
add_action( 'wp_ajax_nopriv_packgens_search', 'packgens_ajax_search' );

/**
 * Append the next page of an archive listing.
 *
 * The client sends only a page number, a listing type and an opaque signed
 * query string, so no arbitrary WP_Query arguments can be injected.
 *
 * @return void
 */
function packgens_ajax_load_more() {
	check_ajax_referer( 'packgens_public', 'nonce' );

	$page = isset( $_POST['page'] ) ? max( 2, absint( wp_unslash( $_POST['page'] ) ) ) : 2;
	$type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'post';
	$raw  = isset( $_POST['query'] ) ? wp_unslash( $_POST['query'] ) : '';

	$vars = packgens_unpack_query( $raw );

	if ( null === $vars ) {
		wp_send_json_error( array( 'message' => __( 'That request could not be verified.', 'packgens' ) ), 400 );
	}

	$per_page = isset( $vars['posts_per_page'] ) ? absint( $vars['posts_per_page'] ) : 9;

	switch ( $type ) {
		case 'product':
			$args = array_merge( $vars, array( 'post_type' => 'product' ) );
			$card = 'template-parts/cards/product';
			break;

		case 'review':
			$args = packgens_reviews_query_args( $page, $per_page );
			$card = 'template-parts/cards/review';
			break;

		default:
			$args = array_merge( $vars, array( 'post_type' => 'post' ) );
			$card = 'template-parts/cards/post';
			break;
	}

	$args['paged']          = $page;
	$args['post_status']    = 'publish';
	$args['posts_per_page'] = $per_page;
	$args['fields']         = 'ids';

	$query = new WP_Query( $args );

	ob_start();

	foreach ( $query->posts as $post_id ) {
		get_template_part( $card, null, array( 'id' => (int) $post_id ) );
	}

	$html = ob_get_clean();

	wp_send_json_success(
		array(
			'html'     => $html,
			'page'     => $page,
			'maxPages' => (int) $query->max_num_pages,
		)
	);
}
add_action( 'wp_ajax_packgens_load_more', 'packgens_ajax_load_more' );
add_action( 'wp_ajax_nopriv_packgens_load_more', 'packgens_ajax_load_more' );

/**
 * Sign a whitelisted set of query vars for the load-more button.
 *
 * @param array $vars Query vars.
 * @return string
 */
function packgens_pack_query( $vars ) {
	$allowed = array( 'posts_per_page', 'cat', 'tag_id', 'author', 'orderby', 'order', 's', 'tax_query', 'meta_key', 'post__in' );
	$clean   = array();

	foreach ( $allowed as $key ) {
		if ( isset( $vars[ $key ] ) ) {
			$clean[ $key ] = $vars[ $key ];
		}
	}

	$payload = wp_json_encode( $clean );

	if ( ! $payload ) {
		return '';
	}

	$encoded = base64_encode( $payload ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Transport, not obfuscation.

	return $encoded . '.' . hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) );
}

/**
 * Verify and decode a packed query string.
 *
 * @param string $raw Packed value.
 * @return array|null Query vars, or null when the signature fails.
 */
function packgens_unpack_query( $raw ) {
	if ( '' === $raw ) {
		return array();
	}

	$parts = explode( '.', (string) $raw );

	if ( 2 !== count( $parts ) ) {
		return null;
	}

	list( $encoded, $signature ) = $parts;

	if ( ! hash_equals( hash_hmac( 'sha256', $encoded, wp_salt( 'nonce' ) ), $signature ) ) {
		return null;
	}

	$decoded = json_decode( base64_decode( $encoded ), true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode

	return is_array( $decoded ) ? $decoded : null;
}

/**
 * Render a load-more button for a query.
 *
 * @param WP_Query $query     Query being listed.
 * @param string   $target_id DOM id of the results container.
 * @param string   $type      Card type: post, product or review.
 * @return void
 */
function packgens_load_more_button( $query, $target_id, $type = 'post', $label = '' ) {
	if ( ! $query instanceof WP_Query || $query->max_num_pages < 2 ) {
		return;
	}

	$current = max( 1, (int) ( $query->get( 'paged' ) ? $query->get( 'paged' ) : 1 ) );

	if ( $current >= $query->max_num_pages ) {
		return;
	}

	$next_url = get_pagenum_link( $current + 1 );
	?>
	<div class="pg-load-more">
		<a class="pg-btn pg-btn--outline"
			href="<?php echo esc_url( $next_url ); ?>"
			data-pg-load-more
			data-target="<?php echo esc_attr( $target_id ); ?>"
			data-type="<?php echo esc_attr( $type ); ?>"
			data-page="<?php echo esc_attr( $current ); ?>"
			data-max-pages="<?php echo esc_attr( $query->max_num_pages ); ?>"
			data-query="<?php echo esc_attr( packgens_pack_query( $query->query_vars ) ); ?>">
			<span class="pg-btn__label"><?php echo esc_html( $label ? $label : __( 'Load more', 'packgens' ) ); ?></span>
		</a>
	</div>
	<?php
}
