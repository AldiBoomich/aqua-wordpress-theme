<?php
/**
 * Social links list (Theme Settings → Social), shared by the footer and the full-screen menu.
 *
 * Args: block — BEM block that styles the list: {block}__social, {block}__social-link.
 *
 * @package aqua
 */

$aqua_block    = sanitize_html_class( $args['block'] ?? 'site-footer' );
$aqua_networks = aqua_social_networks();
$aqua_social   = aqua_rows(
	'social_links',
	array(
		array(
			'network' => 'facebook',
			'url'     => '#',
		),
		array(
			'network' => 'x',
			'url'     => '#',
		),
		array(
			'network' => 'instagram',
			'url'     => '#',
		),
		array(
			'network' => 'linkedin',
			'url'     => '#',
		),
	)
);

if ( ! $aqua_social ) {
	return;
}
?>
<ul class="<?php echo esc_attr( $aqua_block ); ?>__social">
	<?php
	foreach ( $aqua_social as $aqua_item ) :
		$aqua_key  = (string) ( $aqua_item['network'] ?? '' );
		$aqua_net  = $aqua_networks[ $aqua_key ] ?? null;
		$aqua_name = $aqua_net ? $aqua_net['label'] : 'Social link';
		$aqua_url  = $aqua_item['url'] ?? '#';
		$aqua_new  = '#' !== $aqua_url;
		?>
		<li>
			<a class="<?php echo esc_attr( $aqua_block ); ?>__social-link" href="<?php echo esc_url( $aqua_url ); ?>"<?php echo $aqua_new ? ' target="_blank" rel="noopener"' : ''; ?>>
				<?php echo aqua_ui_icon( $aqua_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
				<span class="screen-reader-text"><?php echo esc_html( $aqua_name . ( $aqua_new ? ' (' . aqua_label( 'new_tab', 'opens in a new tab' ) . ')' : '' ) ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
