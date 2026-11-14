/**
 * Hero without WebGL (part of the main chunk, so the "low" level never downloads the WebGL code):
 * - the opening drop (once per visit): a line drop falls, then either the WebGL water answers (onSplash
 *   returns true) or two CSS rings spread from the point;
 * - static mode ("low"): the photo breathes slowly in CSS (see water-hero.css) and a tap / click leaves
 *   two small CSS rings — only that small area is redrawn, never the whole screen.
 */
import { reducedMotion } from '../tokens.js';

const DROP_SVG =
	'<svg class="ui-icon" viewBox="0 0 24 24"><path d="M12 2.6c-3.3 4.2-6.6 8-6.6 11.6a6.6 6.6 0 0 0 13.2 0C18.6 10.6 15.3 6.8 12 2.6Z"/><path class="ui-icon__water" d="M7.7 14.6c1.45-.95 2.85-.95 4.3 0s2.85.95 4.3 0"/></svg>';

/** Where the opening drop lands (px inside the hero media box). */
export function introPoint( media ) {
	const rect = media.getBoundingClientRect();
	const wide = rect.width >= 992;
	return { x: rect.width * ( wide ? 0.72 : 0.62 ), y: rect.height * ( wide ? 0.34 : 0.3 ) };
}

/** Two CSS rings spreading from a point. */
export function cssRings( media, x, y ) {
	for ( let i = 0; i < 2; i++ ) {
		const ring = document.createElement( 'span' );
		ring.className = 'water-hero__ring';
		ring.setAttribute( 'aria-hidden', 'true' );
		ring.style.left = `${ x }px`;
		ring.style.top = `${ y }px`;
		media.appendChild( ring );
		window.setTimeout( () => ring.remove(), 2200 );
	}
}

/**
 * Plays the opening drop if main.js marked this visit for it (data-intro-playing).
 *
 * @param {Element}  hero     The hero section.
 * @param {Function} onSplash Called when the drop lands; return true if the water itself answers.
 */
export function playIntro( hero, onSplash ) {
	if ( ! hero.hasAttribute( 'data-intro-playing' ) || hero.hasAttribute( 'data-intro-done' ) ) {
		return;
	}
	hero.setAttribute( 'data-intro-done', '' );

	const media = hero.querySelector( '.water-hero__media' );
	const { x, y } = introPoint( media );
	const drop = document.createElement( 'span' );
	drop.className = 'water-hero__drop';
	drop.setAttribute( 'aria-hidden', 'true' );
	drop.innerHTML = DROP_SVG;
	drop.style.left = `${ x }px`;
	drop.style.top = `${ y }px`;
	media.appendChild( drop );

	const fall = drop.animate(
		[
			{ transform: `translateY(${ -y - 60 }px) scale(0.9)`, opacity: 0 },
			{ transform: `translateY(${ -y * 0.4 }px) scale(1)`, opacity: 1, offset: 0.5 },
			{ transform: 'translateY(0) scale(1, 1.1)', opacity: 1 },
		],
		{ duration: 750, easing: 'cubic-bezier(0.55, 0, 1, 0.45)', fill: 'forwards' }
	);
	fall.onfinish = () => {
		drop.animate( [ { opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.3, 0.1)' } ], { duration: 220, fill: 'forwards' } ).onfinish = () => drop.remove();
		if ( ! onSplash( x, y ) ) {
			cssRings( media, x, y );
		}
	};
}

/** Static hero ("low"): CSS breathing while on screen, CSS rings on tap / click. */
export function initHeroStatic( hero ) {
	if ( hero.hasAttribute( 'data-static' ) ) {
		return;
	}
	hero.setAttribute( 'data-static', '' );
	const media = hero.querySelector( '.water-hero__media' );

	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( ( entries ) => hero.classList.toggle( 'is-inview', entries[ 0 ].isIntersecting ) ).observe( hero );
	}

	if ( reducedMotion() ) {
		return;
	}
	playIntro( hero, () => false );
	hero.addEventListener( 'pointerdown', ( event ) => {
		if ( event.target.closest( 'a, button' ) || ! hero.hasAttribute( 'data-static' ) ) {
			return;
		}
		const rect = media.getBoundingClientRect();
		cssRings( media, event.clientX - rect.left, event.clientY - rect.top );
	} );
}
