/**
 * Aqua theme script (vanilla JS, no jQuery).
 * - Preloader: hidden once the page is ready.
 * - WOW.js: scroll reveal animations.
 * - Sticky header: .site-header--sticky after scrolling 45px (desktop styles only).
 * - Back to top: .back-to-top--visible after 300px, smooth scroll on click.
 * - Stats counters: count up from 0 when they enter the viewport.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Preloader.
	var preloader = document.getElementById( 'preloader' );
	if ( preloader ) {
		var hidePreloader = function () {
			preloader.classList.add( 'preloader--hidden' );
		};
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', hidePreloader );
		} else {
			hidePreloader();
		}
	}

	// Scroll reveal animations.
	if ( typeof window.WOW === 'function' ) {
		new window.WOW( { live: false } ).init();
	}

	// Sticky header + back to top button.
	var header = document.getElementById( 'site-header' );
	var backToTop = document.querySelector( '.back-to-top' );
	var ticking = false;

	var onScroll = function () {
		var y = window.scrollY || window.pageYOffset;
		if ( header ) {
			header.classList.toggle( 'site-header--sticky', y > 45 );
		}
		if ( backToTop ) {
			backToTop.classList.toggle( 'back-to-top--visible', y > 300 );
		}
		ticking = false;
	};

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			window.requestAnimationFrame( onScroll );
			ticking = true;
		}
	}, { passive: true } );
	onScroll();

	if ( backToTop ) {
		backToTop.addEventListener( 'click', function ( event ) {
			event.preventDefault();
			window.scrollTo( { top: 0, behavior: reduceMotion ? 'auto' : 'smooth' } );
		} );
	}

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
