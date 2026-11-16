/**
 * Contact page.
 * - Map on demand: the Google Maps iframe is created only after the visitor asks for it
 *   (faster page, nothing from Google before the click). Focus moves to the map.
 * - Contact Form 7: after a failed validation, focus goes to the first field with an error,
 *   so keyboard and screen reader users land on what needs fixing; a flagged field is re-checked while typing.
 */
export function initContact() {
	document.querySelectorAll( '[data-map]' ).forEach( ( frame ) => {
		const button = frame.querySelector( '[data-map-open]' );
		if ( ! button ) {
			return;
		}
		button.hidden = false;
		button.addEventListener( 'click', () => {
			const iframe = document.createElement( 'iframe' );
			iframe.className = 'map-panel__map';
			iframe.src = button.dataset.mapSrc;
			iframe.title = button.dataset.mapTitle || '';
			iframe.referrerPolicy = 'no-referrer-when-downgrade';
			iframe.setAttribute( 'tabindex', '0' );
			frame.classList.add( 'is-open' );
			frame.appendChild( iframe );
			frame.querySelector( '[data-map-cover]' )?.setAttribute( 'hidden', '' );
			iframe.focus( { preventScroll: true } );
		} );
	} );

	// Every Contact Form 7 form (contact page, footer newsletter): a field flagged as invalid is checked again
	// while the visitor types, not only when it loses focus. CF7 re-checks on "change", which fires on the
	// press of the submit button itself — the error line under the field disappears, the button jumps up
	// under the pointer and the click misses it (the form needed a second press).
	document.querySelectorAll( 'form.wpcf7-form' ).forEach( ( form ) => {
		form.addEventListener( 'input', ( event ) => {
			const field = event.target;
			if ( field.classList?.contains( 'wpcf7-not-valid' ) && window.wpcf7?.validate ) {
				window.wpcf7.validate( form, { target: field } );
			}
		} );
	} );

	document.querySelectorAll( '.contact-form .wpcf7' ).forEach( ( form ) => {
		form.addEventListener( 'wpcf7invalid', () => {
			// CF7 marks the fields right after the event; wait a frame.
			requestAnimationFrame( () => {
				form.querySelector( '.wpcf7-not-valid' )?.focus();
			} );
		} );
	} );
}
