<?php
/**
 * Contact: the map on demand (block "map-panel"). A quiet panel with the address and a button;
 * the Google Maps iframe is created only after the click (src/js/modules/map.js), so the page stays fast and
 * Google sets nothing until then. Without JavaScript the link opens Google Maps instead.
 *
 * Fields: Theme Settings → Map (show, address, zoom, embed URL); Contact Page → Map (texts).
 *
 * @package aqua
 */

$aqua_embed = aqua_map_embed_url();
if ( ! $aqua_embed ) {
	return;
}
$aqua_page    = get_queried_object_id();
$aqua_address = wp_strip_all_tags( (string) aqua_field( 'map_address', aqua_field( 'contact_address', '123 River Street, New York, NY 10001' ) ) );
$aqua_shown   = wp_strip_all_tags( (string) aqua_field( 'contact_address', $aqua_address ) );
$aqua_open    = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $aqua_address );
?>
<section class="map-panel wrap" aria-labelledby="map-title">
	<div class="map-panel__frame" data-map>
		<div class="map-panel__cover" data-map-cover>
			<?php echo aqua_icon( 'fas fa-map-marker-alt', 'map-panel__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_icon(). ?>
			<h2 class="map-panel__title" id="map-title"><?php echo esc_html( $aqua_shown ); ?></h2>
			<div class="map-panel__actions">
				<button class="button button--primary" type="button" hidden
					data-map-open
					data-map-src="<?php echo esc_url( $aqua_embed ); ?>"
					data-map-title="<?php echo esc_attr( aqua_label( 'map', 'Map' ) . ': ' . $aqua_shown ); ?>"><?php echo esc_html( aqua_field( 'map_button', 'Show map', $aqua_page ) ); ?></button>
				<a class="text-link" href="<?php echo esc_url( $aqua_open ); ?>" target="_blank" rel="noopener"><?php echo esc_html( aqua_field( 'map_link_text', 'Open in Google Maps', $aqua_page ) ); ?><span class="screen-reader-text"> (<?php echo esc_html( aqua_label( 'new_tab', 'opens in a new tab' ) ); ?>)</span></a>
			</div>
			<p class="map-panel__note"><?php echo esc_html( aqua_field( 'map_note', 'The map is provided by Google and loads when you open it.', $aqua_page ) ); ?></p>
		</div>
	</div>
</section>
