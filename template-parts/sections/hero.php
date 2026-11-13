<?php
/**
 * Block "hero" — full-screen slider on the homepage.
 *
 * Fields (Pages → Home → Slider): hero_slides (repeater: image, subtitle, title, text,
 * primary_button, secondary_button), hero_interval.
 * Odd slides show the text on the right (--end), even slides on the left (--start).
 * Slide animations (image zoom, text reveal, cross-fade) live in assets/css/blocks/hero.css.
 *
 * @package aqua
 */

$aqua_slides = aqua_home_rows(
	'hero_slides',
	array(
		array(
			'image'            => 'hero-1.jpg',
			'subtitle'         => 'Pure. Fresh. Delivered.',
			'title'            => 'Clean Drinking Water Delivered to Your Door',
			'text'             => 'Spring water purified through 7-stage filtration and delivered within 24 hours — for homes and offices across the city.',
			'primary_button'   => array(
				'title' => 'View Prices',
				'url'   => aqua_page_url( 'products' ),
			),
			'secondary_button' => array(
				'title' => 'Get a Free Quote',
				'url'   => aqua_page_url( 'contact' ),
			),
		),
		array(
			'image'            => 'hero-2.jpg',
			'subtitle'         => 'From a Natural Source',
			'title'            => 'Mountain Spring Water You Can Trust',
			'text'             => 'Every batch is tested in an accredited laboratory, so your family gets water that is naturally balanced and perfectly safe.',
			'primary_button'   => array(
				'title' => 'Our Story',
				'url'   => aqua_page_url( 'about' ),
			),
			'secondary_button' => array(
				'title' => 'Order Now',
				'url'   => aqua_page_url( 'contact' ),
			),
		),
		array(
			'image'            => 'hero-3.jpg',
			'subtitle'         => 'For Business',
			'title'            => 'Water Coolers & Delivery for Your Office',
			'text'             => 'Free cooler rental with any monthly plan, scheduled deliveries and a personal manager for your company.',
			'primary_button'   => array(
				'title' => 'Business Plans',
				'url'   => aqua_page_url( 'service' ),
			),
			'secondary_button' => array(
				'title' => 'Contact Us',
				'url'   => aqua_page_url( 'contact' ),
			),
		),
	)
);

$aqua_interval = max( 3, (int) aqua_home_field( 'hero_interval', 7 ) ) * 1000;
?>
<section class="hero" aria-label="Featured">
	<div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="<?php echo esc_attr( $aqua_interval ); ?>" style="--aqua-hero-interval: <?php echo esc_attr( $aqua_interval ); ?>ms">
		<?php if ( count( $aqua_slides ) > 1 ) : ?>
			<div class="hero__dots carousel-indicators">
				<?php foreach ( $aqua_slides as $aqua_i => $aqua_slide ) : ?>
					<button type="button" class="hero__dot<?php echo 0 === $aqua_i ? ' active' : ''; ?>" data-bs-target="#hero-carousel" data-bs-slide-to="<?php echo (int) $aqua_i; ?>"<?php echo 0 === $aqua_i ? ' aria-current="true"' : ''; ?> aria-label="<?php echo esc_attr( 'Slide ' . ( $aqua_i + 1 ) ); ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="carousel-inner">
			<?php
			foreach ( $aqua_slides as $aqua_i => $aqua_slide ) :
				$aqua_is_end = 0 === $aqua_i % 2;
				$aqua_tag    = 0 === $aqua_i ? 'h1' : 'h2'; // Only one <h1> per page.
				$aqua_title  = $aqua_slide['title'] ?? '';
				$aqua_btn1   = aqua_link( $aqua_slide['primary_button'] ?? null );
				$aqua_btn2   = aqua_link( $aqua_slide['secondary_button'] ?? null );
				?>
				<div class="hero__slide carousel-item<?php echo 0 === $aqua_i ? ' active' : ''; ?>">
					<?php
					echo aqua_image_tag( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$aqua_slide['image'] ?? '',
						'hero-1.jpg',
						'full',
						array(
							'class'         => 'hero__image',
							'alt'           => $aqua_title,
							'loading'       => 0 === $aqua_i ? 'eager' : 'lazy',
							'fetchpriority' => 0 === $aqua_i ? 'high' : '',
						)
					);
					?>
					<div class="hero__overlay hero__overlay--<?php echo $aqua_is_end ? 'end' : 'start'; ?>">
						<div class="hero__content">
							<?php if ( ! empty( $aqua_slide['subtitle'] ) ) : ?>
								<p class="hero__subtitle"><?php echo esc_html( $aqua_slide['subtitle'] ); ?></p>
							<?php endif; ?>
							<?php if ( $aqua_title ) : ?>
								<<?php echo $aqua_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- h1/h2. ?> class="hero__title"><?php echo esc_html( $aqua_title ); ?></<?php echo $aqua_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
							<?php endif; ?>
							<?php if ( ! empty( $aqua_slide['text'] ) ) : ?>
								<p class="hero__text"><?php echo esc_html( $aqua_slide['text'] ); ?></p>
							<?php endif; ?>
							<?php if ( $aqua_btn1 || $aqua_btn2 ) : ?>
								<div class="hero__actions">
									<?php echo aqua_link_tag( $aqua_btn1, 'button button--primary button--large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php echo aqua_link_tag( $aqua_btn2, 'button button--light button--large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $aqua_slides ) > 1 ) : ?>
			<button class="hero__arrow hero__arrow--prev" type="button" data-bs-target="#hero-carousel" data-bs-slide="prev" aria-label="Previous slide">
				<i class="fas fa-angle-left" aria-hidden="true"></i>
			</button>
			<button class="hero__arrow hero__arrow--next" type="button" data-bs-target="#hero-carousel" data-bs-slide="next" aria-label="Next slide">
				<i class="fas fa-angle-right" aria-hidden="true"></i>
			</button>
		<?php endif; ?>
	</div>
</section>
