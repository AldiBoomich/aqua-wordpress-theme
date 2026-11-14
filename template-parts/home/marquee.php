<?php
/**
 * Home: facts ribbon — short facts sliding across the screen (CSS animation; paused on hover,
 * off screen and for reduced motion, where it becomes a wrapped list).
 *
 * @package aqua
 */

$aqua_items = aqua_home_rows(
	'marquee_items',
	array(
		array( 'text' => 'Free same-day delivery on orders over $20' ),
		array( 'text' => '7-stage filtration' ),
		array( 'text' => 'Every batch checked against 40+ quality markers' ),
		array( 'text' => 'Real people on the line 24/7' ),
		array( 'text' => 'ISO 22000 certified plant' ),
		array( 'text' => 'Reusable 19 L bottles' ),
	)
);
$aqua_items = array_values( array_filter( wp_list_pluck( $aqua_items, 'text' ) ) );
if ( ! $aqua_items ) {
	return;
}

$aqua_list = static function ( $hidden ) use ( $aqua_items ) {
	$html = '<ul class="marquee__list"' . ( $hidden ? ' aria-hidden="true"' : '' ) . '>';
	foreach ( $aqua_items as $text ) {
		$html .= '<li class="marquee__item">' . aqua_ui_icon( 'drop', 'marquee__mark' ) . '<span>' . esc_html( $text ) . '</span></li>';
	}
	return $html . '</ul>';
};
?>
<section class="marquee" data-marquee aria-label="<?php echo esc_attr( aqua_label( 'facts', 'Key facts' ) ); ?>">
	<div class="marquee__track">
		<?php echo $aqua_list( false ) . $aqua_list( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped while building. ?>
	</div>
	<button class="marquee__toggle" type="button" aria-pressed="false" data-marquee-toggle aria-label="<?php echo esc_attr( aqua_label( 'pause', 'Pause moving text' ) ); ?>" data-label-play="<?php echo esc_attr( aqua_label( 'play', 'Play moving text' ) ); ?>">
		<?php echo aqua_ui_icon( 'pause' ) . aqua_ui_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
	</button>
</section>
