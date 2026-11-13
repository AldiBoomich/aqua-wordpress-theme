<?php
/**
 * Block "services" — two columns of service cards around a floating bottle illustration
 * (homepage and Service page).
 *
 * Fields (Pages → Home → Services): service_subtitle, service_title, service_image,
 * service_items (repeater: icon, title, text). The first half goes to the left column.
 *
 * @package aqua
 */

$aqua_services = aqua_home_rows(
	'service_items',
	array(
		array(
			'icon'  => 'fas fa-home',
			'title' => 'Home Delivery',
			'text'  => 'Order online or by phone and get fresh water at your door the same day.',
		),
		array(
			'icon'  => 'fas fa-building',
			'title' => 'Office Supply',
			'text'  => 'Scheduled deliveries, invoices for companies and a dedicated manager.',
		),
		array(
			'icon'  => 'fas fa-snowflake',
			'title' => 'Cooler Rental',
			'text'  => 'Hot & cold water coolers for free with any monthly delivery plan.',
		),
		array(
			'icon'  => 'fas fa-filter',
			'title' => 'Filter Installation',
			'text'  => 'Reverse osmosis systems for your kitchen, installed by certified experts.',
		),
		array(
			'icon'  => 'fas fa-flask',
			'title' => 'Water Testing',
			'text'  => 'Find out what is really in your tap water with a full lab analysis.',
		),
		array(
			'icon'  => 'fas fa-calendar-check',
			'title' => 'Subscription Plans',
			'text'  => 'Set it and forget it — automatic refills and up to 15% savings.',
		),
	)
);

$aqua_half    = (int) ceil( count( $aqua_services ) / 2 );
$aqua_columns = array(
	'left'  => array_slice( $aqua_services, 0, $aqua_half ),
	'right' => array_slice( $aqua_services, $aqua_half ),
);

/**
 * Prints one service card.
 *
 * @param array $item    Service row.
 * @param bool  $reverse Icon on the right (left column).
 */
$aqua_service_card = static function ( $item, $reverse ) {
	?>
	<article class="service-card<?php echo $reverse ? ' service-card--reverse' : ''; ?>">
		<span class="service-card__icon" aria-hidden="true"><i class="<?php echo esc_attr( $item['icon'] ); ?>"></i></span>
		<div class="service-card__body">
			<h3 class="service-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
			<p class="service-card__text"><?php echo esc_html( $item['text'] ); ?></p>
		</div>
	</article>
	<?php
};
?>
<section class="services" id="services">
	<div class="container">
		<div class="section-heading wow fadeInUp" data-wow-delay="0.2s">
			<p class="section-heading__subtitle"><?php echo esc_html( aqua_home_field( 'service_subtitle', 'Our Services' ) ); ?></p>
			<h2 class="section-heading__title"><?php echo esc_html( aqua_home_field( 'service_title', 'Everything You Need for Pure Hydration' ) ); ?></h2>
		</div>
		<div class="row g-4 align-items-center">
			<div class="services__column col-lg-6 col-xl-4 wow fadeInLeft" data-wow-delay="0.2s">
				<?php
				foreach ( $aqua_columns['left'] as $aqua_item ) {
					$aqua_service_card( $aqua_item, true );
				}
				?>
			</div>
			<div class="col-xl-4 d-none d-xl-block wow fadeInUp" data-wow-delay="0.3s">
				<div class="services__visual" aria-hidden="true">
					<span class="services__ring"></span>
					<span class="services__bubble services__bubble--large"></span>
					<span class="services__bubble services__bubble--medium"></span>
					<span class="services__bubble services__bubble--small"></span>
					<?php echo aqua_image_tag( aqua_home_field( 'service_image' ), 'bottles/bottle-19.svg', 'large', array( 'class' => 'services__image' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
			<div class="services__column col-lg-6 col-xl-4 wow fadeInRight" data-wow-delay="0.2s">
				<?php
				foreach ( $aqua_columns['right'] as $aqua_item ) {
					$aqua_service_card( $aqua_item, false );
				}
				?>
			</div>
		</div>
	</div>
</section>
