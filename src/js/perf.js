/**
 * Performance levels: html[data-perf="high|medium|low"].
 * Every effect reads the level from here (or from the attribute in CSS) instead of deciding on its own.
 *
 *  high    hardware GPU and frames on time: WebGL water at full quality, glass header, parallax, pinned scenes.
 *  medium  frames run late: water rendered at a lower resolution, no blur, no parallax.
 *  low     software rendering or a weak device: no WebGL and no GSAP at all — photo, CSS reveals, native scroll.
 *
 * Decision:
 *  1. ?perf=low|medium|high forces a level for this page (testing).
 *  2. low at once for: prefers-reduced-motion, Save-Data, a very small device, no WebGL, a context that
 *     fails with failIfMajorPerformanceCaveat, or a software renderer (SwiftShader, Microsoft Basic Render
 *     Driver, llvmpipe, softpipe…). The WebGL probe runs in a worker (src/js/workers/gl.worker.js), because
 *     creating a context blocks its thread for ~100 ms.
 *  3. Otherwise start at high and measure ~60 real frames after load: late frames → medium, very late → low.
 *     The result is cached in sessionStorage, so inner pages neither probe nor measure again.
 *  4. The monitor keeps running — frames of the page and of the WebGL worker: about 2 s of slow frames in a
 *     row lowers the level on the fly. It never goes back up.
 *
 * ?perf=debug shows a small overlay (level, fps, renderer, render resolution). Combine: ?perf=low,debug.
 * inc/assets.php prints a tiny inline script that applies a cached / forced level before the first paint.
 */
import { ticker } from './ticker.js';

const KEY = 'aqua-perf';
const ORDER = [ 'low', 'medium', 'high' ];

const root = document.documentElement;
const listeners = new Set();
const state = { level: 'high', forced: false, debug: false, reason: '', renderer: '', render: '', fps: 0, waterMs: 0 };
let spare = null; // the probe's worker, kept for the hero water
let slowReports = 0;

const readCache = () => {
	try {
		return window.sessionStorage.getItem( KEY );
	} catch ( error ) {
		return null;
	}
};

const writeCache = ( level ) => {
	try {
		window.sessionStorage.setItem( KEY, level );
	} catch ( error ) {
		// Storage unavailable: the level is decided again on the next page.
	}
};

/** The WebGL worker (probe + hero water). */
export const createGLWorker = () => new Worker( new URL( './workers/gl.worker.js', import.meta.url ), { type: 'module' } );

/** Hands the probe's worker over (once), so the hero does not start a second one. */
export function takeWorker() {
	const taken = spare;
	spare = null;
	return taken;
}

/** Stops the probe's worker if nobody took it (pages without the hero). */
export function releaseWorker() {
	if ( spare ) {
		spare.terminate();
		spare = null;
	}
}

/**
 * Hardware WebGL? Asked in a worker, so the page never blocks on the GPU process.
 * Browsers without OffscreenCanvas in workers cannot be probed cheaply: they are treated as capable
 * (the frame measurement still protects them) and simply keep the static hero.
 */
function probeWebGL() {
	return new Promise( ( resolve ) => {
		if ( ! window.Worker || ! window.OffscreenCanvas ) {
			resolve( { ok: true, renderer: '', why: '' } );
			return;
		}
		let worker;
		const timer = window.setTimeout( () => finish( { ok: true, renderer: '', why: '' } ), 4000 );
		const finish = ( result ) => {
			window.clearTimeout( timer );
			if ( worker ) {
				worker.onmessage = null;
				worker.onerror = null;
				if ( result.ok && ! spare ) {
					spare = worker;
					window.setTimeout( releaseWorker, 15000 );
				} else {
					worker.terminate();
				}
			}
			resolve( result );
		};
		try {
			worker = createGLWorker();
			worker.onmessage = ( event ) => 'probe' === event.data.type && finish( event.data );
			worker.onerror = () => finish( { ok: true, renderer: '', why: '' } );
			worker.postMessage( { type: 'probe' } );
		} catch ( error ) {
			finish( { ok: true, renderer: '', why: '' } );
		}
	} );
}

