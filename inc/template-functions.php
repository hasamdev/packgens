<?php
/**
 * Template helpers: body classes, breadcrumbs, pagination, excerpts.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add classes the CSS relies on.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function packgens_body_class( $classes ) {
	// Views without a coloured hero need the header to paint its own band.
	if ( ! packgens_view_has_hero() ) {
		$classes[] = 'pg-has-plain-header';
	}

	if ( is_singular() && ! is_front_page() ) {
		$classes[] = 'pg-singular';
	}

	return $classes;
}
add_filter( 'body_class', 'packgens_body_class' );

/**
 * Whether the current view renders its own full-bleed hero behind the header.
 *
 * @return bool
 */
function packgens_view_has_hero() {
	$context = packgens_field_context();
	$data    = packgens_field( 'hero_section', $context, array() );

	if ( is_array( $data ) && ! empty( $data ) && ( ! array_key_exists( 'enable', $data ) || $data['enable'] ) ) {
		return true;
	}

	// Templates that always render a compact band of their own.
	return is_singular( 'post' ) || is_archive() || is_search() || is_404() || is_home();
}

/**
 * Breadcrumb trail.
 *
 * Yields to Yoast or Rank Math when either is configured to output one.
 *
 * @return void
 */
function packgens_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'yoast_breadcrumb' ) ) {
		yoast_breadcrumb( '<nav class="pg-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'packgens' ) . '">', '</nav>' );

		return;
	}

	if ( function_exists( 'rank_math_the_breadcrumbs' ) ) {
		echo '<nav class="pg-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'packgens' ) . '">';
		rank_math_the_breadcrumbs();
		echo '</nav>';

		return;
	}

	$items = packgens_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return;
	}

	$last = count( $items ) - 1;
	?>
	<nav class="pg-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'packgens' ); ?>">
		<ol>
			<?php foreach ( $items as $index => $item ) : ?>
				<li>
					<?php if ( $index === $last || empty( $item['url'] ) ) : ?>
						<span aria-current="page"><?php echo esc_html( $item['label'] ); ?></span>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>
	<?php
}

/**
 * Build the breadcrumb trail for the current view.
 *
 * @return array<int,array{label:string,url:string}>
 */
function packgens_breadcrumb_items() {
	$items = array(
		array( 'label' => __( 'Home', 'packgens' ), 'url' => home_url( '/' ) ),
	);

	if ( is_singular( 'product' ) ) {
		$terms = get_the_terms( get_the_ID(), 'product_cat' );

		if ( $terms && ! is_wp_error( $terms ) ) {
			$term = array_shift( $terms );

			foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat' ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, 'product_cat' );

				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$items[] = array( 'label' => $ancestor->name, 'url' => get_term_link( $ancestor ) );
				}
			}

			$items[] = array( 'label' => $term->name, 'url' => get_term_link( $term ) );
		}

		$items[] = array( 'label' => get_the_title(), 'url' => '' );

		return $items;
	}

	if ( is_singular( 'post' ) ) {
		$category = get_the_category();

		if ( $category ) {
			$items[] = array( 'label' => $category[0]->name, 'url' => get_category_link( $category[0] ) );
		}

		$items[] = array( 'label' => get_the_title(), 'url' => '' );

		return $items;
	}

	if ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor_id ) {
			$items[] = array( 'label' => get_the_title( $ancestor_id ), 'url' => get_permalink( $ancestor_id ) );
		}

		$items[] = array( 'label' => get_the_title(), 'url' => '' );

		return $items;
	}

	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();

		if ( $term instanceof WP_Term ) {
			foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) ) as $ancestor_id ) {
				$ancestor = get_term( $ancestor_id, $term->taxonomy );

				if ( $ancestor && ! is_wp_error( $ancestor ) ) {
					$items[] = array( 'label' => $ancestor->name, 'url' => get_term_link( $ancestor ) );
				}
			}

			$items[] = array( 'label' => $term->name, 'url' => '' );
		}

		return $items;
	}

	if ( is_search() ) {
		$items[] = array(
			'label' => sprintf( /* translators: %s: search term. */ __( 'Search: %s', 'packgens' ), get_search_query() ),
			'url'   => '',
		);

		return $items;
	}

	if ( is_home() ) {
		$items[] = array( 'label' => get_the_title( (int) get_option( 'page_for_posts' ) ), 'url' => '' );

		return $items;
	}

	if ( is_404() ) {
		$items[] = array( 'label' => __( 'Page not found', 'packgens' ), 'url' => '' );
	}

	return $items;
}

/**
 * Themed pagination.
 *
 * @param WP_Query|null $query Query to paginate. Defaults to the main query.
 * @return void
 */
