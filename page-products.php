<?php
/**
 * Template Name: Price List
 *
 * Products page: the "pricing" block (edited in Pages → Home → Pricing) + editor content below.
 *
 * @package aqua
 */

get_header();

get_template_part( 'template-parts/sections/pricing' );
get_template_part( 'template-parts/page-editor-content' );

get_footer();
