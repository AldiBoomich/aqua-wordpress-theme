/**
 * Living water: a WebGL (OGL) layer over the hero photo.
 *
 * The surface is a slow ambient swell plus up to 16 circular ripples (pointer moves, taps, the opening
 * drop); the photo is refracted by the slope of that surface and gets a soft specular glint
 * (water-shader.js, drawn by water-renderer.js).
 *
 * Rendering runs in a worker on an OffscreenCanvas (water.worker.js), so the page's main thread never
 * compiles shaders, uploads the photo or waits for a frame; this module only forwards pointer rings,
 * size and visibility. Browsers without OffscreenCanvas WebGL / module workers render on the main thread.
 *
 * Budget: canvas ≤ 1.5 device pixels per CSS pixel (1.25 on phones), rendering only while the hero is on
 * screen and the tab is visible; the GL context is released on pagehide. Not started for reduced motion,
 * Save-Data, or phones with few cores / little memory (the static photo stays).
 */
import { reducedMotion, finePointer } from '../tokens.js';

/** Whether this device should run the shader. */
function capable() {
	if ( reducedMotion() ) {
		return false;
	}
	const connection = navigator.connection;
	if ( connection && connection.saveData ) {
		return false;
	}
	if ( window.matchMedia( '(min-width: 62em)' ).matches ) {
		return true;
	}
	const cores = navigator.hardwareConcurrency || 2;
	const memory = navigator.deviceMemory || 4;
	return cores >= 6 && memory >= 4;
}

