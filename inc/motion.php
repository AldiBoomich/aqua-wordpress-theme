<?php
/**
 * Motion & interaction switches (Theme Settings → Motion).
 *
 * - Page transitions: cross-document View Transitions (opt-in printed inline; the animation is in
 *   src/css/blocks/page-transition.css). Browsers without support simply load the next page.
 * - Faster links: WordPress core already prints Speculation Rules (prefetch on press); when this is on,
 *   the theme asks it to prefetch on hover instead ("moderate").
 * - Water drop cursor and magnetic buttons: announced on <html data-motion="…">, read by src/js/motion.js.
 *   Both run only with a mouse and without reduced motion (decided in JS).
 *
 * @package aqua
 */

/**
 * Enabled pointer effects, for the data-motion attribute.
 *
 * @return string[]
 */
function aqua_motion_features() {
	$features = array();
	if ( aqua_flag( 'motion_cursor', true ) ) {
		$features[] = 'cursor';
	}
	if ( aqua_flag( 'motion_magnetic', true ) ) {
		$features[] = 'magnetic';
	}
	return $features;
}

/**
 * Adds data-motion="cursor magnetic" to <html>.
 *
 * @param string $output Language attributes.
 * @return string
 */
function aqua_motion_html_attribute( $output ) {
	if ( is_admin() ) {
		return $output;
	}
	$features = aqua_motion_features();
	if ( $features ) {
		$output .= ' data-motion="' . esc_attr( implode( ' ', $features ) ) . '"';
	}
	return $output;
}
add_filter( 'language_attributes', 'aqua_motion_html_attribute' );

/**
 * Opts both pages of a navigation into a View Transition. Must be on the old and the new page.
 * Reduced motion: no transition at all.
 */
function aqua_motion_view_transition() {
	if ( ! aqua_flag( 'motion_page_transitions', true ) ) {
		return;
	}
	echo "<style id=\"aqua-view-transition\">@media (prefers-reduced-motion: no-preference) { @view-transition { navigation: auto; } }</style>\n";
	// window.aquaRevealed resolves when the page is on screen: after the page transition, if one runs
	// (heavy GPU start-ups such as the hero water wait for it). No pagereveal support: at once. Safety: 1.5 s.
	// No hardware acceleration (html.no-gpu, src/js/modules/gpu.js): a full-screen transition is drawn in software and
	// stutters, so the page simply opens (like the solid header there instead of the glass).
	echo "<script id=\"aqua-pageswap\">addEventListener('pageswap', function (e) { if (e.viewTransition && document.documentElement.classList.contains('no-gpu')) { e.viewTransition.skipTransition(); } });</script>\n";
	echo "<script id=\"aqua-revealed\">window.aquaRevealed = new Promise(function (done) { if (!('onpagereveal' in window)) { done(); return; } setTimeout(done, 1500); addEventListener('pagereveal', function (e) { if (e.viewTransition) { e.viewTransition.finished.then(done, done); } else { done(); } }, { once: true }); });</script>\n";
}
add_action( 'wp_head', 'aqua_motion_view_transition', 2 );

/**
 * Speculation Rules (WordPress core): prefetch on hover instead of on press.
 * Core's exclusions stay (admin, login, query strings, nofollow, .no-prefetch).
 *
 * @param array|null $config Core configuration.
 * @return array|null
 */
function aqua_motion_speculation( $config ) {
	if ( ! is_array( $config ) || ! aqua_flag( 'motion_prefetch', true ) ) {
		return $config;
	}
	$config['mode']      = 'prefetch';
	$config['eagerness'] = 'moderate';
	return $config;
}
add_filter( 'wp_speculation_rules_configuration', 'aqua_motion_speculation' );
