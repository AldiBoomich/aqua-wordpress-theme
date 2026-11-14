/**
 * Concept notice: the cross hides it and remembers the choice (localStorage, guarded — private modes may throw).
 */
import { reducedMotion } from '../tokens.js';

export function initConceptBar() {
	const bar = document.querySelector( '[data-concept-bar]' );
	const close = bar ? bar.querySelector( '[data-concept-close]' ) : null;
	if ( ! bar || ! close ) {
		return;
	}

	close.addEventListener( 'click', () => {
		try {
			window.localStorage.setItem( bar.dataset.key, '1' );
		} catch ( error ) {
			// Storage unavailable: the notice is hidden for this page view only.
		}

		document.documentElement.classList.add( 'concept-bar-closed-now' );
		let done = false;
		const hide = () => {
			if ( ! done ) {
				done = true;
				bar.hidden = true;
			}
		};
		if ( reducedMotion() ) {
			hide();
			return;
		}
		bar.classList.add( 'is-closing' );
		bar.addEventListener( 'transitionend', hide, { once: true } );
		window.setTimeout( hide, 600 );
	} );
}
