<?php
/**
 * Template Name: Contact
 *
 * Contact page: page head with the phone → the form beside the contact details and hours →
 * the map on demand → editor content (if any).
 *
 * Fields: Page Head and Contact Page fields on this page; address, e-mail, phone, map: Theme Settings;
 * hours: Theme Settings → Footer.
 *
 * @package aqua
 */

get_header();

$aqua_page  = get_queried_object_id();
$aqua_phone = aqua_field( 'contact_phone', '+1 (800) 555-0199' );
$aqua_after = '';
if ( $aqua_phone ) {
	$aqua_after = '<p class="page-head__call"><span class="page-head__call-label">' . esc_html( aqua_field( 'header_delivery_text', 'Free delivery hotline' ) ) . '</span>'
		. '<a class="page-head__phone" href="tel:' . esc_attr( aqua_tel( $aqua_phone ) ) . '">' . esc_html( $aqua_phone ) . '</a></p>';
}

get_template_part(
	'template-parts/page-head',
	null,
	array(
		'title' => aqua_field( 'banner_title', 'Talk to a real person', $aqua_page ),
		'lead'  => aqua_field( 'banner_lead', aqua_field( 'contact_text', 'Questions about delivery, prices or an office plan? Call, write or use the form. We usually reply within 15 minutes during working hours.', $aqua_page ), $aqua_page ),
		'after' => $aqua_after,
	)
);

get_template_part( 'template-parts/inner/contact-panel' );
get_template_part( 'template-parts/inner/map' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
