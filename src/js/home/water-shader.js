/**
 * Hero water shader (shared by the worker renderer and the main-thread fallback).
 *
 * The surface is a slow ambient swell plus up to MAX_RIPPLES circular rings (pointer moves, taps,
 * the opening drop). The photo is refracted by the slope of that surface and gets a soft glint.
 *
 * Cost: the slope is the analytic gradient of the height field, computed in one pass (no repeated
 * height samples). Everything that is the same for every pixel — a ring's radius, its amplitude and
 * the band where it is visible at all — is computed once per frame on the CPU (packRings), so the
 * per-pixel loop visits only live rings and leaves a ring after one dot product when the pixel is
 * outside its band (beyond the band a ring moves the photo by less than 0.01 px).
 */
export const MAX_RIPPLES = 16;

/** A ring lives this long (s), travels this fast (px/s) and fades at this rate (1/s). */
const LIFE = 5.0;
const SPEED = 240.0;
const DECAY = 0.85;
/** Ring profile: 7 sin(0.065 x) exp(-x^2 / 9000), x = distance from the travelling crest. */
const AMP = 7.0;
const WIDTH = 9000.0;
/** Photo displacement per unit of slope (px), as in the shader; the band ends where a ring moves it < 0.01 px. */
const REFRACT = 16.0;
const EPS = 0.01;

export const VERTEX = /* glsl */ `
attribute vec2 uv;
attribute vec2 position;
varying vec2 vUv;
void main() {
	vUv = uv;
	gl_Position = vec4(position, 0.0, 1.0);
}`;

export const FRAGMENT = /* glsl */ `
precision highp float;
uniform sampler2D tMap;
uniform vec2 uRes;      // canvas size in CSS px
uniform vec2 uImg;      // photo size in px
uniform float uTime;
uniform int uCount;     // live rings, packed at the start of the arrays
uniform vec4 uRings[${ MAX_RIPPLES }]; // x, y (CSS px, origin bottom-left), crest radius, amplitude
uniform vec2 uBands[${ MAX_RIPPLES }]; // squared inner / outer radius of the visible band
varying vec2 vUv;

// object-fit: cover
vec2 cover(vec2 uv) {
	float rs = uRes.x / uRes.y;
	float ri = uImg.x / uImg.y;
	vec2 s = rs > ri ? vec2(1.0, ri / rs) : vec2(rs / ri, 1.0);
	return (uv - 0.5) * s + 0.5;
}

// Gradient of the height field
//   h = 0.45 sin(a) sin(b) + 0.25 sin(c) + sum amp sin(0.065 x) exp(-x^2 / 9000),  x = |p - ring| - radius
vec2 slopeAt(vec2 p) {
	float a = p.x * 0.011 + uTime * 0.6;
	float b = p.y * 0.015 - uTime * 0.45;
	float c = (p.x + p.y) * 0.019 - uTime * 0.8;
	float gc = 0.00475 * cos(c);
	vec2 g = vec2(0.00495 * cos(a) * sin(b) + gc, 0.00675 * sin(a) * cos(b) + gc);
	for (int i = 0; i < ${ MAX_RIPPLES }; i++) {
		if (i >= uCount) break;
		vec2 dv = p - uRings[i].xy;
		float d2 = dot(dv, dv);
		if (d2 < uBands[i].x || d2 > uBands[i].y) continue;
		float d = sqrt(d2);
		float x = d - uRings[i].z;
		float phase = x * 0.065;
		float dh = uRings[i].w * exp(-x * x / ${ WIDTH.toFixed( 1 ) }) * (0.065 * cos(phase) - x / ${ ( WIDTH / 2 ).toFixed( 1 ) } * sin(phase));
		g += dh * dv / max(d, 0.001);
	}
	return g;
}

void main() {
	vec2 slope = slopeAt(vUv * uRes);

	vec2 uv = cover(vUv + slope * ${ REFRACT.toFixed( 1 ) } / uRes);
	vec3 color = texture2D(tMap, uv).rgb;

	vec3 n = normalize(vec3(-slope * 1.3, 1.0));
	float glint = pow(max(dot(n, normalize(vec3(0.35, 0.5, 1.0))), 0.0), 70.0);
	color += glint * 0.32;

	gl_FragColor = vec4(color, 1.0);
}`;

