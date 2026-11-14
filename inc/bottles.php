<?php
/**
 * Drawn water bottles (SVG on a 160×320 grid) with live water inside.
 *
 * Used by the price list (one per product) and the services section (the big centre bottle).
 * The water is HTML layers masked to the bottle (GPU-animated waves + bubbles, bottle.css); assets/js/liquid.js
 * tilts the bottle towards the mouse, wobbles it on tap and keeps the water level with the ground.
 *
 * @package aqua
 */

/**
 * Bottle shapes.
 *
 * Keys: label (admin), body (closed outline = the vessel that holds the water), level (y of the water surface),
 * cx (x of the surface pivot), bottom (lowest point inside), label_box (x, y, w, h of the sticker),
 * ribs (y positions of the moulded rings, with their x range), highlights (white glare strokes),
 * x (left and right edge of the body), cap (cap, collar, ridges, rim), handle (drawn behind the body).
 *
 * @return array
 */
function aqua_bottle_shapes() {
	static $shapes = null;
	if ( null !== $shapes ) {
		return $shapes;
	}

	// Vertical ridges of a cap: x from, x to, step, y top, y bottom.
	$ridges = static function ( $x1, $x2, $step, $y1, $y2 ) {
		$d = '';
		for ( $x = $x1; $x <= $x2; $x += $step ) {
			$d .= 'M' . $x . ' ' . $y1 . 'V' . $y2;
		}
		return $d;
	};

	$shapes = array(
		'small'  => array(
			'label'      => '0.5 L sport bottle',
			'body'       => 'M69 62h22c0 13 15 20 15 38v58c0 9-4 14-4 24s4 15 4 24v76c0 9-7 16-16 16H70c-9 0-16-7-16-16v-76c0-9 4-14 4-24s-4-15-4-24v-58c0-18 15-25 15-38z',
			'level'      => 142,
			'bottom'     => 296,
			'x'          => array( 54, 106 ),
			'label_box'  => array( 57, 216, 46, 54 ),
			'ribs'       => array( array( 172, 61, 99 ), array( 182, 60, 100 ), array( 192, 61, 99 ) ),
			'highlights' => 'M61.5 110v42M61.5 216v58',
			'shine'      => 'M98.5 112v28',
			'cap'        => array(
				'collar' => 'M66 54h28a3 3 0 0 1 3 3v4a3 3 0 0 1-3 3H66a3 3 0 0 1-3-3v-4a3 3 0 0 1 3-3z',
				'cap'    => 'M68 33h24a4 4 0 0 1 4 4v17H64V37a4 4 0 0 1 4-4zM73 33v-9a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v9z',
				'ridges' => $ridges( 69, 91, 4, 38, 50 ),
				'top'    => 'M76 18h8',
				'rim'    => 'M67 35.5h26',
			),
		),
		'family' => array(
			'label'      => '1.5 L family bottle',
			'body'       => 'M70 58h20v6c0 14 24 22 24 46v40c0 8-4 12-4 20s4 12 4 20v92c0 9-7 16-16 16H62c-9 0-16-7-16-16v-92c0-8 4-12 4-20s-4-12-4-20v-40c0-24 24-32 24-46z',
			'level'      => 132,
			'bottom'     => 296,
			'x'          => array( 46, 114 ),
			'label_box'  => array( 50, 204, 60, 52 ),
			'ribs'       => array( array( 165, 52, 108 ), array( 175, 52, 108 ), array( 268, 48, 112 ) ),
			'highlights' => 'M54.5 118v30M54.5 194v80',
			'shine'      => 'M106 122v24',
			'cap'        => array(
				'collar' => 'M65 50h30a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3H65a3 3 0 0 1-3-3v-3a3 3 0 0 1 3-3z',
				'cap'    => 'M68 24h24a4 4 0 0 1 4 4v22H64V28a4 4 0 0 1 4-4z',
				'ridges' => $ridges( 68, 92, 4, 29, 46 ),
				'top'    => '',
				'rim'    => 'M67 26.5h26',
			),
		),
		'jug'    => array(
			'label'      => '5 L jug with handle',
			'body'       => 'M68 52h24v8c0 12 34 18 34 46v176c0 10-8 18-18 18H52c-10 0-18-8-18-18V106c0-28 34-34 34-46z',
			'level'      => 146,
			'bottom'     => 298,
			'x'          => array( 34, 126 ),
			'label_box'  => array( 45, 178, 70, 62 ),
			'ribs'       => array( array( 152, 38, 122 ), array( 264, 36, 124 ) ),
			'highlights' => 'M43.5 124v140',
			'shine'      => 'M117 132v20',
			'handle'     => 'M100 71c17-13 35-6 35 14v26c0 8-3 13-9 16',
			'cap'        => array(
				'collar' => 'M65 46h30a3 3 0 0 1 3 3v3a3 3 0 0 1-3 3H65a3 3 0 0 1-3-3v-3a3 3 0 0 1 3-3z',
				'cap'    => 'M68 26h24a4 4 0 0 1 4 4v18H64V30a4 4 0 0 1 4-4z',
				'ridges' => $ridges( 68, 92, 4, 31, 44 ),
				'top'    => '',
				'rim'    => 'M67 28.5h26',
			),
		),
		'cooler' => array(
			'label'      => '19 L cooler bottle',
			'body'       => 'M66 44h28v20c0 22 46 26 46 58v158c0 12-10 22-22 22H42c-12 0-22-10-22-22V122c0-32 46-36 46-58z',
			'level'      => 140,
			'bottom'     => 300,
			'x'          => array( 20, 140 ),
			'label_box'  => array( 43, 174, 74, 60 ),
			'ribs'       => array( array( 148, 22, 138 ), array( 154, 22, 138 ), array( 256, 22, 138 ), array( 262, 22, 138 ) ),
			'highlights' => 'M31.5 130v150',
			'shine'      => 'M129 136v44',
			'cap'        => array(
				'collar' => 'M62 36h36a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3H62a3 3 0 0 1-3-3v-6a3 3 0 0 1 3-3z',
				'cap'    => 'M63 10h34a5 5 0 0 1 5 5v22H58V15a5 5 0 0 1 5-5z',
				'ridges' => $ridges( 64, 96, 4, 16, 32 ),
				'top'    => '',
				'rim'    => 'M62 13h36',
			),
		),
	);

	return $shapes;
}

