/**
 * Hardware acceleration check for GPU-only effects.
 * html.no-gpu when WebGL is missing or software-rendered (SwiftShader, Microsoft Basic Render Driver, llvmpipe):
 * there the frosted header glass would cost every scroll frame, so the bar is solid instead (site-header.css).
 * The answer comes from a tiny worker (no WebGL start-up on the main thread) or, on the homepage, from the
 * hero water (event "aqua:gpu"), whichever is first; it is kept for the visit (sessionStorage).
 */
const KEY = 'aqua-gpu';

const apply = ( software ) => document.documentElement.classList.toggle( 'no-gpu', !! software );

const remember = ( software ) => {
	apply( software );
	try {
		window.sessionStorage.setItem( KEY, software ? 'software' : 'hardware' );
	} catch ( error ) {
		// Storage unavailable: the next page asks again.
	}
};

export function initGpu() {
	let saved = null;
	try {
		saved = window.sessionStorage.getItem( KEY );
	} catch ( error ) {
		// Ask below.
	}
	if ( saved ) {
		apply( saved === 'software' );
		return;
	}

	let done = false;
	const answer = ( software ) => {
		if ( ! done ) {
			done = true;
			remember( software );
		}
	};

	document.addEventListener( 'aqua:gpu', ( event ) => answer( event.detail.software ), { once: true } );

	if ( typeof Worker === 'undefined' ) {
		return;
	}
	const probe = () => {
		if ( done ) {
			return;
		}
		let worker;
		try {
			worker = new Worker( new URL( './gpu-probe.worker.js', import.meta.url ), { type: 'module' } );
		} catch ( error ) {
			return;
		}
		worker.addEventListener( 'message', ( { data } ) => {
			worker.terminate();
			if ( ! data.unknown ) {
				answer( data.software );
			}
		} );
		worker.addEventListener( 'error', () => worker.terminate() );
	};
	// After the page has loaded, so the probe never competes with the first paint or the hero water start-up.
	const idle = window.requestIdleCallback || ( ( fn ) => window.setTimeout( fn, 200 ) );
	if ( document.readyState === 'complete' ) {
		idle( probe );
	} else {
		window.addEventListener( 'load', () => idle( probe ), { once: true } );
	}
}
