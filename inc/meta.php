<?php
/**
 * <head> meta for sharing and installing:
 *  - icons from the brand drop (SVG, PNG 32/180/192/512, apple-touch-icon) + web app manifest,
 *    unless a Site Icon is set in Settings → General (WordPress then prints its own);
 *  - meta description, Open Graph and Twitter card (Theme Settings → Sharing),
 *    skipped when an SEO plugin is active so nothing is printed twice.
 *
 * @package aqua
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL of a file in assets/img/icons/.
 *
 * @param string $file File name.
 * @return string
 */
function aqua_icon_url( $file ) {
	return get_template_directory_uri() . '/assets/img/icons/' . $file;
}

/**
 * Icons, manifest and theme color.
 */
function aqua_head_icons() {
	echo '<meta name="theme-color" content="#f7f9fa">' . "\n";

	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( aqua_icon_url( 'favicon.svg' ) ) );
	printf( '<link rel="icon" href="%s" sizes="32x32" type="image/png">' . "\n", esc_url( aqua_icon_url( 'favicon-32.png' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( aqua_icon_url( 'apple-touch-icon.png' ) ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( home_url( '/?aqua-manifest=1' ) ) );
}
add_action( 'wp_head', 'aqua_head_icons', 3 );

/**
 * Web app manifest, built from the site settings (so the name follows Theme Settings).
 */
function aqua_manifest() {
	if ( empty( $_GET['aqua-manifest'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	$name = (string) aqua_field( 'logo_text', get_bloginfo( 'name' ) );
	$data = array(
		'name'             => trim( $name . ' — ' . get_bloginfo( 'description' ), ' —' ),
		'short_name'       => $name,
		'start_url'        => home_url( '/' ),
		'display'          => 'standalone',
		'background_color' => '#f7f9fa',
		'theme_color'      => '#f7f9fa',
		'icons'            => array(
			array(
				'src'   => aqua_icon_url( 'icon-192.png' ),
				'sizes' => '192x192',
				'type'  => 'image/png',
			),
			array(
				'src'     => aqua_icon_url( 'icon-512.png' ),
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'any maskable',
			),
		),
	);
	header( 'Content-Type: application/manifest+json; charset=utf-8' );
	echo wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
}
add_action( 'wp_loaded', 'aqua_manifest' );

/**
 * Whether an SEO plugin prints its own description / Open Graph tags.
 *
 * @return bool
 */
function aqua_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );
}

/**
 * Title, description, URL and image of the current screen for previews.
 *
 * @return array{title:string,description:string,url:string,image:string,width:int,height:int,alt:string,type:string}
 */
function aqua_share_data() {
	$site    = get_bloginfo( 'name' );
	$default = (string) aqua_field( 'og_description', 'Spring water purified through 7-stage filtration and delivered within 24 hours. A concept website.' );

	$title = is_front_page()
		? (string) aqua_field( 'og_title', trim( $site . ' — ' . get_bloginfo( 'description' ), ' —' ) )
		: wp_get_document_title();

	$description = '';
	$image_id    = 0;
	$url         = home_url( add_query_arg( array() ) );

	if ( is_singular() ) {
		$post = get_queried_object();
		$url  = (string) wp_get_canonical_url( $post );
		if ( has_excerpt( $post ) ) {
			$description = get_the_excerpt( $post );
		} elseif ( 'post' === $post->post_type ) {
			$description = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );
		}
		if ( ! is_front_page() && has_post_thumbnail( $post ) ) {
			$image_id = (int) get_post_thumbnail_id( $post );
		}
	} elseif ( is_category() || is_tag() ) {
		$description = term_description();
	}

	if ( is_front_page() ) {
		$url = home_url( '/' );
	}

	$description = trim( wp_strip_all_tags( (string) $description ) );
	if ( '' === $description ) {
		$description = $default;
	}

	if ( ! $image_id ) {
		$image_id = aqua_attachment_id( aqua_field( 'og_image', 0 ) );
	}

	$image  = aqua_img( 'og/og-default.jpg' );
	$width  = 1200;
	$height = 630;
	$alt    = $title;
	if ( $image_id ) {
		$src = wp_get_attachment_image_src( $image_id, 'full' );
		if ( $src ) {
			list( $image, $width, $height ) = $src;
			$media_alt                      = trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) );
			$alt                            = '' !== $media_alt ? $media_alt : $alt;
		}
	}

	return array(
		'title'       => $title,
		'description' => $description,
		'url'         => $url,
		'image'       => $image,
		'width'       => (int) $width,
		'height'      => (int) $height,
		'alt'         => $alt,
		'type'        => is_singular( 'post' ) ? 'article' : 'website',
	);
}

/**
 * Meta description, Open Graph and Twitter card.
 */
function aqua_head_share_meta() {
	if ( aqua_seo_plugin_active() || is_404() ) {
		return;
	}
	$d    = aqua_share_data();
	$tags = array(
		array( 'name', 'description', $d['description'] ),
		array( 'property', 'og:type', $d['type'] ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:locale', get_locale() ),
		array( 'property', 'og:title', $d['title'] ),
		array( 'property', 'og:description', $d['description'] ),
		array( 'property', 'og:url', $d['url'] ),
		array( 'property', 'og:image', $d['image'] ),
		array( 'property', 'og:image:width', (string) $d['width'] ),
		array( 'property', 'og:image:height', (string) $d['height'] ),
		array( 'property', 'og:image:alt', $d['alt'] ),
		array( 'name', 'twitter:card', 'summary_large_image' ),
		array( 'name', 'twitter:title', $d['title'] ),
		array( 'name', 'twitter:description', $d['description'] ),
		array( 'name', 'twitter:image', $d['image'] ),
		array( 'name', 'twitter:image:alt', $d['alt'] ),
	);
	foreach ( $tags as $tag ) {
		$value = in_array( $tag[1], array( 'og:url', 'og:image', 'twitter:image' ), true ) ? esc_url( $tag[2] ) : esc_attr( $tag[2] );
		printf( '<meta %s="%s" content="%s">' . "\n", esc_attr( $tag[0] ), esc_attr( $tag[1] ), $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
add_action( 'wp_head', 'aqua_head_share_meta', 4 );
