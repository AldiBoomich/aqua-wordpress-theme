<?php
/**
 * Template Name: About
 *
 * About page: page head with a wide photo → story (shared with the homepage) → quality & eco proof →
 * the 7 stages as a short list → numbers → call to order → editor content (if any).
 *
 * Fields: Page Head (title, intro, photo) and About → Quality & Eco on this page; story, stages, numbers and
 * the call to order are edited once on the homepage.
 *
 * @package aqua
 */

get_header();

$aqua_page  = get_queried_object_id();
$aqua_photo = aqua_field( 'banner_image', 0, $aqua_page );
if ( ! $aqua_photo ) {
	// No photo of its own → the spring photo of the homepage "From source to glass" block.
	$aqua_photo = aqua_home_field( 'stages_source_image', 0 );
}

get_template_part(
	'template-parts/page-head',
	null,
	array(
		'title'   => aqua_field( 'banner_title', 'From a mountain spring to your door, since 2010', $aqua_page ),
		'lead'    => aqua_field( 'banner_lead', 'We bottle water from a protected source, purify it in seven stages and deliver it to homes and offices across the city.', $aqua_page ),
		'variant' => 'media',
		'image'   => $aqua_photo,
	)
);

get_template_part(
	'template-parts/home/story',
	null,
	array(
		'link'   => false,
		'fact'   => false,
		'points' => false,
	)
);
get_template_part(
	'template-parts/inner/ledger',
	null,
	array(
		'prefix' => 'proof',
		'title'  => 'Proof in every bottle',
		'text'   => 'Certificates, lab checks and reusable bottles. What we promise on the label, we can show on paper.',
		'items'  => array(
			array(
				'title'  => 'ISO 22000 certified plant',
				'text'   => 'Our food-safety system is audited every year, from the spring to the sealed cap.',
				'detail' => 'Audited yearly',
			),
			array(
				'title'  => 'Every batch lab-tested',
				'text'   => 'An independent lab checks each batch for microbiology, minerals and taste before it leaves the plant.',
				'detail' => '40+ markers',
			),
			array(
				'title'  => 'Reusable 19 L bottles',
				'text'   => 'Large bottles are washed, inspected and refilled. Worn ones are recycled into new bottles.',
				'detail' => 'Up to 50 refills',
			),
			array(
				'title'  => 'Cleaner delivery routes',
				'text'   => 'Routes are planned to avoid empty runs, and our city vans are electric.',
				'detail' => 'Electric city vans',
			),
		),
	)
);
get_template_part( 'template-parts/home/stages', null, array( 'variant' => 'lite' ) );
get_template_part( 'template-parts/home/numbers' );
get_template_part( 'template-parts/home/order' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
