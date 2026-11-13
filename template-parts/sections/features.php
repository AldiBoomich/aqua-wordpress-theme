<?php
/**
 * Block "features" — four highlight cards overlapping the bottom of the hero (homepage).
 *
 * Fields (Pages → Home → Features): features_items (repeater: icon, title, text).
 *
 * @package aqua
 */

$aqua_features = aqua_home_rows(
	'features_items',
	array(
		array(
			'icon'  => 'fas fa-truck',
			'title' => 'Free Delivery',
			'text'  => 'Free same-day delivery on orders over $20.',
		),
		array(
			'icon'  => 'fas fa-filter',
			'title' => '7-Stage Filtration',
			'text'  => 'Reverse osmosis, UV and mineral balancing.',
		),
		array(
			'icon'  => 'fas fa-flask',
			'title' => 'Lab Tested Daily',
			'text'  => 'Every batch checked against 40+ quality markers.',
		),
		array(
			'icon'  => 'fas fa-headset',
			'title' => '24/7 Support',
			'text'  => 'Real people ready to help, day and night.',
		),
	)
);

if ( empty( $aqua_features ) ) {
	return;
}
?>
<section class="features" aria-label="Why choose us">
	<div class="container">
		<div class="features__list row g-4">
			<?php foreach ( $aqua_features as $aqua_i => $aqua_item ) : ?>
				<div class="features__item col-sm-6 col-xl-3 wow fadeInUp" data-wow-delay="<?php echo esc_attr( sprintf( '%.2f', 0.1 + $aqua_i * 0.15 ) ); ?>s">
					<article class="feature-card">
						<span class="feature-card__icon"><?php echo aqua_icon( $aqua_item['icon'], 'feature-card__glyph' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<div>
							<h3 class="feature-card__title"><?php echo esc_html( $aqua_item['title'] ); ?></h3>
							<p class="feature-card__text"><?php echo esc_html( $aqua_item['text'] ); ?></p>
						</div>
					</article>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
