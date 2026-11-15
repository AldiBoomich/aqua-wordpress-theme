<?php
/**
 * Home: services — a sticky title with a photo, and an editorial list of services
 * (liquid line icons, lines that draw in on hover). Fields: Home → Services.
 * Args: detailed — true on the Service page: no photo beside the title (the page head has one), and each service
 * lists what is included (Home → Services → What's Included, one point per line).
 *
 * @package aqua
 */

$aqua_title = aqua_home_field( 'service_title', 'Everything you need for pure hydration' );
$aqua_image = aqua_home_field( 'service_image', 0 );
$aqua_items = aqua_home_rows(
	'service_items',
	array(
		array(
			'icon'    => 'fas fa-home',
			'title'   => 'Home delivery',
			'text'    => 'Order online or by phone and get fresh water at your door the same day.',
			'details' => "Same-day delivery when you order before 2 pm\nFree delivery on orders over $20\nBottles carried to your kitchen",
		),
		array(
			'icon'    => 'fas fa-building',
			'title'   => 'Office supply',
			'text'    => 'Scheduled deliveries, invoices for companies and a dedicated manager.',
			'details' => "Weekly or monthly delivery schedule\nOne invoice a month for your company\nA dedicated account manager",
		),
		array(
			'icon'    => 'fas fa-snowflake',
			'title'   => 'Cooler rental',
			'text'    => 'Hot & cold water coolers for free with any monthly delivery plan.',
			'details' => "Free with any monthly plan of 4+ bottles\nHot and cold water taps\nSanitised every 3 months",
		),
		array(
			'icon'    => 'fas fa-filter',
			'title'   => 'Filter installation',
			'text'    => 'Reverse osmosis systems for your kitchen, installed by certified experts.',
			'details' => "A free water test before we install\nFitted under your sink in about 2 hours\nA text reminder when a filter needs changing",
		),
		array(
			'icon'    => 'fas fa-flask',
			'title'   => 'Water testing',
			'text'    => 'Find out what is really in your tap water with a full lab analysis.',
			'details' => "Sample collected at your door\n40+ markers checked in an accredited lab\nResults by email within 5 working days",
		),
		array(
			'icon'    => 'fas fa-calendar-check',
			'title'   => 'Subscription plans',
			'text'    => 'Set it and forget it — automatic refills and up to 15% savings.',
			'details' => "Automatic refills on your schedule\nUp to 15% off single orders\nPause or change your plan any time",
		),
	)
);
$aqua_detailed = ! empty( $args['detailed'] );
?>
<section class="service-list<?php echo $aqua_detailed ? ' service-list--detailed' : ''; ?>" aria-labelledby="services-title">
	<div class="service-list__grid wrap">
		<div class="service-list__aside">
			<h2 class="section-title" id="services-title" data-reveal="lines"><?php echo esc_html( $aqua_title ); ?></h2>
			<?php if ( ! $aqua_detailed ) : ?>
				<figure class="service-list__media" data-reveal="image">
					<?php echo aqua_photo( $aqua_image, '', array( 'class' => 'service-list__image', 'sizes' => '(min-width: 62em) 36vw, 100vw' ), '1536x1536' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</figure>
			<?php endif; ?>
		</div>

		<ul class="service-list__items">
			<?php foreach ( $aqua_items as $aqua_item ) : ?>
				<li class="service-list__item" data-reveal="fade">
					<?php echo aqua_icon( $aqua_item['icon'] ?? 'fas fa-tint', 'service-list__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aqua_icon(). ?>
					<h3 class="service-list__title"><?php echo esc_html( $aqua_item['title'] ?? '' ); ?></h3>
					<p class="service-list__text"><?php echo esc_html( $aqua_item['text'] ?? '' ); ?></p>
					<?php
					$aqua_details = $aqua_detailed ? aqua_lines( $aqua_item['details'] ?? '' ) : array();
					if ( $aqua_details ) :
						?>
						<ul class="service-list__details" aria-label="<?php echo esc_attr( aqua_label( 'included', 'What’s included' ) . ': ' . ( $aqua_item['title'] ?? '' ) ); ?>">
							<?php foreach ( $aqua_details as $aqua_detail ) : ?>
								<li class="service-list__detail"><?php echo esc_html( $aqua_detail ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
