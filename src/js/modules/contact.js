/**
 * Contact page.
 * - Map on demand: the Google Maps iframe is created only after the visitor asks for it
 *   (faster page, nothing from Google before the click). Focus moves to the map.
 * - Contact Form 7: after a failed validation, focus goes to the first field with an error,
 *   so keyboard and screen reader users land on what needs fixing.
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

	document.querySelectorAll( '.contact-form .wpcf7' ).forEach( ( form ) => {
		form.addEventListener( 'wpcf7invalid', () => {
			// CF7 marks the fields right after the event; wait a frame.
			requestAnimationFrame( () => {
				form.querySelector( '.wpcf7-not-valid' )?.focus();
			} );
		} );
	} );
}
