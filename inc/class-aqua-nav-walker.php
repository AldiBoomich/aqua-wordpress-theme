<?php
/**
 * Navigation walkers (BEM markup) and fallback menus.
 *
 * Header: <ul class="main-nav"> with Bootstrap dropdowns for sub menus.
 * Footer: <ul class="site-footer__links"> — flat list of links.
 *
 * @package aqua
 */

/**
 * Header menu walker.
 */
class Aqua_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Starts a sub menu.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="main-nav__submenu dropdown-menu">';
	}

	/**
	 * Ends a sub menu.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}

	/**
	 * Starts a menu item.
	 *
	 * @param string   $output            Output.
	 * @param WP_Post  $data_object       Menu item.
	 * @param int      $depth             Depth.
	 * @param stdClass $args              Arguments.
	 * @param int      $current_object_id Current object ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item         = $data_object;
		$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
		$has_children = in_array( 'menu-item-has-children', $classes, true );
		$is_active    = (bool) array_intersect( array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'current_page_item', 'current_page_parent' ), $classes );

		$output .= aqua_nav_item_html(
			array(
				'title'    => apply_filters( 'the_title', $item->title, $item->ID ),
				'url'      => ! empty( $item->url ) ? $item->url : '#',
				'active'   => $is_active,
				'depth'    => $depth,
				'dropdown' => 0 === $depth && $has_children,
				'target'   => $item->target,
				'rel'      => $item->xfn,
				'attr'     => $item->attr_title,
			)
		);
	}

	/**
	 * Ends a menu item.
	 *
	 * @param string   $output      Output.
	 * @param WP_Post  $data_object Menu item.
	 * @param int      $depth       Depth.
	 * @param stdClass $args        Arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * Opening <li> + link of a header menu item (shared by the walker and the fallback menu).
 *
 * @param array $item title, url, active, depth, dropdown, target, rel, attr.
 * @return string
 */
function aqua_nav_item_html( $item ) {
	$item = wp_parse_args(
		$item,
		array(
			'title'    => '',
			'url'      => '#',
			'active'   => false,
			'depth'    => 0,
			'dropdown' => false,
			'target'   => '',
			'rel'      => '',
			'attr'     => '',
		)
	);

	$extra = '';
	if ( $item['target'] ) {
		$extra .= ' target="' . esc_attr( $item['target'] ) . '"';
	}
	if ( $item['rel'] ) {
		$extra .= ' rel="' . esc_attr( $item['rel'] ) . '"';
	}
	if ( $item['attr'] ) {
		$extra .= ' title="' . esc_attr( $item['attr'] ) . '"';
	}

	if ( $item['depth'] > 0 ) {
		$link_class = 'main-nav__sublink' . ( $item['active'] ? ' main-nav__sublink--active' : '' );
		if ( $item['active'] ) {
			$extra .= ' aria-current="page"';
		}
		return '<li class="main-nav__subitem"><a class="' . $link_class . '" href="' . esc_url( $item['url'] ) . '"' . $extra . '>' . esc_html( $item['title'] ) . '</a>';
	}

	$li_class   = 'main-nav__item';
	$link_class = 'main-nav__link';
	if ( $item['dropdown'] ) {
		$li_class .= ' main-nav__item--dropdown dropdown';
	}
	if ( $item['active'] ) {
		$li_class   .= ' main-nav__item--active';
		$link_class .= ' main-nav__link--active';
	}

	if ( $item['dropdown'] ) {
		return '<li class="' . $li_class . '"><a class="' . $link_class . '" href="' . esc_url( $item['url'] ) . '" data-bs-toggle="dropdown" role="button" aria-expanded="false"' . $extra . '>' . esc_html( $item['title'] ) . '<i class="main-nav__caret fas fa-chevron-down" aria-hidden="true"></i></a>';
	}

	if ( $item['active'] ) {
		$extra .= ' aria-current="page"';
	}
	return '<li class="' . $li_class . '"><a class="' . $link_class . '" href="' . esc_url( $item['url'] ) . '"' . $extra . '>' . esc_html( $item['title'] ) . '</a>';
}

/**
 * Whether the current request is a blog screen (posts page, single post, archives).
 *
 * @return bool
 */
function aqua_is_blog_context() {
	return is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_date() || is_author();
}

/**
 * WordPress marks the posts page menu item with "current_page_parent" on ANY non-page
 * screen (404, search…). Keep it only on blog screens, and also mark that item's
 * parent so the dropdown is highlighted while reading a post.
 *
 * @param array $items Menu items.
 * @return array
 */
