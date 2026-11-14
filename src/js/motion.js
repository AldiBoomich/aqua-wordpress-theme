/**
 * Motion layer — its own chunk, loaded only on the "high" and "medium" performance levels
 * ("low" uses motion-lite.js and never downloads GSAP).
 *
 * - One loop: GSAP's ticker takes over the shared ticker (src/js/ticker.js); Lenis runs on it too.
 * - Lenis smooth scroll for mouse users, synced with ScrollTrigger.
 * - Reveals from data attributes, all transform / opacity only:
 *     data-reveal="lines"  — lines rise from masks. The text is split only when it comes into view and the
 *                            split is reverted when the animation ends (no extra spans left in the DOM);
 *     data-reveal="fade"   — rise + fade (batched, staggered);
 *     data-reveal="image"  — the frame grows open while the photo settles (two opposite scales: no clip-path,
 *                            no repaint); on "high" the photo then drifts slightly with the scroll (parallax).
 * - data-count — numbers count up once on view (the final value is printed by PHP).
 * - Homepage scenes: 7 stages (pinned), bottles shelf (horizontal). The hero entrance is pure CSS.
 *
 * If the level drops while the page is open: "medium" removes the parallax, "low" also removes the
 * smooth scroll and un-pins the scenes (they fall back to their plain layouts).
 */
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';
import { EASE, finePointer, reducedMotion } from './tokens.js';
import { ticker } from './ticker.js';
import { getPerf, onPerf } from './perf.js';
import { initStages } from './home/stages.js';
import { initShelf } from './home/shelf.js';

gsap.registerPlugin( ScrollTrigger, SplitText );

let lenis = null;
let lenisTick = null;
let parallax = []; // scrubbed tweens ("high" only)
let scenes = []; // { kill() } of the pinned scenes

/** Smooth scroll for mouse users; paused while the full-screen menu is open. */
function initLenis() {
	if ( ! finePointer() ) {
		return;
	}
	lenis = new Lenis( { lerp: 0.1, anchors: { offset: -96 }, autoRaf: false } );
	lenis.on( 'scroll', ScrollTrigger.update );
	lenisTick = ( now ) => lenis.raf( now );
	ticker.add( lenisTick );
	gsap.ticker.lagSmoothing( 0 );
	document.addEventListener( 'aqua:menu', ( event ) => {
		if ( lenis ) {
			if ( event.detail && event.detail.open ) {
				lenis.stop();
			} else {
				lenis.start();
			}
		}
	} );
	window.aquaLenis = lenis;
}

function killLenis() {
	if ( ! lenis ) {
		return;
	}
	ticker.remove( lenisTick );
	lenis.destroy();
	lenis = null;
	delete window.aquaLenis;
}

/** Splits `target` into masked lines, plays them in, then restores the original markup. */
function playLines( target, delay = 0 ) {
	let split = null;
	const finish = () => {
		if ( split ) {
			split.revert();
			split = null;
		}
	};
	split = SplitText.create( target, { type: 'lines', mask: 'lines', linesClass: 'split-line' } );
	gsap.set( target, { autoAlpha: 1 } );
	gsap.from( split.lines, { yPercent: 110, duration: 1.1, ease: EASE.out, stagger: 0.08, delay, onComplete: finish, onInterrupt: finish } );
}

/** Text lines rise from masks. Paragraph containers (WYSIWYG) are split per paragraph. */
function initLines() {
	document.querySelectorAll( '[data-reveal="lines"]' ).forEach( ( el ) => {
		const inner = el.matches( 'h1, h2, h3, p' ) ? [ el ] : [ ...el.querySelectorAll( 'p, h2, h3, li' ) ];
		const targets = inner.length ? inner : [ el ];
		gsap.set( targets, { autoAlpha: 0 } ); // waits hidden; split only when it is about to be seen
		ScrollTrigger.create( {
			trigger: el,
			start: 'top 88%',
			once: true,
			onEnter: () => targets.forEach( ( target, i ) => playLines( target, i * 0.12 ) ),
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
		onEnter: ( batch ) => gsap.to( batch, { autoAlpha: 1, y: 0, duration: 1, ease: EASE.out, stagger: 0.08, overwrite: true, clearProps: 'transform' } ),
	} );
}

/** Photo frames grow open (frame scales up, photo scales down); on "high" the photo then drifts with the scroll. */
function initImages() {
	document.querySelectorAll( '[data-reveal="image"]' ).forEach( ( frame ) => {
		const img = frame.querySelector( 'img' );
		const trigger = { trigger: frame, start: 'top 85%', once: true };
		gsap.fromTo( frame, { scale: 0.86, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: 1.4, ease: EASE.out, scrollTrigger: trigger, clearProps: 'transform' } );
		if ( ! img ) {
			return;
		}
		gsap.fromTo( img, { scale: 1.3 }, { scale: 1.06, duration: 1.6, ease: EASE.out, scrollTrigger: trigger } );
		if ( 'high' === getPerf() ) {
			parallax.push(
				gsap.fromTo( img, { yPercent: -4 }, { yPercent: 4, ease: 'none', scrollTrigger: { trigger: frame, start: 'top bottom', end: 'bottom top', scrub: true } } )
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
		let shown = format( 0 );
		el.textContent = shown;
		ScrollTrigger.create( {
			trigger: el,
			start: 'top 90%',
			once: true,
			onEnter: () =>
				gsap.to( state, {
					value: target,
					duration: 1.8,
					ease: 'power3.out',
					onUpdate: () => {
						const text = format( state.value );
						if ( text !== shown ) {
							shown = text;
							el.textContent = text; // only when the visible number changes
						}
					},
				} ),
		} );
	} );
}

function killParallax() {
	parallax.forEach( ( tween ) => {
		if ( tween.scrollTrigger ) {
			tween.scrollTrigger.kill();
		}
		tween.kill();
	} );
	parallax = [];
}

/** The level only goes down: strip what the new level does not allow. */
function applyLevel( level ) {
	if ( 'high' !== level ) {
		killParallax();
	}
	if ( 'low' === level ) {
		killLenis();
		scenes.forEach( ( scene ) => scene.kill() );
		scenes = [];
		ScrollTrigger.refresh();
	}
}

export function initMotion() {
	if ( reducedMotion() ) {
		return;
	}

	ticker.adopt( gsap.ticker ); // from here on GSAP's ticker is the one loop
	initLenis();
	initLines();
	initFades();
	initImages();
	initCounters();
	scenes = [ initStages( gsap, ScrollTrigger ), initShelf( gsap, ScrollTrigger, () => lenis ) ].filter( Boolean );
	onPerf( applyLevel );

	// Late layout changes (fonts, images) move trigger positions.
	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( () => ScrollTrigger.refresh() );
	}
	window.addEventListener( 'load', () => ScrollTrigger.refresh(), { once: true } );
}
