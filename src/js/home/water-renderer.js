/**
 * The hero water renderer (OGL). Runs inside the water worker on an OffscreenCanvas, or on the main
 * thread when the browser has no OffscreenCanvas / module workers.
 *
 * Same picture as the original single-pass shader, in two passes: the slope of the water surface is
 * computed on a grid SLOPE_STEP device pixels apart into a half-float texture, then a full-resolution
 * pass refracts the photo with the (linearly filtered) slope and adds the glint. The slope field is
 * smooth (~100 px waves), so this matches the single-pass result to within a few pixels' rounding
 * while doing a fraction of the work. Without float render targets it falls back to one pass.
 */
import { Renderer, Program, Mesh, Triangle, Texture, RenderTarget } from 'ogl';
import { MAX_RIPPLES, VERTEX, FRAGMENT, SLOPE_FRAGMENT, COMPOSITE_FRAGMENT, packRings, ringBuffers } from './water-shader.js';

const SLOPE_STEP = 3;

/**
 * @param {HTMLCanvasElement|OffscreenCanvas} canvas
 * @param {{dpr: number, image: TexImageSource, width: number, height: number, flipY: boolean}} options
 *        flipY: false when the image is an ImageBitmap that is already flipped (UNPACK_FLIP_Y does not apply to it)
 * @return {{software: boolean, resize: Function, addRipple: Function, render: Function, lose: Function}|null} null without WebGL
 */
export function createWaterRenderer( canvas, { dpr, image, width, height, flipY } ) {
	let renderer;
	try {
		renderer = new Renderer( { canvas, dpr, alpha: false, antialias: false, depth: false, powerPreference: 'low-power' } );
	} catch ( error ) {
		return null;
	}
	const gl = renderer.gl;
	if ( ! gl ) {
		return null;
	}

	// Software rasterisers (no hardware acceleration): the page uses this to skip GPU-only warm-ups.
	const info = gl.getExtension( 'WEBGL_debug_renderer_info' );
	const rendererName = info ? String( gl.getParameter( info.UNMASKED_RENDERER_WEBGL ) ) : '';
	const software = /SwiftShader|Basic Render|llvmpipe|softpipe|Software/i.test( rendererName );

	const texture = new Texture( gl, { image, flipY, generateMipmaps: false, minFilter: gl.LINEAR, magFilter: gl.LINEAR } );
	// <img>: natural size (width is the layout size); ImageBitmap: width/height.
	const imageSize = [ image.naturalWidth || image.width || 1, image.naturalHeight || image.height || 1 ];
	const packed = ringBuffers();
	const geometry = new Triangle( gl );
	const shared = {
		uRes: { value: [ 1, 1 ] },
		uImg: { value: imageSize },
		uTime: { value: 0 },
		uCount: { value: 0 },
		uRings: { value: packed.rings },
		uBands: { value: packed.bands },
	};

	// Two passes need a filterable half-float render target (WebGL2 + EXT_color_buffer_float/half_float).
	let target = null;
	if ( renderer.isWebgl2 && ( renderer.getExtension( 'EXT_color_buffer_float' ) || renderer.getExtension( 'EXT_color_buffer_half_float' ) ) ) {
		try {
			target = new RenderTarget( gl, {
				width: 1,
				height: 1,
				type: gl.HALF_FLOAT,
				format: gl.RGBA,
				internalFormat: gl.RGBA16F,
				minFilter: gl.LINEAR,
				magFilter: gl.LINEAR,
				depth: false,
			} );
			gl.bindFramebuffer( gl.FRAMEBUFFER, target.buffer );
			if ( gl.checkFramebufferStatus( gl.FRAMEBUFFER ) !== gl.FRAMEBUFFER_COMPLETE ) {
				target = null;
			}
			gl.bindFramebuffer( gl.FRAMEBUFFER, null );
		} catch ( error ) {
			target = null;
		}
	}

	let slopeMesh = null;
	let mesh;
	if ( target ) {
		slopeMesh = new Mesh( gl, { geometry, program: new Program( gl, { vertex: VERTEX, fragment: SLOPE_FRAGMENT, uniforms: shared, depthTest: false, depthWrite: false } ) } );
		mesh = new Mesh( gl, {
			geometry,
			program: new Program( gl, {
				vertex: VERTEX,
				fragment: COMPOSITE_FRAGMENT,
				uniforms: { tMap: { value: texture }, tSlope: { value: target.texture }, uRes: shared.uRes, uImg: shared.uImg },
				depthTest: false,
				depthWrite: false,
			} ),
		} );
	} else {
		mesh = new Mesh( gl, { geometry, program: new Program( gl, { vertex: VERTEX, fragment: FRAGMENT, uniforms: { ...shared, tMap: { value: texture } }, depthTest: false, depthWrite: false } ) } );
	}

	// Rings as [x, y, start time, strength] in shader coordinates; packRings() turns the live ones into uniforms.
	const ripples = Array.from( { length: MAX_RIPPLES }, () => [ 0, 0, -10, 0 ] );
	let next = 0;
	let size = { w: 1, h: 1 };

	const resize = ( w, h ) => {
		size = { w: Math.max( 1, w ), h: Math.max( 1, h ) };
		renderer.setSize( size.w, size.h );
		shared.uRes.value = [ size.w, size.h ];
		if ( target ) {
			target.setSize( Math.ceil( gl.canvas.width / SLOPE_STEP ), Math.ceil( gl.canvas.height / SLOPE_STEP ) );
		}
	};
	resize( width, height );

	return {
		software,
		resize,
		/** x, y in CSS px from the top-left of the hero media; time in the renderer's clock (s). */
		addRipple( x, y, strength, time ) {
			const r = ripples[ next ];
			r[ 0 ] = x;
			r[ 1 ] = size.h - y; // shader origin is bottom-left
			r[ 2 ] = time;
			r[ 3 ] = strength;
			next = ( next + 1 ) % MAX_RIPPLES;
		},
		render( time ) {
			shared.uTime.value = time;
			shared.uCount.value = packRings( ripples, time, packed );
			if ( slopeMesh ) {
				renderer.render( { scene: slopeMesh, target, clear: false } );
			}
			renderer.render( { scene: mesh, clear: false } );
		},
		lose() {
			const lose = gl.getExtension( 'WEBGL_lose_context' );
			if ( lose ) {
				lose.loseContext();
			}
		},
	};
}
