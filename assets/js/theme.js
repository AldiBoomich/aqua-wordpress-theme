/**
 * LEGACY script for the sections that are not rebuilt yet (removed with them).
 * The header, menus and footer now live in src/js/ (Vite build).
 * - WOW.js: scroll reveal animations.
 * - Swipe carousels (.swipe): dots synced with the horizontal scroll.
 * - Hero slider pauses while off screen (.hero--paused).
 * - Stats counters: count up from 0 when they enter the viewport.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Scroll reveal animations.
	if ( typeof window.WOW === 'function' ) {
		new window.WOW( { live: false } ).init();
	}

	// Hero slider: while it is off screen the slide show and the slow zoom pause
	// (a slide change decodes a large photo — no reason to do it while the visitor reads further down).
	var hero = document.querySelector( '.hero' );
	var heroCarousel = document.getElementById( 'hero-carousel' );
	if ( hero && heroCarousel && 'IntersectionObserver' in window ) {
		new IntersectionObserver( function ( entries ) {
			var visible = entries[ 0 ].isIntersecting;
			var slider = window.bootstrap && window.bootstrap.Carousel ? window.bootstrap.Carousel.getInstance( heroCarousel ) : null;
			hero.classList.toggle( 'hero--paused', ! visible );
			if ( slider ) {
				if ( visible ) {
					slider.cycle();
				} else {
					slider.pause();
				}
			}
		} ).observe( hero );
	}

	// Swipe carousels (mobile): dots under the row, synced with the scroll position.
	Array.prototype.forEach.call( document.querySelectorAll( '.swipe' ), function ( track ) {
		var slides = track.children;
		if ( slides.length < 2 ) {
			return;
		}

		var dots = document.createElement( 'div' );
		dots.className = 'swipe__dots';
		Array.prototype.forEach.call( slides, function ( slide, i ) {
			var dot = document.createElement( 'button' );
			dot.type = 'button';
			dot.className = 'swipe__dot' + ( 0 === i ? ' swipe__dot--active' : '' );
			dot.setAttribute( 'aria-label', 'Show item ' + ( i + 1 ) );
			dot.addEventListener( 'click', function () {
				// .swipe is position: relative, so offsetLeft is measured from the track.
				track.scrollTo( { left: slide.offsetLeft, behavior: reduceMotion ? 'auto' : 'smooth' } );
			} );
			dots.appendChild( dot );
		} );
		track.parentNode.insertBefore( dots, track.nextSibling );

		var pending = false;
		track.addEventListener( 'scroll', function () {
			if ( pending ) {
				return;
			}
			pending = true;
			window.requestAnimationFrame( function () {
				pending = false;
				var max = track.scrollWidth - track.clientWidth;
				var current = max > 0 ? Math.round( ( track.scrollLeft / max ) * ( slides.length - 1 ) ) : 0;
				Array.prototype.forEach.call( dots.children, function ( dot, i ) {
					dot.classList.toggle( 'swipe__dot--active', i === current );
				} );
			} );
		}, { passive: true } );
	} );

	// Stats counters.
	var counters = document.querySelectorAll( '[data-count-to]' );
	if ( ! counters.length || reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
		return; // The final number is already printed by PHP.
	}

	var format = function ( value ) {
		return Math.round( value ).toLocaleString( 'en-US' );
	};

	var countUp = function ( el ) {
		var target = parseInt( el.getAttribute( 'data-count-to' ), 10 ) || 0;
		var duration = 1800;
		var start = null;

		var step = function ( time ) {
			if ( null === start ) {
				start = time;
			}
			var progress = Math.min( ( time - start ) / duration, 1 );
			var eased = 1 - Math.pow( 1 - progress, 3 ); // easeOutCubic
			el.textContent = format( target * eased );
			if ( progress < 1 ) {
				window.requestAnimationFrame( step );
			}
		};

		window.requestAnimationFrame( step );
	};

	var observer = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( entry.isIntersecting ) {
				countUp( entry.target );
				observer.unobserve( entry.target );
			}
		} );
	}, { threshold: 0.6 } );

	counters.forEach( function ( el ) {
		el.textContent = '0';
		observer.observe( el );
	} );
} )();
