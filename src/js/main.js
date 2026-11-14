/**
 * Aqua theme script (ES module, bundled by Vite, loaded deferred).
 *
 * Core UI runs right away. The performance level (src/js/perf.js) decides what else is loaded:
 *  - high / medium: the motion chunk (GSAP, Lenis) and, on the homepage, the WebGL water chunk;
 *  - low: neither — CSS reveals from motion-lite.js and the static hero from hero-intro.js.
 * All per-frame work goes through one loop (src/js/ticker.js).
 */
import { initPerf, getPerf } from './perf.js';
import { initHeader } from './modules/header.js';
import { initNav } from './modules/nav.js';
import { initMenuOverlay } from './modules/menu-overlay.js';
import { initFooter } from './modules/footer.js';
import { initConceptBar } from './modules/concept-bar.js';
import { initMarquee } from './modules/marquee.js';
import { initLiquid } from './modules/liquid.js';
import { initMotionLite } from './motion-lite.js';
import { initHeroStatic } from './home/hero-intro.js';
import { reducedMotion } from './tokens.js';

const level = initPerf();

initHeader();
initNav();
initMenuOverlay();
initFooter();
initConceptBar();
initMarquee();
initLiquid();

// Opening drop: decided here, synchronously, once per visit — the hero plays it and the
// headline entrance waits for it.
const hero = document.querySelector( '[data-water-hero]' );
if ( hero && hero.hasAttribute( 'data-intro' ) && ! reducedMotion() ) {
	let seen = false;
	try {
		seen = window.sessionStorage.getItem( 'aqua-intro' ) === '1';
		window.sessionStorage.setItem( 'aqua-intro', '1' );
	} catch ( error ) {
		// Storage unavailable: play it.
	}
	if ( ! seen ) {
		hero.setAttribute( 'data-intro-playing', '' );
	}
}

if ( 'low' === level ) {
	initMotionLite();
	if ( hero ) {
		initHeroStatic( hero );
	}
} else {
	import( './motion.js' ).then( ( m ) => m.initMotion() ).catch( initMotionLite );
	if ( hero ) {
		import( './home/hero-water.js' )
			.then( ( m ) => ( 'low' === getPerf() ? initHeroStatic( hero ) : m.initHeroWater( hero ) ) )
			.catch( () => initHeroStatic( hero ) );
	}
}