/**
 * Options of the ACF "Bottle" select fields.
 *
 * @return array
 */
function aqua_bottle_choices() {
	$choices = array( 'auto' => 'Automatic — by volume' );
	foreach ( aqua_bottle_shapes() as $key => $shape ) {
		$choices[ $key ] = 'Drawn: ' . $shape['label'];
	}
	$choices['image'] = 'Uploaded image instead of a drawing';
	return $choices;
}

/**
 * Bottle shape for a field value: an explicit shape, "image", or picked by the volume text.
 *
 * @param string $choice Field value.
 * @param string $volume Volume text, e.g. "1.5 L" or "500 ml".
 * @return string Shape key or "image".
 */
function aqua_bottle_shape( $choice, $volume = '' ) {
	if ( 'image' === $choice || isset( aqua_bottle_shapes()[ $choice ] ) ) {
		return $choice;
	}

	$liters = (float) str_replace( ',', '.', preg_replace( '/[^0-9.,]/', '', (string) $volume ) );
	if ( preg_match( '/\bml\b/i', (string) $volume ) ) {
		$liters /= 1000;
	}

	if ( ! $liters ) {
		return 'cooler';
	}
	if ( $liters < 1 ) {
		return 'small';
	}
	if ( $liters < 3 ) {
		return 'family';
	}
	return $liters < 10 ? 'jug' : 'cooler';
}

/**
 * Drawn bottle markup.
 *
 * Layers: __back (handle + empty plastic, SVG) → __water (HTML masked to the body: far surface,
 * wave, surface glint, light on the bottom, bubbles — moved only with transform / opacity, so the
 * GPU animates them) → __front (depth, cylinder shading, rings, outline, glare, sticker, cap — SVG).
 * liquid.js rotates __body (tilt / wobble) and counter-rotates __tilt so the water stays level.
 *
 * @param string $shape Shape key (see aqua_bottle_shapes()).
 * @param string $label Text on the sticker, e.g. "19 L".
 * @param string $class Extra class(es) for the wrapper.
 * @return string
 */
