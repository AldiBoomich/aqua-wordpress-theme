<?php
/**
 * Contact Form 7 integration.
 * Forms are styled by the theme (blocks contact-form and subscribe-form),
 * so the plugin's own stylesheet and automatic <p>/<br> tags are disabled.
 *
 * @package aqua
 */

add_filter( 'wpcf7_load_css', '__return_false' );
add_filter( 'wpcf7_autop_or_not', '__return_false' );
