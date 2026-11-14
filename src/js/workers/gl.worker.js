/**
 * WebGL off the main thread (dedicated worker, OffscreenCanvas).
 *
 * Creating a WebGL context blocks the calling thread for ~100 ms, and so does every shader compile and
 * texture upload. Here all of it happens in a worker, so the page never waits for the GPU:
 *
 *  { type: 'probe' }  → { type: 'probe', ok, renderer, why }   is there hardware WebGL? (used by src/js/perf.js)
 *  { type: 'init', canvas, bitmap, width, height, scale }       start the hero water on a transferred canvas
 *  { type: 'size', width, height, scale } · { type: 'run', on } · { type: 'ripple', x, y, w } · { type: 'destroy' }
 *  ← { type: 'ready', info } after the first frame · { type: 'stats', ms } average frame time, about twice a second
 *  ← { type: 'fail' } no hardware context · { type: 'lost' } / { type: 'restored' } context events
 *
 * Shader, one pass per pixel: the slope of the surface is computed analytically (the derivative of the
 * ambient swell and of each circular ripple is known in closed form), so there is no finite-difference
 * sampling. Only the active ripples are looped over (uCount ≤ 8) and a pixel outside a ripple's ring is
 * skipped before any exp / sin. The photo is refracted by the slope and gets a soft glint.
 * At rest the slow swell is drawn at 30 fps; ripples get every frame.
 */
import { Renderer, Program, Mesh, Triangle, Texture } from 'ogl';

const ATTRIBUTES = { alpha: false, depth: false, stencil: false, antialias: false, powerPreference: 'low-power', failIfMajorPerformanceCaveat: true };
const SOFTWARE = /swiftshader|basic render|llvmpipe|softpipe|software|warp/i;
const MAX_RIPPLES = 8;
const LIFE = 4; // seconds a ripple is kept
const DECAY = 1.1; // amplitude falloff per second

const VERTEX = /* glsl */ `
attribute vec2 uv;
attribute vec2 position;
varying vec2 vUv;
void main() {
	vUv = uv;
	gl_Position = vec4(position, 0.0, 1.0);
}`;

// highp for positions and texture coordinates (phones need it at 1000+ px), mediump for colour.
const FRAGMENT = /* glsl */ `
precision highp float;
uniform sampler2D tMap;
uniform vec2 uRes;   // canvas size in CSS px
uniform vec2 uImg;   // photo size in px
uniform float uTime;
uniform int uCount;  // active ripples
uniform vec4 uRipples[${ MAX_RIPPLES }]; // x, y (CSS px, origin bottom-left), start time, strength
varying vec2 vUv;

// object-fit: cover
vec2 cover(vec2 uv) {
	float rs = uRes.x / uRes.y;
	float ri = uImg.x / uImg.y;
	vec2 s = rs > ri ? vec2(1.0, ri / rs) : vec2(rs / ri, 1.0);
	return (uv - 0.5) * s + 0.5;
}

void main() {
	vec2 p = vUv * uRes;

	// Ambient swell: d/dp of 0.45·sin(ax)·sin(by) + 0.25·sin(c).
	float ax = p.x * 0.011 + uTime * 0.6;
	float by = p.y * 0.015 - uTime * 0.45;
	float c = 0.00475 * cos((p.x + p.y) * 0.019 - uTime * 0.8);
	vec2 slope = vec2(0.00495 * cos(ax) * sin(by) + c, 0.00675 * sin(ax) * cos(by) + c);

	// Ripples: h = 7·w·sin(k·x)·exp(-x²/9000)·exp(-decay·age), x = distance from the travelling ring.
	for (int i = 0; i < ${ MAX_RIPPLES }; i++) {
		if (i >= uCount) break;
		vec4 r = uRipples[i];
		float age = uTime - r.z;
		float ring = age * 240.0;
		vec2 q = p - r.xy;
		float d2 = dot(q, q);
		float lo = max(ring - 260.0, 0.0);
		float hi = ring + 260.0;
		if (d2 < lo * lo || d2 > hi * hi) continue; // outside the ring: nothing to add
		float d = sqrt(d2) + 0.001;
		float x = d - ring;
		float k = x * 0.065;
		float envelope = 7.0 * r.w * exp(-x * x / 9000.0 - age * ${ DECAY.toFixed( 2 ) });
		slope += (q / d) * envelope * (0.065 * cos(k) - (x / 4500.0) * sin(k));
	}

	vec2 uv = cover(vUv + slope * 16.0 / uRes);
	mediump vec3 color = texture2D(tMap, uv).rgb;

	mediump vec3 n = normalize(vec3(-slope * 1.3, 1.0));
	mediump float glint = pow(max(dot(n, vec3(0.2933, 0.4190, 0.8381)), 0.0), 70.0);
	gl_FragColor = vec4(color + glint * 0.32, 1.0);
}`;

