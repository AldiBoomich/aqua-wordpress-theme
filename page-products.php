<?php
/**
 * Template Name: Price List
 *
 * Products page: the bottles shelf, the same block as on the homepage (edited in Pages → Home → Price List)
 * + editor content below.
 *
 * @package aqua
 */

get_header();

get_template_part( 'template-parts/home/shelf' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
