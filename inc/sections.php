<?php
/**
 * Section renderer.
 *
 * Pages, the front page, product categories and products all compose the same
 * library of sections. This module resolves which sections a view renders, in
 * what order, and hands each one its field data.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * The custom-field object id for the current view.
 *
 * SCF addresses terms as "term_{id}" and posts by numeric id.
 *
 * @return int|string|false
 */
function packgens_field_context() {
	if ( is_tax() || is_category() || is_tag() ) {
		$term = get_queried_object();

		return ( $term instanceof WP_Term ) ? 'term_' . $term->term_id : false;
	}

	if ( is_singular() ) {
		return get_the_ID();
	}

	if ( is_home() && ! is_front_page() ) {
		$page_id = (int) get_option( 'page_for_posts' );

		return $page_id ? $page_id : false;
	}

	return false;
}

/**
 * The context product categories inherit from.
 *
 * Every category renders the same template and the same section library; what
 * differs is only the content. Rather than making each term carry a full copy of
 * that content, a term stores what it wants to differ and everything else is
 * read from one options screen, so an edit there reaches every category.
 *
 * @param string $view View key.
 * @return string|false Options context id, or false when the view has none.
 */
function packgens_section_defaults_context( $view ) {
	$context = false;

	if ( 'product_cat' === $view && defined( 'PACKGENS_CAT_DEFAULTS_ID' ) ) {
		$context = PACKGENS_CAT_DEFAULTS_ID;
	} elseif ( 'product' === $view && defined( 'PACKGENS_PRODUCT_DEFAULTS_ID' ) ) {
		$context = PACKGENS_PRODUCT_DEFAULTS_ID;
	}

	/**
	 * Filter the fallback context for a view.
	 *
	 * @param string|false $context Options context id.
	 * @param string       $view    View key.
	 */
	return apply_filters( 'packgens_section_defaults_context', $context, $view );
}

/**
 * The sub-fields a term has actually saved for a section.
 *
 * A read through get_field() cannot answer this: ACF fills anything unsaved with
 * the field's own default_value, so an untouched "Layout" select reads as
 * "center" rather than as absent. The stored rows are the only honest record of
 * what the editor set, and ACF writes one per top-level sub-field.
 *
 * @param string     $group   Section group name.
 * @param int|string $context Field context id.
 * @param string[]   $keys    Sub-field names to test.
 * @return string[] Names present in the database.
 */
function packgens_stored_section_keys( $group, $context, $keys ) {
	if ( is_string( $context ) && 0 === strpos( $context, 'term_' ) ) {
		$type = 'term';
		$id   = (int) substr( $context, 5 );
	} elseif ( is_numeric( $context ) && (int) $context > 0 ) {
		$type = 'post';
		$id   = (int) $context;
	} else {
		return $keys;
	}

	$stored = array();

	foreach ( $keys as $key ) {
		if ( metadata_exists( $type, $id, $group . '_' . $key ) ) {
			$stored[] = $key;
		}
	}

	return $stored;
}

/**
 * Lay a term's own values over the shared defaults, sub-field by sub-field.
 *
 * Merging per sub-field rather than per group is what lets a category override
 * only its headline and inherit the rest, instead of having to carry a full copy
 * of every section.
 *
 * @param array      $data     Values read from the term.
 * @param array      $defaults Values from the defaults screen.
 * @param string     $group    Section group name.
 * @param int|string $context  Field context id.
 * @return array
 */
function packgens_merge_section_defaults( $data, $defaults, $group = '', $context = null ) {
	if ( empty( $defaults ) ) {
		return $data;
	}

	if ( empty( $data ) ) {
		return $defaults;
	}

	$own    = packgens_stored_section_keys( $group, $context, array_keys( $data ) );
	$merged = $defaults;

	foreach ( $own as $key ) {
		if ( array_key_exists( $key, $data ) ) {
			$merged[ $key ] = $data[ $key ];
		}
	}

	return $merged;
}

/**
 * Read a section's data for a view, defaults included.
 *
 * @param string          $section Section slug.
 * @param int|string      $context Field context id.
 * @param string          $view    View key.
 * @return array
 */
