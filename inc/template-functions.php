<?php
/**
 * Template functions: body classes, page links and the inner page banner.
 *
 * @package aqua
 */

/**
 * Adds custom classes to the body element.
 *
 * @param array $classes Body classes.
 * @return array
 */
function aqua_body_classes( $classes ) {
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}
	if ( aqua_has_page_head() ) {
		$classes[] = 'has-page-head';
	}
	return $classes;
}
add_filter( 'body_class', 'aqua_body_classes' );

/**
 * Whether the screen starts with the new light page head (instead of the old dark photo banner).
 * Grows as pages are rebuilt.
 *
 * @return bool
 */
function aqua_has_page_head() {
	return is_page_template( 'page-legal.php' );
}

/**
 * Gives every H2 of a legal text an id (kept if the editor already set one) and collects
 * them for the table of contents.
 *
 * @param string $html Filtered post content.
 * @return array{html:string,toc:array<string,string>}
 */
function aqua_legal_sections( $html ) {
	$toc  = array();
	$html = preg_replace_callback(
		'#<h2([^>]*)>(.*?)</h2>#is',
		function ( $m ) use ( &$toc ) {
			$title = trim( wp_strip_all_tags( $m[2] ) );
			if ( preg_match( '#\sid=["\']([^"\']+)["\']#i', $m[1], $id ) ) {
				$anchor = $id[1];
				$attrs  = $m[1];
			} else {
				$anchor = sanitize_title( $title );
				$suffix = 2;
				while ( isset( $toc[ $anchor ] ) ) {
					$anchor = sanitize_title( $title ) . '-' . $suffix++;
				}
				$attrs = $m[1] . ' id="' . esc_attr( $anchor ) . '"';
			}
			$toc[ $anchor ] = $title;
			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		(string) $html
	);

	return array(
		'html' => (string) $html,
		'toc'  => $toc,
	);
}

/**
 * Header text tone at the very top of the screen: light text over the dark top sections
 * that are not rebuilt yet, dark text on the new light pages.
 *
 * @return string 'light-text' or 'dark-text'.
 */
function aqua_header_tone() {
	return aqua_has_page_head() ? 'dark-text' : 'light-text';
}

/**
 * Pingback discovery header for singular screens.
 */
function aqua_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'aqua_pingback_header' );

/**
 * URL of a page by its slug (about, service, products, blog, contact).
 * Falls back to /slug/ when the page does not exist.
 *
 * @param string $slug Page slug.
 * @return string
 */
function aqua_page_url( $slug ) {
	if ( 'blog' === $slug ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		if ( $posts_page ) {
			return get_permalink( $posts_page );
		}
	}

	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page );
	}

	return home_url( '/' . $slug . '/' );
}

/**
 * URL of the blog (posts page).
 *
 * @return string
 */
function aqua_blog_url() {
	return aqua_page_url( 'blog' );
}

/**
 * Title shown in the inner page banner.
 *
 * @return string
 */
function aqua_banner_title() {
	if ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		return $posts_page ? aqua_field( 'banner_title', get_the_title( $posts_page ), $posts_page ) : 'Blog';
	}
	if ( is_singular() ) {
		return aqua_field( 'banner_title', get_the_title(), get_queried_object_id() );
	}
	if ( is_archive() ) {
		return wp_strip_all_tags( get_the_archive_title() );
	}
	if ( is_search() ) {
		/* translators: %s: search query. */
		return sprintf( 'Search: %s', get_search_query() );
	}
	if ( is_404() ) {
		return aqua_field( 'error_title', 'Page Not Found' );
	}
	return get_bloginfo( 'name' );
}

/**
 * Background image of the inner page banner: page's own image → Header Settings → CSS default.
 *
 * @return string Image URL or an empty string (use the CSS default).
 */
function aqua_banner_image_url() {
	$object_id = is_home() ? (int) get_option( 'page_for_posts' ) : ( is_singular() ? get_queried_object_id() : 0 );

	$image = $object_id ? aqua_field( 'banner_image', '', $object_id ) : '';
	if ( ! $image ) {
		$image = aqua_field( 'breadcrumb_image', '' );
	}

	return $image ? aqua_image_url( $image, '', 'full' ) : '';
}

/**
 * Inner page banner: title + breadcrumbs (block "page-banner").
 */
function aqua_breadcrumb() {
	$title  = aqua_banner_title();
	$crumbs = array(
		array(
			'title' => 'Home',
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( 'post' ) || is_archive() ) {
		$crumbs[] = array(
			'title' => 'Blog',
			'url'   => aqua_blog_url(),
		);
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_queried_object_id() ) ) as $ancestor_id ) {
			$crumbs[] = array(
				'title' => get_the_title( $ancestor_id ),
				'url'   => get_permalink( $ancestor_id ),
			);
		}
	}

	$bg    = aqua_banner_image_url();
	$style = $bg ? ' style="background-image: linear-gradient(rgba(0, 20, 66, 0.7), rgba(0, 20, 66, 0.7)), url(' . esc_url( $bg ) . ');"' : '';
	?>
	<section class="page-banner"<?php echo $style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- URL escaped above. ?>>
		<div class="page-banner__inner">
			<h1 class="page-banner__title wow fadeInDown" data-wow-delay="0.1s"><?php echo esc_html( $title ); ?></h1>
			<nav aria-label="Breadcrumb">
				<ol class="page-banner__crumbs wow fadeInDown" data-wow-delay="0.3s">
					<?php foreach ( $crumbs as $crumb ) : ?>
						<li class="page-banner__crumb"><a class="page-banner__link" href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['title'] ); ?></a></li>
					<?php endforeach; ?>
					<li class="page-banner__crumb page-banner__crumb--current" aria-current="page"><?php echo esc_html( $title ); ?></li>
				</ol>
			</nav>
		</div>
	</section>
	<?php
}