function setLevel( level, reason ) {
	if ( level === state.level && root.dataset.perf === level ) {
		return;
	}
	const changed = level !== state.level;
	state.level = level;
	state.reason = reason;
	root.dataset.perf = level;
	if ( ! state.forced ) {
		writeCache( level );
	}
	if ( changed ) {
		listeners.forEach( ( fn ) => fn( level ) );
	}
}

/** One step down (high → medium → low). */
function lower( reason ) {
	const index = ORDER.indexOf( state.level );
	if ( index > 0 && ! state.forced ) {
		setLevel( ORDER[ index - 1 ], reason );
	}
}

/** Runs fn after the page has loaded and settled for a moment. */
function afterLoad( fn ) {
	const go = () => window.setTimeout( fn, 400 );
	if ( 'complete' === document.readyState ) {
		go();
	} else {
		window.addEventListener( 'load', go, { once: true } );
	}
}

/** Step 3: ~60 real frames decide between high / medium / low. */
function calibrate( done ) {
	const samples = [];
	const started = window.performance.now();
	const tick = ( now, delta ) => {
		if ( document.hidden || delta > 1000 ) {
			return;
		}
		samples.push( delta );
		if ( samples.length < 70 && now - started < 5000 ) {
			return;
		}
		ticker.remove( tick );
		const frames = samples.slice( 10 ).sort( ( a, b ) => a - b ); // the first frames after load are noise
		if ( frames.length >= 15 ) {
			const median = frames[ Math.floor( frames.length / 2 ) ];
			const kept = frames.slice( 0, Math.ceil( frames.length * 0.9 ) ); // trimmed mean: ignore the worst 10%
			const mean = kept.reduce( ( a, b ) => a + b, 0 ) / kept.length;
			// With vsync a late frame costs a whole extra interval, so the mean matters as much as the median.
			if ( median > 33 || mean > 30 ) {
				setLevel( 'low', `calibration: median ${ median.toFixed( 1 ) } ms` );
			} else if ( median > 20 || mean > 20 ) {
				setLevel( 'medium', `calibration: mean ${ mean.toFixed( 1 ) } ms` );
			} else {
				writeCache( state.level );
			}
		}
		done();
	};
	ticker.add( tick );
}

/** Step 4: about 2 s of slow frames in a row → one level down. */
function monitor() {
	let windowTime = 0;
	let slowTime = 0;
	const tick = ( now, delta ) => {
		if ( 'low' === state.level ) {
			ticker.remove( tick );
			return;
		}
		if ( document.hidden || delta > 1000 ) {
			windowTime = 0;
			slowTime = 0;
			return;
		}
		windowTime += delta;
		if ( delta > 25 ) {
			slowTime += delta;
		}
		if ( windowTime >= 2000 ) {
			if ( slowTime / windowTime > 0.8 ) {
				lower( 'frames stayed over 25 ms for 2 s' );
			}
			windowTime = 0;
			slowTime = 0;
		}
	};
	ticker.add( tick );
}

/**
 * Frame pacing of the WebGL worker (average frame time, reported about twice a second).
 * Four slow reports in a row (~2 s) lower the level, like slow frames of the page itself.
 */
export function reportFrameTime( ms ) {
	state.waterMs = ms;
	if ( document.hidden || ms > 1000 ) {
		slowReports = 0;
		return;
	}
	slowReports = ms > 25 ? slowReports + 1 : 0;
	if ( slowReports >= 4 ) {
		slowReports = 0;
		lower( `water frames at ${ ms.toFixed( 0 ) } ms` );
	}
}