function packgens_section_data( $section, $context, $view = 'page' ) {
	$group = packgens_section_group_name( $section );
	$data  = packgens_field( $group, $context, array() );
	$data  = is_array( $data ) ? $data : array();

	$fallback = packgens_section_defaults_context( $view );

	if ( $fallback ) {
		$defaults = packgens_field( $group, $fallback, array() );
		$data     = packgens_merge_section_defaults( $data, is_array( $defaults ) ? $defaults : array(), $group, $context );
	}

	// Own-only fields (e.g. the category's SEO content switch) are never taken
	// from the defaults, so the merge above must not have supplied them.
	$inherit = $fallback ? packgens_inherit_context( $context ) : null;

	if ( $inherit && $data ) {
		foreach ( packgens_own_only_section_fields( $inherit['kind'] ) as $name ) {
			if ( 0 === strpos( $name, $group . '_' ) ) {
				$key          = substr( $name, strlen( $group ) + 1 );
				$data[ $key ] = metadata_exists( $inherit['meta'], $inherit['id'], $name )
					? get_metadata( $inherit['meta'], $inherit['id'], $name, true )
					: 0;
			}
		}
	}

	// A product page always carries its process steps and showcase row. When
	// neither the product nor Product Defaults has written any content for
	// one, fall back to the homepage's, so a new product needs no data entry
	// before it looks complete.
	if ( 'product' === $view && in_array( $section, array( 'process', 'showcase' ), true ) && ! packgens_section_has_content( $data, $group ) ) {
		$front = (int) get_option( 'page_on_front' );
		$home  = $front ? packgens_field( $group, $front, array() ) : array();

		if ( is_array( $home ) && packgens_section_has_content( $home, $group ) ) {
			// The content is borrowed; the on/off switch stays the product's.
			$home['enable'] = array_key_exists( 'enable', $data ) ? $data['enable'] : 1;
			$data           = $home;
		}
	}

	// Category Defaults leaves the hero heading and text blank on purpose: each
	// category's banner is about that category. Fill them from the term itself
	// unless the category typed its own.
	if ( 'hero' === $section && 'product_cat' === $view && $data ) {
		$term_id = function_exists( 'packgens_category_context_term' ) ? packgens_category_context_term( $context ) : 0;
		$term    = $term_id ? get_term( $term_id ) : null;

		if ( $term instanceof WP_Term ) {
			if ( empty( $data['title'] ) ) {
				$data['title'] = $term->name;
			}

			if ( empty( $data['description'] ) ) {
				$data['description'] = wp_strip_all_tags( term_description( $term ) );
			}
		}
	}

	return $data;
}

/**
 * Whether section data holds content an editor wrote, beyond switches and
 * layout choices.
 *
 * SCF hands back a never-saved section as its default values -- "Show this
 * section" on, the default layout -- which is not empty but has nothing to
 * show. Only text, media, links and rows count here.
 *
 * @param array  $data  Section values keyed by sub-field name.
 * @param string $group Section group name, e.g. "process_section".
 * @return bool
 */
