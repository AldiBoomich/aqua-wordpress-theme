/**
 * The one animation loop of the site.
 *
 * Every per-frame job (performance monitor, smooth scroll, hero water, liquid physics, scroll-throttled
 * updates) subscribes here instead of calling requestAnimationFrame itself. The loop sleeps when nobody
 * is subscribed. When the motion chunk loads GSAP, `adopt()` hands the loop over to GSAP's ticker,
 * so there is still exactly one requestAnimationFrame loop on the page.
 *
 * Callbacks receive ( now, delta ) in milliseconds.
 */
const subscribers = new Set();
const once = new Set();
let raf = 0;
let last = 0;
let adopted = false;

function dispatch( now ) {
	const delta = last ? now - last : 16.7;
	last = now;
	if ( once.size ) {
		const jobs = [ ...once ];
		once.clear();
		jobs.forEach( ( fn ) => fn( now, delta ) );
	}
	subscribers.forEach( ( fn ) => fn( now, delta ) );
}

function loop( now ) {
	raf = 0;
	dispatch( now );
	if ( ! adopted && ( subscribers.size || once.size ) ) {
		raf = window.requestAnimationFrame( loop );
	} else {
		last = 0;
	}
}

function wake() {
	if ( ! adopted && ! raf ) {
		raf = window.requestAnimationFrame( loop );
	}
}

export const ticker = {
	/** Run fn every frame until removed. */
	add( fn ) {
		subscribers.add( fn );
		wake();
	},
	remove( fn ) {
		subscribers.delete( fn );
	},
	/** Run fn once on the next frame (several calls with the same fn collapse into one). */
	next( fn ) {
		once.add( fn );
		wake();
	},
	/** Hand the loop over to GSAP's ticker (called once by the motion chunk). */
	adopt( gsapTicker ) {
		if ( adopted ) {
			return;
		}
		adopted = true;
		if ( raf ) {
			window.cancelAnimationFrame( raf );
			raf = 0;
		}
		gsapTicker.add( () => {
			if ( subscribers.size || once.size ) {
				dispatch( window.performance.now() );
			} else {
				last = 0;
			}
		} );
	},
};
