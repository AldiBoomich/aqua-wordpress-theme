/**
 * Hero water worker: renders the water on an OffscreenCanvas, so shader compilation, texture upload
 * and every frame stay off the main thread (scrolling and the page's own animations never wait for
 * the water, even when the browser renders WebGL in software).
 *
 * Messages in:  init { canvas, bitmap, dpr, width, height, epoch } · resize { width, height }
 *               ripple { x, y, strength, at } · run { on } · lose
 * Messages out: ready { software } (first frame drawn) · failed (no WebGL here: the page keeps the photo)
 *
 * Clock: seconds since `epoch` (a performance.timeOrigin-based timestamp shared with the page), so
 * rings start exactly when the pointer event happened.
 */
import { createWaterRenderer, createPacer } from './water-renderer.js';

let water = null;
let epoch = 0;
let running = false;
let handle = 0;
let shown = false;

const now = () => ( performance.timeOrigin + performance.now() - epoch ) / 1000;
const hasRaf = typeof self.requestAnimationFrame === 'function';
const schedule = ( f ) => ( hasRaf ? self.requestAnimationFrame( f ) : self.setTimeout( f, 16 ) );
const unschedule = ( id ) => ( hasRaf ? self.cancelAnimationFrame( id ) : self.clearTimeout( id ) );

// About 60 drawn frames a second on any display (see createPacer); the callback itself runs every display frame.
const pacer = createPacer();

const frame = ( t ) => {
	if ( ! running ) {
		return;
	}
	if ( pacer.due( typeof t === 'number' ? t : performance.now() ) ) {
		water.render( now() );
		if ( ! shown ) {
			shown = true;
			self.postMessage( { type: 'ready', software: water.software } );
		}
	}
	handle = schedule( frame );
};

const setRunning = ( on ) => {
	if ( ! water || on === running ) {
		return;
	}
	running = on;
	if ( on ) {
		pacer.reset();
		handle = schedule( frame );
	} else {
		unschedule( handle );
	}
};

self.onmessage = ( { data } ) => {
	switch ( data.type ) {
		case 'init':
			epoch = data.epoch;
			// The bitmap arrives already flipped (UNPACK_FLIP_Y does not apply to ImageBitmap).
			water = createWaterRenderer( data.canvas, { dpr: data.dpr, image: data.bitmap, width: data.width, height: data.height, flipY: false } );
			if ( ! water ) {
				self.postMessage( { type: 'failed' } );
			}
			break;
		case 'resize':
			if ( water ) {
				water.resize( data.width, data.height );
			}
			break;
		case 'ripple':
			if ( water ) {
				water.addRipple( data.x, data.y, data.strength, ( data.at - epoch ) / 1000 );
			}
			break;
		case 'run':
			setRunning( data.on );
			break;
		case 'lose':
			setRunning( false );
			if ( water ) {
				water.lose();
			}
			break;
	}
};
