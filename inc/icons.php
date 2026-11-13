<?php
/**
 * "Liquid" icon set: hand-drawn SVG icons (48×48 grid, 2px rounded strokes) with live water inside.
 *
 * Every icon has a vessel (the area that holds the water), an outline and optional details.
 * The water is two waves (CSS keeps them rolling, see assets/css/blocks/liquid-icon.css);
 * assets/js/liquid.js makes it slosh on scroll, hover, touch and device tilt.
 *
 * ACF "Icon" fields store the historical Font Awesome class names (e.g. "fas fa-tint"),
 * so those stay the keys of the library — no content migration is needed.
 *
 * @package aqua
 */

/**
 * Icon library.
 *
 * Keys: label (shown in the admin), vessel (closed path holding the water and its outline),
 * details (stroked on top), light (white strokes on top), solid (filled with the tile colour on top),
 * fill (filled with the outline colour), steam (animated wisps),
 * level (y of the water surface), cx (x of the surface pivot), bottom (lowest point of the water).
 *
 * @return array
 */
function aqua_icon_library() {
	static $library = null;
	if ( null !== $library ) {
		return $library;
	}

	$circle = 'M24 6a18 18 0 1 1 0 36a18 18 0 1 1 0-36z';
	$dot    = static function ( $x, $y, $r = 1.7 ) {
		return 'M' . $x . ' ' . ( $y - $r ) . 'a' . $r . ' ' . $r . ' 0 1 1 0 ' . ( 2 * $r ) . 'a' . $r . ' ' . $r . ' 0 1 1 0-' . ( 2 * $r ) . 'z';
	};

	$library = array(
		'fas fa-tint'               => array(
			'label'  => 'Water drop',
			'vessel' => 'M24 5c0 0-13 14.6-13 24a13 13 0 0 0 26 0C37 19.6 24 5 24 5z',
			'light'  => 'M17.5 30.5a7 7 0 0 0 4.5 5.8',
			'level'  => 23,
			'bottom' => 42,
		),
		'fas fa-hand-holding-water' => array(
			'label'   => 'Hand with a water drop',
			'vessel'  => 'M24 4c0 0-8 9-8 15a8 8 0 0 0 16 0c0-6-8-15-8-15z',
			'details' => 'M4 33h5.5c2 0 4-.5 5.8-1.4l1.4-.6h9.3a2.6 2.6 0 0 1 0 5.2H20M20 36.2h7.4l8.8-5.1a2.7 2.7 0 0 1 3.1 4.3L30 41.8c-1.3.8-2.8 1.2-4.3 1.2H4',
			'level'   => 15,
			'bottom'  => 27,
		),
		'fas fa-truck'              => array(
			'label'   => 'Water tanker — delivery',
			'vessel'  => 'M10 12h14a6 6 0 0 1 6 6v8a6 6 0 0 1-6 6H10a6 6 0 0 1-6-6v-8a6 6 0 0 1 6-6z',
			'details' => 'M30 20h6.5l6.5 7.5V35H30M4 35h26M35.5 23v5H42M12 39a3.5 3.5 0 1 0 0-.01M36 39a3.5 3.5 0 1 0 0-.01',
			'light'   => 'M9 17h6',
			'level'   => 21,
			'cx'      => 17,
			'bottom'  => 32,
		),
		'fas fa-shipping-fast'      => array(
			'label'   => 'Bottle on the move — fast delivery',
			'vessel'  => 'M25 5h8v5l4 5v23a5 5 0 0 1-5 5h-6a5 5 0 0 1-5-5V15l4-5V5z',
			'details' => 'M4 19h10M7 27h9M4 35h10',
			'light'   => 'M24.5 20v13',
			'level'   => 20,
			'cx'      => 29,
			'bottom'  => 43,
		),
		'fas fa-home'               => array(
			'label'   => 'House',
			'vessel'  => 'M9 21 24 8l15 13v19a3 3 0 0 1-3 3H12a3 3 0 0 1-3-3V21z',
			'details' => 'M4 24 24 6l20 18M20 43v-8a4 4 0 0 1 8 0v8',
			'level'   => 28,
			'bottom'  => 43,
		),
		'fas fa-building'           => array(
			'label'   => 'Office building',
			'vessel'  => 'M10 8a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10h7a2 2 0 0 1 2 2v23H10V8z',
			'details' => 'M6 43h36M15.5 12h3M22 12h3M15.5 18h3M22 18h3M18 43v-6h4v6',
			'light'   => 'M15.5 25h3M22 25h3M34 27h1M34 33h1',
			'level'   => 23,
			'cx'      => 25,
			'bottom'  => 43,
		),
		'fas fa-snowflake'          => array(
			'label'   => 'Glass of ice water — cooling',
			'vessel'  => 'M11 7h26l-3.2 32.4A4 4 0 0 1 29.8 43H18.2a4 4 0 0 1-4-3.6L11 7z',
			'light'   => 'M17.5 21.5l6-1.8 1.8 6-6 1.8zM26 26.5l5.6.9-.9 5.6-5.6-.9z',
			'level'   => 17,
			'bottom'  => 43,
		),
		'fas fa-fire'               => array(
			'label'   => 'Hot drink — hot water',
			'vessel'  => 'M8 17h26v14a11 11 0 0 1-11 11h-4A11 11 0 0 1 8 31V17z',
			'details' => 'M34 21h2.5a5.5 5.5 0 0 1 0 11H34',
			'steam'   => 'M15 4c-2 2.7 2 5.3 0 8M21 4c-2 2.7 2 5.3 0 8M27 4c-2 2.7 2 5.3 0 8',
			'level'   => 23,
			'cx'      => 21,
			'bottom'  => 42,
		),
		'fas fa-filter'             => array(
			'label'   => 'Filter cartridge',
			'vessel'  => 'M6 8h36L28 25.5V39l-8 5V25.5L6 8z',
			'light'   => 'M21 31h6M21 36h6',
			'level'   => 15,
			'bottom'  => 44,
		),
		'fas fa-flask'              => array(
			'label'   => 'Lab flask — water testing',
			'vessel'  => 'M20 5h8v13l11.2 19a4 4 0 0 1-3.4 6H12.2a4 4 0 0 1-3.4-6L20 18V5z',
			'details' => 'M16.5 5h15',
			'light'   => 'M31.5 31h-3M34.5 36h-3',
			'level'   => 28,
			'bottom'  => 43,
		),
		'fas fa-vial'               => array(
			'label'   => 'Test tube',
			'vessel'  => 'M18.5 5h11v32a5.5 5.5 0 0 1-11 0V5z',
			'details' => 'M15 5h18M29.5 13h-3M29.5 19h-3',
			'light'   => 'M22 25v9',
			'level'   => 21,
			'bottom'  => 42,
		),
		'fas fa-award'              => array(
			'label'   => 'Medal — award',
			'vessel'  => 'M24 19a12 12 0 1 1 0 24a12 12 0 1 1 0-24z',
			'details' => 'M17 21.5 11.5 5H20l4.5 9.5M31 21.5 36.5 5H28',
			'light'   => 'M24 25.5l1.8 3.6 4 .6-2.9 2.8.7 4-3.6-1.9-3.6 1.9.7-4-2.9-2.8 4-.6z',
			'level'   => 31,
			'bottom'  => 43,
		),
		'fas fa-certificate'        => array(
			'label'   => 'Quality certificate',
			'vessel'  => 'M11 4h18l9 9v29a2 2 0 0 1-2 2H11a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z',
			'details' => 'M29 4v9h9M15 13h9M15 19h16',
			'light'   => 'M15 30h9M15 36h6',
			'level'   => 25,
			'bottom'  => 44,
		),
		'fas fa-shield-alt'         => array(
			'label'   => 'Shield — safety',
			'vessel'  => 'M24 4l16 6v12c0 10.2-6.8 18.4-16 22C14.8 40.4 8 32.2 8 22V10l16-6z',
			'details' => 'M17 23.5l5 5 9-10',
			'level'   => 22,
			'bottom'  => 44,
		),
		'fas fa-leaf'               => array(
			'label'   => 'Leaf — eco friendly',
			'vessel'  => 'M40 6c-19 0-31 9-31 23 0 5 1.8 9 5 11.5C31 40.5 40 28 40 6z',
			'details' => 'M7 44c6-11 14-20 25-28',
			'level'   => 24,
			'cx'      => 25,
			'bottom'  => 41,
		),
		'fas fa-recycle'            => array(
			'label'   => 'Recyclable bottle',
			'vessel'  => 'M21 11h6v3.5l3 4V34a3 3 0 0 1-3 3h-6a3 3 0 0 1-3-3V18.5l3-4V11z',
			'details' => 'M8 22a16.5 16.5 0 0 1 26.5-12.6M40 26a16.5 16.5 0 0 1-26.5 12.6M35 4.5v5.5h-5.5M13 43.5V38h5.5',
			'level'   => 24,
			'bottom'  => 37,
		),
		'fas fa-calendar-check'     => array(
			'label'   => 'Calendar — subscription',
			'vessel'  => 'M7 13a3 3 0 0 1 3-3h28a3 3 0 0 1 3 3v27a3 3 0 0 1-3 3H10a3 3 0 0 1-3-3V13z',
			'details' => 'M7 19h34M16 5v8M32 5v8M17 31l5 5 9-9',
			'level'   => 29,
			'bottom'  => 43,
		),
		'fas fa-clock'              => array(
			'label'   => 'Clock',
			'vessel'  => $circle,
			'details' => 'M24 13v11l7 5',
			'light'   => 'M24 37v1.5M11 24h1.5M35.5 24H37',
			'level'   => 27,
			'bottom'  => 42,
		),
		'fas fa-headset'            => array(
			'label'   => 'Chat bubble — support',
			'vessel'  => 'M10 7h28a4 4 0 0 1 4 4v18a4 4 0 0 1-4 4H22l-9 8v-8h-3a4 4 0 0 1-4-4V11a4 4 0 0 1 4-4z',
			'fill'    => $dot( 16, 20 ) . $dot( 24, 20 ) . $dot( 32, 20 ),
			'level'   => 22,
			'bottom'  => 41,
		),
		'fas fa-phone-alt'          => array(
			'label'   => 'Smartphone — call us',
			'vessel'  => 'M17 3h14a3 3 0 0 1 3 3v36a3 3 0 0 1-3 3H17a3 3 0 0 1-3-3V6a3 3 0 0 1 3-3z',
			'details' => 'M21 8h6',
			'light'   => 'M22 40h4',
			'level'   => 22,
			'bottom'  => 45,
		),
		'fas fa-smile'              => array(
			'label'   => 'Smile — happy customers',
			'vessel'  => $circle,
			'details' => 'M17.5 18.5v2.5M30.5 18.5v2.5M16 28a9.5 9.5 0 0 0 16 0',
			'level'   => 29,
			'bottom'  => 42,
		),
		'fas fa-users'              => array(
			'label'   => 'People',
			'vessel'  => 'M19 7.5a7.5 7.5 0 1 1 0 15a7.5 7.5 0 1 1 0-15zM5 42v-3.5A10.5 10.5 0 0 1 15.5 28h7A10.5 10.5 0 0 1 33 38.5V42H5z',
			'details' => 'M31 8a7 7 0 0 1 0 13.5M37 28.5a9.5 9.5 0 0 1 6.5 9V42',
			'level'   => 33,
			'cx'      => 19,
			'bottom'  => 42,
		),
		'fas fa-heart'              => array(
			'label'   => 'Heart',
			'vessel'  => 'M24 41S6 30.5 6 17.5a9.5 9.5 0 0 1 18-4.2 9.5 9.5 0 0 1 18 4.2C42 30.5 24 41 24 41z',
			'light'   => 'M12 17a5 5 0 0 1 4-4.5',
			'level'   => 24,
			'bottom'  => 41,
		),
		'fas fa-star'               => array(
			'label'   => 'Star',
			'vessel'  => 'M24 5l5.9 12 13.1 1.9-9.5 9.3 2.3 13.1L24 35.1l-11.8 6.2 2.3-13.1L5 18.9l13.1-1.9z',
			'level'   => 25,
			'bottom'  => 41,
		),
		'fas fa-thumbs-up'          => array(
			'label'   => 'Thumbs up',
			'vessel'  => 'M15 21l6.6-13.2A3.6 3.6 0 0 1 27 11l-1.3 8H37a4 4 0 0 1 3.9 4.7l-2.5 14A4 4 0 0 1 34.5 41H15V21zM6 23a2 2 0 0 1 2-2h7v20H8a2 2 0 0 1-2-2V23z',
			'details' => 'M15 21v20',
			'level'   => 30,
			'cx'      => 23,
			'bottom'  => 41,
		),
		'fas fa-map-marker-alt'     => array(
			'label'   => 'Map pin',
			'vessel'  => 'M24 44S10 31.5 10 19a14 14 0 0 1 28 0c0 12.5-14 25-14 25z',
			'solid'   => 'M24 13.5a5.5 5.5 0 1 1 0 11a5.5 5.5 0 1 1 0-11z',
			'level'   => 23,
			'bottom'  => 44,
		),
		'fas fa-wallet'             => array(
			'label'   => 'Wallet — prices',
			'vessel'  => 'M6 14a4 4 0 0 1 4-4h25v6h3a4 4 0 0 1 4 4v18a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4V14z',
			'details' => 'M6 14.5a4 4 0 0 0 4 4h28',
			'solid'   => 'M42 24h-7a4 4 0 0 0 0 8h7v-8z',
			'level'   => 28,
			'bottom'  => 42,
		),
		'fas fa-percent'            => array(
			'label'   => 'Price tag — discount',
			'vessel'  => 'M6 8v13.2a2 2 0 0 0 .6 1.4l19.8 19.8a2 2 0 0 0 2.8 0l13.2-13.2a2 2 0 0 0 0-2.8L22.6 6.6A2 2 0 0 0 21.2 6H8a2 2 0 0 0-2 2z',
			'details' => 'M23 31l8-8',
			'fill'    => $dot( 23.5, 24.5 ) . $dot( 30.5, 30.5 ),
			'solid'   => 'M14 11.5a2.5 2.5 0 1 1 0 5a2.5 2.5 0 1 1 0-5z',
			'level'   => 25,
			'bottom'  => 42,
		),
		'fas fa-check-circle'       => array(
			'label'   => 'Check mark',
			'vessel'  => $circle,
			'details' => 'M16 24.5l6 6 11-12',
			'level'   => 26,
			'bottom'  => 42,
		),
		'fas fa-envelope'           => array(
			'label'   => 'Envelope',
			'vessel'  => 'M8 9h32a3 3 0 0 1 3 3v24a3 3 0 0 1-3 3H8a3 3 0 0 1-3-3V12a3 3 0 0 1 3-3z',
			'details' => 'M6 11.5 24 25l18-13.5',
			'level'   => 27,
			'bottom'  => 39,
		),
	);

	return $library;
}

