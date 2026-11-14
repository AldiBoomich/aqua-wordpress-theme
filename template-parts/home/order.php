<?php
/**
 * Home: the closing call to order (above the footer). Fields: Home → Order Call; phone from Theme Settings.
 *
 * @package aqua
 */

$aqua_title  = aqua_home_field( 'cta_title', 'Order today. Fresh water tomorrow.' );
$aqua_text   = aqua_home_field( 'cta_text', 'Free delivery on orders over $20 and free cooler rental with any monthly plan.' );
$aqua_button = aqua_link(
	aqua_home_field( 'cta_button' ),
	array(
		'title' => 'Order water',
		'url'   => aqua_page_url( 'products' ),
	)
);
$aqua_label  = aqua_home_field( 'cta_phone_label', 'Or call us' );
$aqua_phone  = aqua_field( 'contact_phone', '+1 (800) 555-0199' );
?>
<section class="order-cta" aria-labelledby="order-title">
	<div class="order-cta__grid wrap">
		<h2 class="order-cta__title" id="order-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
		<div class="order-cta__aside" data-reveal="fade">
			<?php if ( $aqua_text ) : ?>
				<p class="order-cta__text"><?php echo esc_html( $aqua_text ); ?></p>
			<?php endif; ?>
			<div class="order-cta__actions">
				<?php echo aqua_link_tag( $aqua_button, 'button button--primary button--large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( $aqua_phone ) : ?>
					<p class="order-cta__call">
						<span class="order-cta__call-label"><?php echo esc_html( $aqua_label ); ?></span>
						<a class="order-cta__phone" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo esc_html( $aqua_phone ); ?></a>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
