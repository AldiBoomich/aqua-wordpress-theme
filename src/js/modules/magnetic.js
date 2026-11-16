/**
 * Magnetic buttons (mouse only): the main buttons lean a few pixels towards the pointer and spring back.
 * Moves the CSS `translate` property, so the button's own `transform` (press scale) and its rising
 * water fill keep working. Targets: [data-magnetic], the primary / light buttons and the round toggles
 * (menu, marquee pause), not inside the full-screen menu.
 */
const SELECTOR = '[data-magnetic], .button--primary, .button--light, .site-header__toggle, .marquee__toggle';
const PULL = 0.3; // share of the pointer's distance from the centre
const MAX = 8; // px

export function initMagnetic( gsap ) {
	document.querySelectorAll( SELECTOR ).forEach( ( el ) => {
		if ( el.closest( '.menu-overlay' ) ) {
			return;
		}
		const offset = { x: 0, y: 0 };
		const apply = () => {
			const still = Math.abs( offset.x ) < 0.01 && Math.abs( offset.y ) < 0.01;
			el.style.translate = still ? '' : `${ offset.x.toFixed( 2 ) }px ${ offset.y.toFixed( 2 ) }px`;
		};
		const clamp = gsap.utils.clamp( -MAX, MAX );

		el.addEventListener(
			'pointermove',
			( event ) => {
				if ( event.pointerType !== 'mouse' ) {
					return;
				}
				const rect = el.getBoundingClientRect();
				// The rect already includes the current lean; measure from the resting centre.
				const cx = rect.left - offset.x + rect.width / 2;
				const cy = rect.top - offset.y + rect.height / 2;
				gsap.to( offset, {
					x: clamp( ( event.clientX - cx ) * PULL ),
					y: clamp( ( event.clientY - cy ) * PULL ),
					duration: 0.35,
					ease: 'power3.out',
					overwrite: true,
					onUpdate: apply,
				} );
			},
			{ passive: true }
		);

		el.addEventListener(
			'pointerleave',
			() => gsap.to( offset, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.4)', overwrite: true, onUpdate: apply } ),
			{ passive: true }
		);
	} );
}
