<?php
/**
 * Home: hero — one strong screen over a living water surface.
 * The photo loads eagerly with high priority; on capable devices src/js/home/hero-water.js lays a WebGL
 * ripple shader over it. The headline and facts enter with CSS only, so the first screen is complete
 * without any JavaScript. The opening drop plays once per visit.
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
					// The water photo is soft and sits under a WebGL layer rendered at 1x: on dense
					// screens a file one step smaller is indistinguishable and much lighter.
					'sizes'   => '(min-resolution: 2dppx) 60vw, (min-resolution: 1.25dppx) 80vw, 100vw',
				)
			);
			?>
		</picture>
		<span class="water-hero__veil" aria-hidden="true"></span>
	</div>

	<div class="water-hero__inner wrap">
		<?php
		// Each word sits in its own mask and rises in turn (pure CSS, starts with the first paint — the
		// headline never waits for JavaScript). Screen readers get the plain sentence.
		$aqua_words = preg_split( '/\s+/u', trim( (string) $aqua_title ), -1, PREG_SPLIT_NO_EMPTY );
		?>
		<h1 class="water-hero__title" aria-label="<?php echo esc_attr( $aqua_title ); ?>">
			<?php foreach ( $aqua_words as $aqua_i => $aqua_word ) : ?>
				<span class="water-hero__word" aria-hidden="true" style="--i:<?php echo (int) $aqua_i; ?>"><span><?php echo esc_html( $aqua_word ); ?></span></span>
			<?php endforeach; ?>
		</h1>

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
