<?php
/**
 * Portfolio mode — the site is a concept, not a real business.
 * Theme Settings → Portfolio:
 *  - a small dismissible "concept project" notice (dismissal remembered in localStorage);
 *  - demo forms: Contact Form 7 validates as usual but sends nothing and shows a demo message;
 *  - noindex for the whole site (wp_robots), so real customers do not land here from search.
 *
 * @package aqua
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the concept notice is shown.
 *
 * @return bool
 */
function aqua_concept_bar_enabled() {
	return aqua_flag( 'demo_banner_show', true );
}

/**
 * Notice text.
 *
 * @return string
 */
function aqua_concept_bar_text() {
	return (string) aqua_field( 'demo_banner_text', 'Concept project — not a real business.' );
}

/**
 * localStorage key. It changes with the text, so a new notice is shown again to everyone.
 *
 * @return string
 */
function aqua_concept_bar_key() {
	return 'aqua-concept-' . substr( md5( aqua_concept_bar_text() ), 0, 8 );
}

/**
 * Body class while the notice is on (layout keeps room for it).
 *
 * @param array $classes Body classes.
 * @return array
 */
function aqua_concept_bar_body_class( $classes ) {
	if ( aqua_concept_bar_enabled() ) {
		$classes[] = 'has-concept-bar';
	}
	return $classes;
}
add_filter( 'body_class', 'aqua_concept_bar_body_class' );

/**
 * Before the first paint: hide the notice for visitors who closed it (no flash).
 */
function aqua_concept_bar_head() {
	if ( ! aqua_concept_bar_enabled() ) {
		return;
	}
	wp_print_inline_script_tag(
		sprintf(
			"try{if(localStorage.getItem(%s)==='1'){document.documentElement.classList.add('concept-bar-closed')}}catch(e){}",
			wp_json_encode( aqua_concept_bar_key() )
		)
	);
}
add_action( 'wp_head', 'aqua_concept_bar_head', 2 );

/**
 * The notice markup (printed at the end of the page by footer.php).
 */
function aqua_concept_bar() {
	if ( ! aqua_concept_bar_enabled() ) {
		return;
	}
	?>
	<aside class="concept-bar" data-concept-bar data-key="<?php echo esc_attr( aqua_concept_bar_key() ); ?>" aria-label="<?php echo esc_attr( aqua_label( 'concept', 'Site notice' ) ); ?>">
		<span class="concept-bar__dot" aria-hidden="true"></span>
		<p class="concept-bar__text"><?php echo esc_html( aqua_concept_bar_text() ); ?></p>
		<button class="concept-bar__close" type="button" data-concept-close aria-label="<?php echo esc_attr( aqua_field( 'demo_banner_close', 'Hide this notice' ) ); ?>">
			<?php echo aqua_ui_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
		</button>
	</aside>
	<?php
}

/**
 * Search engines: noindex, nofollow for every page while the option is on.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function aqua_demo_noindex( $robots ) {
	if ( aqua_flag( 'demo_noindex', true ) ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
		unset( $robots['max-image-preview'] );
	}
	return $robots;
}
add_filter( 'wp_robots', 'aqua_demo_noindex', 20 );

/**
 * Whether forms run in demo mode.
 *
 * @return bool
 */
function aqua_demo_forms() {
	return aqua_flag( 'demo_forms', true );
}

/**
 * Demo forms: Contact Form 7 skips sending (no email, nothing stored by the theme).
 *
 * @param bool $skip Whether to skip mail.
 * @return bool
 */
function aqua_demo_skip_mail( $skip ) {
	return aqua_demo_forms() ? true : $skip;
}
add_filter( 'wpcf7_skip_mail', 'aqua_demo_skip_mail' );

/**
 * Demo forms: the "sent" message becomes the demo message (a separate one for the newsletter).
 *
 * @param string $message Message.
 * @param string $status  Message status.
 * @return string
 */
function aqua_demo_form_message( $message, $status ) {
	if ( 'mail_sent_ok' !== $status || ! aqua_demo_forms() ) {
		return $message;
	}

	$form       = class_exists( 'WPCF7_ContactForm' ) ? WPCF7_ContactForm::get_current() : null;
	$newsletter = aqua_field( 'subscribe_form', 0 );
	$newsletter = $newsletter instanceof WP_Post ? $newsletter->ID : (int) $newsletter;

	if ( $form && $newsletter && (int) $form->id() === $newsletter ) {
		return (string) aqua_field( 'demo_subscribe_message', 'This is a portfolio demo — your email was not saved.' );
	}
	return (string) aqua_field( 'demo_form_message', 'This is a portfolio demo — no order was placed and nothing was sent.' );
}
add_filter( 'wpcf7_display_message', 'aqua_demo_form_message', 10, 2 );
