<?php
/**
 * Template Name: Services
 *
 * Services page: "services" + "stats" blocks (edited in Pages → Home) + editor content below.
 *
 * @package aqua
 */

get_header();

get_template_part( 'template-parts/sections/services' );
get_template_part( 'template-parts/sections/stats' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
