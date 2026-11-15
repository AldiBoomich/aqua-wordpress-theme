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

/**
 * Newsletter form (fields with the subscribe-form__* classes):
 * - the email placeholder and the button text come from Theme Settings → Footer → Newsletter
 *   (empty fields keep the texts written in the Contact Form 7 form);
 * - the submit <input> becomes a <button> with the same classes, so it gets the theme's button
 *   hover (water rising from the bottom edge is a ::before layer, which an <input> cannot have).
 *
 * @param string $html Form elements HTML.
 * @return string
 */
function aqua_cf7_subscribe_form_elements( $html ) {
	if ( false === strpos( $html, 'subscribe-form__' ) ) {
		return $html;
	}

	$placeholder = (string) aqua_field( 'footer_newsletter_placeholder', '' );
	if ( '' !== $placeholder ) {
		$tags = new WP_HTML_Tag_Processor( $html );
		while ( $tags->next_tag( array( 'tag_name' => 'input', 'class_name' => 'subscribe-form__input' ) ) ) {
			$tags->set_attribute( 'placeholder', $placeholder );
		}
		$html = $tags->get_updated_html();
	}

	return preg_replace_callback(
		'/<input\b[^>]*\bsubscribe-form__submit\b[^>]*>/i',
		static function ( $match ) {
			$tag = new WP_HTML_Tag_Processor( $match[0] );
			if ( ! $tag->next_tag( 'input' ) || 'submit' !== strtolower( (string) $tag->get_attribute( 'type' ) ) ) {
				return $match[0];
			}
			$label = (string) aqua_field( 'footer_newsletter_button', (string) $tag->get_attribute( 'value' ) );
			return sprintf(
				'<button type="submit" class="%1$s">%2$s</button>',
				esc_attr( (string) $tag->get_attribute( 'class' ) ),
				esc_html( $label )
			);
		},
		$html
	);
}
add_filter( 'wpcf7_form_elements', 'aqua_cf7_subscribe_form_elements' );
