<?php
/**
 * ACF helpers.
 *
 * Every text and image in the templates is printed through these helpers.
 * If ACF is not active or a field is empty, the default value (the original design content)
 * is returned, so the site never breaks and always looks complete.
 *
 * Field map: see acf-json/ (field groups are stored as Local JSON in the theme).
 *
 * @package aqua
 */

/**
 * Whether Advanced Custom Fields is active.
 *
 * @return bool
 */
function aqua_has_acf() {
	return function_exists( 'get_field' );
}

/**
 * Returns an ACF field value or a default.
 *
 * @param string     $name    Field name.
 * @param mixed      $default Value used when the field is empty or ACF is inactive.
 * @param int|string $post_id 'option' (Theme Settings) or a post ID.
 * @return mixed
 */
function aqua_field( $name, $default = '', $post_id = 'option' ) {
	if ( aqua_has_acf() ) {
		$value = get_field( $name, $post_id );
		if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
			return $value;
		}
	}
	return $default;
}

/**
 * Returns a True / False field. Unlike aqua_field(), "false" is a real value here;
 * the default is used only when the field has never been saved.
 *
 * @param string     $name    Field name.
 * @param bool       $default Default value.
 * @param int|string $post_id 'option' or a post ID.
 * @return bool
 */
function aqua_flag( $name, $default = true, $post_id = 'option' ) {
	if ( ! aqua_has_acf() ) {
		return (bool) $default;
	}
	$value = get_field( $name, $post_id );
	return null === $value ? (bool) $default : (bool) $value;
}

/**
 * Returns repeater rows or default rows (default keys = sub field names).
 *
 * @param string     $name     Repeater field name.
 * @param array      $defaults Default rows.
 * @param int|string $post_id  'option' or a post ID.
 * @return array
 */
function aqua_rows( $name, $defaults = array(), $post_id = 'option' ) {
	$rows = aqua_field( $name, array(), $post_id );
	return ( is_array( $rows ) && ! empty( $rows ) ) ? $rows : $defaults;
}

/**
 * ID of the page set as the homepage (Settings → Reading).
 *
 * @return int
 */
function aqua_front_page_id() {
	return (int) get_option( 'page_on_front' );
}

/**
 * Field stored on the Home page. Homepage blocks (slider, about, services…) are also
 * reused on inner pages, so they always read their content from the Home page.
 *
 * @param string $name    Field name.
 * @param mixed  $default Default value.
 * @return mixed
 */
function aqua_home_field( $name, $default = '' ) {
	$page_id = aqua_front_page_id();
	return $page_id ? aqua_field( $name, $default, $page_id ) : $default;
}

/**
 * Repeater rows stored on the Home page.
 *
 * @param string $name     Repeater field name.
 * @param array  $defaults Default rows.
 * @return array
 */
function aqua_home_rows( $name, $defaults = array() ) {
	$page_id = aqua_front_page_id();
	return $page_id ? aqua_rows( $name, $defaults, $page_id ) : $defaults;
}

/**
 * URL of a file in assets/img/.
 *
 * @param string $file File name, e.g. 'about.jpg' or 'bottles/bottle-19.svg'.
 * @return string
 */
function aqua_img( $file ) {
	return get_template_directory_uri() . '/assets/img/' . ltrim( $file, '/' );
}

/**
 * Attachment ID from any ACF image return format (array / ID).
 *
 * @param mixed $value Field value.
 * @return int
 */
function aqua_attachment_id( $value ) {
	if ( is_array( $value ) && ! empty( $value['ID'] ) ) {
		return (int) $value['ID'];
	}
	if ( is_numeric( $value ) ) {
		return (int) $value;
	}
	return 0;
}

/**
 * Image URL from an ACF value (array / ID / URL) or a default theme image.
 *
 * @param mixed  $value        Field value.
 * @param string $default_file File in assets/img/ used when the value is empty.
 * @param string $size         Image size.
 * @return string
 */
