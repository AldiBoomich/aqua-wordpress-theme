<?php
/**
 * Site footer: newsletter, social links, brand, menu, opening hours, contacts, copyright.
 *
 * Fields: Theme Settings (contacts, social, newsletter form) and Theme Settings → Footer.
 *
 * @package aqua
 */

$aqua_logo_text   = aqua_field( 'logo_text', get_bloginfo( 'name' ) );
$aqua_footer_text = aqua_field( 'footer_about_text', 'Purified spring water delivered to homes and offices since 2010. Fresh, lab-tested and always on time.' );
$aqua_subscribe   = aqua_cf7_form( aqua_field( 'subscribe_form', 0 ) );
$aqua_networks    = aqua_social_networks();
$aqua_social      = aqua_rows(
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
$aqua_hours       = aqua_rows(
	'footer_hours',
	array(
		array(
			'day'  => 'Mon - Friday:',
			'time' => '09.00 am to 07.00 pm',
		),
		array(
			'day'  => 'Saturday:',
			'time' => '10.00 am to 05.00 pm',
		),
		array(
			'day'  => 'Vacation:',
			'time' => 'All Sunday is our vacation',
		),
	)
);
$aqua_address     = aqua_field( 'contact_address', '123 River Street, New York, NY 10001' );
$aqua_email       = aqua_field( 'contact_email', 'hello@example.com' );
$aqua_phone       = aqua_field( 'contact_phone', '+1 (800) 555-0199' );
$aqua_fax         = aqua_field( 'contact_fax', '+1 (800) 555-0198' );
$aqua_copyright   = aqua_field( 'copyright_text', get_bloginfo( 'name' ) );
?>
	</main>

	<footer class="site-footer wow fadeIn" data-wow-delay="0.2s">
		<div class="site-footer__main">
			<div class="container">
				<div class="site-footer__top row g-4 align-items-center">
					<div class="col-lg-7">
						<?php if ( $aqua_subscribe ) : ?>
							<div class="subscribe-form site-footer__subscribe">
								<?php echo $aqua_subscribe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Contact Form 7 output. ?>
							</div>
						<?php endif; ?>
					</div>
					<div class="col-lg-5">
						<?php if ( $aqua_social ) : ?>
							<ul class="site-footer__social">
								<?php
								foreach ( $aqua_social as $aqua_item ) :
									$aqua_net  = $aqua_networks[ $aqua_item['network'] ?? '' ] ?? null;
									$aqua_icon = $aqua_net ? $aqua_net['icon'] : ( $aqua_item['icon'] ?? 'fas fa-link' );
									$aqua_name = $aqua_net ? $aqua_net['label'] : 'Social link';
									$aqua_url  = $aqua_item['url'] ?? '#';
									?>
									<li>
										<a class="site-footer__social-link" href="<?php echo esc_url( $aqua_url ); ?>" aria-label="<?php echo esc_attr( $aqua_name ); ?>"<?php echo '#' !== $aqua_url ? ' target="_blank" rel="noopener"' : ''; ?>><i class="<?php echo esc_attr( $aqua_icon ); ?>" aria-hidden="true"></i></a>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>

				<div class="row g-4 g-lg-5">
					<div class="col-md-6 col-xl-3">
						<a class="site-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<i class="site-footer__brand-icon fas fa-hand-holding-water" aria-hidden="true"></i><?php echo esc_html( $aqua_logo_text ); ?>
						</a>
						<p class="site-footer__text"><?php echo esc_html( $aqua_footer_text ); ?></p>
					</div>

					<div class="col-md-6 col-xl-3">
						<h2 class="site-footer__title"><?php echo esc_html( aqua_field( 'footer_links_title', 'About Us' ) ); ?></h2>
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
					</div>

					<div class="col-md-6 col-xl-3">
						<h2 class="site-footer__title"><?php echo esc_html( aqua_field( 'footer_hours_title', 'Business Hours' ) ); ?></h2>
						<?php foreach ( $aqua_hours as $aqua_item ) : ?>
							<div class="site-footer__hours-item">
								<p class="site-footer__day"><?php echo esc_html( $aqua_item['day'] ); ?></p>
								<p class="site-footer__time"><?php echo esc_html( $aqua_item['time'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="col-md-6 col-xl-3">
						<h2 class="site-footer__title"><?php echo esc_html( aqua_field( 'footer_contacts_title', 'Contact Info' ) ); ?></h2>
						<ul class="site-footer__contacts">
							<?php if ( $aqua_address ) : ?>
								<li><a class="site-footer__link" href="<?php echo esc_url( aqua_page_url( 'contact' ) ); ?>"><i class="site-footer__link-icon fas fa-map-marker-alt" aria-hidden="true"></i><?php echo esc_html( $aqua_address ); ?></a></li>
							<?php endif; ?>
							<?php if ( $aqua_email ) : ?>
								<li><a class="site-footer__link" href="mailto:<?php echo esc_attr( antispambot( $aqua_email ) ); ?>"><i class="site-footer__link-icon fas fa-envelope" aria-hidden="true"></i><?php echo esc_html( antispambot( $aqua_email ) ); ?></a></li>
							<?php endif; ?>
							<?php if ( $aqua_phone ) : ?>
								<li><a class="site-footer__link" href="tel:<?php echo esc_attr( aqua_tel( $aqua_phone ) ); ?>"><i class="site-footer__link-icon fas fa-phone-alt" aria-hidden="true"></i><?php echo esc_html( $aqua_phone ); ?></a></li>
							<?php endif; ?>
							<?php if ( $aqua_fax ) : ?>
								<li><a class="site-footer__link" href="tel:<?php echo esc_attr( aqua_tel( $aqua_fax ) ); ?>"><i class="site-footer__link-icon fas fa-print" aria-hidden="true"></i><?php echo esc_html( $aqua_fax ); ?></a></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="site-footer__bottom">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-md-6">
						<p class="site-footer__copyright">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <a class="site-footer__bottom-link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $aqua_copyright ); ?></a>. All rights reserved.</p>
					</div>
					<div class="col-md-6">
						<?php /* The template author's credit link must stay (HTML Codex license). */ ?>
						<p class="site-footer__credits">Designed By <a class="site-footer__bottom-link site-footer__bottom-link--underline" href="https://htmlcodex.com" target="_blank" rel="noopener">HTML Codex</a></p>
					</div>
				</div>
			</div>
		</div>
	</footer>

	<a href="#main" class="back-to-top" aria-label="Back to top"><i class="fas fa-arrow-up" aria-hidden="true"></i></a>

<?php wp_footer(); ?>

</body>
</html>
