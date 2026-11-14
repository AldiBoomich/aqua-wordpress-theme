/**
 * Living water: a WebGL (OGL) layer over the hero photo. Loaded only on the "high" and "medium"
 * performance levels (src/js/perf.js); "low" keeps the photo (hero-intro.js).
 *
 * Shader, one pass per pixel: the slope of the surface is computed analytically (the derivative of the
 * ambient swell and of each circular ripple is known in closed form), so there is no finite-difference
 * sampling. Only the active ripples are looped over (uCount ≤ 8) and a pixel outside a ripple's ring is
 * skipped before any exp / sin. The photo is refracted by the slope and gets a soft glint.
 *
 * Budget:
 *  - render resolution: high = 1 device pixel per CSS pixel on desktop (≤ 1.5 on phones),
 *    medium = 0.7 (1 on phones); CSS stretches the canvas — the water is soft, the upscale does not show;
 *  - 30 fps while the water is at rest, 60 fps only while ripples are alive;
 *  - paused off screen, in a background tab and under the open mobile menu;
 *  - context loss is handled (photo shows through, the layer is rebuilt on restore);
 *    everything is released on pagehide and when the level drops to "low".
 */
import { Renderer, Program, Mesh, Triangle, Texture } from 'ogl';
import { ticker } from '../ticker.js';
import { getPerf, onPerf, setRenderInfo } from '../perf.js';
import { finePointer } from '../tokens.js';
import { playIntro, initHeroStatic } from './hero-intro.js';

