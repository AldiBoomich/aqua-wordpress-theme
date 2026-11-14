/**
 * Live water in the liquid icons (.liquid-icon, inc/icons.php) and the drawn bottles (.bottle, inc/bottles.php).
 *
 * The water in every icon is simulated as two damped springs:
 * - tilt of the surface (degrees), pulled back to "level with the ground";
 * - bob of the water level (drawing-grid units), pulled back to rest.
 * Energy comes from scroll acceleration (start / stop of a scroll), the mouse entering
 * and moving over a card, taps, and on phones the tilt of the device (gravity sensor).
 *
 * Only icons on screen are simulated, and the animation loop sleeps once the water
 * has settled. The rolling waves and bubbles are plain CSS (liquid-icon.css, bottle.css).
 * The water is an HTML layer: only its CSS transform changes (GPU), never the layout.
 *
 * Bottles also tilt towards the mouse (--bottle-tilt) and wobble after a tap; their water
 * is counter-rotated so it stays level with the ground while the bottle moves.
 */
( function () {
	'use strict';

	var icons = document.querySelectorAll( '.liquid-icon[data-level], .bottle__body' );
	if ( ! icons.length || ! ( 'IntersectionObserver' in window ) || ! window.requestAnimationFrame ) {
		return;
	}
	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	// Spring constants: ~1.1 Hz slosh with light damping (ζ ≈ 0.12), the level settles a bit faster.
	var TILT_K = 48;
	var TILT_C = 1.7;
	var BOB_K = 70;
	var BOB_C = 2.6;
	var MAX_TILT = 32;
	var MAX_BOB = 5;
	var MAX_GRAVITY = 25;
	var MAX_BOTTLE_TILT = 10;
	var HOSTS = '.feature-card, .service-card, .about-feature, .stat-card, .contact-card, .price-card, .services__visual, .site-header__logo, .site-footer__brand';

	var items = [];
	var gravity = 0; // Target tilt of the surface (device tilt), degrees.
	var running = false;
	var lastTime = 0;

	var clamp = function ( value, min, max ) {
		return value < min ? min : ( value > max ? max : value );
	};

	// Where the surface wants to be: level with the ground, minus the bottle's own rotation.
	var rest = function ( item ) {
		return gravity - item.own;
	};

	// Current rotation of a bottle (CSS transition / animation), degrees.
	var readRotation = function ( el ) {
		var matrix = window.getComputedStyle( el ).transform;
		var m = /matrix\(([^,]+),\s*([^,]+)/.exec( matrix );
		return m ? Math.atan2( parseFloat( m[ 2 ] ), parseFloat( m[ 1 ] ) ) * 180 / Math.PI : 0;
	};

	var render = function ( item ) {
		var stretch = 1 + Math.min( Math.abs( item.va ) / 160, 0.9 ); // Choppier waves while sloshing.
		// The tilt layer pivots around the surface (transform-origin in CSS); bob in % of the drawing height.
		var value = 'translateY(' + ( item.y / item.size * 100 ).toFixed( 2 ) + '%) rotate(' + item.a.toFixed( 2 ) + 'deg) scaleY(' + stretch.toFixed( 3 ) + ')';
		if ( value !== item.drawn ) {
			item.liquid.style.transform = value;
			item.drawn = value;
		}
	};

	var step = function ( item, dt ) {
		var target = rest( item );
		var accA = -TILT_K * ( item.a - target ) - TILT_C * item.va;
		item.va += accA * dt;
		item.a += item.va * dt;
		if ( Math.abs( item.a - target ) > MAX_TILT ) {
			item.a = target + MAX_TILT * Math.sign( item.a - target );
			item.va *= -0.3; // Splash back from the wall.
		}

		var maxBob = MAX_BOB * item.scale;
		var accY = -BOB_K * item.y - BOB_C * item.vy;
		item.vy += accY * dt;
		item.y = clamp( item.y + item.vy * dt, -maxBob, maxBob );

		return Math.abs( item.va ) > 0.05 || Math.abs( item.a - target ) > 0.05 || Math.abs( item.vy ) > 0.02 * item.scale || Math.abs( item.y ) > 0.02 * item.scale;
	};

	var frame = function ( now ) {
		var dt = Math.min( ( now - lastTime ) / 1000, 1 / 30 );
		var busy = false;
		lastTime = now;

		items.forEach( function ( item ) {
			if ( ! item.visible ) {
				return;
			}
			var tracking = item.body && now < item.trackUntil;
			// Reading the computed rotation forces a style update: only while the bottle is moving.
			if ( tracking ) {
				item.own = readRotation( item.body );
			}
			// Two half steps keep the semi-implicit Euler integration stable on slow frames.
			var moving = step( item, dt / 2 );
			moving = step( item, dt / 2 ) || moving || tracking;
			if ( ! moving ) {
				item.a = rest( item );
				item.va = 0;
				item.y = 0;
				item.vy = 0;
			}
			render( item );
			busy = busy || moving;
		} );

		if ( busy ) {
			window.requestAnimationFrame( frame );
		} else {
			running = false;
		}
	};

	var wake = function () {
		if ( ! running ) {
			running = true;
			lastTime = window.performance.now();
			window.requestAnimationFrame( frame );
		}
	};

	var kick = function ( item, tiltSpeed, bobSpeed ) {
		if ( ! item.visible ) {
			return;
		}
		item.va += tiltSpeed;
		item.vy += bobSpeed * item.scale;
		wake();
	};

	// Keep simulating while a bottle is still moving (its CSS transition / animation).
	var track = function ( item, ms ) {
		item.trackUntil = Math.max( item.trackUntil, window.performance.now() + ms );
		wake();
	};

	// Set up the icons.
	Array.prototype.forEach.call( icons, function ( svg, index ) {
		var liquid = svg.querySelector( '.liquid-icon__tilt, .bottle__tilt' );
		if ( ! liquid ) {
			return;
		}

		var item = {
			svg: svg,
			liquid: liquid,
			cx: parseFloat( svg.getAttribute( 'data-cx' ) ) || 24,
			level: parseFloat( svg.getAttribute( 'data-level' ) ) || 24,
			size: parseFloat( svg.getAttribute( 'data-size' ) ) || 48, // Height of the drawing grid.
			scale: parseFloat( svg.getAttribute( 'data-scale' ) ) || 1, // Bottles are drawn on a bigger grid.
			body: svg.classList.contains( 'bottle__body' ) ? svg : null,
			own: 0,
			trackUntil: 0,
			pausedClass: svg.classList.contains( 'bottle__body' ) ? 'bottle__body--paused' : 'liquid-icon--paused',
			a: 0,
			va: 0,
			y: 0,
			vy: 0,
			// Every icon reacts a little differently to the same scroll, like real glasses on a tray.
			side: ( index % 2 ? -1 : 1 ) * ( 0.65 + ( ( index * 37 ) % 10 ) / 25 ),
			visible: false
		};
		items.push( item );

		var host = svg.closest( HOSTS ) || svg.parentNode;

		host.addEventListener( 'pointerenter', function ( event ) {
			if ( 'mouse' !== event.pointerType ) {
				return;
			}
			var rect = host.getBoundingClientRect();
			// Entering from the left pushes the vessel to the right: the water climbs the left wall.
			kick( item, event.clientX < rect.left + rect.width / 2 ? 110 : -110, 6 );
		} );

		host.addEventListener( 'pointermove', function ( event ) {
			if ( 'mouse' === event.pointerType && event.movementX ) {
				kick( item, clamp( event.movementX * 1.2, -18, 18 ), 0 );
			}
		} );

		host.addEventListener( 'pointerdown', function ( event ) {
			if ( 'mouse' !== event.pointerType ) {
				kick( item, item.side * 85, 10 );
			}
		}, { passive: true } );

		if ( ! item.body ) {
			return;
		}

		// Bottle: leans towards the mouse and springs back when it leaves (CSS transition).
		host.addEventListener( 'pointermove', function ( event ) {
			if ( 'mouse' !== event.pointerType ) {
				return;
			}
			var rect = host.getBoundingClientRect();
			var offset = ( event.clientX - rect.left - rect.width / 2 ) / ( rect.width / 2 );
			item.body.style.setProperty( '--bottle-tilt', ( clamp( offset, -1, 1 ) * MAX_BOTTLE_TILT ).toFixed( 2 ) + 'deg' );
			track( item, 1000 );
		} );

		host.addEventListener( 'pointerleave', function () {
			item.body.style.setProperty( '--bottle-tilt', '0deg' );
			track( item, 1300 );
		} );

		// Tap: the bottle rocks on its base.
		host.addEventListener( 'pointerdown', function ( event ) {
			if ( 'mouse' === event.pointerType ) {
				return;
			}
			item.body.classList.remove( 'bottle__body--wobble' );
			void item.body.offsetWidth; // Restart the animation.
			item.body.classList.add( 'bottle__body--wobble' );
			track( item, 1700 );
		}, { passive: true } );

		item.body.addEventListener( 'animationend', function () {
			item.body.classList.remove( 'bottle__body--wobble' );
		} );
	} );

	// Simulate only what is on screen; pause the CSS waves of the rest.
	var observer = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			var item = items.find( function ( candidate ) {
				return candidate.svg === entry.target;
			} );
			if ( ! item ) {
				return;
			}
			item.visible = entry.isIntersecting;
			item.svg.classList.toggle( item.pausedClass, ! entry.isIntersecting );
			if ( entry.isIntersecting ) {
				item.a = rest( item );
				item.va = 0;
				render( item );
			}
		} );
	}, { rootMargin: '60px 0px' } );

	items.forEach( function ( item ) {
		observer.observe( item.svg );
	} );

	// Scroll: the water lags behind when a scroll starts and runs on when it stops.
	var lastY = window.scrollY || window.pageYOffset;
	var lastT = window.performance.now();
	var lastV = 0;
	var stopTimer = 0;

	var onVelocityChange = function ( dv ) {
		items.forEach( function ( item ) {
			kick( item, clamp( dv * 65 * item.side, -90, 90 ), clamp( dv * 9, -16, 16 ) );
		} );
	};

	window.addEventListener( 'scroll', function () {
		var now = window.performance.now();
		var y = window.scrollY || window.pageYOffset;
		var v = ( y - lastY ) / Math.max( now - lastT, 8 ); // px per ms
		var dv = v - lastV;

		lastY = y;
		lastT = now;
		lastV = v;
		if ( Math.abs( dv ) > 0.05 ) { // Ignore tiny jitter of a steady scroll.
			onVelocityChange( dv );
		}

		window.clearTimeout( stopTimer );
		stopTimer = window.setTimeout( function () {
			onVelocityChange( -lastV );
			lastV = 0;
		}, 90 );
	}, { passive: true } );

	// Device tilt: keep the surface level with the ground. Permission is never requested:
	// where the browser needs it (iOS Safari) the events simply do not arrive and no prompt is shown.
	if ( 'DeviceMotionEvent' in window ) {
		window.addEventListener( 'devicemotion', function ( event ) {
			var g = event.accelerationIncludingGravity;
			if ( ! g || null === g.x || null === g.y ) {
				return;
			}

			var target = 0;
			if ( Math.sqrt( g.x * g.x + g.y * g.y ) > 3 ) { // Not lying flat.
				var angle = ( window.screen.orientation && window.screen.orientation.angle ) || window.orientation || 0;
				var roll = Math.atan2( -g.x, g.y ) * 180 / Math.PI + angle;
				roll = ( ( roll + 540 ) % 360 ) - 180;
				target = clamp( -roll, -MAX_GRAVITY, MAX_GRAVITY );
			}

			var previous = gravity;
			gravity += ( target - gravity ) * 0.15; // Low-pass filter against sensor noise.

			// A sideways shake of the phone sloshes the water too.
			var shake = event.acceleration && event.acceleration.x ? clamp( event.acceleration.x * 3, -25, 25 ) : 0;
			if ( Math.abs( gravity - previous ) > 0.05 || Math.abs( shake ) > 2 ) {
				items.forEach( function ( item ) {
					kick( item, shake, 0 );
				} );
				wake();
			}
		}, { passive: true } );
	}
} )();
