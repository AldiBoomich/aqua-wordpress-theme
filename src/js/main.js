/**
 * Aqua theme script (ES module, bundled by Vite, loaded deferred).
 * Core UI runs right away; the motion layer (GSAP, Lenis) and the hero WebGL water load as separate chunks.
 */
import { initHeader } from './modules/header.js';
import { initNav } from './modules/nav.js';
import { initMenuOverlay } from './modules/menu-overlay.js';
import { initFooter } from './modules/footer.js';
import { initConceptBar } from './modules/concept-bar.js';
import { initMarquee } from './modules/marquee.js';
import { initContact } from './modules/contact.js';
import { initGpu } from './modules/gpu.js';
import { reducedMotion } from './tokens.js';

initGpu();
initHeader();
initNav();
initMenuOverlay();
initFooter();
initConceptBar();
initMarquee();
initContact();

// Opening drop: decided here, synchronously, once per visit — the hero water plays it and the
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

import( './motion.js' ).then( ( m ) => m.initMotion() );

if ( hero ) {
	const startWater = () => import( './home/hero-water.js' ).then( ( m ) => m.initHeroWater() );
	// The water canvas fades in over the same photo once it is ready, so after a page transition it can start when
	// the transition has finished (its WebGL start-up dropped transition frames). The opening drop keeps its timing.
	if ( window.aquaRevealed && ! hero.hasAttribute( 'data-intro-playing' ) ) {
		window.aquaRevealed.then( startWater );
	} else {
		startWater();
	}
}
