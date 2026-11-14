<?php
/**
 * Home: hero — one strong screen over a living water surface.
 * The photo is the LCP image (eager, high priority); src/js/home/hero-water.js lays a WebGL ripple
 * shader over it on capable devices and plays the opening drop once per visit.
 *
 * @package aqua
 */

$aqua_title     = aqua_home_field( 'hero_title', 'Clean drinking water, delivered to your door.' );
$aqua_text      = aqua_home_field( 'hero_text', 'Spring water purified through 7-stage filtration and delivered within 24 hours, for homes and offices across the city.' );
$aqua_primary   = aqua_link(
	aqua_home_field( 'hero_primary' ),
	array(
		'title' => 'Order water',
		'url'   => aqua_page_url( 'products' ),
	)
);
$aqua_secondary = aqua_link(
	aqua_home_field( 'hero_secondary' ),
	array(
		'title' => 'Get a free quote',
		'url'   => aqua_page_url( 'contact' ),
	)
);
$aqua_image     = aqua_home_field( 'hero_image', 0 );
$aqua_mobile    = aqua_attachment_id( aqua_home_field( 'hero_image_mobile', 0 ) );
$aqua_intro     = aqua_flag( 'hero_intro', true, aqua_front_page_id() );
$aqua_facts     = aqua_home_rows(
	'hero_facts',
	array(
		array(
			'value' => '24 h',
			'label' => 'delivery',
		),
		array(
			'value' => '$20',
			'label' => 'free delivery from',
		),
		array(
			'value' => '7',
			'label' => 'filtration stages',
		),
		array(
			'value' => '40+',
			'label' => 'quality markers',
		),
	)
);
?>
<section class="water-hero" data-water-hero<?php echo $aqua_intro ? ' data-intro' : ''; ?>>
	<div class="water-hero__media">
		<picture>
			<?php if ( $aqua_mobile ) : ?>
				<source media="(max-width: 47.99em)" srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $aqua_mobile, 'large' ) ); ?>" sizes="100vw">
			<?php endif; ?>
			<?php
			echo aqua_photo( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() / escaped fallback.
				$aqua_image,
				'hero-water.webp',
				array(
					'class'   => 'water-hero__image',
					'alt'     => '',
					'loading' => 'eager',
					'sizes'   => '100vw',
				)
			);
			?>
		</picture>
		<span class="water-hero__veil" aria-hidden="true"></span>
	</div>

	<div class="water-hero__inner wrap">
		<h1 class="water-hero__title" data-hero-title><?php echo esc_html( $aqua_title ); ?></h1>

		<div class="water-hero__aside">
			<?php if ( $aqua_text ) : ?>
				<p class="water-hero__text"><?php echo esc_html( $aqua_text ); ?></p>
			<?php endif; ?>
			<div class="water-hero__actions">
				<?php echo aqua_link_tag( $aqua_primary, 'button button--primary button--large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo aqua_link_tag( $aqua_secondary, 'button button--secondary button--large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>

		<?php if ( $aqua_facts ) : ?>
			<ul class="water-hero__facts" data-hero-fade>
				<?php foreach ( $aqua_facts as $aqua_fact ) : ?>
					<li class="water-hero__fact">
						<span class="water-hero__fact-value"><?php echo esc_html( $aqua_fact['value'] ?? '' ); ?></span>
						<span class="water-hero__fact-label"><?php echo esc_html( $aqua_fact['label'] ?? '' ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
