<?php
/**
 * Site footer — "below the surface": intro + newsletter, menu, hours, contacts, social links,
 * the wordmark that fills with water, and the bottom line.
 *
 * Fields: Theme Settings (contacts, social, newsletter form, labels) and Theme Settings → Footer.
 *
 * @package aqua
 */

$aqua_logo_text = aqua_field( 'logo_text', get_bloginfo( 'name' ) );
$aqua_about     = aqua_field( 'footer_about_text', 'Purified spring water delivered to homes and offices since 2010. Fresh, lab-tested and always on time.' );
$aqua_nl_title  = aqua_field( 'footer_newsletter_title', 'Monthly notes from the source' );
$aqua_nl_text   = aqua_field( 'footer_newsletter_text', 'Seasonal offers, delivery news and simple tips on drinking better water. One email a month.' );
$aqua_subscribe = aqua_cf7_form( aqua_field( 'subscribe_form', 0 ) );
$aqua_wordmark  = aqua_field( 'footer_wordmark', $aqua_logo_text );
$aqua_hours     = aqua_rows(
	'footer_hours',
	array(
		array(
			'day'  => 'Mon - Friday',
			'time' => '09.00 am to 07.00 pm',
		),
		array(
			'day'  => 'Saturday',
			'time' => '10.00 am to 05.00 pm',
		),
		array(
			'day'  => 'Vacation',
			'time' => 'All Sunday is our vacation',
		),
	)
);
$aqua_address   = aqua_field( 'contact_address', '123 River Street, New York, NY 10001' );
$aqua_email     = aqua_field( 'contact_email', 'hello@example.com' );
$aqua_phone     = aqua_field( 'contact_phone', '+1 (800) 555-0199' );
$aqua_copyright = aqua_field( 'copyright_text', get_bloginfo( 'name' ) );
$aqua_legal     = aqua_link(
	aqua_field( 'footer_legal_link' ),
	array(
		'title' => 'Terms & Conditions',
		'url'   => aqua_page_url( 'terms-and-conditions' ),
	)
);
?>
	</main>

	<footer class="site-footer" data-footer>
		<div class="site-footer__wave" aria-hidden="true">
			<svg class="site-footer__wave-layer site-footer__wave-layer--back" viewBox="0 0 1600 64" preserveAspectRatio="none"><path d="M0 40C100 22 200 22 300 32S500 52 600 40 700 18 800 30 1000 54 1100 40 1300 18 1400 30 1550 46 1600 40V64H0Z"/></svg>
			<svg class="site-footer__wave-layer" viewBox="0 0 1600 64" preserveAspectRatio="none"><path d="M0 34C133 54 267 54 400 34S667 14 800 34 1067 54 1200 34 1467 14 1600 34V64H0Z"/></svg>
		</div>

		<div class="site-footer__body wrap">
			<div class="site-footer__top">
				<div class="site-footer__intro">
					<?php get_template_part( 'template-parts/brand', null, array( 'tone' => 'on-deep' ) ); ?>
					<?php if ( $aqua_about ) : ?>
						<p class="site-footer__about"><?php echo esc_html( $aqua_about ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $aqua_subscribe ) : ?>
					<div class="site-footer__newsletter">
						<h2 class="site-footer__newsletter-title"><?php echo esc_html( $aqua_nl_title ); ?></h2>
						<?php if ( $aqua_nl_text ) : ?>
							<p class="site-footer__newsletter-text"><?php echo esc_html( $aqua_nl_text ); ?></p>
						<?php endif; ?>
						<div class="subscribe-form site-footer__subscribe">
							<?php echo $aqua_subscribe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contact Form 7 output. ?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<div class="site-footer__columns">
				<nav aria-labelledby="footer-links-title">
					<h2 class="site-footer__title" id="footer-links-title"><?php echo esc_html( aqua_field( 'footer_links_title', 'About Us' ) ); ?></h2>
					<?php
					// Appearance → Menus → "Footer Menu".
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'items_wrap'     => '<ul class="site-footer__links">%3$s</ul>',
							'depth'          => 1,
							'walker'         => new Aqua_Footer_Nav_Walker(),
							'fallback_cb'    => 'aqua_footer_menu_fallback',
						)
					);
					?>
				</nav>

				<div>
					<h2 class="site-footer__title"><?php echo esc_html( aqua_field( 'footer_hours_title', 'Business Hours' ) ); ?></h2>
					<dl class="site-footer__hours">
						<?php foreach ( $aqua_hours as $aqua_item ) : ?>
							<div>
								<dt><?php echo esc_html( rtrim( (string) ( $aqua_item['day'] ?? '' ), ': ' ) ); ?></dt>
								<dd><?php echo esc_html( $aqua_item['time'] ?? '' ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				</div>

				<div>
					<h2 class="site-footer__title"><?php echo esc_html( aqua_field( 'footer_contacts_title', 'Contact Info' ) ); ?></h2>
					<ul class="site-footer__contacts">
						<?php if ( $aqua_address ) : ?>
							<li><a class="site-footer__link" href="<?php echo esc_url( aqua_page_url( 'contact' ) ); ?>"><?php echo aqua_ui_icon( 'pin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $aqua_address ); ?></span></a></li>
						<?php endif; ?>
						<?php if ( $aqua_email ) : ?>
							<li><a class="site-footer__link" href="mailto:<?php echo esc_attr( antispambot( $aqua_email ) ); ?>"><?php echo aqua_ui_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( antispambot( $aqua_email ) ); ?></span></a></li>
						<?php endif; ?>
						<?php if ( $aqua_phone ) : ?>
							<li><a class="site-footer__link" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><?php echo aqua_ui_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( $aqua_phone ); ?></span></a></li>
						<?php endif; ?>
					</ul>
				</div>

				<div>
					<h2 class="site-footer__title"><?php echo esc_html( aqua_field( 'footer_social_title', 'Follow us' ) ); ?></h2>
					<?php get_template_part( 'template-parts/social-links', null, array( 'block' => 'site-footer' ) ); ?>
				</div>
			</div>
		</div>

		<?php if ( $aqua_wordmark ) : ?>
			<p class="site-footer__wordmark" aria-hidden="true">
				<span class="site-footer__wordmark-outline"><?php echo esc_html( $aqua_wordmark ); ?></span>
				<span class="site-footer__wordmark-water"><?php echo esc_html( $aqua_wordmark ); ?></span>
			</p>
		<?php endif; ?>

		<div class="site-footer__bottom wrap">
			<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $aqua_copyright ); ?>. <?php echo esc_html( aqua_label( 'rights', 'All rights reserved.' ) ); ?></p>
			<p class="site-footer__bottom-links">
				<?php echo aqua_link_tag( $aqua_legal, 'site-footer__link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_link_tag(). ?>
				<a class="site-footer__link" href="#main"><?php echo esc_html( aqua_label( 'back_to_top', 'Back to top' ) ); ?></a>
			</p>
		</div>
	</footer>

<?php wp_footer(); ?>

</body>
</html>
