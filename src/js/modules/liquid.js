/**
 * Live water in the liquid icons (.liquid-icon, inc/icons.php) and the drawn bottles (.bottle, inc/bottles.php).
 *
 * The water in every icon is two damped springs: the tilt of the surface (degrees, pulled back to
 * "level with the ground") and the bob of the level (drawing-grid units). Energy comes from scroll
 * acceleration, the mouse entering and moving over a card, taps and, on phones, the tilt of the device.
 * Bottles also lean towards the mouse and wobble after a tap; their water is counter-rotated so it stays level.
 *
 * Cost control:
 *  - only icons on screen are simulated (IntersectionObserver); the CSS waves of the rest are paused;
 *  - one subscription to the shared ticker (no own requestAnimationFrame); it is dropped once the water settles;
 *  - while the motion is calm the physics runs at 30 Hz, at full rate only during a real slosh;
 *  - each frame writes at most one transform per moving icon (HTML layers: compositor only, no layout);
 *  - "low" performance level and reduced motion: no physics at all (icons keep their still water line).
 */
import { ticker } from '../ticker.js';
import { getPerf, onPerf } from '../perf.js';
import { reducedMotion } from '../tokens.js';

// Spring constants: ~1.1 Hz slosh with light damping (ζ ≈ 0.12), the level settles a bit faster.
const TILT_K = 48;
const TILT_C = 1.7;
const BOB_K = 70;
const BOB_C = 2.6;
const MAX_TILT = 32;
const MAX_BOB = 5;
const MAX_GRAVITY = 25;
const MAX_BOTTLE_TILT = 10;
const CALM_SPEED = 14; // deg/s: below this the surface only breathes — 30 Hz is enough
const HOSTS = '.story__point, .service-list__item, .shelf__item, .feature-card, .service-card, .about-feature, .stat-card, .contact-card, .price-card, .services__visual';

const clamp = ( value, min, max ) => ( value < min ? min : value > max ? max : value );

