<?php
/**
 * Front-end assets.
 *
 * - Theme CSS/JS are built by Vite (src/ → assets/dist/) and enqueued from the build manifest.
 * - Switzer is self-hosted (assets/fonts/switzer/).
 * - Legacy assets keep the sections that are not rebuilt yet working; they are removed with them.
 *
 * @package aqua
 */

defined( 'ABSPATH' ) || exit;

/**
 * Vite build manifest (assets/dist/.vite/manifest.json).
 *
 * @return array
 */
function aqua_vite_manifest() {
	static $manifest = null;
	if ( null === $manifest ) {
		$file     = get_template_directory() . '/assets/dist/.vite/manifest.json';
		$manifest = file_exists( $file ) ? (array) json_decode( (string) file_get_contents( $file ), true ) : array(); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}
	return $manifest;
}

/**
 * URL of a built file for a source entry, e.g. 'src/js/main.js'.
 *
 * @param string $entry Source path as listed in the manifest.
 * @return string Empty string when the entry was not built.
 */
function aqua_vite_url( $entry ) {
	$manifest = aqua_vite_manifest();
	if ( empty( $manifest[ $entry ]['file'] ) ) {
		return '';
	}
	return get_template_directory_uri() . '/assets/dist/' . $manifest[ $entry ]['file'];
}

/**
 * Self-hosted variable font file.
 *
 * @param bool $url True for the URL, false for the file path.
 * @return string
 */
function aqua_font_file( $url = true ) {
	$rel = '/assets/fonts/switzer/Switzer-Variable.woff2';
	return $url ? get_template_directory_uri() . $rel : get_template_directory() . $rel;
}

/**
 * Very first lines of <head>: the js/no-js switch, the font preload and @font-face
 * (inline, so text never waits for a stylesheet to discover the font).
 * The fallback face is Arial resized to Switzer's metrics, so the swap does not shift the layout.
 */
function aqua_head_early() {
	// js/no-js switch + the performance level known so far (forced by ?perf=, cached for this visit, or
	// "low" for reduced motion), applied before the first paint. src/js/perf.js decides the rest.
	// html.intro: first page of the visit — the hero plays the opening drop and its headline waits for it.
	wp_print_inline_script_tag(
		"(function(d){d.classList.replace('no-js','js');try{var m=/[?&]perf=([^&#]+)/.exec(location.search),f=m&&/(low|medium|high)/.exec(m[1]),l=f?f[1]:(matchMedia('(prefers-reduced-motion: reduce)').matches?'low':sessionStorage.getItem('aqua-perf'));if(/^(low|medium|high)$/.test(l))d.dataset.perf=l;if(!sessionStorage.getItem('aqua-intro')&&!matchMedia('(prefers-reduced-motion: reduce)').matches)d.classList.add('intro')}catch(e){}})(document.documentElement);"
	);

	if ( ! file_exists( aqua_font_file( false ) ) ) {
		return;
	}

	$font = esc_url( aqua_font_file() );
	echo '<link rel="preload" href="' . $font . '" as="font" type="font/woff2" crossorigin>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	echo '<style id="aqua-fonts">'
		. '@font-face{font-family:"Switzer";src:url("' . $font . '") format("woff2");font-weight:100 900;font-style:normal;font-display:swap}' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		. '@font-face{font-family:"Switzer Fallback";src:local("Arial"),local("Helvetica Neue"),local("Helvetica");size-adjust:' . AQUA_FALLBACK_SIZE_ADJUST . '%;ascent-override:' . round( 98 / AQUA_FALLBACK_SIZE_ADJUST * 100, 2 ) . '%;descent-override:' . round( 25 / AQUA_FALLBACK_SIZE_ADJUST * 100, 2 ) . '%;line-gap-override:' . round( 9 / AQUA_FALLBACK_SIZE_ADJUST * 100, 2 ) . '%}'
		. "</style>\n";
}
add_action( 'wp_head', 'aqua_head_early', 1 );

