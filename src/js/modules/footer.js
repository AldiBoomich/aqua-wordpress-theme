/**
 * Footer:
 * - the water line at the top edge drifts only while the footer is on screen (.is-active);
 * - the wordmark is sized to fill the content width (any text from Theme Settings fits);
 * - aqua fills the letters as the footer scrolls into view, to the brim at the bottom of the page
 *   (--fill 0 → FULL, --wave-x follows the scroll, so nothing repaints while the page is still).
 */
import { reducedMotion } from '../tokens.js';

/** Fill at the end of the page (see .site-footer__wordmark-water: above 1 the waves clear the letter tops). */
const FULL = 1.1;

export function initFooter() {
	const footer = document.querySelector( '[data-footer]' );
	if ( ! footer ) {
		return;
	}

	const wordmark = footer.querySelector( '.site-footer__wordmark' );
	const outline = wordmark ? wordmark.querySelector( '.site-footer__wordmark-outline' ) : null;
	const water = wordmark ? wordmark.querySelector( '.site-footer__wordmark-water' ) : null;

	// Fit the wordmark text to the available width.
	const fit = () => {
		if ( ! wordmark || ! outline ) {
			return;
		}
		wordmark.style.fontSize = '';
		const style = window.getComputedStyle( wordmark );
		const available = wordmark.clientWidth - parseFloat( style.paddingLeft ) - parseFloat( style.paddingRight );
		const width = outline.getBoundingClientRect().width;
		if ( width > 0 && available > 0 ) {
			const size = parseFloat( style.fontSize ) * ( available / width );
			wordmark.style.fontSize = `${ Math.min( size, 600 ) }px`;
		}
	};

	let visible = false;
	let queued = false;

	const update = () => {
		queued = false;
		if ( ! water || reducedMotion() ) {
			return;
		}
		const rect = wordmark.getBoundingClientRect();
		const vh = window.innerHeight;
		// Starts when the wordmark's top enters the bottom of the screen and ends at the bottom of the page,
		// where the letters are filled to the brim (FULL > 1 lifts the wave crests above the letters).
		const maxScroll = document.documentElement.scrollHeight - vh;
		const topAtEnd = rect.top - Math.max( 0, maxScroll - window.scrollY );
		const span = Math.max( 1, vh - topAtEnd );
		const fill = FULL * Math.min( 1, Math.max( 0, ( vh - rect.top ) / span ) );
		water.style.setProperty( '--fill', fill.toFixed( 3 ) );
		water.style.setProperty( '--wave-x', `${ ( fill * 120 ).toFixed( 1 ) }%` );
	};

	const onScroll = () => {
		if ( visible && ! queued ) {
			queued = true;
			window.requestAnimationFrame( update );
		}
	};

	if ( 'IntersectionObserver' in window ) {
		new IntersectionObserver( ( entries ) => {
			visible = entries[ 0 ].isIntersecting;
			footer.classList.toggle( 'is-active', visible && ! reducedMotion() );
			if ( visible ) {
				update();
			}
		} ).observe( footer );
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );

	let resizeTimer = 0;
	window.addEventListener( 'resize', () => {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( () => {
			fit();
			update();
		}, 120 );
	} );

	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( fit );
	}
	fit();
}
