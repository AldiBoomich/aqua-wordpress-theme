<?php
/**
 * Front-end assets.
 *
 * - Theme CSS/JS are built by Vite (src/ → assets/dist/) and enqueued from the build manifest.
 * - Switzer is self-hosted (assets/fonts/switzer/).
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
	wp_print_inline_script_tag( "document.documentElement.classList.replace('no-js','js');" );

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
 * Front-end styles and scripts.
 */
function aqua_scripts() {
	$uri  = get_template_directory_uri() . '/assets';
	$deps = array();

	// Theme build.
	$css = aqua_vite_url( 'src/css/main.css' );
	if ( $css ) {
		wp_enqueue_style( 'aqua-main', $css, $deps, null );
		$deps = array( 'aqua-main' );
	}
	wp_enqueue_style( 'aqua-style', get_stylesheet_uri(), $deps, _S_VERSION );

	// Liquid physics for the line icons and the bottles (still a plain script).
	wp_enqueue_script( 'aqua-liquid', $uri . '/js/liquid.js', array(), _S_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	// Theme module (deferred by nature; chunks load on demand).
	$js = aqua_vite_url( 'src/js/main.js' );
	if ( $js ) {
		wp_enqueue_script_module( 'aqua-main', $js, array(), null );
	}
}
add_action( 'wp_enqueue_scripts', 'aqua_scripts' );
