<?php
/**
 * Homepage. The hero slider is printed in header.php.
 * All content is edited in Pages → Home (tabs: Slider, Features, About, Services, Stats, Pricing, Blog).
 *
 * @package aqua
 */

get_header();

get_template_part( 'template-parts/sections/features' );
get_template_part( 'template-parts/sections/about' );
get_template_part( 'template-parts/sections/services' );
get_template_part( 'template-parts/sections/stats' );
get_template_part( 'template-parts/sections/pricing' );
get_template_part( 'template-parts/sections/blog' );

get_footer();
