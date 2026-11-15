<?php
/**
 * Template Name: Price List
 *
 * Products page: page head → the bottles shelf (shared with the homepage, edited in Pages → Home → Price List) →
 * delivery terms → call to order → editor content (if any).
 *
 * Fields: Page Head (title, intro) and Products → Delivery Terms on this page.
 *
 * @package aqua
 */

get_header();

$aqua_page = get_queried_object_id();

get_template_part(
	'template-parts/page-head',
	null,
	array(
		'title' => aqua_field( 'banner_title', 'Bottles and prices', $aqua_page ),
		'lead'  => aqua_field( 'banner_lead', 'Spring water in four sizes, from a 0.5 L bottle to the 19 L cooler bottle. Delivery is free on orders over $20.', $aqua_page ),
	)
);

get_template_part( 'template-parts/home/shelf' );
get_template_part(
	'template-parts/inner/ledger',
	null,
	array(
		'prefix' => 'delivery',
		'title'  => 'How delivery works',
		'text'   => 'Clear terms before you order. Questions? Call us, real people answer around the clock.',
		'items'  => array(
			array(
				'title'  => 'Delivery zones',
				'text'   => 'We deliver across the city and to suburbs within 30 km. Further out, ask us for a quote.',
				'detail' => '30 km radius',
			),
			array(
				'title'  => 'Minimum order',
				'text'   => 'Delivery is free on orders over $20. Smaller orders ship for a flat fee.',
				'detail' => '$3 under $20',
			),
			array(
				'title'  => 'Delivery times',
				'text'   => 'Order before 2 pm for same-day delivery, or choose a two-hour window for tomorrow.',
				'detail' => 'Same day before 2 pm',
			),
			array(
				'title'  => 'Bottle deposit',
				'text'   => 'Each 19 L bottle takes a refundable deposit. Hand the empties to the driver on your next delivery.',
				'detail' => '$7, refundable',
			),
		),
	)
);
get_template_part( 'template-parts/home/order' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
