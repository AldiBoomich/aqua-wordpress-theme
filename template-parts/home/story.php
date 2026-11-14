<?php
/**
 * Home: story ("About") — a large statement, the story text, a portrait photo that opens up,
 * one big number and two proof points with liquid icons. Fields: Home → About.
 *
 * @package aqua
 */

$aqua_title  = aqua_home_field( 'about_title', 'Pure water from source to your glass' );
$aqua_text   = aqua_home_field( 'about_text', '<p>For over 15 years we have been delivering crystal-clear spring water to families and businesses. Our water is drawn from a protected mountain source, purified through a 7-stage system and bottled on a fully automated line.</p>' );
$aqua_image  = aqua_home_field( 'about_image', 0 );
$aqua_number = aqua_home_field( 'about_years_number', '15' );
$aqua_suffix = aqua_home_field( 'about_years_suffix', '' );
$aqua_label  = aqua_home_field( 'about_years_label', 'years of delivering spring water' );
$aqua_points = aqua_home_rows(
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
$aqua_button = aqua_link(
	aqua_home_field( 'about_button' ),
	array(
		'title' => 'Read our story',
		'url'   => aqua_page_url( 'about' ),
	)
);
?>
<section class="story" aria-labelledby="story-title">
	<div class="story__grid wrap">
		<h2 class="story__title section-title" id="story-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>

		<figure class="story__media" data-reveal="image">
			<?php
			echo aqua_photo( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$aqua_image,
				'',
				array(
					'class' => 'story__image',
					'sizes' => '(min-width: 62em) 40vw, 100vw',
				),
				'1536x1536'
			);
			?>
		</figure>

		<div class="story__body">
			<div class="story__text" data-reveal="lines"><?php echo wp_kses_post( $aqua_text ); ?></div>

			<?php if ( $aqua_number ) : ?>
				<p class="story__fact" data-reveal="fade">
					<span class="story__number" data-count="<?php echo esc_attr( $aqua_number ); ?>"><?php echo esc_html( $aqua_number ); ?></span><?php if ( $aqua_suffix ) : ?><span class="story__suffix"><?php echo esc_html( $aqua_suffix ); ?></span><?php endif; ?>
					<span class="story__fact-label"><?php echo esc_html( $aqua_label ); ?></span>
				</p>
			<?php endif; ?>

			<?php if ( $aqua_points ) : ?>
				<ul class="story__points">
					<?php foreach ( $aqua_points as $aqua_point ) : ?>
						<li class="story__point" data-reveal="fade">
							<?php echo aqua_icon( $aqua_point['icon'] ?? 'fas fa-tint', 'story__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_icon(). ?>
							<h3 class="story__point-title"><?php echo esc_html( $aqua_point['title'] ?? '' ); ?></h3>
							<p class="story__point-text"><?php echo esc_html( $aqua_point['text'] ?? '' ); ?></p>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php echo aqua_link_tag( $aqua_button, 'text-link story__link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
</section>
