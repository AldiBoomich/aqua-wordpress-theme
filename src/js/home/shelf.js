/**
 * Bottles shelf: on desktop the section is pinned; the title stays on the left while the bottles slide past.
 * Keyboard users: focusing a card scrolls the page to the point where that card is in view.
 * Phones keep the native horizontal scroller.
 * The scene follows the breakpoint (gsap.matchMedia) and is torn down when the screen gets narrow.
 */
const DESKTOP = '(min-width: 62em) and (min-height: 600px)';

export function initShelf( gsap, ScrollTrigger, getLenis ) {
	const section = document.querySelector( '[data-shelf]' );
	if ( ! section ) {
		return;
	}
	gsap.matchMedia().add( DESKTOP, () => buildScene( section, gsap, getLenis ) );
}

function buildScene( section, gsap, getLenis ) {
	const pin = section.querySelector( '[data-shelf-pin]' );
	const track = section.querySelector( '[data-shelf-track]' );
	const viewport = section.querySelector( '[data-shelf-viewport]' );
	const bar = section.querySelector( '[data-shelf-progress]' );
	if ( ! pin || ! track || ! viewport ) {
		return;
	}

	section.classList.add( 'is-scene' );
	const distance = () => Math.max( 0, track.scrollWidth - viewport.clientWidth );

	const tween = gsap.to( track, {
		x: () => -distance(),
		ease: 'none',
		scrollTrigger: {
			trigger: pin,
			start: 'top top',
			end: () => `+=${ distance() }`,
			pin: true,
			scrub: true,
			anticipatePin: 1,
			invalidateOnRefresh: true,
			onUpdate: ( self ) => {
				if ( bar ) {
					bar.style.transform = `scaleX(${ self.progress.toFixed( 4 ) })`;
				}
			},
		},
	} );

	// Bring a focused card into view by scrolling to its position on the timeline.
	const onFocus = ( event ) => {
		const item = event.target.closest( '.shelf__item' );
		const st = tween.scrollTrigger;
		const max = distance();
		if ( ! item || ! st || ! max ) {
			return;
		}
		const ratio = gsap.utils.clamp( 0, 1, ( item.offsetLeft - viewport.clientWidth * 0.25 ) / max );
		const y = st.start + ( st.end - st.start ) * ratio;
		const lenis = getLenis();
		if ( lenis ) {
			lenis.scrollTo( y, { immediate: true } );
		} else {
			window.scrollTo( { top: y, behavior: 'instant' } );
		}
	};
	track.addEventListener( 'focusin', onFocus );

	// Leaving the desktop range: matchMedia reverts the tween (track x) and kills the pin.
	return () => {
		track.removeEventListener( 'focusin', onFocus );
		tween.scrollTrigger?.kill();
		tween.kill();
		gsap.set( track, { clearProps: 'all' } );
		section.classList.remove( 'is-scene' );
		if ( bar ) {
			bar.style.removeProperty( 'transform' );
		}
	};
}
