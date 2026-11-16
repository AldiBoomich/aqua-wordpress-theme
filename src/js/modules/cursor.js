/**
 * Water drop cursor (mouse only; the system arrow stays).
 * A small drop trails the pointer on the shared GSAP ticker (transform only) and sleeps when it catches up.
 * States from what is under the pointer:
 *   is-link  — links, buttons, form controls: the drop swells into a soft lens;
 *   is-text  — text fields: hidden (the text caret is enough);
 *   is-light — deep backgrounds (footer, business panel): the drop turns brand aqua.
 * Hidden when the pointer leaves the window (or enters an iframe).
 */
const LINK = 'a[href], button, [role="button"], label, summary, select, input[type="checkbox"], input[type="radio"], input[type="submit"], [data-cursor="link"]';
const TEXT = 'input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]), textarea, [contenteditable="true"]';
const LIGHT = '.site-footer, .business__panel, [data-cursor-tone="light"]';

export function initCursor( gsap ) {
	const root = document.createElement( 'div' );
	root.className = 'cursor';
	root.setAttribute( 'aria-hidden', 'true' );
	root.innerHTML = '<span class="cursor__drop"></span>';
	document.body.append( root );

	const pos = { x: 0, y: 0 };
	const target = { x: 0, y: 0 };
	let running = false;
	let placed = false;

	const render = () => {
		root.style.transform = `translate3d(${ pos.x }px, ${ pos.y }px, 0)`;
	};

	const tick = () => {
		// Frame-rate independent easing (same feel at 60 and 165 Hz).
		const k = 1 - Math.pow( 1 - 0.28, gsap.ticker.deltaRatio() );
		pos.x += ( target.x - pos.x ) * k;
		pos.y += ( target.y - pos.y ) * k;
		if ( Math.abs( target.x - pos.x ) < 0.1 && Math.abs( target.y - pos.y ) < 0.1 ) {
			pos.x = target.x;
			pos.y = target.y;
			gsap.ticker.remove( tick );
			running = false;
		}
		render();
	};

	const setState = ( el ) => {
		const node = el instanceof Element ? el : null;
		const text = !! ( node && node.closest( TEXT ) );
		const link = ! text && !! ( node && node.closest( LINK ) );
		root.classList.toggle( 'is-text', text );
		root.classList.toggle( 'is-link', link );
		root.classList.toggle( 'is-light', !! ( node && node.closest( LIGHT ) ) );
	};

	document.addEventListener(
		'pointermove',
		( event ) => {
			if ( event.pointerType !== 'mouse' ) {
				return;
			}
			target.x = event.clientX;
			target.y = event.clientY;
			if ( ! placed ) {
				// First move: appear in place, do not fly in from the corner.
				pos.x = target.x;
				pos.y = target.y;
				placed = true;
				render();
			}
			root.classList.add( 'is-visible' );
			if ( ! running ) {
				running = true;
				gsap.ticker.add( tick );
			}
		},
		{ passive: true }
	);

	document.addEventListener( 'pointerover', ( event ) => event.pointerType === 'mouse' && setState( event.target ), { passive: true } );

	document.addEventListener(
		'pointerout',
		( event ) => {
			if ( ! event.relatedTarget ) {
				root.classList.remove( 'is-visible' );
			}
		},
		{ passive: true }
	);

	document.addEventListener( 'pointerdown', () => root.classList.add( 'is-pressed' ), { passive: true } );
	document.addEventListener( 'pointerup', () => root.classList.remove( 'is-pressed' ), { passive: true } );
	window.addEventListener( 'blur', () => root.classList.remove( 'is-visible', 'is-pressed' ) );
}
