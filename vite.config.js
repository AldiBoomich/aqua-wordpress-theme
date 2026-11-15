/**
 * Front-end build for the Aqua theme.
 *
 * Sources live in src/ (CSS: tokens, base, layout and one file per BEM block; JS: ES modules).
 * `npm run build` writes hashed files + .vite/manifest.json to assets/dist/,
 * and inc/assets.php enqueues them from the manifest. assets/dist/ is committed,
 * so the theme can be deployed as a plain zip without Node on the server.
 */
import { defineConfig } from 'vite';

export default defineConfig( {
	base: './',
	publicDir: false,
	// The hero water worker (src/js/home/water.worker.js) is an ES module worker.
	worker: {
		format: 'es',
	},
	build: {
		outDir: 'assets/dist',
		emptyOutDir: true,
		manifest: true,
		target: 'es2020',
		cssMinify: true,
		assetsInlineLimit: 0,
		rollupOptions: {
			input: {
				main: 'src/js/main.js',
				style: 'src/css/main.css',
			},
		},
	},
} );
