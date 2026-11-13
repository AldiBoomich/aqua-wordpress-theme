<?php
/**
 * Regular page: title in the banner, editor content below.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package aqua
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/content', 'page' );
endwhile;

get_footer();
