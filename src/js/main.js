/**
 * Aqua theme script (ES module, bundled by Vite, loaded deferred).
 *
 * 1. Core UI right away (cheap): header, menus, footer, notice, facts ribbon, the opening drop.
 * 2. In idle time: the performance level (src/js/perf.js; its WebGL probe runs in a worker), then what the level allows:
 *    - high / medium: the motion chunk (GSAP, Lenis) and, on the homepage, the WebGL water chunk;
 *    - low: neither — CSS reveals from motion-lite.js and the static hero from hero-intro.js.
 *    Each step is its own task, so no single task blocks the main thread for long.
 * All per-frame work goes through one loop (src/js/ticker.js).
 */
import { initPerf, getPerf, releaseWorker } from './perf.js';
import { initHeader } from './modules/header.js';
import { initNav } from './modules/nav.js';
import { initMenuOverlay } from './modules/menu-overlay.js';
import { initFooter } from './modules/footer.js';
import { initConceptBar } from './modules/concept-bar.js';
import { initMarquee } from './modules/marquee.js';
import { initLiquid } from './modules/liquid.js';
import { initMotionLite } from './motion-lite.js';
import { initHeroStatic, playIntro } from './home/hero-intro.js';

const root = document.documentElement;
const hero = document.querySelector( '[data-water-hero]' );

/** Runs fn when the main thread is free (or after a short timeout). */
const idle = ( fn ) => ( 'requestIdleCallback' in window ? window.requestIdleCallback( fn, { timeout: 1200 } ) : window.setTimeout( fn, 120 ) );

initHeader();
initNav();
initMenuOverlay();
initFooter();
initConceptBar();
initMarquee();

// Opening drop, once per visit: <head> marked the first page of the visit with html.intro (the CSS entrance of
// the headline waits for it); it starts now and does not depend on WebGL.
if ( hero && hero.hasAttribute( 'data-intro' ) && root.classList.contains( 'intro' ) ) {
	try {
		window.sessionStorage.setItem( 'aqua-intro', '1' );
	} catch ( error ) {
		// Storage unavailable: it will simply play again on the next page.
	}
	hero.setAttribute( 'data-intro-playing', '' );
	playIntro( hero );
}

idle( async () => {
	const level = await initPerf();
	initLiquid();

	if ( 'low' === level ) {
		initMotionLite();
		if ( hero ) {
			initHeroStatic( hero );
		}
		releaseWorker();
		return;
	}

	idle( () => import( './motion.js' ).then( ( m ) => m.initMotion() ).catch( initMotionLite ) );
	if ( hero ) {
		idle( () =>
			import( './home/hero-water.js' )
				.then( ( m ) => ( 'low' === getPerf() ? initHeroStatic( hero ) : m.initHeroWater( hero ) ) )
				.catch( () => initHeroStatic( hero ) )
		);
	} else {
		releaseWorker(); // no hero on this page: stop the probe's worker
	}
} );