export function initLiquid() {
	const nodes = document.querySelectorAll( '.liquid-icon[data-level], .bottle__body' );
	if ( ! nodes.length || ! ( 'IntersectionObserver' in window ) || reducedMotion() || 'low' === getPerf() ) {
		return;
	}

	const items = [];
	const abort = new AbortController();
	const on = ( target, type, handler, options ) => target.addEventListener( type, handler, { signal: abort.signal, ...options } );
	let gravity = 0; // target tilt of the surface (device tilt), degrees
	let running = false;
	let skip = false;
	let carry = 0;

	// Where the surface wants to be: level with the ground, minus the bottle's own rotation.
	const rest = ( item ) => gravity - item.own;

	// Current rotation of a bottle (CSS transition / animation), degrees.
	const readRotation = ( el ) => {
		const m = /matrix\(([^,]+),\s*([^,]+)/.exec( window.getComputedStyle( el ).transform );
		return m ? ( Math.atan2( parseFloat( m[ 2 ] ), parseFloat( m[ 1 ] ) ) * 180 ) / Math.PI : 0;
	};

	const render = ( item ) => {
		const stretch = 1 + Math.min( Math.abs( item.va ) / 160, 0.9 ); // choppier waves while sloshing
		// The tilt layer pivots around the surface (transform-origin in CSS); bob in % of the drawing height.
		const value = `translateY(${ ( ( item.y / item.size ) * 100 ).toFixed( 2 ) }%) rotate(${ item.a.toFixed( 2 ) }deg) scaleY(${ stretch.toFixed( 3 ) })`;
		if ( value !== item.drawn ) {
			item.liquid.style.transform = value;
			item.drawn = value;
		}
	};

	const step = ( item, dt ) => {
		const target = rest( item );
		item.va += ( -TILT_K * ( item.a - target ) - TILT_C * item.va ) * dt;
		item.a += item.va * dt;
		if ( Math.abs( item.a - target ) > MAX_TILT ) {
			item.a = target + MAX_TILT * Math.sign( item.a - target );
			item.va *= -0.3; // splash back from the wall
		}
		const maxBob = MAX_BOB * item.scale;
		item.vy += ( -BOB_K * item.y - BOB_C * item.vy ) * dt;
		item.y = clamp( item.y + item.vy * dt, -maxBob, maxBob );
		return Math.abs( item.va ) > 0.05 || Math.abs( item.a - target ) > 0.05 || Math.abs( item.vy ) > 0.02 * item.scale || Math.abs( item.y ) > 0.02 * item.scale;
	};

	const frame = ( now, delta ) => {
		// Calm water: every other frame (30 Hz), carrying the skipped time over.
		if ( skip ) {
			skip = false;
			carry = delta;
			return;
		}
		const dt = Math.min( ( delta + carry ) / 1000, 1 / 20 );
		carry = 0;
		let busy = false;
		let fastest = 0;

		for ( const item of items ) {
			if ( ! item.visible ) {
				continue;
			}
			const tracking = item.body && now < item.trackUntil;
			// Reading the computed rotation forces a style update: only while the bottle is moving.
			if ( tracking ) {
				item.own = readRotation( item.body );
			}
			// Two half steps keep the semi-implicit Euler integration stable on slow frames.
			let moving = step( item, dt / 2 );
			moving = step( item, dt / 2 ) || moving || tracking;
			if ( ! moving ) {
				item.a = rest( item );
				item.va = 0;
				item.y = 0;
				item.vy = 0;
			}
			render( item );
			busy = busy || moving;
			fastest = Math.max( fastest, Math.abs( item.va ), tracking ? CALM_SPEED : 0 );
		}

		if ( ! busy ) {
			running = false;
			ticker.remove( frame );
			return;
		}
		skip = fastest < CALM_SPEED;
	};

	const wake = () => {
		if ( ! running ) {
			running = true;
			skip = false;
			carry = 0;
			ticker.add( frame );
		}
	};

	const kick = ( item, tiltSpeed, bobSpeed ) => {
		if ( ! item.visible ) {
			return;
		}
		item.va += tiltSpeed;
		item.vy += bobSpeed * item.scale;
		wake();
	};

	// Keep simulating while a bottle is still moving (its CSS transition / animation).
	const track = ( item, ms ) => {
		item.trackUntil = Math.max( item.trackUntil, window.performance.now() + ms );
		wake();
	};

	nodes.forEach( ( node, index ) => {
		const liquid = node.querySelector( '.liquid-icon__tilt, .bottle__tilt' );
		if ( ! liquid ) {
			return;
		}
		const isBottle = node.classList.contains( 'bottle__body' );
		const item = {
			node,
			liquid,
			size: parseFloat( node.getAttribute( 'data-size' ) ) || 48, // height of the drawing grid
			scale: parseFloat( node.getAttribute( 'data-scale' ) ) || 1, // bottles are drawn on a bigger grid
			body: isBottle ? node : null,
			own: 0,
			trackUntil: 0,
			pausedClass: isBottle ? 'bottle__body--paused' : 'liquid-icon--paused',
			a: 0,
			va: 0,
			y: 0,
			vy: 0,
			// Every icon reacts a little differently to the same scroll, like real glasses on a tray.
			side: ( index % 2 ? -1 : 1 ) * ( 0.65 + ( ( index * 37 ) % 10 ) / 25 ),
			visible: false,
			drawn: '',
		};
		items.push( item );

		const host = node.closest( HOSTS ) || node.parentNode;

		on( host, 'pointerenter', ( event ) => {
			if ( 'mouse' !== event.pointerType ) {
				return;
			}
			const rect = host.getBoundingClientRect();
			// Entering from the left pushes the vessel to the right: the water climbs the left wall.
			kick( item, event.clientX < rect.left + rect.width / 2 ? 110 : -110, 6 );
		} );
		on( host, 'pointermove', ( event ) => {
			if ( 'mouse' === event.pointerType && event.movementX ) {
				kick( item, clamp( event.movementX * 1.2, -18, 18 ), 0 );
			}
		} );
		on(
			host,
			'pointerdown',
			( event ) => {
				if ( 'mouse' !== event.pointerType ) {
					kick( item, item.side * 85, 10 );
				}
			},
			{ passive: true }
		);

		if ( ! item.body ) {
			return;
		}

		// Bottle: leans towards the mouse and settles back when it leaves (CSS transition).
		on( host, 'pointermove', ( event ) => {
			if ( 'mouse' !== event.pointerType ) {
				return;
			}
			const rect = host.getBoundingClientRect();
			const offset = ( event.clientX - rect.left - rect.width / 2 ) / ( rect.width / 2 );
			item.body.style.setProperty( '--bottle-tilt', `${ ( clamp( offset, -1, 1 ) * MAX_BOTTLE_TILT ).toFixed( 2 ) }deg` );
			track( item, 1000 );
		} );
		on( host, 'pointerleave', () => {
			item.body.style.setProperty( '--bottle-tilt', '0deg' );
			track( item, 1300 );
		} );
		// Tap: the bottle rocks on its base.
		on(
			host,
			'pointerdown',
			( event ) => {
				if ( 'mouse' === event.pointerType ) {
					return;
				}
				item.body.classList.remove( 'bottle__body--wobble' );
				void item.body.offsetWidth; // restart the animation
				item.body.classList.add( 'bottle__body--wobble' );
				track( item, 1700 );
			},
			{ passive: true }
		);
		on( item.body, 'animationend', () => item.body.classList.remove( 'bottle__body--wobble' ) );
	} );

	// Simulate only what is on screen; pause the CSS waves of the rest.
	const byNode = new Map( items.map( ( item ) => [ item.node, item ] ) );
	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				const item = byNode.get( entry.target );
				if ( ! item ) {
					return;
				}
				item.visible = entry.isIntersecting;
				item.node.classList.toggle( item.pausedClass, ! entry.isIntersecting );
				if ( entry.isIntersecting ) {
					item.a = rest( item );
					item.va = 0;
					render( item );
				}
			} );
		},
		{ rootMargin: '60px 0px' }
	);
	items.forEach( ( item ) => observer.observe( item.node ) );

	// Scroll: the water lags behind when a scroll starts and runs on when it stops.
	let lastY = window.scrollY;
	let lastT = window.performance.now();
	let lastV = 0;
	let stopTimer = 0;
	const onVelocityChange = ( dv ) => {
		for ( const item of items ) {
			kick( item, clamp( dv * 65 * item.side, -90, 90 ), clamp( dv * 9, -16, 16 ) );
		}
	};
	on(
		window,
		'scroll',
		() => {
			const now = window.performance.now();
			const y = window.scrollY;
			const v = ( y - lastY ) / Math.max( now - lastT, 8 ); // px per ms
			const dv = v - lastV;
			lastY = y;
			lastT = now;
			lastV = v;
			if ( Math.abs( dv ) > 0.05 ) {
				onVelocityChange( dv ); // ignores the tiny jitter of a steady scroll
			}
			window.clearTimeout( stopTimer );
			stopTimer = window.setTimeout( () => {
				onVelocityChange( -lastV );
				lastV = 0;
			}, 90 );
		},
		{ passive: true }
	);

	// Device tilt: keep the surface level with the ground. Permission is never requested:
	// where the browser needs it (iOS Safari) the events simply do not arrive and no prompt is shown.
	if ( 'DeviceMotionEvent' in window ) {
		on(
			window,
			'devicemotion',
			( event ) => {
				const g = event.accelerationIncludingGravity;
				if ( ! g || null === g.x || null === g.y ) {
					return;
				}
				let target = 0;
				if ( Math.sqrt( g.x * g.x + g.y * g.y ) > 3 ) {
					// Not lying flat.
					const angle = ( window.screen.orientation && window.screen.orientation.angle ) || window.orientation || 0;
					let roll = ( Math.atan2( -g.x, g.y ) * 180 ) / Math.PI + angle;
					roll = ( ( roll + 540 ) % 360 ) - 180;
					target = clamp( -roll, -MAX_GRAVITY, MAX_GRAVITY );
				}
				const previous = gravity;
				gravity += ( target - gravity ) * 0.15; // low-pass filter against sensor noise
				// A sideways shake of the phone sloshes the water too.
				const shake = event.acceleration && event.acceleration.x ? clamp( event.acceleration.x * 3, -25, 25 ) : 0;
				if ( Math.abs( gravity - previous ) > 0.05 || Math.abs( shake ) > 2 ) {
					for ( const item of items ) {
						kick( item, shake, 0 );
					}
					wake();
				}
			},
			{ passive: true }
		);
	}

	// Level dropped to "low": stop for good and put every surface back to rest.
	onPerf( ( level ) => {
		if ( 'low' !== level ) {
			return;
		}
		abort.abort();
		observer.disconnect();
		window.clearTimeout( stopTimer );
		ticker.remove( frame );
		running = false;
		for ( const item of items ) {
			item.liquid.style.transform = '';
			if ( item.body ) {
				item.body.style.removeProperty( '--bottle-tilt' );
			}
		}
	} );
}
