<?php
/**
 * Editor content on template pages (About, Service, Products, Contact),
 * printed below the block only when the editor is not empty.
 *
 * @package aqua
 */

while ( have_posts() ) :
	the_post();
	if ( '' !== trim( get_the_content() ) ) {
		get_template_part( 'template-parts/content', 'page' );
	}
endwhile;
