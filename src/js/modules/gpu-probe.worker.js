/**
 * GPU probe (module worker): creates a 1×1 WebGL context on an OffscreenCanvas, off the main thread,
 * and reports whether it runs on real graphics hardware.
 * Message out: { software: boolean } — true without WebGL or with a software rasteriser;
 *              { unknown: true } — this browser cannot do WebGL in a worker (the page keeps its defaults).
 */
const SOFTWARE = /SwiftShader|Basic Render|llvmpipe|softpipe|Software/i;

let result = { unknown: true };
try {
	if ( typeof OffscreenCanvas !== 'undefined' ) {
		const gl = new OffscreenCanvas( 1, 1 ).getContext( 'webgl', { powerPreference: 'low-power' } );
		if ( ! gl ) {
			result = { software: true };
		} else {
			const info = gl.getExtension( 'WEBGL_debug_renderer_info' );
			const name = info ? String( gl.getParameter( info.UNMASKED_RENDERER_WEBGL ) ) : '';
			result = { software: SOFTWARE.test( name ) };
			const lose = gl.getExtension( 'WEBGL_lose_context' );
			if ( lose ) {
				lose.loseContext();
			}
		}
	}
} catch ( error ) {
	// Keep "unknown".
}
self.postMessage( result );