/** The opening drop: a line drop falls and the water answers (rings in CSS when WebGL is off). */
function playIntro( hero, x, y, onSplash ) {
	// Decided synchronously in main.js (once per visit), so the headline can wait for the drop.
	if ( ! hero.hasAttribute( 'data-intro-playing' ) ) {
		return false;
	}

	const media = hero.querySelector( '.water-hero__media' );
	const drop = document.createElement( 'span' );
	drop.className = 'water-hero__drop';
	drop.setAttribute( 'aria-hidden', 'true' );
	drop.innerHTML =
		'<svg class="ui-icon" viewBox="0 0 24 24"><path d="M12 2.6c-3.3 4.2-6.6 8-6.6 11.6a6.6 6.6 0 0 0 13.2 0C18.6 10.6 15.3 6.8 12 2.6Z"/><path class="ui-icon__water" d="M7.7 14.6c1.45-.95 2.85-.95 4.3 0s2.85.95 4.3 0"/></svg>';
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
		drop.animate( [ { opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.3, 0.1)' } ], { duration: 220, fill: 'forwards' } ).onfinish = () =>
			drop.remove();
		if ( ! onSplash() ) {
			for ( let i = 0; i < 2; i++ ) {
				const ring = document.createElement( 'span' );
				ring.className = 'water-hero__ring';
				ring.style.left = `${ x }px`;
				ring.style.top = `${ y }px`;
				media.appendChild( ring );
				window.setTimeout( () => ring.remove(), 2200 );
			}
		}
	};
	return true;
}

/** Can this browser render WebGL on an OffscreenCanvas inside a module worker? */
function offscreenSupported() {
	return (
		typeof Worker === 'function' &&
		typeof OffscreenCanvas === 'function' &&
		typeof window.createImageBitmap === 'function' &&
		'transferControlToOffscreen' in HTMLCanvasElement.prototype &&
		typeof OffscreenCanvas.prototype.getContext === 'function'
	);
}

/** Wall-clock timestamp (ms) shared by the page and the worker. */
const stamp = () => performance.timeOrigin + performance.now();

/**
 * Worker backend: same interface as the main-thread one. Starts at once (no waiting for the worker),
 * so the opening drop keeps its timing; if the worker reports it has no WebGL, onFail() is called.
 */
function workerBackend( canvas, bitmap, dpr, size, epoch, onFail ) {
	const offscreen = canvas.transferControlToOffscreen();
	const worker = new Worker( new URL( './water.worker.js', import.meta.url ), { type: 'module' } );
	const fail = () => {
		worker.terminate();
		onFail();
	};
	worker.addEventListener( 'error', fail, { once: true } );
	worker.addEventListener( 'message', ( { data } ) => {
		if ( data.type === 'failed' ) {
			fail();
		} else if ( data.type === 'ready' ) {
			canvas.classList.add( 'is-ready' );
		}
	} );
	worker.postMessage( { type: 'init', canvas: offscreen, bitmap, dpr, width: size.w, height: size.h, epoch }, [ offscreen, bitmap ] );
	return {
		resize: ( w, h ) => worker.postMessage( { type: 'resize', width: w, height: h } ),
		addRipple: ( x, y, strength, at ) => worker.postMessage( { type: 'ripple', x, y, strength, at } ),
		run: ( on ) => worker.postMessage( { type: 'run', on } ),
		lose: () => worker.postMessage( { type: 'lose' } ),
	};
}

/** Main-thread backend (no OffscreenCanvas): the renderer chunk loads only here. */
async function mainBackend( canvas, image, dpr, size, epoch ) {
	const { createWaterRenderer } = await import( './water-renderer.js' );
	const water = createWaterRenderer( canvas, { dpr, image, width: size.w, height: size.h, flipY: true } );
	if ( ! water ) {
		return null;
	}
	const now = () => ( stamp() - epoch ) / 1000;
	let running = false;
	let raf = 0;
	let shown = false;
	const frame = () => {
		water.render( now() );
		if ( ! shown ) {
			shown = true;
			canvas.classList.add( 'is-ready' );
		}
		raf = window.requestAnimationFrame( frame );
	};
	return {
		resize: ( w, h ) => water.resize( w, h ),
		addRipple: ( x, y, strength, at ) => water.addRipple( x, y, strength, ( at - epoch ) / 1000 ),
		run: ( on ) => {
			if ( on === running ) {
				return;
			}
			running = on;
			if ( on ) {
				raf = window.requestAnimationFrame( frame );
			} else {
				window.cancelAnimationFrame( raf );
			}
		},
		lose: () => {
			window.cancelAnimationFrame( raf );
			running = false;
			water.lose();
		},
	};
}

export function initHeroWater() {
	const hero = document.querySelector( '[data-water-hero]' );
	const image = hero ? hero.querySelector( '.water-hero__image' ) : null;
	if ( ! hero || ! image ) {
		return;
	}

	const media = hero.querySelector( '.water-hero__media' );
	const introPoint = () => {
		const rect = media.getBoundingClientRect();
		const wide = rect.width >= 992;
		return { x: rect.width * ( wide ? 0.72 : 0.62 ), y: rect.height * ( wide ? 0.34 : 0.3 ) };
	};

	if ( ! capable() ) {
		const point = introPoint();
		playIntro( hero, point.x, point.y, () => false );
		return;
	}

	const small = ! window.matchMedia( '(min-width: 62em)' ).matches;
	const dpr = Math.min( window.devicePixelRatio || 1, small ? 1.25 : 1.5 );
	const epoch = stamp();
	const measure = () => {
		const rect = media.getBoundingClientRect();
		return { w: Math.max( 1, rect.width ), h: Math.max( 1, rect.height ) };
	};

	let water = null; // backend, once started
	let onScreen = true;
	const shouldRun = () => onScreen && ! document.hidden;

	const addRipple = ( x, y, strength ) => {
		if ( water ) {
			water.addRipple( x, y, strength, stamp() );
		}
	};

	// Pointer: moving makes small rings (throttled), a press makes a big one.
	let last = { x: -999, y: -999, t: 0 };
	const local = ( event ) => {
		const rect = media.getBoundingClientRect();
		return { x: event.clientX - rect.left, y: event.clientY - rect.top };
	};
	if ( finePointer() ) {
		hero.addEventListener( 'pointermove', ( event ) => {
			const p = local( event );
			const t = ( stamp() - epoch ) / 1000;
			const moved = Math.hypot( p.x - last.x, p.y - last.y );
			if ( t - last.t > 0.06 && moved > 22 ) {
				addRipple( p.x, p.y, Math.min( 0.55, 0.18 + moved / 180 ) );
				last = { x: p.x, y: p.y, t };
			}
		} );
	}
	hero.addEventListener( 'pointerdown', ( event ) => {
		if ( event.target.closest( 'a, button' ) ) {
			return;
		}
		const p = local( event );
		addRipple( p.x, p.y, 1.1 );
	} );

	// Render only while visible.
	new IntersectionObserver( ( entries ) => {
		onScreen = entries[ 0 ].isIntersecting;
		if ( water ) {
			water.run( shouldRun() );
		}
	} ).observe( hero );
	document.addEventListener( 'visibilitychange', () => water && water.run( shouldRun() ) );
	window.addEventListener( 'pagehide', () => water && water.lose() );

	let resizeTimer = 0;
	window.addEventListener( 'resize', () => {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( () => {
			if ( water ) {
				const size = measure();
				water.resize( size.w, size.h );
			}
		}, 100 );
	} );

	const makeCanvas = () => {
		const canvas = document.createElement( 'canvas' );
		canvas.className = 'water-hero__canvas';
		canvas.setAttribute( 'aria-hidden', 'true' );
		const size = measure();
		canvas.width = Math.round( size.w * dpr );
		canvas.height = Math.round( size.h * dpr );
		media.insertBefore( canvas, hero.querySelector( '.water-hero__veil' ) );
		return canvas;
	};

	const startMain = async () => {
		const canvas = makeCanvas();
		water = await mainBackend( canvas, image, dpr, measure(), epoch );
		if ( ! water ) {
			canvas.remove(); // No WebGL: the photo stays.
		}
		return water;
	};

	// Start once the photo (the texture) is decoded.
	const begin = async () => {
		let bitmap = null;
		if ( offscreenSupported() ) {
			// Flipped here: UNPACK_FLIP_Y does not apply to ImageBitmap uploads.
			bitmap = await window.createImageBitmap( image, { imageOrientation: 'flipY' } ).catch( () => null );
		}
		if ( bitmap ) {
			const canvas = makeCanvas();
			water = workerBackend( canvas, bitmap, dpr, measure(), epoch, () => {
				// The worker has no WebGL: a transferred canvas cannot get a context on this thread, so
				// start over on a fresh canvas.
				canvas.remove();
				water = null;
				startMain().then( ( main ) => main && main.run( shouldRun() ) );
			} );
		} else if ( ! ( await startMain() ) ) {
			return;
		}
		const point = introPoint();
		playIntro( hero, point.x, point.y, () => {
			addRipple( point.x, point.y, 2.2 );
			return true;
		} );
		water.run( shouldRun() );
	};
	const ready = image.complete && image.naturalWidth ? Promise.resolve() : new Promise( ( r ) => image.addEventListener( 'load', r, { once: true } ) );
	ready.then( () => ( image.decode ? image.decode().catch( () => {} ) : null ) ).then( begin );
}
