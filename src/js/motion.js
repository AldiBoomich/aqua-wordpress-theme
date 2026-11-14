/**
 * Motion layer (loaded as its own chunk on every page).
 * - Lenis smooth scroll on devices with a mouse, synced with GSAP ScrollTrigger.
 * - Reveals from data attributes:
 *     data-reveal="lines"  — lines rise from masks (SplitText);
 *     data-reveal="fade"   — rise + fade (batched, staggered);
 *     data-reveal="image"  — the frame opens (clip-path) while the photo settles, then drifts slightly (parallax).
 * - data-count — numbers count up once on view (the final value is printed by PHP).
 * - Homepage scenes: hero entrance, 7 stages (pinned), bottles shelf (horizontal).
 * Reduced motion: no smooth scroll, no reveals, no scenes — content is simply there.
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';
import { EASE, finePointer, reducedMotion } from './tokens.js';
import { initStages } from './home/stages.js';
import { initShelf } from './home/shelf.js';

gsap.registerPlugin( ScrollTrigger, SplitText );

let lenis = null;

/** Smooth scroll for mouse users; paused while the full-screen menu is open. */
function initLenis() {
	if ( ! finePointer() ) {
		return;
	}
	lenis = new Lenis( { lerp: 0.1, anchors: { offset: -96 }, autoRaf: false } );
	lenis.on( 'scroll', ScrollTrigger.update );
	gsap.ticker.add( ( time ) => lenis.raf( time * 1000 ) );
	gsap.ticker.lagSmoothing( 0 );
	document.addEventListener( 'aqua:menu', ( event ) => ( event.detail && event.detail.open ? lenis.stop() : lenis.start() ) );
	window.aquaLenis = lenis;
}

/** Text lines rise from masks. Paragraph containers (WYSIWYG) are split per paragraph. */
function initLines() {
	document.querySelectorAll( '[data-reveal="lines"]' ).forEach( ( el ) => {
		const targets = el.matches( 'h1, h2, h3, p' ) ? [ el ] : [ ...el.querySelectorAll( 'p, h2, h3, li' ) ];
		( targets.length ? targets : [ el ] ).forEach( ( target ) => {
			SplitText.create( target, {
				type: 'lines',
				mask: 'lines',
				linesClass: 'split-line',
				autoSplit: true,
				onSplit: ( self ) =>
					gsap.from( self.lines, {
						yPercent: 110,
						duration: 1.1,
						ease: EASE.out,
						stagger: 0.08,
						scrollTrigger: { trigger: target, start: 'top 88%', once: true },
					} ),
			} );
		} );
	} );
}

/** Rise + fade, batched so neighbours arrive one after another. */
function initFades() {
	const items = gsap.utils.toArray( '[data-reveal="fade"]' );
	if ( ! items.length ) {
		return;
	}
	gsap.set( items, { autoAlpha: 0, y: 28 } );
	ScrollTrigger.batch( items, {
		start: 'top 90%',
		once: true,
		onEnter: ( batch ) => gsap.to( batch, { autoAlpha: 1, y: 0, duration: 1, ease: EASE.out, stagger: 0.08, overwrite: true } ),
	} );
}

/** Photo frames open up; the photo inside drifts a little slower than the page. */
function initImages() {
	document.querySelectorAll( '[data-reveal="image"]' ).forEach( ( frame ) => {
		const img = frame.querySelector( 'img' );
		gsap.fromTo(
			frame,
			{ clipPath: 'inset(14% 10% 14% 10% round 28px)' },
			{ clipPath: 'inset(0% 0% 0% 0% round 28px)', duration: 1.4, ease: EASE.out, scrollTrigger: { trigger: frame, start: 'top 85%', once: true } }
		);
		if ( img ) {
			gsap.fromTo( img, { scale: 1.18 }, { scale: 1.06, duration: 1.6, ease: EASE.out, scrollTrigger: { trigger: frame, start: 'top 85%', once: true } } );
			gsap.fromTo(
				img,
				{ yPercent: -4 },
				{ yPercent: 4, ease: 'none', scrollTrigger: { trigger: frame, start: 'top bottom', end: 'bottom top', scrub: true } }
			);
		}
	} );
}

/** Numbers count up once (en-US grouping, like number_format_i18n on an English site). */
function initCounters() {
	document.querySelectorAll( '[data-count]' ).forEach( ( el ) => {
		const target = parseInt( el.getAttribute( 'data-count' ), 10 ) || 0;
		if ( target <= 0 ) {
			return;
		}
		const format = ( value ) => Math.round( value ).toLocaleString( 'en-US' );
		const state = { value: 0 };
		el.textContent = format( 0 );
		ScrollTrigger.create( {
			trigger: el,
			start: 'top 90%',
			once: true,
			onEnter: () =>
				gsap.to( state, {
					value: target,
					duration: 1.8,
					ease: 'power3.out',
					onUpdate: () => ( el.textContent = format( state.value ) ),
				} ),
		} );
	} );
}

/** Homepage hero: headline lines rise, then text, buttons and facts. Waits for the opening drop. */
function initHeroEntrance() {
	const hero = document.querySelector( '[data-water-hero]' );
	if ( ! hero ) {
		return;
	}
	const title = hero.querySelector( '[data-hero-title]' );
	const fades = hero.querySelectorAll( '[data-hero-fade]' );
	const delay = hero.hasAttribute( 'data-intro-playing' ) ? 0.85 : 0.1;

	hero.classList.add( 'is-ready' );
	if ( title ) {
		SplitText.create( title, {
			type: 'lines',
			mask: 'lines',
			linesClass: 'split-line',
			autoSplit: true,
			onSplit: ( self ) => gsap.from( self.lines, { yPercent: 115, duration: 1.25, ease: EASE.out, stagger: 0.09, delay } ),
		} );
	}
	gsap.from( fades, { autoAlpha: 0, y: 22, duration: 1.1, ease: EASE.out, stagger: 0.1, delay: delay + 0.35 } );
}

export function initMotion() {
	const hero = document.querySelector( '[data-water-hero]' );

	if ( reducedMotion() ) {
		if ( hero ) {
			hero.classList.add( 'is-ready' );
		}
		return;
	}

	initLenis();
	initHeroEntrance();
	initLines();
	initFades();
	initImages();
	initCounters();
	initStages( gsap, ScrollTrigger );
	initShelf( gsap, ScrollTrigger, () => lenis );

	// Late layout changes (fonts, images) move trigger positions.
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( () => ScrollTrigger.refresh() );
	}
	window.addEventListener( 'load', () => ScrollTrigger.refresh(), { once: true } );
}
