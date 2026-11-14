/**
 * "From source to glass": pins the scene on desktop and maps the scroll to the 7 stages.
 *
 * Per scroll frame it writes three transforms (clear water, the drop, the progress line) and, near the end,
 * one opacity (the glass photo) — all on their own HTML layers, so nothing is repainted. Stage changes only
 * toggle classes (the text and the impurities of a stage cross-fade with opacity).
 * Phones, short screens and the "low" level keep the plain list (no pin).
 */
const DESKTOP = '(min-width: 62em) and (min-height: 600px)';

export function initStages( gsap, ScrollTrigger ) {
	const section = document.querySelector( '[data-stages]' );
	if ( ! section || ! window.matchMedia( DESKTOP ).matches ) {
		return null;
	}

	const scene = section.querySelector( '[data-stages-scene]' );
	const items = [ ...section.querySelectorAll( '[data-stage]' ) ];
	const dirt = [ ...section.querySelectorAll( '[data-stages-dirt]' ) ];
	const ticks = [ ...section.querySelectorAll( '[data-stages-tick]' ) ];
	const drop = section.querySelector( '[data-stages-drop]' );
	const clear = section.querySelector( '[data-stages-clear]' );
	const bar = section.querySelector( '[data-stages-progress]' );
	const glass = section.querySelector( '.stages__photo--glass' );
	const count = items.length;
	if ( ! scene || ! count ) {
		return null;
	}

	section.classList.add( 'is-scene' );

	let active = -1;
	let travel = 0; // px the drop can move inside the tube
	let glassShown = '';

	const measure = () => {
		travel = drop && drop.parentNode ? Math.max( 0, drop.parentNode.clientHeight - drop.offsetHeight - 8 ) : 0;
	};

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
		if ( drop ) {
			drop.style.transform = `translate3d(0, ${ ( 4 + progress * travel ).toFixed( 1 ) }px, 0)`;
		}
		if ( clear ) {
			clear.style.transform = `scaleY(${ progress.toFixed( 4 ) })`;
		}
		if ( bar ) {
			bar.style.transform = `scaleX(${ progress.toFixed( 4 ) })`;
		}
		if ( glass ) {
			const opacity = gsap.utils.clamp( 0, 1, ( progress - 0.72 ) / 0.2 ).toFixed( 2 );
			if ( opacity !== glassShown ) {
				glassShown = opacity;
				glass.style.opacity = opacity;
			}
		}
	};

	const trigger = ScrollTrigger.create( {
		trigger: scene,
		start: 'top top',
		end: () => `+=${ Math.round( count * window.innerHeight * 0.55 ) }`,
		pin: true,
		anticipatePin: 1,
		invalidateOnRefresh: true,
		onRefresh: ( self ) => {
			measure();
			render( self.progress );
		},
		onUpdate: ( self ) => render( self.progress ),
	} );

	measure();
	render( 0 );

	return {
		/** Un-pin and fall back to the plain list (the level dropped to "low"). */
		kill() {
			trigger.kill( true );
			section.classList.remove( 'is-scene' );
			items.forEach( ( el ) => el.classList.remove( 'is-active' ) );
			dirt.forEach( ( el ) => el.classList.remove( 'is-clean' ) );
			[ drop, clear, bar, glass ].forEach( ( el ) => el && el.removeAttribute( 'style' ) );
		},
	};
}
