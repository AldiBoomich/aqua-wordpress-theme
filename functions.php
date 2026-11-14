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
	define( '_S_VERSION', '2.0.0-dev' );
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
 * Theme helpers. Front-end CSS/JS: inc/assets.php (Vite build from src/).
 */
require get_template_directory() . '/inc/acf-helpers.php';
require get_template_directory() . '/inc/assets.php';
require get_template_directory() . '/inc/meta.php';
require get_template_directory() . '/inc/portfolio.php';
require get_template_directory() . '/inc/ui-icons.php';
require get_template_directory() . '/inc/icons.php';
require get_template_directory() . '/inc/bottles.php';
require get_template_directory() . '/inc/acf-setup.php';
require get_template_directory() . '/inc/class-aqua-nav-walker.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/contact-form-7.php';
require get_template_directory() . '/inc/admin.php';
