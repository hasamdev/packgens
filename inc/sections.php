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
	$context = ( 'product_cat' === $view && defined( 'PACKGENS_CAT_DEFAULTS_ID' ) )
		? PACKGENS_CAT_DEFAULTS_ID
		: false;

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
	if ( ! is_string( $context ) || 0 !== strpos( $context, 'term_' ) ) {
		return $keys;
	}

	$term_id = (int) substr( $context, 5 );
	$stored  = array();

	foreach ( $keys as $key ) {
		if ( metadata_exists( 'term', $term_id, $group . '_' . $key ) ) {
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

	return $data;
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
	if ( null === $context ) {
		$context = packgens_field_context();
	}

	$default  = packgens_default_section_order( $view );
	$saved    = packgens_field( 'section_order', $context, null );
	$fallback = packgens_section_defaults_context( $view );

	if ( ( ! is_array( $saved ) || empty( $saved ) ) && $fallback ) {
		$saved = packgens_field( 'section_order', $fallback, null );
	}

	if ( ! is_array( $saved ) || empty( $saved ) ) {
		return $default;
	}

	$known = array_keys( packgens_section_choices() );

	return array_values( array_intersect( $saved, $known ) );
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
