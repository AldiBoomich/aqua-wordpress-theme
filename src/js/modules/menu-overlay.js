/**
 * Full-screen mobile menu (#menu-overlay, a sibling of the header).
 * Opens from the header toggle; Esc, any link, or switching to the desktop layout closes it.
 * While open: page scroll is locked, the rest of the page is inert, Tab stays inside (menu + toggle).
 */
import { DESKTOP } from '../tokens.js';

export function initMenuOverlay() {
	const overlay = document.getElementById( 'menu-overlay' );
	const toggle = document.querySelector( '[data-menu-toggle]' );
	if ( ! overlay || ! toggle ) {
		return;
	}

	const root = document.documentElement;
	const outside = [ document.getElementById( 'main' ), document.querySelector( '.site-footer' ) ].filter( Boolean );

	// Stagger index for the rise-in animation.
	overlay.querySelectorAll( '.overlay-nav__item, .menu-overlay__footer > *' ).forEach( ( el, i ) => {
		el.style.setProperty( '--i', String( i ) );
	} );

	const setInert = ( el, on ) => {
		if ( on ) {
			el.setAttribute( 'inert', '' );
		} else {
			el.removeAttribute( 'inert' );
		}
	};

	const isOpen = () => overlay.classList.contains( 'is-open' );

	const setOpen = ( open, restoreFocus ) => {
		if ( open === isOpen() ) {
			return;
		}
		overlay.classList.toggle( 'is-open', open );
		root.classList.toggle( 'has-menu-open', open );
		setInert( overlay, ! open );
		overlay.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
		outside.forEach( ( el ) => setInert( el, open ) );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		toggle.setAttribute( 'aria-label', toggle.dataset[ open ? 'labelClose' : 'labelOpen' ] || '' );

		if ( open ) {
			const first = overlay.querySelector( 'a[href]' );
			window.setTimeout( () => first && first.focus( { preventScroll: true } ), 320 );
		} else if ( restoreFocus ) {
			toggle.focus();
		}
	};

	setInert( overlay, true );

	toggle.addEventListener( 'click', () => setOpen( ! isOpen(), true ) );

	overlay.addEventListener( 'click', ( event ) => {
		if ( event.target.closest( 'a[href]' ) ) {
			setOpen( false, false );
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( ! isOpen() ) {
			return;
		}
		if ( 'Escape' === event.key ) {
			setOpen( false, true );
			return;
		}
		if ( 'Tab' !== event.key ) {
			return;
		}
		const list = [ toggle, ...overlay.querySelectorAll( 'a[href], button:not([disabled]), input, select, textarea' ) ].filter(
			( el ) => el.offsetParent !== null
		);
		const index = list.indexOf( document.activeElement );
		if ( event.shiftKey && index <= 0 ) {
			event.preventDefault();
			list[ list.length - 1 ].focus();
		} else if ( ! event.shiftKey && index === list.length - 1 ) {
			event.preventDefault();
			list[ 0 ].focus();
		}
	} );

	window.matchMedia( DESKTOP ).addEventListener( 'change', ( event ) => {
		if ( event.matches ) {
			setOpen( false, false );
		}
	} );
}