function aqua_bottle( $shape, $label = '', $class = '' ) {
	static $uid = 0;

	$shapes = aqua_bottle_shapes();
	if ( ! isset( $shapes[ $shape ] ) ) {
		$shape = 'cooler';
	}
	$b  = wp_parse_args(
		$shapes[ $shape ],
		array(
			'cx'     => 80,
			'handle' => '',
		)
	);
	$id = 'aqua-bottle-' . ( ++$uid );

	$cx     = (float) $b['cx'];
	$level  = (float) $b['level'];
	$bottom = (float) $b['bottom'];

	list( $left, $right )      = $b['x'];
	list( $lx, $ly, $lw, $lh ) = $b['label_box'];
	$bend                      = round( $lw * 0.07, 1 ); // How much the sticker edges curve around the bottle.

	$style = '--liquid-mask:' . aqua_svg_mask_url( '0 0 160 320', $b['body'] ) . ';--liquid-cx:' . aqua_grid_pct( $cx, 160 ) . ';--liquid-level:' . aqua_grid_pct( $level, 320 ) . ';--liquid-floor:' . aqua_grid_pct( $bottom, 320 );

	$html = '<div class="' . esc_attr( trim( 'bottle bottle--' . $shape . ' ' . $class ) ) . '"><span class="bottle__shadow" aria-hidden="true"></span>';
	$html .= '<div class="bottle__body" style="' . esc_attr( $style ) . '" data-cx="' . esc_attr( $cx ) . '" data-level="' . esc_attr( $level ) . '" data-size="320" data-scale="3" aria-hidden="true">';

	// ---------- Back: handle + empty plastic ----------
	$html .= '<svg class="bottle__back" viewBox="0 0 160 320" focusable="false">';
	$html .= '<defs><linearGradient id="' . $id . '-plastic" gradientUnits="userSpaceOnUse" x1="' . $left . '" x2="' . $right . '" y1="0" y2="0"><stop offset="0" stop-color="#8eeaff" stop-opacity=".75"/><stop offset=".3" stop-color="#eefdff" stop-opacity=".5"/><stop offset=".7" stop-color="#d9f7ff" stop-opacity=".45"/><stop offset="1" stop-color="#5fd5f2" stop-opacity=".8"/></linearGradient></defs>';
	if ( $b['handle'] ) {
		$html .= '<path d="' . esc_attr( $b['handle'] ) . '" fill="none" stroke="#7fdcf4" stroke-width="11" stroke-linecap="round"/>';
		$html .= '<path d="' . esc_attr( $b['handle'] ) . '" fill="none" stroke="#d8f8ff" stroke-width="8" stroke-linecap="round"/>';
		$html .= '<path d="' . esc_attr( $b['handle'] ) . '" fill="none" stroke="#fff" stroke-opacity=".9" stroke-width="2" stroke-linecap="round" transform="translate(-1.5 -1.5)"/>';
	}
	$html .= '<path d="' . esc_attr( $b['body'] ) . '" fill="url(#' . $id . '-plastic)"/>';
	$html .= '</svg>';

	// ---------- Water (HTML, masked to the body) ----------
	$html .= '<div class="bottle__water"><div class="bottle__tilt">';
	$html .= '<span class="bottle__wave bottle__wave--back"></span><span class="bottle__wave bottle__wave--front"></span><span class="bottle__wave bottle__wave--surface"></span>';
	$html .= '</div>';
	$html .= '<span class="bottle__caustic"></span><span class="bottle__caustic bottle__caustic--slow"></span>';
	$span     = $right - $left;
	$bubbles  = array();
	$offsets  = array( array( -0.3, 2.6 ), array( 0.22, 1.8 ), array( -0.06, 3.4 ), array( 0.34, 2 ) );
	foreach ( $offsets as $offset ) {
		$bubbles[] = array( $cx + $offset[0] * $span, $bottom - 8, $offset[1] );
	}
	$html .= aqua_liquid_bubbles( 'bottle__bubble', $bubbles, ( $bottom - $level ) * 0.8, 160, 320 );
	$html .= '</div>';

	// ---------- Front: shading, rings, outline, glare, sticker, cap ----------
	$html .= '<svg class="bottle__front" viewBox="0 0 160 320" focusable="false">';
	$html .= '<defs>';
	$html .= '<clipPath id="' . $id . '-clip"><path d="' . esc_attr( $b['body'] ) . '"/></clipPath>';
	$html .= '<linearGradient id="' . $id . '-depth" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="' . $level . '" y2="' . $bottom . '"><stop offset="0" stop-color="#002f80" stop-opacity="0"/><stop offset="1" stop-color="#002f80" stop-opacity=".3"/></linearGradient>';
	$html .= '<linearGradient id="' . $id . '-shade" gradientUnits="userSpaceOnUse" x1="' . $left . '" x2="' . $right . '" y1="0" y2="0"><stop offset="0" stop-color="#002a6e" stop-opacity=".3"/><stop offset=".16" stop-color="#002a6e" stop-opacity="0"/><stop offset=".3" stop-color="#fff" stop-opacity=".22"/><stop offset=".42" stop-color="#fff" stop-opacity="0"/><stop offset=".82" stop-color="#002a6e" stop-opacity="0"/><stop offset="1" stop-color="#002a6e" stop-opacity=".32"/></linearGradient>';
	$html .= '<linearGradient id="' . $id . '-glare" gradientUnits="userSpaceOnUse" x1="0" x2="0" y1="' . ( $level - 40 ) . '" y2="' . ( $bottom - 6 ) . '"><stop offset="0" stop-color="#fff" stop-opacity="0"/><stop offset=".15" stop-color="#fff" stop-opacity=".85"/><stop offset=".8" stop-color="#fff" stop-opacity=".6"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></linearGradient>';
	$html .= '<linearGradient id="' . $id . '-cap" x1="0" x2="1" y1="0" y2="0"><stop offset="0" stop-color="#1d43a6"/><stop offset=".3" stop-color="#4b7cf0"/><stop offset=".55" stop-color="#2f5fd0"/><stop offset="1" stop-color="#132f7a"/></linearGradient>';
	$html .= '<linearGradient id="' . $id . '-sticker" gradientUnits="userSpaceOnUse" x1="' . $lx . '" x2="' . ( $lx + $lw ) . '" y1="0" y2="0"><stop offset="0" stop-color="#cfe8f2"/><stop offset=".22" stop-color="#fff"/><stop offset=".7" stop-color="#fff"/><stop offset="1" stop-color="#c4e2ee"/></linearGradient>';
	$html .= '</defs>';

	// Depth of the water and the round shape of the bottle.
	$html .= '<g clip-path="url(#' . $id . '-clip)">';
	$html .= '<rect x="0" y="' . $level . '" width="160" height="' . ( 320 - $level ) . '" fill="url(#' . $id . '-depth)"/>';
	$html .= '<rect x="0" y="0" width="160" height="320" fill="url(#' . $id . '-shade)"/>';
	$html .= '</g>';

	// Moulded rings (curved: the bottle is seen slightly from above).
	foreach ( $b['ribs'] as $rib ) {
		list( $y, $x1, $x2 ) = $rib;
		$ring  = 'M' . $x1 . ' ' . $y . 'Q' . $cx . ' ' . ( $y + 2 * round( ( $x2 - $x1 ) * 0.06, 1 ) ) . ' ' . $x2 . ' ' . $y;
		$html .= '<path d="' . $ring . '" fill="none" stroke="#0a5f96" stroke-opacity=".22" stroke-width="2.6"/>';
		$html .= '<path d="' . $ring . '" fill="none" stroke="#fff" stroke-opacity=".6" stroke-width="1.2" transform="translate(0 -1.8)"/>';
	}

	// Base: the thick bottom of the bottle.
	$base_y = $bottom - 9;
	$html  .= '<path d="M' . ( $left + 9 ) . ' ' . $base_y . 'Q' . $cx . ' ' . ( $base_y + 10 ) . ' ' . ( $right - 9 ) . ' ' . $base_y . '" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="1.4"/>';

	// Outline + glare.
	$html .= '<path d="' . esc_attr( $b['body'] ) . '" fill="none" stroke="#6fd6f2" stroke-width="1.6"/>';
	$html .= '<path d="' . esc_attr( $b['highlights'] ) . '" stroke="url(#' . $id . '-glare)" stroke-width="5" stroke-linecap="round"/>';
	$html .= '<path d="' . esc_attr( $b['shine'] ) . '" stroke="#fff" stroke-opacity=".55" stroke-width="2.5" stroke-linecap="round"/>';

	// Sticker wrapped around the bottle.
	$top    = 'M' . $lx . ' ' . $ly . 'Q' . $cx . ' ' . ( $ly + 2 * $bend ) . ' ' . ( $lx + $lw ) . ' ' . $ly;
	$html  .= '<path d="' . $top . 'V' . ( $ly + $lh ) . 'Q' . $cx . ' ' . ( $ly + $lh + 2 * $bend ) . ' ' . $lx . ' ' . ( $ly + $lh ) . 'z" fill="url(#' . $id . '-sticker)"/>';
	$html  .= '<path d="' . $top . 'v9Q' . $cx . ' ' . ( $ly + 9 + 2 * $bend ) . ' ' . $lx . ' ' . ( $ly + 9 ) . 'z" fill="#19409a"/>';
	$html  .= '<path d="M' . $lx . ' ' . ( $ly + $lh - 3 ) . 'Q' . $cx . ' ' . ( $ly + $lh - 3 + 2 * $bend ) . ' ' . ( $lx + $lw ) . ' ' . ( $ly + $lh - 3 ) . '" fill="none" stroke="#00d1f9" stroke-width="3.5"/>';
	$size   = min( 17, round( $lw * 0.25 ) );
	$drop_y = $ly + 12 + $bend + $size * 0.55;
	$drop_r = $size * 0.42;
	$html  .= '<path d="M' . $cx . ' ' . ( $drop_y - $drop_r * 1.7 ) . 'c0 0-' . $drop_r . ' ' . ( $drop_r * 1.1 ) . '-' . $drop_r . ' ' . ( $drop_r * 1.75 ) . 'a' . $drop_r . ' ' . $drop_r . ' 0 0 0 ' . ( 2 * $drop_r ) . ' 0c0-' . ( $drop_r * 0.65 ) . '-' . $drop_r . '-' . ( $drop_r * 1.75 ) . '-' . $drop_r . '-' . ( $drop_r * 1.75 ) . 'z" fill="#00c4ef"/>';
	if ( $label ) {
		$html .= '<text x="' . $cx . '" y="' . round( $drop_y + $drop_r + $size + 1, 1 ) . '" text-anchor="middle" font-family="Open Sans, Arial, sans-serif" font-size="' . $size . '" font-weight="800" letter-spacing=".3" fill="#19409a">' . esc_html( $label ) . '</text>';
	}

	// Cap.
	$cap   = $b['cap'];
	$html .= '<path d="' . esc_attr( $cap['collar'] ) . '" fill="#1a40a0"/>';
	$html .= '<path d="' . esc_attr( $cap['cap'] ) . '" fill="url(#' . $id . '-cap)"/>';
	$html .= '<path d="' . esc_attr( $cap['ridges'] ) . '" stroke="#fff" stroke-opacity=".28" stroke-width="1.4" stroke-linecap="round"/>';
	$html .= '<path d="' . esc_attr( $cap['rim'] ) . '" stroke="#fff" stroke-opacity=".45" stroke-width="1.6" stroke-linecap="round"/>';
	if ( $cap['top'] ) {
		$html .= '<path d="' . esc_attr( $cap['top'] ) . '" stroke="#15378a" stroke-width="4" stroke-linecap="round"/>';
	}
	$html .= '</svg>';

	return $html . '</div></div>';
}
