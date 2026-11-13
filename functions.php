<?php
/**
 * Aqua theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package aqua
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Bump on every asset change to bust the browser cache.
	define( '_S_VERSION', '1.3.0' );
}

/**
 * Theme setup.
 */
function aqua_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'header' => 'Header Menu',
			'footer' => 'Footer Menu',
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'aqua_setup' );

/**
 * Content width for embeds.
 *
 * @global int $content_width
 */
function aqua_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'aqua_content_width', 1140 );
}
add_action( 'after_setup_theme', 'aqua_content_width', 0 );

/**
 * BEM block stylesheets in assets/css/blocks/ (loaded in this order).
 *
 * @return string[]
 */
function aqua_css_blocks() {
	return array(
		'button',
		'liquid-icon',
		'bottle',
		'preloader',
		'site-header',
		'main-nav',
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
		'subscribe-form',
		'site-footer',
		'back-to-top',
		'entry',
		'error-page',
	);
}

/**
 * Front-end styles and scripts.
 * No jQuery: the theme script, Bootstrap 5 and WOW are all vanilla JS.
 */
function aqua_scripts() {
	$uri = get_template_directory_uri() . '/assets';

	// Vendor styles.
	wp_enqueue_style( 'aqua-google-fonts', 'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wdth,wght@0,75..100,300..800;1,75..100,300..800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap', array(), null );
	wp_enqueue_style( 'aqua-fontawesome', 'https://use.fontawesome.com/releases/v5.15.4/css/all.css', array(), '5.15.4' );
	wp_enqueue_style( 'aqua-animate', $uri . '/lib/animate/animate.min.css', array(), _S_VERSION );
	wp_enqueue_style( 'aqua-bootstrap', $uri . '/css/bootstrap.min.css', array(), _S_VERSION );

	// Theme styles: base + one file per BEM block.
	wp_enqueue_style( 'aqua-base', $uri . '/css/base.css', array( 'aqua-bootstrap' ), _S_VERSION );
	foreach ( aqua_css_blocks() as $block ) {
		wp_enqueue_style( 'aqua-block-' . $block, $uri . '/css/blocks/' . $block . '.css', array( 'aqua-base' ), _S_VERSION );
	}
	wp_enqueue_style( 'aqua-style', get_stylesheet_uri(), array( 'aqua-base' ), _S_VERSION );

	// Scripts (footer).
	wp_enqueue_script( 'aqua-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js', array(), '5.0.0', true );
	wp_enqueue_script( 'aqua-wow', $uri . '/lib/wow/wow.min.js', array(), _S_VERSION, true );
	wp_enqueue_script( 'aqua-theme', $uri . '/js/theme.js', array( 'aqua-bootstrap', 'aqua-wow' ), _S_VERSION, true );
	wp_enqueue_script( 'aqua-liquid', $uri . '/js/liquid.js', array(), _S_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'aqua_scripts' );

/**
 * Hides the preloader when JavaScript is disabled (otherwise it would never go away).
 */
function aqua_noscript_styles() {
	echo '<noscript><style>.preloader{display:none}</style></noscript>' . "\n";
}
add_action( 'wp_head', 'aqua_noscript_styles' );

/**
 * Theme helpers.
 */
require get_template_directory() . '/inc/acf-helpers.php';
require get_template_directory() . '/inc/icons.php';
require get_template_directory() . '/inc/bottles.php';
require get_template_directory() . '/inc/acf-setup.php';
require get_template_directory() . '/inc/class-aqua-nav-walker.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/contact-form-7.php';
require get_template_directory() . '/inc/admin.php';
