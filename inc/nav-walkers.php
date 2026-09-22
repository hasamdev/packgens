<?php
/**
 * Navigation walkers.
 *
 * Two walkers cover every menu in the theme:
 *  - Packgens_Nav_Walker         desktop bar, with an optional mega panel
 *  - Packgens_Mobile_Nav_Walker  drawer, rendered as a nested accordion
 *
 * A top level item opts into the mega panel by adding the CSS class "mega" in
 * Appearance > Menus. Add "cards" alongside it to render children as image
 * cards, and "columns-2" ... "columns-5" to control the grid.
 *
 * @package Packgens
 */

defined( 'ABSPATH' ) || exit;

/**
 * Desktop navigation walker.
 */
class Packgens_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Whether the branch currently being written is a mega panel.
	 *
	 * @var bool
	 */
	protected $in_mega = false;

	/**
	 * Whether the mega panel renders children as image cards.
	 *
	 * @var bool
	 */
	protected $mega_cards = false;

	/**
	 * Column count for the current mega panel.
	 *
	 * @var int
	 */
	protected $mega_columns = 4;

	/**
	 * The Explore All link for the current mega panel, if one was declared.
	 *
	 * @var array{title:string,url:string}|null
	 */
	protected $mega_explore = null;

	/**
	 * Whether the item being written is consumed by the panel chrome.
	 *
	 * @var bool
	 */
	protected $skip_el = false;

	/**
	 * Close a menu item.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// The Explore All item was folded into the panel footer; it has no <li>.
		if ( $this->skip_el ) {
			$this->skip_el = false;

			return;
		}

		$output .= "</li>\n";
	}

	/**
	 * Open a sub level.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );

		if ( 0 === $depth && $this->in_mega ) {
			$classes = 'pg-mega__grid';

			if ( $this->mega_cards ) {
				$classes .= ' pg-mega__grid--cards';
			}

			$output .= "\n{$indent}<div class=\"pg-mega\"><div class=\"pg-mega__inner\"><ul class=\"" . esc_attr( $classes ) . '" style="--pg-mega-cols:' . absint( $this->mega_columns ) . '">' . "\n";

			return;
		}

		$class = 0 === $depth ? 'pg-nav__submenu' : 'pg-mega__sublist';
		$output .= "\n{$indent}<ul class=\"" . esc_attr( $class ) . "\">\n";
	}

	/**
	 * Close a sub level.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent = str_repeat( "\t", $depth );

		if ( 0 === $depth && $this->in_mega ) {
			$output .= "{$indent}</ul>\n";

			if ( $this->mega_explore ) {
				$output .= sprintf(
					'<div class="pg-mega__foot"><a class="pg-btn pg-btn--primary" href="%1$s">%2$s%3$s</a></div>',
					esc_url( $this->mega_explore['url'] ),
					esc_html( $this->mega_explore['title'] ),
					packgens_get_icon( 'arrow-right', 'pg-btn__icon' )
				);
			}

			$output .= "{$indent}</div></div>\n";

			return;
		}

		$output .= "{$indent}</ul>\n";
	}

	/**
	 * Open a menu item.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Menu item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$item_classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $item_classes, true );

		if ( 0 === $depth ) {
			$this->in_mega      = $has_children && in_array( 'mega', $item_classes, true );
			$this->mega_cards   = in_array( 'cards', $item_classes, true );
			$this->mega_columns = 4;
			$this->mega_explore = null;

			foreach ( $item_classes as $class ) {
				if ( preg_match( '/^columns-([2-6])$/', $class, $m ) ) {
					$this->mega_columns = (int) $m[1];
				}
			}
		}

		if ( 1 === $depth && $this->in_mega && in_array( 'mega-explore', $item_classes, true ) ) {
			$this->mega_explore = array(
				'title' => wp_strip_all_tags( apply_filters( 'the_title', $item->title, $item->ID ) ),
				'url'   => ! empty( $item->url ) ? $item->url : '',
			);
			$this->skip_el      = true;

			return;
		}

		$classes   = array_filter( $item_classes );
		$classes[] = 'menu-item-' . $item->ID;
		$classes[] = 0 === $depth ? 'pg-nav__item' : 'pg-mega__item';

		if ( $has_children ) {
			$classes[] = 0 === $depth ? 'pg-nav__item--has-children' : 'pg-mega__item--has-children';
		}

		if ( 0 === $depth && $this->in_mega ) {
			$classes[] = 'pg-nav__item--mega';
		}

		if ( 1 === $depth && $this->in_mega && $this->mega_cards && ! $has_children ) {
			$classes[] = 'pg-mega__item--card';
		}

		$class_attr = ' class="' . esc_attr( implode( ' ', array_unique( $classes ) ) ) . '"';
		$output    .= '<li id="menu-item-' . absint( $item->ID ) . '"' . $class_attr . '>';

		$atts = array(
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
			'href'   => ! empty( $item->url ) ? $item->url : '',
		);

		if ( '_blank' === $atts['target'] && empty( $atts['rel'] ) ) {
			$atts['rel'] = 'noopener';
		}

		$atts['class'] = 0 === $depth ? 'pg-nav__link' : 'pg-mega__link';

		if ( in_array( 'current-menu-item', $item_classes, true ) || in_array( 'current-menu-ancestor', $item_classes, true ) ) {
			$atts['class']       .= ' is-current';
			$atts['aria-current'] = 'page';
		}

		$attributes = '';

		foreach ( $atts as $attr => $value ) {
			if ( '' === $value || false === $value ) {
				continue;
			}

			$value       = 'href' === $attr ? esc_url( $value ) : esc_attr( $value );
			$attributes .= ' ' . $attr . '="' . $value . '"';
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );

		$inner = '';

		// Mega cards lead with the menu item image.
		if ( 1 === $depth && $this->in_mega && $this->mega_cards && ! $has_children ) {
			$image_id = (int) get_post_meta( $item->ID, '_packgens_menu_image', true );

			if ( $image_id ) {
				$inner .= '<span class="pg-mega__card-media">' . wp_get_attachment_image(
					$image_id,
					'packgens-thumb',
					false,
					array( 'alt' => esc_attr( wp_strip_all_tags( $title ) ), 'loading' => 'lazy', 'decoding' => 'async' )
				) . '</span>';
			}
		}

		$inner .= '<span class="pg-nav__label">' . esc_html( wp_strip_all_tags( $title ) ) . '</span>';

		if ( ! empty( $item->description ) && $depth > 0 ) {
			$inner .= '<span class="pg-mega__desc">' . esc_html( $item->description ) . '</span>';
		}

		if ( 0 === $depth && $has_children ) {
			$inner .= packgens_get_icon( 'chevron-down', 'pg-nav__caret' );
		}

		$output .= '<a' . $attributes . '>' . $inner . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.

		// A separate toggle keeps the parent link navigable on touch devices.
		if ( 0 === $depth && $has_children ) {
			$output .= sprintf(
				'<button type="button" class="pg-nav__toggle" aria-expanded="false" aria-label="%s">%s</button>',
				esc_attr( sprintf( /* translators: %s: menu item name. */ __( 'Show submenu for %s', 'packgens' ), wp_strip_all_tags( $title ) ) ),
				packgens_get_icon( 'chevron-down' )
			);
		}
	}
}

