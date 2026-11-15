/**
 * "From source to glass": pins the scene on desktop and maps the scroll to the 7 stages.
 * Per frame it only sets transforms; stage changes toggle classes.
 * Phones and short screens keep the plain list (no pin).
 * The scene follows the breakpoint (gsap.matchMedia): rotating a tablet or narrowing the window tears it down,
 * otherwise the 12-column desktop grid stays on a narrow screen and makes the page wider than the viewport.
 */
const DESKTOP = '(min-width: 62em) and (min-height: 600px)';

export function initStages( gsap, ScrollTrigger ) {
	const section = document.querySelector( '[data-stages]' );
	if ( ! section ) {
		return;
	}
	gsap.matchMedia().add( DESKTOP, () => buildScene( section, gsap, ScrollTrigger ) );
}

function buildScene( section, gsap, ScrollTrigger ) {
	const scene = section.querySelector( '[data-stages-scene]' );
	const items = [ ...section.querySelectorAll( '[data-stage]' ) ];
	const dirt = [ ...section.querySelectorAll( '[data-stages-dirt]' ) ];
	const ticks = [ ...section.querySelectorAll( '[data-stages-tick]' ) ];
	const clear = section.querySelector( '[data-stages-clear]' );
	const bar = section.querySelector( '[data-stages-progress]' );
	const glass = section.querySelector( '.stages__photo--glass' );
	const count = items.length;
	if ( ! scene || ! count ) {
		return;
	}

	section.classList.add( 'is-scene' );

	let active = -1;
	const setActive = ( index ) => {
		if ( index === active ) {
			return;
		}
		active = index;
		items.forEach( ( el, i ) => el.classList.toggle( 'is-active', i === index ) );
		ticks.forEach( ( el, i ) => el.classList.toggle( 'is-active', i === index ) );
		dirt.forEach( ( el, i ) => el.classList.toggle( 'is-clean', i < index ) );
	};

	const render = ( progress ) => {
		setActive( Math.min( count - 1, Math.floor( progress * count ) ) );
		if ( clear ) {
			clear.style.transform = `scaleY(${ progress.toFixed( 4 ) })`;
		}
		if ( bar ) {
			bar.style.transform = `scaleX(${ progress.toFixed( 4 ) })`;
		}
		if ( glass ) {
			glass.style.opacity = gsap.utils.clamp( 0, 1, ( progress - 0.72 ) / 0.2 ).toFixed( 3 );
		}
	};

	ScrollTrigger.create( {
		trigger: scene,
		start: 'top top',
		end: () => `+=${ Math.round( count * window.innerHeight * 0.55 ) }`,
		pin: true,
		anticipatePin: 1,
		invalidateOnRefresh: true,
		onUpdate: ( self ) => render( self.progress ),
	} );

	render( 0 );

	// Leaving the desktop range: matchMedia kills the pin; put the plain list back.
	return () => {
		section.classList.remove( 'is-scene' );
		[ ...items, ...ticks ].forEach( ( el ) => el.classList.remove( 'is-active' ) );
		dirt.forEach( ( el ) => el.classList.remove( 'is-clean' ) );
		[ clear, bar ].forEach( ( el ) => el && el.style.removeProperty( 'transform' ) );
		if ( glass ) {
			glass.style.removeProperty( 'opacity' );
		}
	};
}
