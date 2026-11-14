/**
 * Aqua theme script (vanilla JS, no jQuery).
 * - Preloader: hidden once the page is ready.
 * - WOW.js: scroll reveal animations.
 * - Sticky header: .site-header--sticky after scrolling 45px (shadow on mobile, fixed bar on desktop).
 * - Mobile menu panel: open / close, Esc, backdrop, focus trap, page scroll lock.
 * - Swipe carousels (.swipe): dots synced with the horizontal scroll.
 * - Hero slider pauses while off screen (.hero--paused).
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

	// Mobile menu panel.
	var toggler = header ? header.querySelector( '.site-header__toggler' ) : null;
	var panel = document.getElementById( 'site-menu' );
	var backdrop = header ? header.querySelector( '.site-header__backdrop' ) : null;
	var desktop = window.matchMedia( '(min-width: 992px)' );

	if ( toggler && panel ) {
		var isOpen = function () {
			return header.classList.contains( 'site-header--menu-open' );
		};

		var focusables = function () {
			return Array.prototype.filter.call(
				panel.querySelectorAll( 'a[href], button:not([disabled]), input, select, textarea' ),
				function ( el ) {
					return el.offsetParent !== null;
				}
			).concat( [ toggler ] );
		};

		var setMenu = function ( open, restoreFocus ) {
			if ( open === isOpen() ) {
				return;
			}
			if ( open ) {
				// The panel starts right under the bar (the admin bar may push the header down).
				header.style.setProperty( '--aqua-menu-top', Math.max( 0, header.getBoundingClientRect().bottom ) + 'px' );
			}
			header.classList.toggle( 'site-header--menu-open', open );
			document.documentElement.classList.toggle( 'has-menu-open', open );
			toggler.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			toggler.setAttribute( 'aria-label', toggler.getAttribute( open ? 'data-label-close' : 'data-label-open' ) );
			if ( open ) {
				var first = panel.querySelector( 'a[href]' );
				if ( first ) {
					window.setTimeout( function () {
						first.focus( { preventScroll: true } );
					}, 250 );
				}
			} else if ( restoreFocus ) {
				toggler.focus();
			}
		};

		toggler.addEventListener( 'click', function () {
			setMenu( ! isOpen(), true );
		} );

		if ( backdrop ) {
			backdrop.addEventListener( 'click', function () {
				setMenu( false, true );
			} );
		}

		// Following a link closes the panel (dropdown toggles only open their sub menu).
		panel.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( 'a[href]' );
			if ( link && ! link.hasAttribute( 'data-bs-toggle' ) ) {
				setMenu( false, false );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( ! isOpen() ) {
				return;
			}
			if ( 'Escape' === event.key ) {
				setMenu( false, true );
				return;
			}
			// Keep keyboard focus inside the panel + menu button.
			if ( 'Tab' === event.key ) {
				var list = focusables();
				var index = list.indexOf( document.activeElement );
				if ( event.shiftKey && index <= 0 ) {
					event.preventDefault();
					list[ list.length - 1 ].focus();
				} else if ( ! event.shiftKey && index === list.length - 1 ) {
					event.preventDefault();
					list[ 0 ].focus();
				}
			}
		} );

		var onBreakpoint = function () {
			if ( desktop.matches ) {
				setMenu( false, false );
			}
		};
		if ( desktop.addEventListener ) {
			desktop.addEventListener( 'change', onBreakpoint );
		} else if ( desktop.addListener ) {
			desktop.addListener( onBreakpoint );
		}
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
