/**
 * Facts ribbon: runs only while on screen; the button pauses / resumes it (WCAG 2.2.2).
 */
export function initMarquee() {
	document.querySelectorAll( '[data-marquee]' ).forEach( ( marquee ) => {
		const toggle = marquee.querySelector( '[data-marquee-toggle]' );

		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( ( entries ) => {
				marquee.classList.toggle( 'is-active', entries[ 0 ].isIntersecting );
			} ).observe( marquee );
		} else {
			marquee.classList.add( 'is-active' );
		}

		if ( toggle ) {
			const labels = { pause: toggle.getAttribute( 'aria-label' ), play: toggle.dataset.labelPlay || toggle.getAttribute( 'aria-label' ) };
			toggle.addEventListener( 'click', () => {
				const paused = marquee.classList.toggle( 'is-paused' );
				toggle.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
				toggle.setAttribute( 'aria-label', paused ? labels.play : labels.pause );
			} );
		}
	} );
}
