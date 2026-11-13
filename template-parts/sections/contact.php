<?php
/**
 * Block "contact" — form + contact cards + map (Contact page).
 *
 * Fields (Pages → Contact): contact_subtitle, contact_title, contact_text, contact_form (CF7 form),
 * contact_address_label, contact_email_label, contact_phone_label.
 * Address, e-mail, phone and map: Theme Settings → Contacts / Map.
 *
 * @package aqua
 */

$aqua_page_id = get_queried_object_id();
$aqua_text    = aqua_field( 'contact_text', 'Questions about delivery, prices or business plans? We usually reply within 15 minutes during working hours.', $aqua_page_id );
$aqua_form    = aqua_cf7_form( aqua_field( 'contact_form', 0, $aqua_page_id ) );
$aqua_address = aqua_field( 'contact_address', '123 River Street, New York, NY 10001' );
$aqua_email   = aqua_field( 'contact_email', 'hello@example.com' );
$aqua_phone   = aqua_field( 'contact_phone', '+1 (800) 555-0199' );
$aqua_map     = aqua_map_embed_url();
?>
<section class="contact" id="contact">
	<div class="container">
		<div class="row g-4 g-lg-5">
			<div class="col-lg-6 wow fadeInUp" data-wow-delay="0.2s">
				<div class="section-heading">
					<p class="section-heading__subtitle"><?php echo esc_html( aqua_field( 'contact_subtitle', 'Let’s Connect', $aqua_page_id ) ); ?></p>
					<h2 class="section-heading__title"><?php echo esc_html( aqua_field( 'contact_title', 'Send Us a Message', $aqua_page_id ) ); ?></h2>
					<?php if ( $aqua_text ) : ?>
						<p class="section-heading__text fs-5"><?php echo esc_html( $aqua_text ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $aqua_form ) : ?>
					<div class="contact-form">
						<?php echo $aqua_form; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contact Form 7 output. ?>
					</div>
				<?php else : ?>
					<div class="contact__fallback">
						<p class="mb-3">Prefer to talk? Call us — we answer 24/7.</p>
						<a class="button button--primary button--large" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><i class="fas fa-phone-alt" aria-hidden="true"></i> <?php echo esc_html( $aqua_phone ); ?></a>
					</div>
				<?php endif; ?>
			</div>

			<div class="col-lg-6 wow fadeInUp" data-wow-delay="0.4s">
				<div class="row g-4">
					<?php if ( $aqua_address ) : ?>
						<div class="col-12">
							<div class="contact-card">
								<i class="contact-card__icon fas fa-map-marker-alt" aria-hidden="true"></i>
								<div>
									<h3 class="contact-card__title"><?php echo esc_html( aqua_field( 'contact_address_label', 'Address', $aqua_page_id ) ); ?></h3>
									<p class="contact-card__text"><?php echo esc_html( $aqua_address ); ?></p>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $aqua_email ) : ?>
						<div class="col-xl-6">
							<div class="contact-card">
								<i class="contact-card__icon fas fa-envelope" aria-hidden="true"></i>
								<div>
									<h3 class="contact-card__title"><?php echo esc_html( aqua_field( 'contact_email_label', 'Mail Us', $aqua_page_id ) ); ?></h3>
									<p class="contact-card__text"><a class="contact-card__link" href="mailto:<?php echo esc_attr( antispambot( $aqua_email ) ); ?>"><?php echo esc_html( antispambot( $aqua_email ) ); ?></a></p>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $aqua_phone ) : ?>
						<div class="col-xl-6">
							<div class="contact-card">
								<i class="contact-card__icon fas fa-phone-alt" aria-hidden="true"></i>
								<div>
									<h3 class="contact-card__title"><?php echo esc_html( aqua_field( 'contact_phone_label', 'Telephone', $aqua_page_id ) ); ?></h3>
									<p class="contact-card__text"><a class="contact-card__link" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo esc_html( $aqua_phone ); ?></a></p>
								</div>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( $aqua_map ) : ?>
						<div class="col-12">
							<div class="contact__map">
								<iframe class="contact__map-frame" src="<?php echo esc_url( $aqua_map ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php echo esc_attr( 'Map: ' . $aqua_address ); ?>"></iframe>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
