/**
 * Motion tokens for JS (GSAP and hand-written animations). Keep in sync with src/css/tokens.css.
 */
export const EASE = {
	out: 'expo.out', // CSS --ease-out: cubic-bezier(0.16, 1, 0.3, 1)
	inOut: 'power3.inOut', // CSS --ease-in-out: cubic-bezier(0.65, 0, 0.35, 1)
};

export const DUR = {
	press: 0.16,
	ui: 0.28,
	reveal: 0.6,
	scene: 1,
};

/** Desktop layout starts at 62em (992px). */
export const DESKTOP = '(min-width: 62em)';

export const reducedMotion = () => window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

/** Real mouse / trackpad (custom cursor, hover-only effects). */
export const finePointer = () => window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