function aqua_image_url( $value, $default_file = '', $size = 'full' ) {
	$id = aqua_attachment_id( $value );
	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, $size );
		if ( $url ) {
			return $url;
		}
	}

	if ( is_array( $value ) && ! empty( $value['url'] ) ) {
		return $value['url'];
	}

	if ( is_string( $value ) && '' !== $value && ! is_numeric( $value ) ) {
		if ( preg_match( '#^(https?:)?//#', $value ) || 0 === strpos( $value, '/' ) ) {
			return $value;
		}
		return aqua_img( $value );
	}

	return $default_file ? aqua_img( $default_file ) : '';
}

/**
 * Image URL from a field (or a default theme image).
 *
 * @param string     $name         Field name.
 * @param string     $default_file Default file in assets/img/.
 * @param int|string $post_id      'option' or a post ID.
 * @param string     $size         Image size.
 * @return string
 */
function aqua_image( $name, $default_file = '', $post_id = 'option', $size = 'full' ) {
	return aqua_image_url( aqua_field( $name, '', $post_id ), $default_file, $size );
}

/**
 * <img> tag for an ACF image value. Media Library images get srcset/sizes and their own alt text;
 * default theme images get a plain tag.
 *
 * @param mixed  $value        Field value (array / ID / URL / file name).
 * @param string $default_file Default file in assets/img/.
 * @param string $size         Image size for Media Library images.
 * @param array  $attr         Attributes (class, alt, loading…). Alt is used if the attachment has none.
 * @return string
 */
function aqua_image_tag( $value, $default_file = '', $size = 'large', $attr = array() ) {
	$attr = wp_parse_args(
		$attr,
		array(
			'class'    => '',
			'alt'      => '',
			'loading'  => 'lazy',
			'decoding' => 'async',
		)
	);

	$id = aqua_attachment_id( $value );
	if ( $id && wp_attachment_is_image( $id ) ) {
		$media_alt = trim( (string) get_post_meta( $id, '_wp_attachment_image_alt', true ) );
		if ( '' !== $media_alt ) {
			$attr['alt'] = $media_alt;
		}
		return wp_get_attachment_image( $id, $size, false, $attr );
	}

	$url = aqua_image_url( $value, $default_file, $size );
	if ( ! $url ) {
		return '';
	}

	$html = '<img src="' . esc_url( $url ) . '"';
	foreach ( $attr as $key => $val ) {
		if ( '' === $val && 'alt' !== $key ) {
			continue;
		}
		$html .= ' ' . esc_attr( $key ) . '="' . esc_attr( $val ) . '"';
	}
	return $html . '>';
}

/**
 * Normalizes an ACF Link field (array) or a default into [ url, title, target ].
 *
 * @param mixed $value   Field value.
 * @param array $default Default link: [ 'url' => '', 'title' => '', 'target' => '' ].
 * @return array|null    Null when there is nothing to show.
 */
function aqua_link( $value, $default = array() ) {
	$link = ( is_array( $value ) && ! empty( $value['url'] ) ) ? $value : $default;
	if ( empty( $link['url'] ) || empty( $link['title'] ) ) {
		return null;
	}
	return array(
		'url'    => $link['url'],
		'title'  => $link['title'],
		'target' => ! empty( $link['target'] ) ? $link['target'] : '',
	);
}

/**
 * <a> tag for a link returned by aqua_link().
 *
 * @param array|null $link  Normalized link.
 * @param string     $class CSS classes.
 * @return string
 */
function aqua_link_tag( $link, $class = '' ) {
	if ( ! $link ) {
		return '';
	}
	$target = '';
	if ( '_blank' === $link['target'] ) {
		$target = ' target="_blank" rel="noopener"';
	}
	return '<a class="' . esc_attr( $class ) . '" href="' . esc_url( $link['url'] ) . '"' . $target . '>' . esc_html( $link['title'] ) . '</a>';
}

/**
 * Value for a tel: link (digits and +).
 *
 * @param string $phone Phone number in any format.
 * @return string
 */
function aqua_tel( $phone ) {
	return preg_replace( '/[^0-9+]/', '', (string) $phone );
}

/**
 * Splits a textarea value into non-empty trimmed lines.
 *
 * @param string $text Text.
 * @return string[]
 */
function aqua_lines( $text ) {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) $text ) ) ) );
}

/**
 * Icons offered in the "Icon" select fields (Font Awesome 5 class => label).
 * The admin shows a live preview next to each option (assets/admin/acf-icons.js).
 *
 * @return array
 */
