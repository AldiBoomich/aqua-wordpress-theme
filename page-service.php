<?php
/**
 * Template Name: Services
 *
 * Service page: page head with a wide photo → the services in detail (shared with the homepage, plus
 * "What's included") → for business → call to order → editor content (if any).
 *
 * Fields: Page Head (title, intro, photo) and Service → For Business on this page; the services and the call
 * to order are edited once on the homepage.
 *
 * @package aqua
 */

get_header();

$aqua_page  = get_queried_object_id();
$aqua_photo = aqua_field( 'banner_image', 0, $aqua_page );
if ( ! $aqua_photo ) {
	// No photo of its own → the office cooler photo of the homepage services block.
	$aqua_photo = aqua_home_field( 'service_image', 0 );
}

get_template_part(
	'template-parts/page-head',
	null,
	array(
		'title'   => aqua_field( 'banner_title', 'Water delivery and everything around it', $aqua_page ),
		'lead'    => aqua_field( 'banner_lead', 'Bottles to your door, coolers, home filters and lab tests, run by one team you can call around the clock.', $aqua_page ),
		'variant' => 'media',
		'image'   => $aqua_photo,
	)
);

get_template_part( 'template-parts/home/services', null, array( 'detailed' => true ) );
get_template_part( 'template-parts/inner/business' );
get_template_part( 'template-parts/home/order' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
