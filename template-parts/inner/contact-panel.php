<?php
/**
 * Contact: the form (Contact Form 7, styled by the theme — block "contact-form") beside the contact details:
 * phone, e-mail, address, hours and social links (block "contact-panel").
 *
 * Fields: Contact Page (title, form, labels); address, e-mail, phone: Theme Settings → Contacts;
 * hours: Theme Settings → Footer → Business Hours; social links: Theme Settings → Social.
 *
 * @package aqua
 */

$aqua_page    = get_queried_object_id();
$aqua_form    = aqua_cf7_form( aqua_field( 'contact_form', 0, $aqua_page ) );
$aqua_address = aqua_field( 'contact_address', '123 River Street, New York, NY 10001' );
$aqua_email   = aqua_field( 'contact_email', 'hello@example.com' );
$aqua_phone   = aqua_field( 'contact_phone', '+1 (800) 555-0199' );
$aqua_hours   = aqua_rows(
	'footer_hours',
	array(
		array(
			'day'  => 'Monday to Friday',
			'time' => '9 am to 7 pm',
		),
		array(
			'day'  => 'Saturday',
			'time' => '10 am to 5 pm',
		),
	)
);
?>
<section class="contact-panel" aria-labelledby="contact-form-title">
	<div class="contact-panel__grid wrap">
		<div class="contact-panel__form">
			<h2 class="contact-panel__title" id="contact-form-title"><?php echo esc_html( aqua_field( 'contact_title', 'Send us a message', $aqua_page ) ); ?></h2>
			<?php if ( $aqua_form ) : ?>
				<div class="contact-form">
					<?php echo $aqua_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contact Form 7 output. ?>
				</div>
			<?php elseif ( $aqua_phone ) : ?>
				<a class="button button--primary button--large" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo esc_html( $aqua_phone ); ?></a>
			<?php endif; ?>
		</div>

		<dl class="contact-panel__details">
			<?php if ( $aqua_phone ) : ?>
				<div class="contact-panel__row">
					<dt class="contact-panel__label"><?php echo esc_html( aqua_field( 'contact_phone_label', 'Phone', $aqua_page ) ); ?></dt>
					<dd class="contact-panel__value"><a class="contact-panel__link" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo esc_html( $aqua_phone ); ?></a></dd>
				</div>
			<?php endif; ?>
			<?php if ( $aqua_email ) : ?>
				<div class="contact-panel__row">
					<dt class="contact-panel__label"><?php echo esc_html( aqua_field( 'contact_email_label', 'Email', $aqua_page ) ); ?></dt>
					<dd class="contact-panel__value"><a class="contact-panel__link" href="mailto:<?php echo esc_attr( antispambot( $aqua_email ) ); ?>"><?php echo esc_html( antispambot( $aqua_email ) ); ?></a></dd>
				</div>
			<?php endif; ?>
			<?php if ( $aqua_address ) : ?>
				<div class="contact-panel__row">
					<dt class="contact-panel__label"><?php echo esc_html( aqua_field( 'contact_address_label', 'Address', $aqua_page ) ); ?></dt>
					<dd class="contact-panel__value"><?php echo esc_html( $aqua_address ); ?></dd>
				</div>
			<?php endif; ?>
			<?php if ( $aqua_hours ) : ?>
				<div class="contact-panel__row">
					<dt class="contact-panel__label"><?php echo esc_html( aqua_field( 'contact_hours_label', 'Hours', $aqua_page ) ); ?></dt>
					<dd class="contact-panel__value">
						<ul class="contact-panel__hours">
							<?php foreach ( $aqua_hours as $aqua_row ) : ?>
								<li class="contact-panel__hour">
									<span><?php echo esc_html( rtrim( (string) ( $aqua_row['day'] ?? '' ), ': ' ) ); ?></span>
									<span class="contact-panel__time"><?php echo esc_html( $aqua_row['time'] ?? '' ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</dd>
				</div>
			<?php endif; ?>
			<div class="contact-panel__row">
				<dt class="contact-panel__label"><?php echo esc_html( aqua_field( 'contact_social_label', 'Follow us', $aqua_page ) ); ?></dt>
				<dd class="contact-panel__value"><?php get_template_part( 'template-parts/social-links', null, array( 'block' => 'contact-panel' ) ); ?></dd>
			</div>
		</dl>
	</div>
</section>