function aqua_icon_choices() {
	return array(
		'fas fa-tint'                 => 'Water drop',
		'fas fa-hand-holding-water'   => 'Hand with water',
		'fas fa-truck'                => 'Truck — delivery',
		'fas fa-shipping-fast'        => 'Fast delivery',
		'fas fa-home'                 => 'Home',
		'fas fa-building'             => 'Office building',
		'fas fa-snowflake'            => 'Snowflake — cooling',
		'fas fa-fire'                 => 'Flame — hot water',
		'fas fa-filter'               => 'Filter',
		'fas fa-flask'                => 'Flask — lab testing',
		'fas fa-vial'                 => 'Test tube',
		'fas fa-award'                => 'Award',
		'fas fa-certificate'          => 'Certificate',
		'fas fa-shield-alt'           => 'Shield — safety',
		'fas fa-leaf'                 => 'Leaf — eco',
		'fas fa-recycle'              => 'Recycle',
		'fas fa-calendar-check'       => 'Calendar — subscription',
		'fas fa-clock'                => 'Clock',
		'fas fa-headset'              => 'Headset — support',
		'fas fa-phone-alt'            => 'Phone',
		'fas fa-smile'                => 'Smile — happy customers',
		'fas fa-users'                => 'People',
		'fas fa-heart'                => 'Heart',
		'fas fa-star'                 => 'Star',
		'fas fa-thumbs-up'            => 'Thumbs up',
		'fas fa-map-marker-alt'       => 'Map marker',
		'fas fa-wallet'               => 'Wallet — prices',
		'fas fa-percent'              => 'Percent — discount',
		'fas fa-check-circle'         => 'Check mark',
	);
}

/**
 * Social networks offered in Theme Settings → Social (slug => label + icon).
 *
 * @return array
 */
function aqua_social_networks() {
	return array(
		'facebook'  => array( 'label' => 'Facebook', 'icon' => 'fab fa-facebook-f' ),
		'instagram' => array( 'label' => 'Instagram', 'icon' => 'fab fa-instagram' ),
		'x'         => array( 'label' => 'X (Twitter)', 'icon' => 'fab fa-twitter' ),
		'linkedin'  => array( 'label' => 'LinkedIn', 'icon' => 'fab fa-linkedin-in' ),
		'youtube'   => array( 'label' => 'YouTube', 'icon' => 'fab fa-youtube' ),
		'tiktok'    => array( 'label' => 'TikTok', 'icon' => 'fab fa-tiktok' ),
		'telegram'  => array( 'label' => 'Telegram', 'icon' => 'fab fa-telegram-plane' ),
		'whatsapp'  => array( 'label' => 'WhatsApp', 'icon' => 'fab fa-whatsapp' ),
	);
}

/**
 * Google Maps embed URL for the contact map (no API key needed).
 * Priority: custom embed URL → map address → contact address.
 *
 * @return string Empty string when the map is turned off.
 */
function aqua_map_embed_url() {
	if ( ! aqua_flag( 'map_show', true ) ) {
		return '';
	}

	$custom = aqua_field( 'map_embed_url', '' );
	if ( $custom ) {
		return $custom;
	}

	$address = aqua_field( 'map_address', aqua_field( 'contact_address', '123 River Street, New York, NY 10001' ) );
	$zoom    = min( 20, max( 1, (int) aqua_field( 'map_zoom', 14 ) ) );

	$lang = substr( get_locale(), 0, 2 );

	return 'https://maps.google.com/maps?q=' . rawurlencode( wp_strip_all_tags( $address ) ) . '&z=' . $zoom . '&hl=' . rawurlencode( $lang ) . '&output=embed';
}

/**
 * Rendered Contact Form 7 form, or an empty string.
 *
 * @param mixed $form Field value: CF7 form post ID or WP_Post.
 * @return string
 */
function aqua_cf7_form( $form ) {
	$id = $form instanceof WP_Post ? $form->ID : (int) $form;
	if ( ! $id || ! function_exists( 'wpcf7_contact_form' ) || ! wpcf7_contact_form( $id ) ) {
		return '';
	}
	return do_shortcode( '[contact-form-7 id="' . $id . '"]' );
}
