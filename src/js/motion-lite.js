/**
 * Reveals without GSAP — the "low" performance level (software rendering, weak devices, Save-Data).
 * [data-reveal] elements fade in once with a short CSS transition when they come into view;
 * numbers show their final value (printed by PHP). No smooth scroll, no parallax, no pinned scenes:
 * the 7 stages are a list and the bottles shelf is a native scroller (their default CSS).
 * Reduced motion: nothing is hidden at all.
 */
import { reducedMotion } from './tokens.js';

export function initMotionLite() {
	const hero = document.querySelector( '[data-water-hero]' );
	if ( hero ) {
		hero.classList.add( 'is-ready' );
	}
	if ( reducedMotion() || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}

	const items = document.querySelectorAll( '[data-reveal]' );
	if ( ! items.length ) {
		return;
	}
	document.documentElement.classList.add( 'reveal-lite' );
	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-revealed' );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ rootMargin: '0px 0px -8% 0px' }
	);
	items.forEach( ( el ) => observer.observe( el ) );
}