function packgens_section_has_content( $data, $group ) {
	if ( empty( $data ) || ! is_array( $data ) ) {
		return false;
	}

	$settings = array( 'true_false', 'select', 'radio', 'button_group', 'checkbox', 'tab', 'message', 'number', 'color_picker' );
	$types    = array();

	foreach ( array( 'product', 'product_cat' ) as $kind ) {
		$field = packgens_inherited_section_groups( $kind )[ $group ] ?? null;

		if ( $field ) {
			foreach ( (array) $field['sub_fields'] as $sub ) {
				$types[ $sub['name'] ] = $sub['type'];
			}
			break;
		}
	}

	foreach ( $data as $key => $value ) {
		if ( 'enable' === $key || in_array( $types[ $key ] ?? '', $settings, true ) ) {
			continue;
		}

		if ( ! packgens_is_blank_field_value( packgens_normalise_field_value( $value ) ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Default section order per view type.
 *
 * @param string $view One of front, page, product_cat, product.
 * @return string[]
 */
function packgens_default_section_order( $view = 'page' ) {
	$orders = array(
		// Mirrors the Figma "final" homepage frame, top to bottom.
		'front'       => array(
			'hero', 'categories', 'about', 'showcase', 'cta', 'products', 'promo',
			'process', 'why', 'comparison', 'cta_alt', 'content', 'chat', 'stats',
			'industries', 'faqs', 'brands', 'reviews', 'blogs',
		),
		'page'        => array(
			'hero', 'stats', 'about', 'values', 'quote_band', 'why', 'process',
			'materials', 'industries', 'products', 'faqs', 'reviews', 'content',
			'cta', 'blogs',
		),
		'product_cat' => array(
			'hero', 'products', 'cta', 'content', 'materials', 'why', 'process',
			'reviews', 'faqs',
		),
		'product'     => array(
			'products', 'process', 'showcase',
		),
	);

	/**
	 * Filter the default section order for a view.
	 *
	 * @param string[] $order Section slugs.
	 * @param string   $view  View key.
	 */
	return apply_filters( 'packgens_default_section_order', $orders[ $view ] ?? $orders['page'], $view );
}

/**
 * Resolve the ordered list of sections for the current view.
 *
 * @param string          $view    View key.
 * @param int|string|null $context Field context id.
 * @return string[]
 */
function packgens_section_order( $view = 'page', $context = null ) {
	unset( $context ); // Kept for callers; the order no longer varies per page.

	// The order is fixed per view. Editors used to be able to reorder (and
	// drop) sections per page; that option is gone, and any order still stored
	// from it is ignored. A section is hidden with its own Show toggle.
	return packgens_default_section_order( $view );
}

/**
 * Render every enabled section for the current view.
 *
 * @param string          $view    View key: front, page, product_cat, product.
 * @param int|string|null $context Field context id.
 * @param string[]        $only    Optional whitelist of sections.
 * @param string[]        $exclude Sections already rendered by the template.
 * @return void
 */
function packgens_render_sections( $view = 'page', $context = null, $only = array(), $exclude = array() ) {
	if ( null === $context ) {
		$context = packgens_field_context();
	}

	$sections = packgens_section_order( $view, $context );

	if ( ! empty( $only ) ) {
		$sections = array_values( array_intersect( $sections, $only ) );
	}

	if ( ! empty( $exclude ) ) {
		$sections = array_values( array_diff( $sections, $exclude ) );
	}

	foreach ( $sections as $section ) {
		packgens_render_section( $section, $context, $view );
	}
}

/**
 * Render one section if its data says it should appear.
 *
 * @param string          $section Section slug.
 * @param int|string|null $context Field context id.
 * @param string          $view    View key.
 * @return void
 */
function packgens_render_section( $section, $context = null, $view = 'page' ) {
	$section = sanitize_key( $section );

	if ( ! array_key_exists( $section, packgens_section_choices() ) ) {
		return;
	}

	if ( null === $context ) {
		$context = packgens_field_context();
	}

	$data = packgens_section_data( $section, $context, $view );

	// An absent group means the section was never configured for this view.
	if ( empty( $data ) ) {
		return;
	}

	if ( array_key_exists( 'enable', $data ) && ! $data['enable'] ) {
		return;
	}

	get_template_part(
		'template-parts/sections/' . $section,
		null,
		array(
			'data'    => $data,
			'context' => $context,
			'view'    => $view,
		)
	);
}

/**
 * Map a section slug to its field group name.
 *
 * @param string $section Section slug.
 * @return string
 */
function packgens_section_group_name( $section ) {
	$map = array(
		'why' => 'why_section',
	);

	return $map[ $section ] ?? $section . '_section';
}

/**
 * Print a section heading block.
 *
 * @param array $data Section data.
 * @param array $args Display options.
 * @return void
 */
function packgens_section_header( $data, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'align'     => 'left',   // left | center.
			'tag'       => 'h2',
			'button'    => 'button',
			'variant'   => 'link',
			'class'     => '',
			'icon'      => 'arrow-right',
			'nav'       => false,     // Render carousel arrows in the actions slot.
			'nav_id'    => '',
		)
	);

	$eyebrow = $data['eyebrow'] ?? '';
	$title   = $data['title'] ?? '';
	$desc    = $data['description'] ?? '';
	$button  = $args['button'] ? ( $data[ $args['button'] ] ?? null ) : null;

	if ( ! $eyebrow && ! $title && ! $desc && ! $button && ! $args['nav'] ) {
		return;
	}

	$classes = 'pg-section-head';

	if ( 'center' === $args['align'] ) {
		$classes .= ' pg-section-head--center';
	}

	if ( $args['class'] ) {
		$classes .= ' ' . $args['class'];
	}

	$tag = in_array( $args['tag'], array( 'h1', 'h2', 'h3' ), true ) ? $args['tag'] : 'h2';
	?>
	<div class="<?php echo esc_attr( $classes ); ?>">
		<div class="pg-section-head__copy">
			<?php if ( $eyebrow ) : ?>
				<span class="pg-eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
			<?php endif; ?>

			<?php if ( $title ) : ?>
				<<?php echo esc_attr( $tag ); ?> class="pg-section-head__title"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $tag ); ?>>
			<?php endif; ?>

			<?php if ( $desc ) : ?>
				<p class="pg-section-head__desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $button || $args['nav'] ) : ?>
			<div class="pg-section-head__actions">
				<?php if ( $args['nav'] && $args['nav_id'] ) : ?>
					<?php packgens_carousel_nav( $args['nav_id'] ); ?>
				<?php endif; ?>

				<?php if ( $button ) : ?>
					<?php packgens_button( $button, $args['variant'], __( 'View all', 'packgens' ), $args['icon'] ); ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Open a Swiper carousel.
 *
 * Emits the wrapper, the Swiper container and the slide wrapper. Every slide
 * must go through packgens_carousel_slide(), and the block is closed with
 * packgens_carousel_close().
 *
 * Before Swiper initialises — and if it never does — the track falls back to a
 * native scroll-snap row, so the content is usable either way.
 *
 * @param string $id   DOM id.
 * @param array  $args {
 *     @type string             $class    Extra classes for the track.
 *     @type bool               $bleed    Let slides run past the container edge.
 *     @type array<int|string,float> $per_view Slides per view, keyed by min-width breakpoint.
 *     @type string             $label    Accessible name for the carousel.
 *     @type string             $group    Shared id for rows that move together. Pass
 *                                        the same value to packgens_carousel_nav().
 *     @type bool               $stagger  Offset this row by half a slide, for the
 *                                        second row in the design.
 *     @type string             $auto     Continuous scroll: "forward" or "reverse".
 *     @type string             $effect   Transition: "fade" cross-fades in place.
 *     @type int                $autoplay Milliseconds between slides, 0 for none.
 *     @type bool               $loop     Wrap around instead of stopping at the ends.
 * }
 * @return void
 */
function packgens_carousel_open( $id, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'class'    => '',
			'bleed'    => false,
			'per_view' => array(),
			'label'    => '',
			'group'    => '',
			'stagger'  => false,
			'auto'     => '',
			'effect'   => '',
			'autoplay' => 0,
			'loop'     => false,
		)
	);

	$wrapper = 'pg-carousel'
		. ( $args['bleed'] ? ' pg-carousel--bleed' : '' )
		. ( $args['stagger'] ? ' pg-carousel--stagger' : '' );

	// A drifting row is a marquee, not a Swiper: it needs a duplicated track
	// and one uninterrupted animation, so it takes a different shape entirely.
	if ( in_array( $args['auto'], array( 'forward', 'reverse' ), true ) ) {
		$track = 'pg-marquee__viewport';

		if ( $args['class'] ) {
			$track .= ' ' . $args['class'];
		}
		?>
		<div class="<?php echo esc_attr( $wrapper . ' pg-carousel--marquee' ); ?>">
			<div
				id="<?php echo esc_attr( $id ); ?>"
				class="<?php echo esc_attr( $track ); ?>"
				data-pg-marquee="<?php echo esc_attr( $args['auto'] ); ?>"
				<?php if ( $args['group'] ) : ?>
					data-pg-carousel-group="<?php echo esc_attr( $args['group'] ); ?>"
				<?php endif; ?>
				<?php if ( $args['label'] ) : ?>
					role="group" aria-label="<?php echo esc_attr( $args['label'] ); ?>"
				<?php endif; ?>
			>
				<div class="pg-marquee__shift">
					<div class="pg-marquee__track">
		<?php
		// Buffered so the slides can be emitted a second time for the loop.
		packgens_carousel_is_marquee( true );
		ob_start();

		return;
	}

	packgens_carousel_is_marquee( false );

	$track   = 'swiper pg-carousel__track';

	/*
	 * A row that only ever shows one slide should not lay its slides out
	 * side by side before Swiper boots: on the hero that means all the
	 * headlines flashing across the band and the section standing at the
	 * height of the tallest, until the script snaps it back to one.
	 */
	$per_view = array_filter( (array) $args['per_view'] );
	$single   = 'fade' === $args['effect']
		|| ( $per_view && max( $per_view ) <= 1 );

	if ( $single ) {
		$track .= ' pg-carousel__track--single';
	}

	if ( $args['class'] ) {
		$track .= ' ' . $args['class'];
	}

	// Built by hand rather than with wp_json_encode(), which renders floats at
	// serialize_precision and turns 2.2 into 2.2000000000000002 in the markup.
	$options = array();

	if ( $args['per_view'] ) {
		$pairs = array();

		foreach ( $args['per_view'] as $breakpoint => $count ) {
			$count   = rtrim( rtrim( number_format( (float) $count, 2, '.', '' ), '0' ), '.' );
			$pairs[] = sprintf( '"%d":%s', (int) $breakpoint, $count );
		}

		$options[] = '"perView":{' . implode( ',', $pairs ) . '}';
	}

	if ( in_array( $args['auto'], array( 'forward', 'reverse' ), true ) ) {
		$options[] = '"auto":"' . $args['auto'] . '"';
	}

	if ( 'fade' === $args['effect'] ) {
		$options[] = '"effect":"fade"';
	}

	if ( $args['autoplay'] > 0 ) {
		$options[] = '"autoplay":' . absint( $args['autoplay'] );
	}

	if ( $args['loop'] ) {
		$options[] = '"loop":true';
	}

	$options = $options ? '{' . implode( ',', $options ) . '}' : '';
	?>
	<div class="<?php echo esc_attr( $wrapper ); ?>">
		<div
			id="<?php echo esc_attr( $id ); ?>"
			class="<?php echo esc_attr( $track ); ?>"
			data-pg-carousel
			<?php if ( $args['group'] ) : ?>
				data-pg-carousel-group="<?php echo esc_attr( $args['group'] ); ?>"
			<?php endif; ?>
			<?php if ( $options ) : ?>
				data-pg-swiper="<?php echo esc_attr( $options ); ?>"
			<?php endif; ?>
			<?php if ( $args['label'] ) : ?>
				aria-label="<?php echo esc_attr( $args['label'] ); ?>"
			<?php endif; ?>
		>
			<div class="swiper-wrapper">
	<?php
}

