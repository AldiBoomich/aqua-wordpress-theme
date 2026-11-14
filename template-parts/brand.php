<?php
/**
 * Logo link: the uploaded logo (Theme Settings → Header → Logo) or the line drop mark + site name.
 *
 * Args: class — extra classes; tone — 'on-deep' for dark backgrounds.
 *
 * @package aqua
 */

$aqua_class = 'brand';
if ( ! empty( $args['tone'] ) ) {
	$aqua_class .= ' brand--' . sanitize_html_class( $args['tone'] );
}
if ( ! empty( $args['class'] ) ) {
	$aqua_class .= ' ' . $args['class'];
}

$aqua_logo      = aqua_field( 'logo', get_theme_mod( 'custom_logo' ) );
$aqua_logo_text = aqua_field( 'logo_text', get_bloginfo( 'name' ) );
?>
<a class="<?php echo esc_attr( $aqua_class ); ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
	<?php if ( $aqua_logo ) : ?>
		<?php echo aqua_image_tag( $aqua_logo, '', 'medium', array( 'class' => 'brand__image', 'alt' => $aqua_logo_text, 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image(). ?>
	<?php else : ?>
		<span class="brand__mark"><?php echo aqua_ui_icon( 'drop' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
		<span class="brand__name"><?php echo esc_html( $aqua_logo_text ); ?></span>
	<?php endif; ?>
</a>
