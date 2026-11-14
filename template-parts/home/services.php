<?php
/**
 * Home: services — a sticky title with a photo, and an editorial list of services
 * (liquid line icons, lines that draw in on hover). Fields: Home → Services.
 *
 * @package aqua
 */

$aqua_title = aqua_home_field( 'service_title', 'Everything you need for pure hydration' );
$aqua_image = aqua_home_field( 'service_image', 0 );
$aqua_items = aqua_home_rows(
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
?>
<section class="service-list" aria-labelledby="services-title">
	<div class="service-list__grid wrap">
		<div class="service-list__aside">
			<h2 class="section-title" id="services-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
			<figure class="service-list__media" data-reveal="image">
				<?php echo aqua_photo( $aqua_image, '', array( 'class' => 'service-list__image', 'sizes' => '(min-width: 62em) 36vw, 100vw' ), '1536x1536' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</figure>
		</div>

		<ul class="service-list__items">
			<?php foreach ( $aqua_items as $aqua_item ) : ?>
				<li class="service-list__item" data-reveal="fade">
					<?php echo aqua_icon( $aqua_item['icon'] ?? 'fas fa-tint', 'service-list__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_icon(). ?>
					<h3 class="service-list__title"><?php echo esc_html( $aqua_item['title'] ?? '' ); ?></h3>
					<p class="service-list__text"><?php echo esc_html( $aqua_item['text'] ?? '' ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