/**
 * Render one carousel slide around a template part.
 *
 * @param string      $slug      Template slug.
 * @param string|null $name      Template name.
 * @param array       $part_args Arguments passed to the part.
 * @return void
 */
function packgens_carousel_slide( $slug, $name = null, $part_args = array() ) {
	echo '<div class="swiper-slide">';
	get_template_part( $slug, $name, $part_args );
	echo '</div>';
}

/**
 * Close a carousel opened with packgens_carousel_open().
 *
 * @param string $nav       Optional carousel or group id; renders arrows
 *                          positioned against the track.
 * @param string $nav_class Extra classes for those arrows.
 * @return void
 */
function packgens_carousel_close( $nav = '', $nav_class = '' ) {
	if ( packgens_carousel_is_marquee() ) {
		packgens_carousel_is_marquee( false );
		$slides = ob_get_clean();

		echo $slides; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Buffered template output.
		?>
					<div class="pg-marquee__clone" aria-hidden="true">
						<?php echo $slides; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Buffered template output. ?>
					</div>
					</div><!-- .pg-marquee__track -->
				</div><!-- .pg-marquee__shift -->
			</div><!-- .pg-marquee__viewport -->
			<?php
			if ( $nav ) {
				packgens_carousel_nav( $nav, $nav_class );
			}
			?>
		</div><!-- .pg-carousel -->
		<?php
		return;
	}
	?>
			</div><!-- .swiper-wrapper -->
		</div><!-- .pg-carousel__track -->
		<?php
		if ( $nav ) {
			packgens_carousel_nav( $nav, $nav_class );
		}
		?>
	</div><!-- .pg-carousel -->
	<?php
}