/** ?perf=debug overlay. */
function debugOverlay() {
	const box = document.createElement( 'div' );
	box.setAttribute( 'aria-hidden', 'true' );
	box.style.cssText = 'position:fixed;left:8px;top:8px;z-index:99999;max-width:min(92vw,560px);padding:8px 10px;border-radius:8px;background:rgba(14,42,59,.92);color:#f7f9fa;font:12px/1.45 ui-monospace,Consolas,monospace;pointer-events:none;white-space:pre-wrap';
	document.body.appendChild( box );
	let frames = 0;
	let since = window.performance.now();
	ticker.add( ( now ) => {
		frames++;
		if ( now - since < 500 ) {
			return;
		}
		state.fps = Math.round( ( frames * 1000 ) / ( now - since ) );
		frames = 0;
		since = now;
		box.textContent =
			`perf: ${ state.level }${ state.forced ? ' (forced)' : '' } · ${ state.fps } fps\n` +
			`${ state.reason || 'no reason to lower' }\n` +
			`renderer: ${ state.renderer || 'not probed (level cached or forced)' }\n` +
			`water: ${ state.render ? `${ state.render }, frame every ${ state.waterMs.toFixed( 1 ) } ms` : 'static photo (no WebGL)' } · screen DPR ${ window.devicePixelRatio }`;
	} );
}

/** Current level: 'high' | 'medium' | 'low'. */
export const getPerf = () => state.level;

/** Subscribe to level changes (the level only ever goes down). */
export const onPerf = ( fn ) => listeners.add( fn );

/** Effects report what they render at (shown in the debug overlay). */
export const setRenderInfo = ( text ) => ( state.render = text );

/**
 * Decides the level. Resolves to 'high' | 'medium' | 'low' (after the worker probe on a first visit).
 */
export async function initPerf() {
	// URLSearchParams decodes the value (WordPress may redirect ?perf=low,debug to ?perf=low%2Cdebug).
	const param = new URLSearchParams( window.location.search ).get( 'perf' ) || '';
	const forced = ( /(low|medium|high)/.exec( param ) || [] )[ 1 ];
	state.debug = /debug/.test( param );
	if ( state.debug ) {
		debugOverlay();
	}

	if ( forced ) {
		state.forced = true;
		setLevel( forced, 'forced by ?perf=' );
		if ( state.debug ) {
			probeWebGL().then( ( gl ) => {
				state.renderer = gl.renderer;
				if ( 'low' === forced ) {
					releaseWorker();
				}
			} );
		}
		return state.level;
	}

	if ( window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		setLevel( 'low', 'prefers-reduced-motion' );
		return state.level;
	}
	if ( navigator.connection && navigator.connection.saveData ) {
		setLevel( 'low', 'Save-Data' );
		return state.level;
	}

	const cached = readCache();
	if ( 'low' === cached ) {
		setLevel( 'low', 'cached for this visit' );
		return state.level;
	}
	if ( 'medium' === cached || 'high' === cached ) {
		setLevel( cached, 'cached for this visit' );
		afterLoad( monitor );
		return state.level;
	}

	// Device hints (where the browser gives them): very small devices skip the heavy path altogether.
	const cores = navigator.hardwareConcurrency || 8;
	const memory = navigator.deviceMemory || 8;
	if ( cores <= 2 || memory <= 2 ) {
		setLevel( 'low', `small device (${ cores } cores, ${ memory } GB)` );
		return state.level;
	}

	const gl = await probeWebGL();
	state.renderer = gl.renderer;
	if ( ! gl.ok ) {
		setLevel( 'low', gl.why );
		return state.level;
	}

	// Modest phones start one level down. The frame measurement below can still lower the level.
	const phone = window.matchMedia( '(pointer: coarse) and (max-width: 61.99em)' ).matches;
	setLevel( phone && ( cores < 6 || memory < 4 ) ? 'medium' : 'high', 'measuring…' );
	afterLoad( () =>
		calibrate( () => {
			if ( 'high' === state.level ) {
				state.reason = 'calibration: frames on time';
			}
			monitor();
		} )
	);
	return state.level;
}
