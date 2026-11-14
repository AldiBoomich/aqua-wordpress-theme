/**
 * Living water: a WebGL (OGL) layer over the hero photo.
 *
 * The surface height is computed analytically in the fragment shader: a slow ambient swell plus up to
 * 16 circular ripples (pointer moves, taps, the opening drop) — no frame buffers, so it runs on WebGL1.
 * The photo is refracted by the slope of that surface and gets a soft specular glint.
 *
 * Budget: canvas ≤ 1.5 device pixels per CSS pixel (1.25 on phones), rendering only while the hero is on
 * screen and the tab is visible; the GL context is released on pagehide. Not started for reduced motion,
 * Save-Data, or phones with few cores / little memory (the static photo stays).
 */
import { Renderer, Program, Mesh, Triangle, Texture } from 'ogl';
import { reducedMotion, finePointer } from '../tokens.js';

const MAX_RIPPLES = 16;

const VERTEX = /* glsl */ `
attribute vec2 uv;
attribute vec2 position;
varying vec2 vUv;
void main() {
	vUv = uv;
	gl_Position = vec4(position, 0.0, 1.0);
}`;

const FRAGMENT = /* glsl */ `
precision highp float;
uniform sampler2D tMap;
uniform vec2 uRes;      // canvas size in CSS px
uniform vec2 uImg;      // photo size in px
uniform float uTime;
uniform vec4 uRipples[${ MAX_RIPPLES }]; // x, y (CSS px, origin bottom-left), start time, strength
varying vec2 vUv;

// object-fit: cover
vec2 cover(vec2 uv) {
	float rs = uRes.x / uRes.y;
	float ri = uImg.x / uImg.y;
	vec2 s = rs > ri ? vec2(1.0, ri / rs) : vec2(rs / ri, 1.0);
	return (uv - 0.5) * s + 0.5;
}

float surface(vec2 p) {
	float h = 0.45 * sin(p.x * 0.011 + uTime * 0.6) * sin(p.y * 0.015 - uTime * 0.45);
	h += 0.25 * sin((p.x + p.y) * 0.019 - uTime * 0.8);
	for (int i = 0; i < ${ MAX_RIPPLES }; i++) {
		vec4 r = uRipples[i];
		float age = uTime - r.z;
		if (r.w <= 0.0 || age < 0.0 || age > 5.0) continue;
		float d = distance(p, r.xy);
		float x = d - age * 240.0;                       // ring travels outward
		float envelope = exp(-x * x / 9000.0) * exp(-age * 0.85) * r.w;
		h += sin(x * 0.065) * envelope * 7.0;
	}
	return h;
}

void main() {
	vec2 p = vUv * uRes;
	float e = 1.5;
	vec2 slope = vec2(
		surface(p + vec2(e, 0.0)) - surface(p - vec2(e, 0.0)),
		surface(p + vec2(0.0, e)) - surface(p - vec2(0.0, e))
	) / (2.0 * e);

	vec2 uv = cover(vUv + slope * 16.0 / uRes);
	vec3 color = texture2D(tMap, uv).rgb;

	vec3 n = normalize(vec3(-slope * 1.3, 1.0));
	float glint = pow(max(dot(n, normalize(vec3(0.35, 0.5, 1.0))), 0.0), 70.0);
	color += glint * 0.32;

	gl_FragColor = vec4(color, 1.0);
}`;

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
	let renderer;
	try {
		renderer = new Renderer( {
			dpr: Math.min( window.devicePixelRatio || 1, small ? 1.25 : 1.5 ),
			alpha: false,
			antialias: false,
			powerPreference: 'low-power',
		} );
	} catch ( error ) {
		return; // No WebGL: the photo stays.
	}
	const gl = renderer.gl;
	const canvas = gl.canvas;
	canvas.className = 'water-hero__canvas';
	canvas.setAttribute( 'aria-hidden', 'true' );

	// A plain Array of vec4s: OGL only uploads uniform arrays given as Array (flattened per frame).
	const ripples = Array.from( { length: MAX_RIPPLES }, () => [ 0, 0, -10, 0 ] );
	let next = 0;
	const texture = new Texture( gl, { generateMipmaps: false, minFilter: gl.LINEAR, magFilter: gl.LINEAR } );
	const program = new Program( gl, {
		vertex: VERTEX,
		fragment: FRAGMENT,
		uniforms: {
			tMap: { value: texture },
			uRes: { value: [ 1, 1 ] },
			uImg: { value: [ 1, 1 ] },
			uTime: { value: 0 },
			uRipples: { value: ripples },
		},
	} );
	const mesh = new Mesh( gl, { geometry: new Triangle( gl ), program } );

	const start = performance.now();
	const now = () => ( performance.now() - start ) / 1000;
	let size = { w: 1, h: 1 };

	const addRipple = ( x, y, strength ) => {
		const r = ripples[ next ];
		r[ 0 ] = x;
		r[ 1 ] = size.h - y; // shader origin is bottom-left
		r[ 2 ] = now();
		r[ 3 ] = strength;
		next = ( next + 1 ) % MAX_RIPPLES;
	};

	const resize = () => {
		const rect = media.getBoundingClientRect();
		size = { w: Math.max( 1, rect.width ), h: Math.max( 1, rect.height ) };
		renderer.setSize( size.w, size.h );
		program.uniforms.uRes.value = [ size.w, size.h ];
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
			const t = now();
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

	// Render loop: only while visible.
	let running = false;
	let raf = 0;
	let shown = false;
	const frame = () => {
		program.uniforms.uTime.value = now();
		renderer.render( { scene: mesh } );
		if ( ! shown ) {
			shown = true;
			canvas.classList.add( 'is-ready' );
		}
		raf = window.requestAnimationFrame( frame );
	};
	const setRunning = ( on ) => {
		if ( on === running ) {
			return;
		}
		running = on;
		if ( on ) {
			raf = window.requestAnimationFrame( frame );
		} else {
			window.cancelAnimationFrame( raf );
		}
	};

	let onScreen = true;
	new IntersectionObserver( ( entries ) => {
		onScreen = entries[ 0 ].isIntersecting;
		setRunning( onScreen && ! document.hidden );
	} ).observe( hero );
	document.addEventListener( 'visibilitychange', () => setRunning( onScreen && ! document.hidden ) );
	window.addEventListener( 'pagehide', () => {
		setRunning( false );
		const lose = gl.getExtension( 'WEBGL_lose_context' );
		if ( lose ) {
			lose.loseContext();
		}
	} );

	let resizeTimer = 0;
	window.addEventListener( 'resize', () => {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( resize, 100 );
	} );

	// Start once the photo (the texture) is decoded.
	const begin = () => {
		texture.image = image;
		program.uniforms.uImg.value = [ image.naturalWidth || 1, image.naturalHeight || 1 ];
		media.insertBefore( canvas, hero.querySelector( '.water-hero__veil' ) );
		resize();
		const point = introPoint();
		playIntro( hero, point.x, point.y, () => {
			addRipple( point.x, point.y, 2.2 );
			return true;
		} );
		setRunning( onScreen && ! document.hidden );
	};
	const ready = image.complete && image.naturalWidth ? Promise.resolve() : new Promise( ( r ) => image.addEventListener( 'load', r, { once: true } ) );
	ready.then( () => ( image.decode ? image.decode().catch( () => {} ) : null ) ).then( begin );
}