const context = ( canvas ) => canvas.getContext( 'webgl2', ATTRIBUTES ) || canvas.getContext( 'webgl', ATTRIBUTES );

const rendererName = ( gl ) => {
	const info = gl.getExtension( 'WEBGL_debug_renderer_info' );
	return info ? String( gl.getParameter( info.UNMASKED_RENDERER_WEBGL ) ) : '';
};

/** Is there hardware WebGL? */
function probe() {
	try {
		const gl = context( new OffscreenCanvas( 1, 1 ) );
		if ( ! gl ) {
			// Name the software renderer for the debug overlay, if a context exists without the caveat flag.
			const soft = new OffscreenCanvas( 1, 1 ).getContext( 'webgl' );
			return { ok: false, renderer: soft ? rendererName( soft ) : '', why: 'no hardware WebGL' };
		}
		const renderer = rendererName( gl );
		const lose = gl.getExtension( 'WEBGL_lose_context' );
		if ( lose ) {
			lose.loseContext();
		}
		return SOFTWARE.test( renderer ) ? { ok: false, renderer, why: 'software renderer' } : { ok: true, renderer, why: '' };
	} catch ( error ) {
		return { ok: false, renderer: '', why: 'WebGL error' };
	}
}

// ---------- hero water ----------
let water = null;