/**
 * Two-pass variant (same picture, much less per-pixel work).
 * Pass 1 (SLOPE_FRAGMENT) writes the slope of the surface into a half-float texture on a coarser grid:
 * the slope is smooth (rings are ~100 px long waves), so linear filtering restores it exactly enough.
 * Pass 2 (COMPOSITE_FRAGMENT) runs at full resolution: one slope fetch, the refracted photo, the glint.
 */
const SLOPE_UNIFORMS = FRAGMENT.slice( 0, FRAGMENT.indexOf( 'void main()' ) );

export const SLOPE_FRAGMENT = /* glsl */ `${ SLOPE_UNIFORMS }
void main() {
	gl_FragColor = vec4(slopeAt(vUv * uRes), 0.0, 1.0);
}`;

export const COMPOSITE_FRAGMENT = /* glsl */ `
precision highp float;
uniform sampler2D tMap;
uniform sampler2D tSlope;
uniform vec2 uRes;
uniform vec2 uImg;
varying vec2 vUv;

vec2 cover(vec2 uv) {
	float rs = uRes.x / uRes.y;
	float ri = uImg.x / uImg.y;
	vec2 s = rs > ri ? vec2(1.0, ri / rs) : vec2(rs / ri, 1.0);
	return (uv - 0.5) * s + 0.5;
}

void main() {
	vec2 slope = texture2D(tSlope, vUv).xy;

	vec2 uv = cover(vUv + slope * ${ REFRACT.toFixed( 1 ) } / uRes);
	vec3 color = texture2D(tMap, uv).rgb;

	vec3 n = normalize(vec3(-slope * 1.3, 1.0));
	float glint = pow(max(dot(n, normalize(vec3(0.35, 0.5, 1.0))), 0.0), 70.0);
	color += glint * 0.32;

	gl_FragColor = vec4(color, 1.0);
}`;

/**
 * Per-frame ring uniforms.
 * @param {number[][]} ripples [x, y, start time, strength] (shader coordinates)
 * @param {number} time current shader time (s)
 * @param {{rings: number[][], bands: number[][]}} out reused arrays of MAX_RIPPLES vec4 / vec2
 * @return {number} the number of live rings written to the start of out.rings / out.bands
 */
export function packRings( ripples, time, out ) {
	let count = 0;
	for ( const r of ripples ) {
		const age = time - r[ 2 ];
		if ( r[ 3 ] <= 0 || age < 0 || age > LIFE ) {
			continue;
		}
		const amp = AMP * Math.exp( -age * DECAY ) * r[ 3 ];
		// Slope of the ring <= amp * (0.065 + |x| / 4500) * exp(-x^2 / WIDTH) <= amp * 0.14 * exp(...) inside
		// |x| < 340: the band is where it can still move the photo by EPS.
		const peak = amp * 0.14 * REFRACT;
		const reach = peak > EPS ? Math.sqrt( WIDTH * Math.log( peak / EPS ) ) + 2 : 0;
		if ( ! reach ) {
			continue;
		}
		const radius = age * SPEED;
		const inner = Math.max( 0, radius - reach );
		const ring = out.rings[ count ];
		ring[ 0 ] = r[ 0 ];
		ring[ 1 ] = r[ 1 ];
		ring[ 2 ] = radius;
		ring[ 3 ] = amp;
		const band = out.bands[ count ];
		band[ 0 ] = inner * inner;
		band[ 1 ] = ( radius + reach ) * ( radius + reach );
		count++;
	}
	return count;
}

/** Arrays for packRings (plain Arrays of vecs: OGL uploads uniform arrays given that way). */
export function ringBuffers() {
	return {
		rings: Array.from( { length: MAX_RIPPLES }, () => [ 0, 0, 0, 0 ] ),
		bands: Array.from( { length: MAX_RIPPLES }, () => [ 0, 0 ] ),
	};
}
