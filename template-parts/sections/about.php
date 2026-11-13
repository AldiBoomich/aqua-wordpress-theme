<?php
/**
 * Block "about" — image with a decorative frame + company text (homepage and About page).
 *
 * Fields (Pages → Home → About): about_image, about_badge, about_subtitle, about_title,
 * about_text, about_items (repeater: icon, title, text), about_button.
 *
 * @package aqua
 */

$aqua_items = aqua_home_rows(
	'about_items',
	array(
		array(
			'icon'  => 'fas fa-award',
			'title' => 'Certified Quality',
			'text'  => 'ISO 22000 certified plant and independent lab checks for every single batch.',
		),
		array(
			'icon'  => 'fas fa-leaf',
			'title' => 'Eco-Friendly by Design',
			'text'  => 'Reusable 19 L bottles, recyclable packaging and low-emission delivery vans.',
		),
	)
);

$aqua_title  = aqua_home_field( 'about_title', 'Pure Water From Source to Your Glass' );
$aqua_badge  = aqua_home_field( 'about_badge', '15 Years of Experience' );
$aqua_button = is_page( 'about' ) ? null : aqua_link(
	aqua_home_field( 'about_button' ),
	array(
		'title' => 'Learn More',
		'url'   => aqua_page_url( 'about' ),
	)
);
$aqua_text   = aqua_home_field( 'about_text', "For over 15 years we have been delivering crystal-clear spring water to families and businesses. Our water is drawn from a protected mountain source, purified through a 7-stage system and bottled on a fully automated line — untouched by human hands.\n\nWe believe great water should be simple: honest prices, punctual delivery and friendly people on the other end of the phone." );
?>
<section class="about" id="about">
	<div class="container">
		<div class="row g-4 g-lg-5 align-items-center">
			<div class="col-xl-6 wow fadeInLeft" data-wow-delay="0.2s">
				<div class="about__media">
					<?php echo aqua_image_tag( aqua_home_field( 'about_image' ), 'about.jpg', 'large', array( 'class' => 'about__image', 'alt' => $aqua_title ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php if ( $aqua_badge ) : ?>
						<p class="about__badge"><?php echo esc_html( $aqua_badge ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-xl-6 wow fadeInRight" data-wow-delay="0.2s">
				<div class="section-heading section-heading--start">
					<p class="section-heading__subtitle"><?php echo esc_html( aqua_home_field( 'about_subtitle', 'About Us' ) ); ?></p>
					<h2 class="section-heading__title"><?php echo esc_html( $aqua_title ); ?></h2>
				</div>
				<div class="about__text"><?php echo wp_kses_post( wpautop( $aqua_text ) ); ?></div>
				<?php foreach ( $aqua_items as $aqua_item ) : ?>
					<div class="about-feature">
						<span class="about-feature__icon" aria-hidden="true"><i class="<?php echo esc_attr( $aqua_item['icon'] ); ?>"></i></span>
						<div>
							<h3 class="about-feature__title"><?php echo esc_html( $aqua_item['title'] ); ?></h3>
							<p class="about-feature__text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
				<?php echo aqua_link_tag( $aqua_button, 'about__button button button--secondary button--large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</section>
