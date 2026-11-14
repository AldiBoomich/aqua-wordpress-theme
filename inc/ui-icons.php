<?php
/**
 * Interface icons: one hand-drawn line set (24 × 24 grid, 1.5px strokes that stay 1.5px at any size,
 * round caps, currentColor). Social marks are simplified to the same line style.
 *
 * Usage: echo aqua_ui_icon( 'phone', 'block__icon' );
 *
 * @package aqua
 */

defined( 'ABSPATH' ) || exit;

/**
 * Icon shapes (inner SVG markup).
 *
 * @return array<string,string>
 */
function aqua_ui_icons() {
	return array(
		// Brand: a drop with a water line inside (the line is coloured by CSS: .ui-icon__water).
		'drop'      => '<path d="M12 2.6c-3.3 4.2-6.6 8-6.6 11.6a6.6 6.6 0 0 0 13.2 0C18.6 10.6 15.3 6.8 12 2.6Z"/><path class="ui-icon__water" d="M7.7 14.6c1.45-.95 2.85-.95 4.3 0s2.85.95 4.3 0"/>',
		'phone'     => '<path d="M5.1 3.75h3.05l1.55 3.9-1.95 1.25a10.6 10.6 0 0 0 7.35 7.35l1.25-1.95 3.9 1.55v3.05a1.75 1.75 0 0 1-1.9 1.75A15.9 15.9 0 0 1 3.35 5.65a1.75 1.75 0 0 1 1.75-1.9Z"/>',
		'mail'      => '<rect x="3.25" y="5.25" width="17.5" height="13.5" rx="2.5"/><path d="m4.25 7 7.75 6 7.75-6"/>',
		'pin'       => '<path d="M12 21.25s-6.75-5.6-6.75-11a6.75 6.75 0 0 1 13.5 0c0 5.4-6.75 11-6.75 11Z"/><circle cx="12" cy="10.25" r="2.5"/>',
		'clock'     => '<circle cx="12" cy="12" r="8.75"/><path d="M12 7.25V12l3.25 2"/>',
		'chevron'   => '<path d="m6.5 9.5 5.5 5.5 5.5-5.5"/>',
		'arrow-up'  => '<path d="M12 19.25V4.75M6.25 10.5 12 4.75l5.75 5.75"/>',
		'close'     => '<path d="m6.25 6.25 11.5 11.5M17.75 6.25 6.25 17.75"/>',
		'pause'     => '<path d="M9 6.25v11.5M15 6.25v11.5"/>',
		'play'      => '<path d="M8.75 6.1v11.8L18.25 12Z"/>',
		'search'    => '<circle cx="10.75" cy="10.75" r="6.5"/><path d="m15.5 15.5 4.75 4.75"/>',
		// Social (simplified line marks).
		'facebook'  => '<path d="M14.75 3.75h-1.6a4 4 0 0 0-4 4v2.75H6.75v3.25h2.4v6.5h3.35v-6.5h2.6l.45-3.25H12.5V8.4c0-.6.45-1.05 1.05-1.05h1.2Z"/>',
		'instagram' => '<rect x="3.75" y="3.75" width="16.5" height="16.5" rx="5"/><circle cx="12" cy="12" r="3.75"/><path d="M17.1 6.9h.01"/>',
		'x'         => '<path d="M4.5 4.5h4.25l10.75 15h-4.25Z"/><path d="m19 4.5-5.9 6.35M10.9 13.15 5 19.5"/>',
		'linkedin'  => '<rect x="3.75" y="3.75" width="16.5" height="16.5" rx="3.5"/><path d="M8.25 10.5v5.75M8.25 7.75v.01M11.75 16.25V10.5M11.75 13c0-1.5 1.05-2.5 2.35-2.5s2.15.9 2.15 2.45v3.3"/>',
		'youtube'   => '<rect x="2.75" y="5.75" width="18.5" height="12.5" rx="4"/><path d="m10.25 9.25 4.5 2.75-4.5 2.75Z"/>',
		'tiktok'    => '<path d="M13.75 3.75v10.5a3.75 3.75 0 1 1-3.75-3.75M13.75 3.75a4.75 4.75 0 0 0 4.75 4.75"/>',
		'telegram'  => '<path d="m20.5 4.25-17 6.6 5.6 2.05 2.1 6.15 3.05-4 4.55 3.45Z"/><path d="m9.1 12.9 7.15-5.15"/>',
		'whatsapp'  => '<path d="m3.75 20.25 1.3-3.85a8.5 8.5 0 1 1 2.6 2.6Z"/><path d="M9.15 8.6c.1 3.3 2.95 6.15 6.25 6.25l.95-1.65-1.9-.95-.95.95a4.1 4.1 0 0 1-2.7-2.7l.95-.95-.95-1.9Z"/>',
	);
}

/**
 * Inline SVG icon. Decorative by default (aria-hidden); pass $title for a meaningful icon.
 *
 * @param string $name  Icon name from aqua_ui_icons().
 * @param string $class Extra classes.
 * @param string $title Accessible name (optional).
 * @return string
 */
function aqua_ui_icon( $name, $class = '', $title = '' ) {
	$icons = aqua_ui_icons();
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	$a11y = $title
		? ' role="img" aria-label="' . esc_attr( $title ) . '"'
		: ' aria-hidden="true" focusable="false"';

	return '<svg class="' . esc_attr( trim( 'ui-icon ui-icon--' . $name . ' ' . $class ) ) . '" viewBox="0 0 24 24" width="24" height="24"' . $a11y . '>' . $icons[ $name ] . '</svg>';
}
