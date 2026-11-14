/**
 * Living water: a WebGL layer over the hero photo, rendered in a worker (src/js/workers/gl.worker.js)
 * on an OffscreenCanvas — the page's main thread never creates a context, compiles a shader or uploads
 * a texture. Loaded only on the "high" and "medium" performance levels; "low" keeps the photo (hero-intro.js).
 *
 * This file is the thin page side: it hands the canvas and the decoded photo to the worker and forwards
 * what the worker cannot see — pointer ripples, size, visibility.
 *
 * Budget:
 *  - render resolution: high = 1 device pixel per CSS pixel on desktop (≤ 1.5 on phones),
 *    medium = 0.7 (1 on phones); CSS stretches the canvas — the water is soft, the upscale does not show;
 *  - 30 fps while the water is at rest, 60 fps only while ripples are alive (in the worker);
 *  - paused off screen, in a background tab and under the open mobile menu;
 *  - context loss is handled (the photo shows through, the layer is rebuilt on restore);
 *    everything is released on pagehide and when the level drops to "low".
 * No worker / OffscreenCanvas support, or no hardware context: the static hero.
 */
import { getPerf, onPerf, setRenderInfo, reportFrameTime, takeWorker, createGLWorker } from '../perf.js';
import { finePointer } from '../tokens.js';
import { setSplashHandler, initHeroStatic } from './hero-intro.js';

/** Render scale (canvas pixels per CSS pixel) for a level. */
function scaleFor( level ) {
	const dpr = window.devicePixelRatio || 1;
	const phone = ! window.matchMedia( '(min-width: 62em)' ).matches;
	if ( 'medium' === level ) {
		return phone ? Math.min( dpr, 1 ) : 0.7;
	}
	return phone ? Math.min( dpr, 1.5 ) : Math.min( dpr, 1 );
}

/**
 * Builds the layer. Resolves to a controller, or rejects when the browser cannot do it.
 *
 * @param {Element}  hero    The hero section.
 * @param {Function} onGone  Called when the layer cannot go on (no hardware context).
 * @param {Function} onRenew Called when a lost context came back (the caller rebuilds the layer).
 */
