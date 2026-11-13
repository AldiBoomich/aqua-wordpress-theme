<?php
/**
 * Template Name: About
 *
 * About page: the "about" block (edited in Pages → Home → About) + editor content below.
 *
 * @package aqua
 */

get_header();

get_template_part( 'template-parts/sections/about' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