/**
 * Icons offered in ACF "Icon" select fields (value => label).
 *
 * @return array
 */
function aqua_icon_choices() {
	$choices = array();
	foreach ( aqua_icon_library() as $key => $icon ) {
		if ( 'fas fa-envelope' === $key ) {
			continue; // Used by the contact cards only.
		}
		$choices[ $key ] = $icon['label'];
	}
	return $choices;
}

/**
 * Closed wave path (surface at y = 0, body below), wide enough to roll one period
 * and to stay inside the vessel when the water is tilted.
 *
 * @param float $amp    Wave height.
 * @param float $period Wave length.
 * @param float $reach  Half width covered on each side of the pivot.
 * @param float $depth  Depth of the body; 0 = surface line only (open path).
 * @return string
 */
function aqua_liquid_wave( $amp, $period, $reach = 60, $depth = 64 ) {
	$half  = $period / 2;
	$start = -$reach - $period;
	$count = (int) ceil( ( 2 * $reach + $period ) / $half );

	$d = 'M' . $start . ' 0q' . ( $period / 4 ) . ' ' . ( -$amp ) . ' ' . $half . ' 0';
	for ( $i = 1; $i < $count; $i++ ) {
		$d .= 't' . $half . ' 0';
	}
	return $depth ? $d . 'V' . $depth . 'H' . $start . 'z' : $d;
}