/**
 * Whether the carousel currently open is a marquee.
 *
 * Tracked with a flag rather than inspected, so close() knows which shape to
 * finish without the call sites having to repeat themselves.
 *
 * @param bool|null $set Set the flag, or omit to read it.
 * @return bool
 */
function packgens_carousel_is_marquee( $set = null ) {
	static $flag = false;

	if ( null !== $set ) {
		$flag = (bool) $set;
	}

	return $flag;
}

/**
 * Print previous / next controls bound to a carousel id.
 *
 * @param string $target Carousel DOM id, or the group id for linked rows.
 * @param string $class  Extra classes, e.g. pg-carousel__nav--sides.
 * @return void
 */
function packgens_carousel_nav( $target, $class = '' ) {
	?>
	<div class="pg-carousel__nav<?php echo $class ? ' ' . esc_attr( $class ) : ''; ?>">
		<button type="button" class="pg-icon-btn" data-pg-carousel-prev="<?php echo esc_attr( $target ); ?>">
			<?php packgens_icon( 'chevron-left' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Previous', 'packgens' ); ?></span>
		</button>
		<button type="button" class="pg-icon-btn" data-pg-carousel-next="<?php echo esc_attr( $target ); ?>">
			<?php packgens_icon( 'chevron-right' ); ?>
			<span class="pg-sr-only"><?php esc_html_e( 'Next', 'packgens' ); ?></span>
		</button>
	</div>
	<?php
}

/**
 * Build the section wrapper attributes.
 *
 * @param string $name    Section slug.
 * @param string $surface Surface modifier: none|cream|gray|gray-cool|navy|blue|green.
 * @param string $extra   Extra class names.
 * @return string
 */
function packgens_section_class( $name, $surface = '', $extra = '' ) {
	$classes = array( 'pg-section', 'pg-section--' . sanitize_html_class( $name ) );

	if ( $surface ) {
		$classes[] = 'pg-section--' . sanitize_html_class( $surface );
	}

	if ( $extra ) {
		$classes[] = $extra;
	}

	return implode( ' ', $classes );
}

/**
 * Print a yes / no marker inside the comparison table.
 *
 * @param bool $yes Whether the feature is offered.
 * @return void
 */
function packgens_comparison_mark( $yes ) {
	printf(
		'<span class="pg-mark %1$s">%2$s<span class="pg-sr-only">%3$s</span></span>',
		$yes ? 'pg-mark--yes' : 'pg-mark--no',
		packgens_get_icon( $yes ? 'check-circle' : 'x-circle' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $yes ? __( 'Included', 'packgens' ) : __( 'Not included', 'packgens' ) )
	);
}


/* -------------------------------------------------------------------------
 * Shared sections: categories and products inherit, overriding only changes
 *
 * Every product category shows the sections from Packgens -> Category Defaults,
 * and every product the sections from Packgens -> Product Defaults. A category
 * or product keeps only the sub-fields an editor actually changed; everything
 * else keeps following the defaults, later edits there included.
 *
 * Two things used to break that. SCF writes a row for every field on the
 * screen when an object is saved, blank or untouched alike, and a stored row
 * counts as "this object's own value" -- so one save froze it on a copy (or on
 * blanks). And an unsaved field showed the editor an empty box or the field's
 * default_value rather than the shared content.
 *
 * So: the edit screen loads the shared value wherever the object has no row of
 * its own, and saving drops every top-level sub-field that is blank or still
 * equal to the shared value.
 * ---------------------------------------------------------------------- */

/**
 * The term id behind an SCF "term_{id}" context, when it is a product category.
 *
 * @param mixed $post_id SCF object id.
 * @return int Term id, or 0.
 */
function packgens_category_context_term( $post_id ) {
	if ( ! is_string( $post_id ) || 0 !== strpos( $post_id, 'term_' ) ) {
		return 0;
	}

	$term_id = (int) substr( $post_id, 5 );
	$term    = $term_id ? get_term( $term_id ) : null;

	return ( $term instanceof WP_Term && 'product_cat' === $term->taxonomy ) ? $term_id : 0;
}

/**
 * The section group fields an object type edits, keyed by name.
 *
 * @param string $kind "product_cat" or "product".
 * @return array<string,array> Group field arrays, with their sub_fields.
 */
function packgens_inherited_section_groups( $kind ) {
	static $groups = array();

	if ( isset( $groups[ $kind ] ) ) {
		return $groups[ $kind ];
	}

	$groups[ $kind ] = array();

	if ( ! function_exists( 'acf_get_field_groups' ) ) {
		return $groups[ $kind ];
	}

	$screen = 'product_cat' === $kind ? array( 'taxonomy' => 'product_cat' ) : array( 'post_type' => 'product' );

	foreach ( acf_get_field_groups( $screen ) as $field_group ) {
		foreach ( (array) acf_get_fields( $field_group ) as $field ) {
			if ( 'group' === ( $field['type'] ?? '' ) && '_section' === substr( $field['name'], -8 ) ) {
				$groups[ $kind ][ $field['name'] ] = $field;
			}
		}
	}

	return $groups[ $kind ];
}

/**
 * Where an SCF object inherits its sections from, if anywhere.
 *
 * @param mixed $post_id SCF object id.
 * @return array{meta:string,id:int,defaults:string,kind:string}|null
 */
function packgens_inherit_context( $post_id ) {
	static $cache = array();

	$key = (string) $post_id;

	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}

	$context = null;
	$term_id = packgens_category_context_term( $post_id );

	if ( $term_id && defined( 'PACKGENS_CAT_DEFAULTS_ID' ) ) {
		$context = array(
			'meta'     => 'term',
			'id'       => $term_id,
			'defaults' => PACKGENS_CAT_DEFAULTS_ID,
			'kind'     => 'product_cat',
		);
	} elseif ( is_numeric( $post_id ) && (int) $post_id > 0 && defined( 'PACKGENS_PRODUCT_DEFAULTS_ID' ) && 'product' === get_post_type( (int) $post_id ) ) {
		$context = array(
			'meta'     => 'post',
			'id'       => (int) $post_id,
			'defaults' => PACKGENS_PRODUCT_DEFAULTS_ID,
			'kind'     => 'product',
		);
	}

	$cache[ $key ] = $context;

	return $context;
}

