<?php
/**
 * Navigation walkers (BEM markup) and fallback menus.
 *
 * Header menu, two renderings of the same WordPress menu:
 *  - 'main-nav'    desktop bar; a parent item opens its sub menu with a real <button> (aria-expanded);
 *  - 'overlay-nav' full-screen mobile menu; sub items are always listed under their parent.
 * Footer menu: flat list of links.
 *
 * @package aqua
 */

defined( 'ABSPATH' ) || exit;

/**
 * Header menu walker.
 */
class Aqua_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * BEM block: 'main-nav' or 'overlay-nav'.
	 *
	 * @var string
	 */
	private $block;

	/**
	 * ID of the parent item whose sub menu is being printed.
	 *
	 * @var int
	 */
	private $parent_id = 0;

	/**
	 * Constructor.
	 *
	 * @param string $block BEM block.
	 */
	public function __construct( $block = 'main-nav' ) {
		$this->block = 'overlay-nav' === $block ? 'overlay-nav' : 'main-nav';
	}

	/**
	 * Starts a sub menu.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Arguments.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= aqua_nav_sublist_open( $this->block, $this->parent_id );
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

		if ( 0 === $depth && $has_children ) {
			$this->parent_id = (int) $item->ID;
		}

		$output .= aqua_nav_item_html(
			array(
				'id'       => (int) $item->ID,
				'title'    => apply_filters( 'the_title', $item->title, $item->ID ),
				'url'      => ! empty( $item->url ) ? $item->url : '#',
				'current'  => (bool) array_intersect( array( 'current-menu-item', 'current_page_item' ), $classes ),
				'active'   => (bool) array_intersect( array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor', 'current_page_item', 'current_page_parent' ), $classes ),
				'depth'    => $depth,
				'children' => $has_children,
				'target'   => $item->target,
				'rel'      => $item->xfn,
				'attr'     => $item->attr_title,
			),
			$this->block
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
 * Opening tag of a sub menu list.
 *
 * @param string $block     'main-nav' or 'overlay-nav'.
 * @param int    $parent_id Parent item ID (used for aria-controls).
 * @return string
 */
function aqua_nav_sublist_open( $block, $parent_id ) {
	if ( 'overlay-nav' === $block ) {
		return '<ul class="overlay-nav__sublist">';
	}
	return '<ul class="main-nav__submenu" id="' . esc_attr( 'main-sub-' . $parent_id ) . '">';
}

/**
 * Opening <li> + link (or sub menu toggle) of a header menu item.
 * Shared by the walker and the fallback menu.
 *
 * @param array  $item  id, title, url, current, active, depth, children, target, rel, attr.
 * @param string $block 'main-nav' or 'overlay-nav'.
 * @return string
 */