function packgens_pagination( $query = null ) {
	$total = $query instanceof WP_Query ? $query->max_num_pages : 0;

	if ( $total < 2 && ! $query ) {
		global $wp_query;
		$total = $wp_query->max_num_pages;
	}

	if ( $total < 2 ) {
		return;
	}

	$links = paginate_links(
		array(
			'total'     => $total,
			'current'   => max( 1, get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 ),
			'mid_size'  => 1,
			'end_size'  => 1,
			'type'      => 'list',
			'prev_text' => packgens_get_icon( 'chevron-left' ) . '<span class="pg-sr-only">' . esc_html__( 'Previous', 'packgens' ) . '</span>',
			'next_text' => packgens_get_icon( 'chevron-right' ) . '<span class="pg-sr-only">' . esc_html__( 'Next', 'packgens' ) . '</span>',
		)
	);

	if ( ! $links ) {
		return;
	}

	printf(
		'<nav class="pg-pagination" aria-label="%s">%s</nav>',
		esc_attr__( 'Pagination', 'packgens' ),
		wp_kses_post( $links )
	);
}

/**
 * Shorter excerpts than core's 55 words.
 *
 * @param int $length Word count.
 * @return int
 */
function packgens_excerpt_length( $length ) {
	return is_admin() ? $length : 24;
}
add_filter( 'excerpt_length', 'packgens_excerpt_length', 999 );

/**
 * Ellipsis instead of the bracketed hellip.
 *
 * @param string $more More string.
 * @return string
 */
function packgens_excerpt_more( $more ) {
	return is_admin() ? $more : '&hellip;';
}
add_filter( 'excerpt_more', 'packgens_excerpt_more' );

/**
 * Estimated reading time for a post.
 *
 * @param int $post_id Post ID.
 * @return int Minutes, minimum 1.
 */
function packgens_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$words   = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );

	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Share links for a single post.
 *
 * @param int $post_id Post ID.
 * @return array<string,string> Icon key => URL.
 */
function packgens_share_links( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$url     = rawurlencode( get_permalink( $post_id ) );
	$title   = rawurlencode( wp_strip_all_tags( get_the_title( $post_id ) ) );

	return array(
		'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
		'x'        => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
		'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . $url,
		'whatsapp' => 'https://wa.me/?text=' . $title . '%20' . $url,
	);
}

/**
 * Build a table of contents from the h2 headings in a block of HTML.
 *
 * @param string $content Post content.
 * @return array<int,array{id:string,text:string}>
 */
function packgens_table_of_contents( $content ) {
	if ( ! $content || ! preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/is', $content, $matches ) ) {
		return array();
	}

	$items = array();

	foreach ( $matches[1] as $index => $heading ) {
		$text = trim( wp_strip_all_tags( $heading ) );

		if ( '' === $text ) {
			continue;
		}

		$items[] = array(
			'id'   => 'section-' . ( $index + 1 ),
			'text' => $text,
		);
	}

	return $items;
}

/**
 * Add matching ids to the h2 headings so the table of contents can link to them.
 *
 * @param string $content Post content.
 * @return string
 */
function packgens_add_heading_ids( $content ) {
	if ( ! is_singular( 'post' ) || is_admin() ) {
		return $content;
	}

	$index = 0;

	return preg_replace_callback(
		'/<h2(?![^>]*\bid=)([^>]*)>/i',
		function ( $matches ) use ( &$index ) {
			++$index;

			return '<h2 id="section-' . $index . '"' . $matches[1] . '>';
		},
		$content
	);
}
add_filter( 'the_content', 'packgens_add_heading_ids', 9 );

/**
 * Add an author photo picker to the user profile screen.
 */
function packgens_user_avatar_field( $user ) {
	$attachment_id = (int) get_user_meta( $user->ID, 'packgens_avatar_id', true );
	?>
	<h2><?php esc_html_e( 'Author photo', 'packgens' ); ?></h2>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="packgens_avatar_id"><?php esc_html_e( 'Media library image ID', 'packgens' ); ?></label></th>
			<td>
				<input type="number" name="packgens_avatar_id" id="packgens_avatar_id" class="regular-text"
					value="<?php echo $attachment_id ? esc_attr( $attachment_id ) : ''; ?>" min="0" step="1" />
				<p class="description"><?php esc_html_e( 'Shown on post bylines. Leave empty to use Gravatar.', 'packgens' ); ?></p>
				<?php if ( $attachment_id ) : ?>
					<p><?php echo wp_get_attachment_image( $attachment_id, array( 64, 64 ) ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
	</table>
	<?php
}
add_action( 'show_user_profile', 'packgens_user_avatar_field' );
add_action( 'edit_user_profile', 'packgens_user_avatar_field' );

/**
 * Save the author photo picker.
 */
function packgens_save_user_avatar_field( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}

	check_admin_referer( 'update-user_' . $user_id );

	$attachment_id = isset( $_POST['packgens_avatar_id'] ) ? absint( wp_unslash( $_POST['packgens_avatar_id'] ) ) : 0;

	if ( $attachment_id ) {
		update_user_meta( $user_id, 'packgens_avatar_id', $attachment_id );
	} else {
		delete_user_meta( $user_id, 'packgens_avatar_id' );
	}
}
add_action( 'personal_options_update', 'packgens_save_user_avatar_field' );
add_action( 'edit_user_profile_update', 'packgens_save_user_avatar_field' );