function aqua_nav_menu_parent_active( $items ) {
	$is_blog = aqua_is_blog_context();
	$parents = array();

	foreach ( $items as $item ) {
		if ( ! in_array( 'current_page_parent', (array) $item->classes, true ) ) {
			continue;
		}
		if ( ! $is_blog ) {
			$item->classes = array_diff( (array) $item->classes, array( 'current_page_parent' ) );
			continue;
		}
		if ( $item->menu_item_parent ) {
			$parents[] = (int) $item->menu_item_parent;
		}
	}

	if ( $parents ) {
		foreach ( $items as $item ) {
			if ( in_array( (int) $item->ID, $parents, true ) ) {
				$item->classes[] = 'current-menu-ancestor';
			}
		}
	}

	return $items;
}
add_filter( 'wp_nav_menu_objects', 'aqua_nav_menu_parent_active' );

/**
 * Fallback header menu (same links as the original design) — used until
 * a menu is assigned to the "Header Menu" location.
 *
 * @param array $args wp_nav_menu() arguments.
 * @return string|void
 */
function aqua_menu_fallback( $args = array() ) {
	$is_blog = aqua_is_blog_context();

	$items = array(
		array( 'Home', home_url( '/' ), is_front_page(), array() ),
		array( 'About', aqua_page_url( 'about' ), is_page( 'about' ), array() ),
		array( 'Service', aqua_page_url( 'service' ), is_page( 'service' ), array() ),
		array(
			'Pages',
			'#',
			is_page( 'products' ) || $is_blog,
			array(
				array( 'Products', aqua_page_url( 'products' ), is_page( 'products' ) ),
				array( 'Blog Posts', aqua_blog_url(), $is_blog ),
			),
		),
		array( 'Contact', aqua_page_url( 'contact' ), is_page( 'contact' ), array() ),
	);

	$html = '<ul class="main-nav">';
	foreach ( $items as $item ) {
		$html .= aqua_nav_item_html(
			array(
				'title'    => $item[0],
				'url'      => $item[1],
				'active'   => $item[2],
				'dropdown' => ! empty( $item[3] ),
			)
		);
		if ( ! empty( $item[3] ) ) {
			$html .= '<ul class="main-nav__submenu dropdown-menu">';
			foreach ( $item[3] as $child ) {
				$html .= aqua_nav_item_html(
					array(
						'title'  => $child[0],
						'url'    => $child[1],
						'active' => $child[2],
						'depth'  => 1,
					)
				) . '</li>';
			}
			$html .= '</ul>';
		}
		$html .= '</li>';
	}
	$html .= '</ul>';

	if ( isset( $args['echo'] ) && ! $args['echo'] ) {
		return $html;
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_nav_item_html().
}

/**
 * Footer menu walker: flat list of links with an arrow icon.
 */
class Aqua_Footer_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Sub menus are not displayed in the footer.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Sub menus are not displayed in the footer.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Starts a menu item.
	 *
	 * @param string   $output            Output.
	 * @param WP_Post  $data_object       Menu item.
	 * @param int      $depth             Depth.
	 * @param stdClass $args              Arguments.
	 * @param int      $current_object_id Current object ID.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$target  = ! empty( $data_object->target ) ? $data_object->target : '';
		$output .= aqua_footer_link_html( apply_filters( 'the_title', $data_object->title, $data_object->ID ), ! empty( $data_object->url ) ? $data_object->url : '#', $target );
	}

	/**
	 * Ends a menu item.
	 *
	 * @param string   $output      Output.
	 * @param WP_Post  $data_object Menu item.
	 * @param int      $depth       Depth.
	 * @param stdClass $args        Arguments.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * Opening <li> + link of a footer menu item.
 *
 * @param string $title  Link text.
 * @param string $url    URL.
 * @param string $target Link target.
 * @return string
 */
function aqua_footer_link_html( $title, $url, $target = '' ) {
	$extra = $target ? ' target="' . esc_attr( $target ) . '"' : '';
	return '<li><a class="site-footer__link" href="' . esc_url( $url ) . '"' . $extra . '><i class="site-footer__link-icon fas fa-angle-right" aria-hidden="true"></i>' . esc_html( $title ) . '</a>';
}

/**
 * Fallback footer menu (links from the original design).
 *
 * @param array $args wp_nav_menu() arguments.
 * @return string|void
 */
function aqua_footer_menu_fallback( $args = array() ) {
	$items = array(
		array( 'Why Choose Us', aqua_page_url( 'about' ) ),
		array( 'Free Water Bottles', aqua_page_url( 'products' ) ),
		array( 'Water Dispensers', aqua_page_url( 'products' ) ),
		array( 'Bottled Water Coolers', aqua_page_url( 'products' ) ),
		array( 'Contact us', aqua_page_url( 'contact' ) ),
		array( 'Terms & Conditions', '#' ),
	);

	$html = '<ul class="site-footer__links">';
	foreach ( $items as $item ) {
		$html .= aqua_footer_link_html( $item[0], $item[1] ) . '</li>';
	}
	$html .= '</ul>';

	if ( isset( $args['echo'] ) && ! $args['echo'] ) {
		return $html;
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_footer_link_html().
}