function startWater( { canvas, bitmap, width, height, scale } ) {
	const gl0 = context( canvas );
	if ( ! gl0 || gl0.isContextLost() ) {
		self.postMessage( { type: 'fail' } );
		return;
	}

	const renderer = new Renderer( { canvas, dpr: scale, alpha: false, depth: false, antialias: false } );
	const gl = renderer.gl;
	const slots = Array.from( { length: MAX_RIPPLES }, () => [ 0, 0, -10, 0 ] ); // plain arrays: OGL flattens them
	// The bitmap was flipped when it was created (ImageBitmap ignores UNPACK_FLIP_Y).
	const texture = new Texture( gl, { image: bitmap, flipY: false, generateMipmaps: false, minFilter: gl.LINEAR, magFilter: gl.LINEAR } );
	const geometry = new Triangle( gl );
	const program = new Program( gl, {
		vertex: VERTEX,
		fragment: FRAGMENT,
		depthTest: false,
		depthWrite: false,
		uniforms: {
			tMap: { value: texture },
			uRes: { value: [ width, height ] },
			uImg: { value: [ bitmap.width, bitmap.height ] },
			uTime: { value: 0 },
			uCount: { value: 0 },
			uRipples: { value: slots },
		},
	} );
	const mesh = new Mesh( gl, { geometry, program } );

	const started = self.performance.now();
	const time = () => ( self.performance.now() - started ) / 1000;
	let ripples = []; // { x, y, t, w }
	let size = { w: width, h: height };
	let raf = 0;
	let shown = false;
	let dirty = true;
	let frameNo = 0;
	let lastFrame = 0;
	let sum = 0;
	let count = 0;

	const resize = ( w, h, s ) => {
		size = { w, h };
		renderer.dpr = s;
		renderer.setSize( w, h );
		program.uniforms.uRes.value = [ w, h ];
		dirty = true; // redraw on the next frame even at rest
	};

	const draw = ( stamp ) => {
		raf = self.requestAnimationFrame( draw );

		// Frame pacing of this thread, reported to the performance monitor on the page.
		if ( lastFrame ) {
			sum += stamp - lastFrame;
			count++;
			if ( count >= 30 ) {
				self.postMessage( { type: 'stats', ms: sum / count } );
				sum = 0;
				count = 0;
			}
		}
		lastFrame = stamp;

		const now = time();
		ripples = ripples.filter( ( r ) => now - r.t < LIFE );
		frameNo++;
		if ( ! ripples.length && shown && ! dirty && frameNo % 2 ) {
			return; // at rest: 30 fps
		}
		dirty = false;
		ripples.forEach( ( r, i ) => {
			const slot = slots[ i ];
			slot[ 0 ] = r.x;
			slot[ 1 ] = r.y;
			slot[ 2 ] = r.t;
			slot[ 3 ] = r.w;
		} );
		program.uniforms.uCount.value = ripples.length;
		program.uniforms.uTime.value = now;
		renderer.render( { scene: mesh } );
		if ( ! shown ) {
			shown = true;
			self.postMessage( { type: 'ready', info: `WebGL in a worker, ${ canvas.width }×${ canvas.height } (×${ renderer.dpr } of CSS px)` } );
		}
	};

	const run = ( on ) => {
		if ( on && ! raf ) {
			lastFrame = 0;
			raf = self.requestAnimationFrame( draw );
		} else if ( ! on && raf ) {
			self.cancelAnimationFrame( raf );
			raf = 0;
		}
	};

	canvas.addEventListener( 'webglcontextlost', ( event ) => {
		event.preventDefault();
		run( false );
		self.postMessage( { type: 'lost' } );
	} );
	canvas.addEventListener( 'webglcontextrestored', () => self.postMessage( { type: 'restored' } ) );

	resize( width, height, scale );

	water = {
		run,
		resize( w, h, s ) {
			resize( w, h, s );
			self.postMessage( { type: 'info', info: `WebGL in a worker, ${ canvas.width }×${ canvas.height } (×${ renderer.dpr } of CSS px)` } );
		},
		ripple( x, y, w ) {
			const ripple = { x, y: size.h - y, t: time(), w }; // shader origin is bottom-left
			if ( ripples.length < MAX_RIPPLES ) {
				ripples.push( ripple );
				return;
			}
			// Full: replace the faintest ripple, so strong ones (a click, the opening drop) never pop out.
			let weakest = 0;
			let min = Infinity;
			ripples.forEach( ( r, i ) => {
				const amplitude = r.w * Math.exp( -( ripple.t - r.t ) * DECAY );
				if ( amplitude < min ) {
					min = amplitude;
					weakest = i;
				}
			} );
			ripples[ weakest ] = ripple;
		},
		destroy() {
			run( false );
			try {
				gl.deleteTexture( texture.texture );
				program.remove();
				geometry.remove();
				const lose = gl.getExtension( 'WEBGL_lose_context' );
				if ( lose ) {
					lose.loseContext();
				}
			} catch ( error ) {
				// The context is already gone.
			}
			if ( bitmap.close ) {
				bitmap.close();
			}
		},
	};
}

self.onmessage = ( event ) => {
	const m = event.data;
	switch ( m.type ) {
		case 'probe':
			self.postMessage( { type: 'probe', ...probe() } );
			break;
		case 'init':
			try {
				startWater( m );
			} catch ( error ) {
				self.postMessage( { type: 'fail' } );
			}
			break;
		case 'run':
			if ( water ) {
				water.run( m.on );
			}
			break;
		case 'size':
			if ( water ) {
				water.resize( m.width, m.height, m.scale );
			}
			break;
		case 'ripple':
			if ( water ) {
				water.ripple( m.x, m.y, m.w );
			}
			break;
		case 'destroy':
			if ( water ) {
				water.destroy();
				water = null;
			}
			self.close();
			break;
	}
};