const MAX_RIPPLES = 8;
const LIFE = 4; // seconds a ripple is kept
const DECAY = '1.1'; // amplitude falloff per second (GLSL float literal)

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
		float envelope = 7.0 * r.w * exp(-x * x / 9000.0 - age * ${ DECAY });
		slope += (q / d) * envelope * (0.065 * cos(k) - (x / 4500.0) * sin(k));
	}

	vec2 uv = cover(vUv + slope * 16.0 / uRes);
	mediump vec3 color = texture2D(tMap, uv).rgb;

	mediump vec3 n = normalize(vec3(-slope * 1.3, 1.0));
	mediump float glint = pow(max(dot(n, vec3(0.2933, 0.4190, 0.8381)), 0.0), 70.0);
	gl_FragColor = vec4(color + glint * 0.32, 1.0);
}`;

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
 * Builds the WebGL layer. Throws when no hardware context is available.
 *
 * @param {Element}  hero      The hero section.
 * @param {Function} onRestore Called when a lost context comes back (the caller rebuilds the layer).
 */
function createWater( hero, onRestore ) {
	const media = hero.querySelector( '.water-hero__media' );
	const image = hero.querySelector( '.water-hero__image' );
	const canvas = document.createElement( 'canvas' );
	const attributes = { alpha: false, depth: false, stencil: false, antialias: false, powerPreference: 'low-power', failIfMajorPerformanceCaveat: true };
	// Create the context ourselves (OGL then reuses it): software renderers are refused here.
	const context = canvas.getContext( 'webgl2', attributes ) || canvas.getContext( 'webgl', attributes );
	if ( ! context ) {
		throw new Error( 'no hardware WebGL' );
	}

	const renderer = new Renderer( { canvas, dpr: scaleFor( getPerf() ), alpha: false, depth: false, antialias: false } );
	const gl = renderer.gl;
	canvas.className = 'water-hero__canvas';
	canvas.setAttribute( 'aria-hidden', 'true' );

	const slots = Array.from( { length: MAX_RIPPLES }, () => [ 0, 0, -10, 0 ] ); // plain arrays: OGL flattens them
	const texture = new Texture( gl, { image, generateMipmaps: false, minFilter: gl.LINEAR, magFilter: gl.LINEAR } );
	const geometry = new Triangle( gl );
	const program = new Program( gl, {
		vertex: VERTEX,
		fragment: FRAGMENT,
		depthTest: false,
		depthWrite: false,
		uniforms: {
			tMap: { value: texture },
			uRes: { value: [ 1, 1 ] },
			uImg: { value: [ image.naturalWidth || 1, image.naturalHeight || 1 ] },
			uTime: { value: 0 },
			uCount: { value: 0 },
			uRipples: { value: slots },
		},
	} );
	const mesh = new Mesh( gl, { geometry, program } );

	const started = window.performance.now();
	const time = () => ( window.performance.now() - started ) / 1000;
	const abort = new AbortController();
	const listen = { signal: abort.signal };
	let ripples = []; // { x, y, t, w }
	let size = { w: 1, h: 1 };
	let onScreen = true;
	let menuOpen = false;
	let lost = false;
	let running = false;
	let shown = false;
	let frameNo = 0;

	const addRipple = ( x, y, strength ) => {
		const ripple = { x, y: size.h - y, t: time(), w: strength }; // shader origin is bottom-left
		if ( ripples.length < MAX_RIPPLES ) {
			ripples.push( ripple );
			return;
		}
		// Full: replace the faintest ripple, so strong ones (a click, the opening drop) never pop out.
		const now = ripple.t;
		let weakest = 0;
		let min = Infinity;
		ripples.forEach( ( r, i ) => {
			const amplitude = r.w * Math.exp( -( now - r.t ) * parseFloat( DECAY ) );
			if ( amplitude < min ) {
				min = amplitude;
				weakest = i;
			}
		} );
		ripples[ weakest ] = ripple;
	};

	const resize = () => {
		const rect = media.getBoundingClientRect();
		size = { w: Math.max( 1, Math.round( rect.width ) ), h: Math.max( 1, Math.round( rect.height ) ) };
		renderer.setSize( size.w, size.h );
		canvas.style.width = '';
		canvas.style.height = ''; // sized by CSS (100% × 100%)
		program.uniforms.uRes.value = [ size.w, size.h ];
		setRenderInfo( `WebGL ${ canvas.width }×${ canvas.height } (×${ renderer.dpr } of CSS px)` );
	};

	const frame = () => {
		const now = time();
		ripples = ripples.filter( ( r ) => now - r.t < LIFE );
		frameNo++;
		// At rest the slow swell is drawn at 30 fps; ripples get every frame.
		if ( ! ripples.length && shown && frameNo % 2 ) {
			return;
		}
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
			canvas.classList.add( 'is-ready' );
		}
	};

	const update = () => {
		const should = onScreen && ! document.hidden && ! menuOpen && ! lost;
		if ( should === running ) {
			return;
		}
		running = should;
		if ( should ) {
			ticker.add( frame );
		} else {
			ticker.remove( frame );
		}
	};

	// Pointer: moving leaves small rings (throttled), a press leaves a big one.
	const local = ( event ) => {
		const rect = media.getBoundingClientRect();
		return { x: event.clientX - rect.left, y: event.clientY - rect.top };
	};
	let last = { x: -999, y: -999, t: 0 };
	if ( finePointer() ) {
		hero.addEventListener(
			'pointermove',
			( event ) => {
				const p = local( event );
				const t = time();
				const moved = Math.hypot( p.x - last.x, p.y - last.y );
				if ( t - last.t > 0.09 && moved > 28 ) {
					addRipple( p.x, p.y, Math.min( 0.55, 0.18 + moved / 180 ) );
					last = { x: p.x, y: p.y, t };
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
				addRipple( p.x, p.y, 1.1 );
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
	window.addEventListener(
		'resize',
		() => {
			window.clearTimeout( resizeTimer );
			resizeTimer = window.setTimeout( resize, 120 );
		},
		listen
	);

	canvas.addEventListener(
		'webglcontextlost',
		( event ) => {
			event.preventDefault(); // allow a restore
			lost = true;
			canvas.classList.remove( 'is-ready' ); // the photo underneath shows through
			update();
		},
		listen
	);
	canvas.addEventListener( 'webglcontextrestored', () => onRestore(), listen );

	media.insertBefore( canvas, hero.querySelector( '.water-hero__veil' ) );
	resize();
	update();

	return {
		addRipple,
		/** Apply the render scale of the current level. */
		rescale() {
			renderer.dpr = scaleFor( getPerf() );
			resize();
		},
		destroy() {
			abort.abort();
			observer.disconnect();
			window.clearTimeout( resizeTimer );
			ticker.remove( frame );
			running = false;
			try {
				gl.deleteTexture( texture.texture );
				program.remove();
				geometry.remove();
				const lose = gl.getExtension( 'WEBGL_lose_context' );
				if ( lose && ! lost ) {
					lose.loseContext();
				}
			} catch ( error ) {
				// The context is already gone.
			}
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

	const toStatic = () => {
		if ( water ) {
			water.destroy();
			water = null;
		}
		initHeroStatic( hero );
	};

	const build = () => {
		if ( water || 'low' === getPerf() ) {
			return;
		}
		try {
			water = createWater( hero, () => {
				// Restored context: every GL resource is gone — rebuild the layer from scratch.
				water.destroy();
				water = null;
				build();
			} );
		} catch ( error ) {
			toStatic();
			return;
		}
		hero.removeAttribute( 'data-static' );
		playIntro( hero, ( x, y ) => {
			if ( ! water ) {
				return false;
			}
			water.addRipple( x, y, 2.2 );
			return true;
		} );
	};

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

	// Start once the photo (the texture) is decoded.
	const ready = image.complete && image.naturalWidth ? Promise.resolve() : new Promise( ( resolve ) => image.addEventListener( 'load', resolve, { once: true } ) );
	ready.then( () => ( image.decode ? image.decode().catch( () => {} ) : null ) ).then( build );
}
