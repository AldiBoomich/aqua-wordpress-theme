/**
 * Bottles shelf: on desktop the section is pinned; the title stays on the left while the bottles slide past.
 * The slide is one translate3d on the track (its own compositor layer while the scene is active; no layout).
 * Keyboard users: focusing a card scrolls the page to the point where that card is in view.
 * Phones and the "low" level keep the native horizontal scroller.
 */
const DESKTOP = '(min-width: 62em) and (min-height: 600px)';

export function initShelf( gsap, ScrollTrigger, getLenis ) {
	const section = document.querySelector( '[data-shelf]' );
	if ( ! section || ! window.matchMedia( DESKTOP ).matches ) {
		return null;
	}

	const pin = section.querySelector( '[data-shelf-pin]' );
	const track = section.querySelector( '[data-shelf-track]' );
	const viewport = section.querySelector( '[data-shelf-viewport]' );
	const bar = section.querySelector( '[data-shelf-progress]' );
	if ( ! pin || ! track || ! viewport ) {
		return null;
	}

	section.classList.add( 'is-scene' );
	const distance = () => Math.max( 0, track.scrollWidth - viewport.clientWidth );

	const tween = gsap.to( track, {
		x: () => -distance(),
		ease: 'none',
		force3D: true,
		scrollTrigger: {
			trigger: pin,
			start: 'top top',
			end: () => `+=${ distance() }`,
			pin: true,
			scrub: true,
			anticipatePin: 1,
			invalidateOnRefresh: true,
			// will-change only while the scene is on screen.
			onToggle: ( self ) => section.classList.toggle( 'is-moving', self.isActive ),
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

	return {
		/** Un-pin and fall back to the native scroller (the level dropped to "low"). */
		kill() {
			track.removeEventListener( 'focusin', onFocus );
			if ( tween.scrollTrigger ) {
				tween.scrollTrigger.kill( true );
			}
			tween.kill();
			gsap.set( track, { clearProps: 'all' } );
			if ( bar ) {
				bar.removeAttribute( 'style' );
			}
			section.classList.remove( 'is-scene', 'is-moving' );
		},
	};
}