/**
 * Width ratio Switzer / Arial for the same text (measured in Chrome, see DESIGN.md → Type).
 */
if ( ! defined( 'AQUA_FALLBACK_SIZE_ADJUST' ) ) {
	define( 'AQUA_FALLBACK_SIZE_ADJUST', 100.7 );
}

/**
 * Old BEM stylesheets still needed by sections that are not rebuilt yet (assets/css/blocks/).
 *
 * @return string[]
 */
function aqua_legacy_css_blocks() {
	return array(
		'hero',
		'page-banner',
		'section-heading',
		'features',
		'about',
		'services',
		'stats',
		'pricing',
		'blog',
		'swipe',
		'contact',
		'entry',
		'error-page',
	);
}

/**
 * Front-end styles and scripts.
 */
function aqua_scripts() {
	$uri    = get_template_directory_uri() . '/assets';
	$deps   = array();
	$legacy = ! aqua_has_page_head(); // Rebuilt screens (home, legal…) need none of the old assets.

	// Legacy (removed when the inner pages are rebuilt).
	if ( $legacy ) {
		wp_enqueue_style( 'aqua-fontawesome', 'https://use.fontawesome.com/releases/v5.15.4/css/all.css', array(), '5.15.4' );
		wp_enqueue_style( 'aqua-animate', $uri . '/lib/animate/animate.min.css', array(), _S_VERSION );
		wp_enqueue_style( 'aqua-bootstrap', $uri . '/css/bootstrap.min.css', array(), _S_VERSION );
		wp_enqueue_style( 'aqua-legacy-base', $uri . '/css/base.css', array( 'aqua-bootstrap' ), _S_VERSION );
		$deps[] = 'aqua-legacy-base';
		foreach ( aqua_legacy_css_blocks() as $block ) {
			wp_enqueue_style( 'aqua-block-' . $block, $uri . '/css/blocks/' . $block . '.css', array( 'aqua-legacy-base' ), _S_VERSION );
			$deps[] = 'aqua-block-' . $block;
		}
	}

	// Theme build (loaded after the legacy files so the new design system wins).
	$css = aqua_vite_url( 'src/css/main.css' );
	if ( $css ) {
		wp_enqueue_style( 'aqua-main', $css, $deps, null );
		$deps = array( 'aqua-main' );
	}
	wp_enqueue_style( 'aqua-style', get_stylesheet_uri(), $deps, _S_VERSION );

	// Legacy scripts.
	if ( $legacy ) {
		wp_enqueue_script( 'aqua-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js', array(), '5.0.0', array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_enqueue_script( 'aqua-wow', $uri . '/lib/wow/wow.min.js', array(), _S_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
		wp_enqueue_script( 'aqua-legacy', $uri . '/js/theme.js', array( 'aqua-bootstrap', 'aqua-wow' ), _S_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}

	// Theme module (deferred by nature; chunks load on demand).
	$js = aqua_vite_url( 'src/js/main.js' );
	if ( $js ) {
		wp_enqueue_script_module( 'aqua-main', $js, array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'aqua_scripts' );

/**
 * Front-end diet: things WordPress prints by default that this theme never uses.
 * - emoji detection script and styles (the design has no emoji);
 * - block library / global styles on screens built from theme templates (no blocks there);
 *   kept wherever post content is shown (posts, plain pages, the legal template);
 * - Dashicons for visitors (only the admin bar needs them).
 */
function aqua_frontend_diet() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'aqua_frontend_diet' );

/**
 * Dequeues unused core styles (late, after everything is registered).
 */
function aqua_dequeue_unused() {
	if ( ! is_user_logged_in() ) {
		wp_deregister_style( 'dashicons' );
	}
	$has_blocks = is_singular() && ! is_front_page() && ! is_page_template( array( 'page-about.php', 'page-service.php', 'page-products.php', 'page-contact.php' ) );
	if ( ! $has_blocks ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'aqua_dequeue_unused', 100 );
