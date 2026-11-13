<?php
/**
 * ACF setup: options pages, Local JSON, dynamic choices and editor tweaks.
 *
 * @package aqua
 */

/**
 * Options pages: Theme Settings → Header / Footer.
 * All their fields are stored as 'option' and read with aqua_field( 'name' ).
 */
function aqua_acf_options_pages() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => 'Theme Settings',
			'menu_title' => 'Theme Settings',
			'menu_slug'  => 'theme-general-settings',
			'capability' => 'edit_posts',
			'icon_url'   => 'dashicons-admin-customizer',
			'position'   => 59,
			'redirect'   => false,
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Header Settings',
			'menu_title'  => 'Header',
			'menu_slug'   => 'theme-header-settings',
			'parent_slug' => 'theme-general-settings',
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Footer Settings',
			'menu_title'  => 'Footer',
			'menu_slug'   => 'theme-footer-settings',
			'parent_slug' => 'theme-general-settings',
		)
	);
}
add_action( 'acf/init', 'aqua_acf_options_pages' );

/**
 * Local JSON: field groups are saved to and loaded from the theme (acf-json/),
 * so they are version-controlled and travel with the theme.
 *
 * @return string
 */
function aqua_acf_json_save_point() {
	return get_stylesheet_directory() . '/acf-json';
}
add_filter( 'acf/settings/save_json', 'aqua_acf_json_save_point' );

/**
 * Local JSON load path.
 *
 * @param array $paths Paths.
 * @return array
 */
function aqua_acf_json_load_point( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return array_unique( $paths );
}
add_filter( 'acf/settings/load_json', 'aqua_acf_json_load_point' );

/**
 * Fills "Icon", "Social network" and "Bottle" select fields from a single source of truth
 * (aqua_icon_choices(), aqua_social_networks(), aqua_bottle_choices()). Fields are marked by a wrapper class.
 *
 * @param array $field Field settings.
 * @return array
 */
function aqua_acf_dynamic_choices( $field ) {
	$class = isset( $field['wrapper']['class'] ) ? (string) $field['wrapper']['class'] : '';

	if ( false !== strpos( $class, 'aqua-icon-select' ) ) {
		$field['choices'] = aqua_icon_choices();
	} elseif ( false !== strpos( $class, 'aqua-social-select' ) ) {
		$field['choices'] = wp_list_pluck( aqua_social_networks(), 'label' );
	} elseif ( false !== strpos( $class, 'aqua-bottle-select' ) ) {
		$field['choices'] = aqua_bottle_choices();
	}

	return $field;
}
add_filter( 'acf/load_field/type=select', 'aqua_acf_dynamic_choices' );

/**
 * Message fields may contain {home_edit_url} / {settings_url} placeholders —
 * replaced with real admin links so the hints always point to the right screen.
 *
 * @param array $field Field settings.
 * @return array
 */
function aqua_acf_message_links( $field ) {
	if ( empty( $field['message'] ) || false === strpos( $field['message'], '{' ) ) {
		return $field;
	}

	$home_id   = aqua_front_page_id();
	$home_edit = $home_id ? get_edit_post_link( $home_id, 'raw' ) : admin_url( 'edit.php?post_type=page' );

	$field['message'] = strtr(
		$field['message'],
		array(
			'{home_edit_url}' => esc_url( $home_edit ),
			'{settings_url}'  => esc_url( admin_url( 'admin.php?page=theme-general-settings' ) ),
			'{forms_url}'     => esc_url( admin_url( 'admin.php?page=wpcf7' ) ),
		)
	);

	return $field;
}
add_filter( 'acf/load_field/type=message', 'aqua_acf_message_links' );

/**
 * Pages built from ACF fields use the classic edit screen, so the fields are shown
 * right under the title (in the block editor they would be hidden in a collapsed
 * "Meta Boxes" panel at the bottom): the Home page and pages with the theme templates.
 *
 * @param bool    $use_block_editor Whether to use the block editor.
 * @param WP_Post $post             Post being edited.
 * @return bool
 */
function aqua_classic_screen_for_acf_pages( $use_block_editor, $post ) {
	if ( ! $post || 'page' !== $post->post_type ) {
		return $use_block_editor;
	}

	$templates = array( 'page-about.php', 'page-service.php', 'page-products.php', 'page-contact.php' );
	if ( (int) $post->ID === aqua_front_page_id() || in_array( get_page_template_slug( $post ), $templates, true ) ) {
		return false;
	}
	return $use_block_editor;
}
add_filter( 'use_block_editor_for_post', 'aqua_classic_screen_for_acf_pages', 10, 2 );

/**
 * Removes the (unused) content editor from the Home page screen.
 */
function aqua_remove_editor_on_home() {
	$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $post_id && $post_id === aqua_front_page_id() ) {
		remove_post_type_support( 'page', 'editor' );
	}
}
add_action( 'load-post.php', 'aqua_remove_editor_on_home' );