/**
 * Mobile drawer walker: nested lists with accordion toggles.
 */
class Packgens_Mobile_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Open a sub level.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<div class="pg-mnav__panel"><div><ul class="pg-mnav__sublist">';
	}

	/**
	 * Close a sub level.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul></div></div>';
	}

	/**
	 * Open a menu item.
	 *
	 * @param string   $output Menu HTML, by reference.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth of the current item.
	 * @param stdClass $args   Menu arguments.
	 * @param int      $id     Menu item ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$item_classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $item_classes, true );
		$is_current   = in_array( 'current-menu-item', $item_classes, true );

		$classes = array( 'pg-mnav__item', 'pg-mnav__item--depth-' . $depth );

		if ( $has_children ) {
			$classes[] = 'pg-mnav__item--has-children';
		}

		$title = apply_filters( 'the_title', $item->title, $item->ID );

		$output .= '<li class="' . esc_attr( implode( ' ', $classes ) ) . '">';
		$output .= '<div class="pg-mnav__row">';
		$output .= sprintf(
			'<a class="pg-mnav__link%1$s" href="%2$s"%3$s>%4$s</a>',
			$is_current ? ' is-current' : '',
			esc_url( $item->url ),
			$is_current ? ' aria-current="page"' : '',
			esc_html( wp_strip_all_tags( $title ) )
		);

		if ( $has_children ) {
			$output .= sprintf(
				'<button type="button" class="pg-mnav__toggle" aria-expanded="false" aria-label="%s">%s</button>',
				esc_attr( sprintf( /* translators: %s: menu item name. */ __( 'Show submenu for %s', 'packgens' ), wp_strip_all_tags( $title ) ) ),
				packgens_get_icon( 'chevron-down' )
			);
		}

		$output .= '</div>';
	}
}

/**
 * Render the primary menu, or a helpful prompt when no menu is assigned.
 *
 * @return void
 */
function packgens_primary_menu() {
	if ( ! has_nav_menu( 'primary' ) ) {
		if ( current_user_can( 'edit_theme_options' ) ) {
			printf(
				'<p class="pg-nav__empty"><a href="%s">%s</a></p>',
				esc_url( admin_url( 'nav-menus.php' ) ),
				esc_html__( 'Assign a menu to the Primary location', 'packgens' )
			);
		}

		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'pg-nav__list',
			'depth'          => 3,
			'walker'         => new Packgens_Nav_Walker(),
			'fallback_cb'    => false,
		)
	);
}

/**
 * Render the primary menu inside the mobile drawer.
 *
 * @return void
 */
function packgens_mobile_menu() {
	if ( ! has_nav_menu( 'primary' ) ) {
		return;
	}

	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'pg-mnav__list',
			'depth'          => 3,
			'walker'         => new Packgens_Mobile_Nav_Walker(),
			'fallback_cb'    => false,
		)
	);
}
