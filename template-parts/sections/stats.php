<?php
/**
 * Block "stats" — animated counters on a dark gradient (homepage and Service page).
 * Counting animation: assets/js/theme.js.
 *
 * Fields (Pages → Home → Stats): stats_subtitle, stats_title,
 * stats_items (repeater: icon, number, suffix, label).
 *
 * @package aqua
 */

$aqua_stats = aqua_home_rows(
	'stats_items',
	array(
		array(
			'icon'   => 'fas fa-award',
			'number' => 15,
			'suffix' => '+',
			'label'  => 'Years of Experience',
		),
		array(
			'icon'   => 'fas fa-smile',
			'number' => 12000,
			'suffix' => '+',
			'label'  => 'Happy Customers',
		),
		array(
			'icon'   => 'fas fa-tint',
			'number' => 85,
			'suffix' => 'K',
			'label'  => 'Bottles Delivered Monthly',
		),
		array(
			'icon'   => 'fas fa-map-marker-alt',
			'number' => 24,
			'suffix' => '',
			'label'  => 'Cities We Serve',
		),
	)
);

if ( empty( $aqua_stats ) ) {
	return;
}
?>
<section class="stats">
	<div class="stats__inner container">
		<div class="section-heading section-heading--light wow fadeInUp" data-wow-delay="0.2s">
			<p class="section-heading__subtitle"><?php echo esc_html( aqua_home_field( 'stats_subtitle', 'Why Families Choose Us' ) ); ?></p>
			<h2 class="section-heading__title"><?php echo esc_html( aqua_home_field( 'stats_title', 'Trusted by Thousands Every Day' ) ); ?></h2>
		</div>
		<div class="row g-4">
			<?php
			foreach ( $aqua_stats as $aqua_i => $aqua_item ) :
				$aqua_number = (int) preg_replace( '/\D/', '', (string) $aqua_item['number'] );
				?>
				<div class="col-6 col-lg-3 wow fadeInUp" data-wow-delay="<?php echo esc_attr( sprintf( '%.2f', 0.2 + $aqua_i * 0.15 ) ); ?>s">
					<div class="stat-card">
						<div class="stat-card__icon"><?php echo aqua_icon( $aqua_item['icon'], 'stat-card__glyph liquid-icon--on-dark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
						<div class="stat-card__value">
							<span class="stat-card__number" data-count-to="<?php echo esc_attr( $aqua_number ); ?>"><?php echo esc_html( number_format_i18n( $aqua_number ) ); ?></span><span class="stat-card__suffix"><?php echo esc_html( $aqua_item['suffix'] ); ?></span>
						</div>
						<p class="stat-card__label"><?php echo esc_html( $aqua_item['label'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
