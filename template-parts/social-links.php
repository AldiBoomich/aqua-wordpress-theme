<?php
/**
 * Social links list (Theme Settings → Social), shared by the footer and the mobile menu.
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
		$aqua_net  = $aqua_networks[ $aqua_item['network'] ?? '' ] ?? null;
		$aqua_icon = $aqua_net ? $aqua_net['icon'] : ( $aqua_item['icon'] ?? 'fas fa-link' );
		$aqua_name = $aqua_net ? $aqua_net['label'] : 'Social link';
		$aqua_url  = $aqua_item['url'] ?? '#';
		?>
		<li>
			<a class="<?php echo esc_attr( $aqua_block ); ?>__social-link" href="<?php echo esc_url( $aqua_url ); ?>" aria-label="<?php echo esc_attr( $aqua_name ); ?>"<?php echo '#' !== $aqua_url ? ' target="_blank" rel="noopener"' : ''; ?>><i class="<?php echo esc_attr( $aqua_icon ); ?>" aria-hidden="true"></i></a>
		</li>
	<?php endforeach; ?>
</ul>
