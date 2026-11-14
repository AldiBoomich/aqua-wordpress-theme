/**
 * Aqua theme script (ES module, bundled by Vite, loaded deferred).
 * Heavy parts (WebGL, scroll scenes) are split into chunks and imported only when needed.
 */
import { initHeader } from './modules/header.js';
import { initNav } from './modules/nav.js';
import { initMenuOverlay } from './modules/menu-overlay.js';
import { initFooter } from './modules/footer.js';
import { initConceptBar } from './modules/concept-bar.js';

initHeader();
initNav();
initMenuOverlay();
initFooter();
initConceptBar();
