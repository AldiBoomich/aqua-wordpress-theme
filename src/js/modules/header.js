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
	warmGlass( header );
}

/**
 * Builds the header glass on the graphics card before the first scroll (see .site-header__warm):
 * only with hardware WebGL (reported by the hero water, event "aqua:gpu"), on desktop where the glass
 * exists, and while the page is still at the top. Software rendering has nothing to warm up.
 */
function warmGlass( header ) {
	document.addEventListener(
		'aqua:gpu',
		( event ) => {
			if (
				event.detail.software ||
				reducedMotion() ||
				header.classList.contains( 'is-scrolled' ) ||
				! window.matchMedia( '(min-width: 62em)' ).matches
			) {
				return;
			}
			const layer = document.createElement( 'div' );
			layer.className = 'site-header__warm';
			layer.setAttribute( 'aria-hidden', 'true' );
			layer.style.height = `${ header.getBoundingClientRect().height }px`;
			document.body.appendChild( layer );
			let frames = 0;
			const tick = () => {
				if ( ++frames < 12 ) {
					window.requestAnimationFrame( tick );
				} else {
					layer.remove();
				}
			};
			window.requestAnimationFrame( tick );
		},
		{ once: true }
	);
}
