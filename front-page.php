<?php
/**
 * Homepage: living-water hero → facts ribbon → story → from source to glass (7 stages) →
 * bottles shelf → services → numbers → journal → order call.
 *
 * Content: Pages → Home (ACF "Homepage Content"). One template part per section in template-parts/home/.
 *
 * @package aqua
 */

get_header();

foreach ( array( 'hero', 'marquee', 'story', 'stages', 'shelf', 'services', 'numbers', 'journal', 'order' ) as $aqua_section ) {
	get_template_part( 'template-parts/home/' . $aqua_section );
}

get_footer();