function aqua_nav_item_html( $item, $block = 'main-nav' ) {
	$item = wp_parse_args(
		$item,
		array(
			'id'       => 0,
			'title'    => '',
			'url'      => '#',
			'current'  => false,
			'active'   => false,
			'depth'    => 0,
			'children' => false,
			'target'   => '',
			'rel'      => '',
			'attr'     => '',
		)
	);

	$extra = '';
	if ( $item['target'] ) {
		$extra .= ' target="' . esc_attr( $item['target'] ) . '"';
		$extra .= '_blank' === $item['target'] && ! $item['rel'] ? ' rel="noopener"' : '';
	}
	if ( $item['rel'] ) {
		$extra .= ' rel="' . esc_attr( $item['rel'] ) . '"';
	}
	if ( $item['attr'] ) {
		$extra .= ' title="' . esc_attr( $item['attr'] ) . '"';
	}
	if ( $item['current'] ) {
		$extra .= ' aria-current="page"';
	}

	$title    = esc_html( $item['title'] );
	$url      = esc_url( $item['url'] );
	$no_page  = in_array( $item['url'], array( '', '#' ), true );
	$sub_id   = 'main-sub-' . (int) $item['id'];
	$chevron  = aqua_ui_icon( 'chevron', 'main-nav__chevron' );
	$sub_name = sprintf( '%s: %s', aqua_label( 'submenu', 'Show submenu' ), wp_strip_all_tags( $item['title'] ) );

	// Sub items.
	if ( $item['depth'] > 0 ) {
		return 'overlay-nav' === $block
			? '<li class="overlay-nav__subitem"><a class="overlay-nav__sublink" href="' . $url . '"' . $extra . '>' . $title . '</a>'
			: '<li class="main-nav__subitem"><a class="main-nav__sublink" href="' . $url . '"' . $extra . '>' . $title . '</a>';
	}

	// Full-screen menu: a parent without its own page is a label above its children.
	if ( 'overlay-nav' === $block ) {
		if ( $item['children'] && $no_page ) {
			return '<li class="overlay-nav__item overlay-nav__item--group"><span class="overlay-nav__label">' . $title . '</span>';
		}
		return '<li class="overlay-nav__item"><a class="overlay-nav__link" href="' . $url . '"' . $extra . '>' . $title . '</a>';
	}

	// Desktop bar.
	$li_class = 'main-nav__item';
	if ( $item['children'] ) {
		$li_class .= ' main-nav__item--has-sub';
	}
	if ( $item['active'] ) {
		$li_class .= ' main-nav__item--active';
	}

	if ( $item['children'] && $no_page ) {
		return '<li class="' . $li_class . '"><button class="main-nav__link" type="button" aria-expanded="false" aria-controls="' . esc_attr( $sub_id ) . '" data-submenu-toggle>' . $title . $chevron . '</button>';
	}

	$html = '<li class="' . $li_class . '"><a class="main-nav__link" href="' . $url . '"' . $extra . '>' . $title . '</a>';
	if ( $item['children'] ) {
		$html .= '<button class="main-nav__sub-toggle" type="button" aria-expanded="false" aria-controls="' . esc_attr( $sub_id ) . '" aria-label="' . esc_attr( $sub_name ) . '" data-submenu-toggle>' . $chevron . '</button>';
	}
	return $html;
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
 * Fallback header menu — used until a menu is assigned to the "Header Menu" location.
 * wp_nav_menu() passes its arguments, including our 'aqua_block'.
 *
 * @param array $args wp_nav_menu() arguments.
 * @return string|void
 */
function aqua_menu_fallback( $args = array() ) {
	$block   = isset( $args['aqua_block'] ) ? $args['aqua_block'] : 'main-nav';
	$is_blog = aqua_is_blog_context();

	$items = array(
		array( 1, 'Home', home_url( '/' ), is_front_page(), array() ),
		array( 2, 'About', aqua_page_url( 'about' ), is_page( 'about' ), array() ),
		array( 3, 'Service', aqua_page_url( 'service' ), is_page( 'service' ), array() ),
		array(
			4,
			'Pages',
			'#',
			is_page( 'products' ) || $is_blog,
			array(
				array( 'Products', aqua_page_url( 'products' ), is_page( 'products' ) ),
				array( 'Blog Posts', aqua_blog_url(), $is_blog ),
			),
		),
		array( 5, 'Contact', aqua_page_url( 'contact' ), is_page( 'contact' ), array() ),
	);

	$html = '<ul class="' . esc_attr( $block ) . '">';
	foreach ( $items as $item ) {
		$html .= aqua_nav_item_html(
			array(
				'id'       => $item[0],
				'title'    => $item[1],
				'url'      => $item[2],
				'current'  => $item[3] && empty( $item[4] ),
				'active'   => $item[3],
				'children' => ! empty( $item[4] ),
			),
			$block
		);
		if ( ! empty( $item[4] ) ) {
			$html .= aqua_nav_sublist_open( $block, $item[0] );
			foreach ( $item[4] as $child ) {
				$html .= aqua_nav_item_html(
					array(
						'title'   => $child[0],
						'url'     => $child[1],
						'current' => $child[2],
						'depth'   => 1,
					),
					$block
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
 * Footer menu walker: flat list of links.
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
	$extra = $target ? ' target="' . esc_attr( $target ) . '"' . ( '_blank' === $target ? ' rel="noopener"' : '' ) : '';
	return '<li><a class="site-footer__link" href="' . esc_url( $url ) . '"' . $extra . '>' . esc_html( $title ) . '</a>';
}

/**
 * Fallback footer menu.
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
		array( 'Terms & Conditions', aqua_page_url( 'terms-and-conditions' ) ),
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
