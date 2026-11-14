/**
 * Desktop sub menus (.main-nav__item--has-sub).
 * Open on hover (mouse) with a short close delay, or with the toggle button (click / Enter / Space).
 * Esc closes and returns focus to the toggle; clicking outside or tabbing away closes too.
 */
import { finePointer } from '../tokens.js';

const CLOSE_DELAY = 180;

export function initNav() {
	const items = document.querySelectorAll( '.main-nav__item--has-sub' );

	items.forEach( ( item ) => {
		const toggle = item.querySelector( '[data-submenu-toggle]' );
		if ( ! toggle ) {
			return;
		}
		let timer = 0;

		const setOpen = ( open ) => {
			window.clearTimeout( timer );
			item.classList.toggle( 'is-open', open );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		};

		toggle.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			setOpen( ! item.classList.contains( 'is-open' ) );
		} );

		item.addEventListener( 'mouseenter', () => {
			if ( finePointer() ) {
				setOpen( true );
			}
		} );

		item.addEventListener( 'mouseleave', () => {
			if ( finePointer() ) {
				timer = window.setTimeout( () => setOpen( false ), CLOSE_DELAY );
			}
		} );

		item.addEventListener( 'keydown', ( event ) => {
			if ( 'Escape' === event.key && item.classList.contains( 'is-open' ) ) {
				setOpen( false );
				toggle.focus();
			}
		} );

		item.addEventListener( 'focusout', ( event ) => {
			if ( ! item.contains( event.relatedTarget ) ) {
				setOpen( false );
			}
		} );

		document.addEventListener( 'click', ( event ) => {
			if ( ! item.contains( event.target ) ) {
				setOpen( false );
			}
		} );
	} );
}
