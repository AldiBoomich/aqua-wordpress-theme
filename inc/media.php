<?php
/**
 * Images: modern formats and sizes for the full-bleed photos of the design.
 *
 * - JPEG uploads get their resized copies (used in srcset) as WebP: smaller files, decoded fast on phones.
 *   The original stays JPEG (and is what "full" points to).
 * - "aqua-2560": the hero photo on large and high-density screens.
 *
 * @package aqua
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resized copies of JPEG uploads are saved as WebP.
 *
 * @param array $formats Map of source mime => output mime.
 * @return array
 */
function aqua_image_output_format( $formats ) {
	if ( wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		$formats['image/jpeg'] = 'image/webp';
	}
	return $formats;
}
add_filter( 'image_editor_output_format', 'aqua_image_output_format' );

/**
 * WebP quality.
 *
 * @param int    $quality   Quality.
 * @param string $mime_type Mime type.
 * @return int
 */
function aqua_webp_quality( $quality, $mime_type = '' ) {
	return 'image/webp' === $mime_type ? 80 : $quality;
}
add_filter( 'wp_editor_set_quality', 'aqua_webp_quality', 10, 2 );

/**
 * Image sizes.
 */
function aqua_image_sizes() {
	add_image_size( 'aqua-2560', 2560, 0, false );
}
add_action( 'after_setup_theme', 'aqua_image_sizes' );

/**
 * Big uploads are scaled down to 2560px (WordPress default) — matches the largest size above.
 *
 * @return int
 */
function aqua_big_image_threshold() {
	return 2560;
}
add_filter( 'big_image_size_threshold', 'aqua_big_image_threshold' );

/**
 * <img> for a Media Library image with responsive srcset, or a theme fallback file.
 * Unlike aqua_image_tag(), the alt text can be forced (empty alt for decorative photos).
 *
 * @param mixed  $value        ACF image value (ID / array).
 * @param string $default_file Fallback in assets/img/.
 * @param array  $attr         Attributes: class, sizes, loading, fetchpriority, alt (null = Media Library alt).
 * @param string $size         Largest size to use.
 * @return string
 */
function aqua_photo( $value, $default_file, $attr = array(), $size = 'aqua-2560' ) {
	$attr = wp_parse_args(
		$attr,
		array(
			'class'    => '',
			'sizes'    => '100vw',
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => null,
		)
	);

	$id = aqua_attachment_id( $value );
	if ( $id && wp_attachment_is_image( $id ) ) {
		if ( null === $attr['alt'] ) {
			unset( $attr['alt'] );
		}
		if ( 'eager' === $attr['loading'] ) {
			$attr['fetchpriority'] = $attr['fetchpriority'] ?? 'high';
		}
		return wp_get_attachment_image( $id, $size, false, array_filter( $attr, 'is_string' ) );
	}

	if ( ! $default_file ) {
		return '';
	}
	$file = get_template_directory() . '/assets/img/' . $default_file;
	$dims = file_exists( $file ) ? wp_getimagesize( $file ) : false;
	$html = '<img src="' . esc_url( aqua_img( $default_file ) ) . '"';
	if ( $dims ) {
		$html .= ' width="' . (int) $dims[0] . '" height="' . (int) $dims[1] . '"';
	}
	foreach ( array( 'class', 'loading', 'decoding', 'fetchpriority' ) as $key ) {
		if ( ! empty( $attr[ $key ] ) ) {
			$html .= ' ' . $key . '="' . esc_attr( $attr[ $key ] ) . '"';
		}
	}
	return $html . ' alt="' . esc_attr( (string) $attr['alt'] ) . '">';
}