/**
 * Liquid icon markup (inline SVG). Unknown values fall back to a Font Awesome <i>.
 *
 * @param string $value ACF value / library key, e.g. "fas fa-tint".
 * @param string $class Extra class(es) for the <svg>.
 * @return string
 */
function aqua_icon( $value, $class = '' ) {
	static $uid = 0;

	$library = aqua_icon_library();
	$value   = trim( (string) $value );

	if ( ! isset( $library[ $value ] ) ) {
		return $value ? '<i class="' . esc_attr( trim( $value . ' ' . $class ) ) . '" aria-hidden="true"></i>' : '';
	}

	$icon   = wp_parse_args(
		$library[ $value ],
		array(
			'details' => '',
			'light'   => '',
			'solid'   => '',
			'fill'    => '',
			'steam'   => '',
			'cx'      => 24,
			'level'   => 24,
			'bottom'  => 42,
		)
	);
	$id     = 'aqua-liquid-' . ( ++$uid );
	$cx     = (float) $icon['cx'];
	$level  = (float) $icon['level'];
	$bottom = (float) $icon['bottom'];
	$rise   = max( 4, ( $bottom - $level ) * 0.75 );

	static $waves = null;
	if ( null === $waves ) {
		$waves = array(
			'back'  => aqua_liquid_wave( 2.2, 20 ),
			'front' => aqua_liquid_wave( 1.6, 16 ),
		);
	}

	$stroke = ' stroke-width="2" stroke-linecap="round" stroke-linejoin="round"';

	$svg  = '<svg class="' . esc_attr( trim( 'liquid-icon ' . $class ) ) . '" viewBox="0 0 48 48" fill="none" aria-hidden="true" focusable="false"';
	$svg .= ' data-cx="' . esc_attr( $cx ) . '" data-level="' . esc_attr( $level ) . '" style="--liquid-rise:' . esc_attr( round( $rise, 1 ) ) . 'px">';
	$svg .= '<defs><clipPath id="' . esc_attr( $id ) . '"><path d="' . esc_attr( $icon['vessel'] ) . '"/></clipPath></defs>';
	$svg .= '<path class="liquid-icon__vessel" d="' . esc_attr( $icon['vessel'] ) . '" fill="#00d1f9" fill-opacity=".08"/>';
	$svg .= '<g clip-path="url(#' . esc_attr( $id ) . ')">';
	$svg .= '<g class="liquid-icon__liquid" transform="translate(' . esc_attr( $cx ) . ' ' . esc_attr( $level ) . ')">';
	$svg .= '<path class="liquid-icon__wave liquid-icon__wave--back" d="' . esc_attr( $waves['back'] ) . '" fill="#19409a" fill-opacity=".35"/>';
	$svg .= '<path class="liquid-icon__wave liquid-icon__wave--front" d="' . esc_attr( $waves['front'] ) . '" fill="#00d1f9"/>';
	$svg .= '</g>';
	foreach ( array( array( -5, 1.3 ), array( 4, 1 ), array( -0.5, 0.75 ) ) as $bubble ) {
		$svg .= '<circle class="liquid-icon__bubble" cx="' . esc_attr( $cx + $bubble[0] ) . '" cy="' . esc_attr( $bottom - 3 ) . '" r="' . esc_attr( $bubble[1] ) . '" fill="#fff"/>';
	}
	$svg .= '</g>';
	$svg .= '<path class="liquid-icon__outline" d="' . esc_attr( $icon['vessel'] ) . '" stroke="currentColor"' . $stroke . '/>';
	if ( $icon['solid'] ) {
		$svg .= '<path class="liquid-icon__solid" d="' . esc_attr( $icon['solid'] ) . '" fill="#fff" stroke="currentColor"' . $stroke . '/>';
	}
	if ( $icon['light'] ) {
		$svg .= '<path class="liquid-icon__light" d="' . esc_attr( $icon['light'] ) . '" stroke="#fff" stroke-opacity=".85"' . $stroke . '/>';
	}
	if ( $icon['details'] ) {
		$svg .= '<path class="liquid-icon__detail" d="' . esc_attr( $icon['details'] ) . '" stroke="currentColor"' . $stroke . '/>';
	}
	if ( $icon['fill'] ) {
		$svg .= '<path class="liquid-icon__dots" d="' . esc_attr( $icon['fill'] ) . '" fill="currentColor"/>';
	}
	if ( $icon['steam'] ) {
		$svg .= '<path class="liquid-icon__steam" d="' . esc_attr( $icon['steam'] ) . '" stroke="currentColor" stroke-opacity=".55"' . $stroke . '/>';
	}
	$svg .= '</svg>';

	return $svg;
}