async function createWater( hero, onGone, onRenew ) {
	const media = hero.querySelector( '.water-hero__media' );
	const image = hero.querySelector( '.water-hero__image' );
	const canvas = document.createElement( 'canvas' );
	if ( ! canvas.transferControlToOffscreen || ! window.createImageBitmap ) {
		throw new Error( 'OffscreenCanvas is not supported' );
	}
	// Decoded off the main thread; flipped here because WebGL ignores UNPACK_FLIP_Y for bitmaps.
	const bitmap = await window.createImageBitmap( image, { imageOrientation: 'flipY' } );
	const worker = takeWorker() || createGLWorker();
	const offscreen = canvas.transferControlToOffscreen();

	canvas.className = 'water-hero__canvas';
	canvas.setAttribute( 'aria-hidden', 'true' );
	media.insertBefore( canvas, hero.querySelector( '.water-hero__veil' ) );

	const abort = new AbortController();
	const listen = { signal: abort.signal };
	const size = () => {
		const rect = media.getBoundingClientRect();
		return { width: Math.max( 1, Math.round( rect.width ) ), height: Math.max( 1, Math.round( rect.height ) ), scale: scaleFor( getPerf() ) };
	};
	let onScreen = true;
	let menuOpen = false;
	let lost = false;
	let running = null;

	const update = () => {
		const should = onScreen && ! document.hidden && ! menuOpen && ! lost;
		if ( should !== running ) {
			running = should;
			worker.postMessage( { type: 'run', on: should } );
		}
	};

	worker.onmessage = ( event ) => {
		const m = event.data;
		if ( 'ready' === m.type ) {
			canvas.classList.add( 'is-ready' ); // fades in over the photo
			setRenderInfo( m.info );
		} else if ( 'info' === m.type ) {
			setRenderInfo( m.info );
		} else if ( 'stats' === m.type ) {
			reportFrameTime( m.ms );
		} else if ( 'fail' === m.type ) {
			onGone();
		} else if ( 'lost' === m.type ) {
			lost = true;
			canvas.classList.remove( 'is-ready' ); // the photo underneath shows through
			update();
		} else if ( 'restored' === m.type ) {
			onRenew();
		}
	};
	worker.onerror = () => onGone();

	worker.postMessage( { type: 'init', canvas: offscreen, bitmap, ...size() }, [ offscreen, bitmap ] );

	// Pointer: moving leaves small rings (throttled), a press leaves a big one.
	const local = ( event ) => {
		const rect = media.getBoundingClientRect();
		return { x: event.clientX - rect.left, y: event.clientY - rect.top };
	};
	const ripple = ( x, y, w ) => worker.postMessage( { type: 'ripple', x, y, w } );
	let last = { x: -999, y: -999, t: 0 };
	if ( finePointer() ) {
		hero.addEventListener(
			'pointermove',
			( event ) => {
				const p = local( event );
				const moved = Math.hypot( p.x - last.x, p.y - last.y );
				if ( event.timeStamp - last.t > 90 && moved > 28 ) {
					ripple( p.x, p.y, Math.min( 0.55, 0.18 + moved / 180 ) );
					last = { x: p.x, y: p.y, t: event.timeStamp };
				}
			},
			listen
		);
	}
	hero.addEventListener(
		'pointerdown',
		( event ) => {
			if ( ! event.target.closest( 'a, button' ) ) {
				const p = local( event );
				ripple( p.x, p.y, 1.1 );
			}
		},
		listen
	);

	const observer = new IntersectionObserver( ( entries ) => {
		onScreen = entries[ 0 ].isIntersecting;
		update();
	} );
	observer.observe( hero );
	document.addEventListener( 'visibilitychange', update, listen );
	document.addEventListener(
		'aqua:menu',
		( event ) => {
			menuOpen = !! ( event.detail && event.detail.open );
			update();
		},
		listen
	);

	let resizeTimer = 0;
	const resize = () => worker.postMessage( { type: 'size', ...size() } );
	window.addEventListener(
		'resize',
		() => {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( resize, 120 );
		},
		listen
	);

	update();

	return {
		ripple,
		/** Apply the render scale of the current level. */
		rescale: resize,
		destroy() {
			abort.abort();
			observer.disconnect();
			window.clearTimeout( resizeTimer );
			worker.onmessage = null;
			worker.onerror = null;
			worker.postMessage( { type: 'destroy' } ); // frees the GL resources, then closes itself
			canvas.remove();
			setRenderInfo( '' );
		},
	};
}

export function initHeroWater( hero ) {
	const image = hero.querySelector( '.water-hero__image' );
	if ( ! image ) {
		return;
	}
	let water = null;
	let building = false;

	const toStatic = () => {
		if ( water ) {
			water.destroy();
			water = null;
		}
		initHeroStatic( hero );
	};

	const build = () => {
		if ( water || building || 'low' === getPerf() ) {
			return;
		}
		building = true;
		createWater( hero, toStatic, () => {
			// Restored context: every GL resource is gone — rebuild the layer from scratch.
			if ( water ) {
				water.destroy();
				water = null;
			}
			build();
		} )
			.then( ( created ) => {
				building = false;
				if ( 'low' === getPerf() ) {
					created.destroy();
					initHeroStatic( hero );
					return;
				}
				water = created;
				hero.removeAttribute( 'data-static' );
			} )
			.catch( () => {
				building = false;
				toStatic();
			} );
	};

	// The opening drop (hero-intro.js) lands in the water if it is running by then.
	setSplashHandler( ( x, y ) => {
		if ( ! water ) {
			return false;
		}
		water.ripple( x, y, 2.2 );
		return true;
	} );

	onPerf( ( level ) => {
		if ( 'low' === level ) {
			toStatic();
		} else if ( water ) {
			water.rescale();
		}
	} );

	// Release the GPU when the page is put away; come back cleanly from the back/forward cache.
	window.addEventListener( 'pagehide', () => {
		if ( water ) {
			water.destroy();
			water = null;
		}
	} );
	window.addEventListener( 'pageshow', ( event ) => {
		if ( event.persisted ) {
			build();
		}
	} );

	// Start once the photo is decoded.
	const ready = image.complete && image.naturalWidth ? Promise.resolve() : new Promise( ( resolve ) => image.addEventListener( 'load', resolve, { once: true } ) );
	ready.then( build );
}