/**
 * Section fields a category or product always sets for itself.
 *
 * These never inherit from the defaults screen and are never pruned on save:
 * unsaved, they read as off. The SEO content block starts hidden on every
 * category and shows only where an editor switches it on for that category.
 *
 * @param string $kind "product_cat" or "product".
 * @return string[] Full meta names.
 */
function packgens_own_only_section_fields( $kind ) {
	$fields = array(
		'product_cat' => array( 'content_section_enable' ),
		'product'     => array(),
	);

	return $fields[ $kind ] ?? array();
}

/**
 * Whether a stored field name belongs to one of the given section groups.
 *
 * @param string $name Full meta name, e.g. "cta_section_title".
 * @param string $kind "product_cat" or "product".
 * @return bool
 */
function packgens_is_inherited_section_name( $name, $kind ) {
	foreach ( array_keys( packgens_inherited_section_groups( $kind ) ) as $group ) {
		if ( 0 === strpos( (string) $name, $group . '_' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Load the shared value wherever a category or product has no row of its own.
 *
 * Runs per leaf field (SCF rebuilds groups from their sub-fields, and repeater
 * rows from theirs), so an object that changed only a headline still loads
 * every other field, and every repeater row, from its defaults screen. This is
 * what the edit screen shows and what the front end reads.
 *
 * @param mixed      $value   Value SCF loaded (or the field's default_value).
 * @param int|string $post_id SCF object id.
 * @param array      $field   Field, with its full meta name.
 * @return mixed
 */
function packgens_load_shared_section_value( $value, $post_id, $field ) {
	if ( 'group' === ( $field['type'] ?? '' ) ) {
		return $value;
	}

	$context = packgens_inherit_context( $post_id );

	if ( ! $context || ! packgens_is_inherited_section_name( $field['name'] ?? '', $context['kind'] ) ) {
		return $value;
	}

	if ( metadata_exists( $context['meta'], $context['id'], $field['name'] ) ) {
		return $value;
	}

	if ( in_array( $field['name'], packgens_own_only_section_fields( $context['kind'] ), true ) ) {
		return 0;
	}

	return acf_get_value( $context['defaults'], $field );
}
add_filter( 'acf/load_value', 'packgens_load_shared_section_value', 5, 3 );

/**
 * A value reduced to strings, so a stored "1" equals a default 1 and an
 * unsaved false equals an empty string.
 *
 * @param mixed $value Field value.
 * @return mixed
 */
function packgens_normalise_field_value( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'packgens_normalise_field_value', $value );
	}

	return ( null === $value || false === $value ) ? '' : (string) $value;
}

/**
 * Whether a normalised value is blank: empty text, no choice, no rows.
 *
 * @param mixed $value Normalised value.
 * @return bool
 */
function packgens_is_blank_field_value( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $item ) {
			if ( ! packgens_is_blank_field_value( $item ) ) {
				return false;
			}
		}

		return true;
	}

	return '' === $value;
}

/**
 * Drop an object's section sub-fields that are blank or match the defaults.
 *
 * Compares each top-level sub-field of each section group, raw (unformatted)
 * on both sides. Whatever is dropped is read from the defaults screen again.
 * A true/false switched off is "0", not blank, so turning a section off on one
 * category or product survives.
 *
 * @param int|string $post_id SCF object id ("term_12" or a product id).
 * @return int Number of sub-fields dropped.
 */
function packgens_prune_inherited_sections( $post_id ) {
	global $wpdb;

	$context = packgens_inherit_context( $post_id );

	if ( ! $context || ! function_exists( 'acf_get_value' ) ) {
		return 0;
	}

	$is_term = 'term' === $context['meta'];
	$table   = $is_term ? $wpdb->termmeta : $wpdb->postmeta;
	$column  = $is_term ? 'term_id' : 'post_id';
	$dropped = 0;

	foreach ( packgens_inherited_section_groups( $context['kind'] ) as $group => $field ) {
		foreach ( (array) ( $field['sub_fields'] ?? array() ) as $sub ) {
			if ( empty( $sub['name'] ) ) {
				continue; // Tabs and messages store nothing.
			}

			$sub['name'] = $group . '_' . $sub['name'];

			if ( in_array( $sub['name'], packgens_own_only_section_fields( $context['kind'] ), true ) ) {
				continue;
			}

			if ( ! metadata_exists( $context['meta'], $context['id'], $sub['name'] ) ) {
				continue;
			}

			$own    = packgens_normalise_field_value( acf_get_value( $post_id, $sub ) );
			$shared = packgens_normalise_field_value( acf_get_value( $context['defaults'], $sub ) );

			if ( ! packgens_is_blank_field_value( $own ) && $own !== $shared ) {
				continue;
			}

			// The value row, its "_name" key reference and, for repeaters and
			// nested groups, every row stored beneath it.
			$name    = preg_quote( $sub['name'], '/' );
			$pattern = in_array( $sub['type'], array( 'repeater', 'flexible_content' ), true )
				? '^_?' . $name . '(_[0-9]+_.+)?$'
				: ( 'group' === $sub['type'] ? '^_?' . $name . '(_.+)?$' : '^_?' . $name . '$' );

			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare( "DELETE FROM {$table} WHERE {$column} = %d AND meta_key REGEXP %s", $context['id'], $pattern ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			);
			++$dropped;
		}

		// The group's own (empty) marker row is harmless; it tells SCF the
		// group exists and holds no value of its own.
	}

	if ( $dropped ) {
		wp_cache_delete( $context['id'], $is_term ? 'term_meta' : 'post_meta' );

		if ( function_exists( 'acf_flush_value_cache' ) ) {
			acf_flush_value_cache( $post_id );
		}
	}

	return $dropped;
}

/**
 * Category wrapper kept for callers that pass a term id.
 *
 * @param int $term_id Product category id.
 * @return int Number of sub-fields dropped.
 */
function packgens_prune_category_sections( $term_id ) {
	return packgens_prune_inherited_sections( 'term_' . (int) $term_id );
}

/**
 * After a category or product is saved, keep only what the editor changed.
 *
 * @param int|string $post_id SCF object id.
 * @return void
 */
function packgens_prune_inherited_on_save( $post_id ) {
	packgens_prune_inherited_sections( $post_id );
}
add_action( 'acf/save_post', 'packgens_prune_inherited_on_save', 20 );

/**
 * Once per site: prune every category saved under the old behaviour, so
 * categories carrying full copies of the defaults start inheriting again.
 *
 * Bump the version to run it again after changing what counts as inherited.
 *
 * @return void
 */
function packgens_prune_all_categories_once() {
	$version = '1';

	if ( get_option( 'packgens_category_prune' ) === $version || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return;
	}

	foreach ( $terms as $term_id ) {
		packgens_prune_category_sections( (int) $term_id );
	}

	update_option( 'packgens_category_prune', $version, false );
}
add_action( 'admin_init', 'packgens_prune_all_categories_once' );

/**
 * Once per site: give Product Defaults the sections products already carry,
 * then prune every product's identical copies so they start inheriting.
 *
 * A section the defaults screen has no content for is filled from the first
 * product (oldest first) that has content in it. Products whose copy differs
 * keep it as their own; nothing that differs is deleted.
 *
 * @return void
 */
function packgens_prune_all_products_once() {
	$version = '1';

	if ( get_option( 'packgens_product_prune' ) === $version || ! current_user_can( 'manage_options' ) || ! defined( 'PACKGENS_PRODUCT_DEFAULTS_ID' ) ) {
		return;
	}

	$ids = get_posts(
		array(
			'post_type'   => 'product',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);

	foreach ( packgens_inherited_section_groups( 'product' ) as $group => $field ) {
		if ( ! packgens_is_blank_field_value( packgens_normalise_field_value( acf_get_value( PACKGENS_PRODUCT_DEFAULTS_ID, $field ) ) ) ) {
			continue;
		}

		foreach ( $ids as $product_id ) {
			$raw = acf_get_value( $product_id, $field );

			if ( ! packgens_is_blank_field_value( packgens_normalise_field_value( $raw ) ) ) {
				acf_update_value( $raw, PACKGENS_PRODUCT_DEFAULTS_ID, $field );
				acf_flush_value_cache( PACKGENS_PRODUCT_DEFAULTS_ID );
				break;
			}
		}
	}

	foreach ( $ids as $product_id ) {
		packgens_prune_inherited_sections( (int) $product_id );
	}

	update_option( 'packgens_product_prune', $version, false );
}
add_action( 'admin_init', 'packgens_prune_all_products_once' );

/* -------------------------------------------------------------------------
 * Add New Product: per-product values only
 *
 * A new product only needs what differs per product: WooCommerce's own fields
 * plus the badge, subtitle, quantity tiers and specifications. Everything else
 * comes from Product Defaults, so the shared Product Details tabs, the optional
 * extras and the section boxes are left off this screen and appear on the edit
 * screen once the product exists. Fields carry "pg_edit_only" / "pg_add_only"
 * flags in inc/fields.php.
 * ---------------------------------------------------------------------- */

/**
 * Whether the current admin screen is Products -> Add New.
 *
 * @return bool
 */
function packgens_is_new_product_screen() {
	global $pagenow, $typenow;

	return is_admin() && 'post-new.php' === $pagenow && 'product' === $typenow;
}

/**
 * Drop the section boxes from Add New Product.
 *
 * @param array $field_groups Field groups.
 * @return array
 */
function packgens_new_product_field_groups( $field_groups ) {
	// The one-time passes on admin_init need the full list to see sections.
	if ( ! packgens_is_new_product_screen() || doing_action( 'admin_init' ) ) {
		return $field_groups;
	}

	return array_values(
		array_filter(
			$field_groups,
			static function ( $field_group ) {
				foreach ( (array) acf_get_fields( $field_group ) as $field ) {
					if ( 'group' === ( $field['type'] ?? '' ) && '_section' === substr( $field['name'], -8 ) ) {
						return false;
					}
				}

				return true;
			}
		)
	);
}
add_filter( 'acf/load_field_groups', 'packgens_new_product_field_groups', 99 );

/**
 * Hide edit-only fields when adding a product, and add-only notes otherwise.
 *
 * @param array|false $field Field being rendered.
 * @return array|false
 */
function packgens_new_product_field_visibility( $field ) {
	if ( ! is_array( $field ) ) {
		return $field;
	}

	$is_new = packgens_is_new_product_screen();

	if ( ( $is_new && ! empty( $field['pg_edit_only'] ) ) || ( ! $is_new && ! empty( $field['pg_add_only'] ) ) ) {
		return false;
	}

	return $field;
}
add_filter( 'acf/prepare_field', 'packgens_new_product_field_visibility', 20 );

/**
 * Keep the "Add new category" form to WordPress's own fields.
 *
 * SCF draws every section box onto the add form too, which buried the Add
 * button under a long scroll for content most new categories never set: they
 * inherit everything from Category Defaults anyway. The boxes stay on the edit
 * screen, where a category changes what it needs after it exists.
 *
 * SCF keeps no handle on the object that hooks the add form, so instead the
 * field group list is emptied just while that form is printed (priorities 9
 * and 11 bracket SCF's own at 10). WooCommerce's thumbnail and display fields
 * on the same hook are untouched.
 *
 * @return void
 */
function packgens_category_add_form_start() {
	add_filter( 'acf/load_field_groups', '__return_empty_array', 99 );
}
add_action( 'product_cat_add_form_fields', 'packgens_category_add_form_start', 9 );

/**
 * Restore the field group list after the add form, with a pointer to where
 * the shared section content lives.
 *
 * @return void
 */
function packgens_category_add_form_end() {
	remove_filter( 'acf/load_field_groups', '__return_empty_array', 99 );

	if ( ! defined( 'PACKGENS_CAT_DEFAULTS_PAGE' ) ) {
		return;
	}

	printf(
		'<div class="form-field"><p class="description">%s</p></div>',
		wp_kses(
			sprintf(
				/* translators: %s: link to the Category Defaults screen. */
				__( 'The new category shows the shared sections from %s straight away. To change any of them for this category only, edit it after adding.', 'packgens' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=' . PACKGENS_CAT_DEFAULTS_PAGE ) ) . '">' . esc_html__( 'Packgens → Category Defaults', 'packgens' ) . '</a>'
			),
			array( 'a' => array( 'href' => array() ) )
		)
	);
}
add_action( 'product_cat_add_form_fields', 'packgens_category_add_form_end', 11 );
