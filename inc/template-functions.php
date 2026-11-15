<?php
/**
 * Template functions: body classes, page links, page head helpers, pagination.
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
	return $classes;
}
add_filter( 'body_class', 'aqua_body_classes' );

/**
 * Splits a headline into words wrapped in masks, so the page head entrance (pure CSS) can raise
 * them one by one from the first paint, without waiting for JavaScript.
 *
 * @param string $text Plain headline text.
 * @return string Escaped HTML.
 */
function aqua_title_words( $text ) {
	$words = preg_split( '/\s+/u', trim( (string) $text ), -1, PREG_SPLIT_NO_EMPTY );
	$html  = array();
	foreach ( (array) $words as $i => $word ) {
		$html[] = '<span class="page-head__word" style="--i:' . (int) $i . '"><span class="page-head__word-inner">' . esc_html( $word ) . '</span></span>';
	}
	return implode( ' ', $html );
}

/**
 * Archive titles without the "Category:" / "Tag:" prefix (the page head says it plainly).
 */
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

/**
 * Reading time of a post in minutes (about 220 words a minute, at least 1).
 *
 * @param int|WP_Post|null $post Post (default: current).
 * @return int
 */
function aqua_read_time( $post = null ) {
	$words = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post ) ) );
	return max( 1, (int) ceil( $words / 220 ) );
}

/**
 * Previous / next + page numbers for archives and search (block "pagination").
 *
 * @param WP_Query|null $query Query to paginate (default: the main query).
 */
function aqua_pagination( $query = null ) {
	global $wp_query;
	$query = $query ? $query : $wp_query;
	$total = (int) $query->max_num_pages;
	if ( $total < 2 ) {
		return;
	}

	$links = paginate_links(
		array(
			'total'     => $total,
			'current'   => max( 1, (int) get_query_var( 'paged' ) ),
			'type'      => 'array',
			'mid_size'  => 1,
			'prev_text' => esc_html( aqua_label( 'prev', 'Previous' ) ),
			'next_text' => esc_html( aqua_label( 'next', 'Next' ) ),
		)
	);
	if ( ! $links ) {
		return;
	}
	?>
	<nav class="pagination" aria-label="<?php echo esc_attr( aqua_label( 'pagination', 'Pages' ) ); ?>">
		<ul class="pagination__list">
			<?php foreach ( $links as $link ) : ?>
				<li class="pagination__item">
					<?php
					// paginate_links() returns escaped markup; only the classes are changed here.
					echo str_replace( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core markup.
						array( 'page-numbers current', 'prev page-numbers', 'next page-numbers', 'page-numbers dots', 'page-numbers' ),
						array( 'pagination__link pagination__link--current', 'pagination__link pagination__link--prev', 'pagination__link pagination__link--next', 'pagination__link pagination__link--dots', 'pagination__link' ),
						$link
					);
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}

/**
 * Key pages offered when nothing was found (404, empty search): Theme Settings → Labels decide the titles.
 *
 * @return array<int,array{title:string,url:string}>
 */
function aqua_helpful_links() {
	return array(
		array(
			'title' => aqua_label( 'link_home', 'Homepage' ),
			'url'   => home_url( '/' ),
		),
		array(
			'title' => aqua_label( 'link_products', 'Bottles and prices' ),
			'url'   => aqua_page_url( 'products' ),
		),
		array(
			'title' => aqua_label( 'link_blog', 'Journal' ),
			'url'   => aqua_blog_url(),
		),
		array(
			'title' => aqua_label( 'link_contact', 'Contact us' ),
			'url'   => aqua_page_url( 'contact' ),
		),
	);
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
 * Header text tone at the very top of the screen. Every page starts light (hero water photo or the light
 * page head), so the text is dark; site-header.css also has --light-text for a page that opens on a dark photo.
 *
 * @return string 'dark-text' or 'light-text'.
 */
function aqua_header_tone() {
	return 'dark-text';
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
 * Default H1 of the page head (Page Head → Title, else the page / archive title).
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
		return aqua_field( 'error_title', 'Page not found' );
	}
	return get_bloginfo( 'name' );
}

