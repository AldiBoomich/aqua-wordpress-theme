/**
 * Site header: .is-scrolled after leaving the top, .is-hidden while scrolling down,
 * back on any scroll up. Never hides while the menu is open, a sub menu is open,
 * focus is inside the header, or the visitor prefers reduced motion.
 */
import { reducedMotion } from '../tokens.js';

const HIDE_AFTER = 160; // px from the top before the header may hide
const DELTA = 4; // ignore tiny scroll jitter

export function initHeader() {
	const header = document.querySelector( '[data-header]' );
	if ( ! header ) {
		return;
	}

	let lastY = window.scrollY;
	let queued = false;

	const isLocked = () =>
		reducedMotion() ||
		document.documentElement.classList.contains( 'has-menu-open' ) ||
		header.contains( document.activeElement ) ||
		header.querySelector( '.is-open' );

	const update = () => {
		queued = false;
		const y = Math.max( 0, window.scrollY );
		header.classList.toggle( 'is-scrolled', y > 8 );

		if ( isLocked() || y <= HIDE_AFTER ) {
			header.classList.remove( 'is-hidden' );
		} else if ( y > lastY + DELTA ) {
			header.classList.add( 'is-hidden' );
		} else if ( y < lastY - DELTA ) {
			header.classList.remove( 'is-hidden' );
		}

		if ( Math.abs( y - lastY ) > DELTA ) {
			lastY = y;
		}
	};

	window.addEventListener(
		'scroll',
		() => {
			if ( ! queued ) {
				queued = true;
				window.requestAnimationFrame( update );
			}
		},
		{ passive: true }
	);

	header.addEventListener( 'focusin', () => header.classList.remove( 'is-hidden' ) );
	update();
}
