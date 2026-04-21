/**
 * RunPace – Webpack configuration
 *
 * Extends @wordpress/scripts defaults to:
 *   1. Build all four custom blocks from their src/ directories.
 *   2. Build the block-variations editor script.
 *   3. Output to each block's own build/ directory so block.json
 *      can reference `file:./build/index.js` etc.
 *
 * Usage:
 *   npm run build    → production build
 *   npm run start    → development watch
 */

const path                     = require( 'path' );
const defaultConfig            = require( '@wordpress/scripts/config/webpack.config' );
const { getWebpackEntryPoints } = require( '@wordpress/scripts/utils/config' );

/**
 * Helper — resolves a path relative to this config file.
 *
 * @param  {...string} segments Path segments.
 * @returns {string}
 */
const r = ( ...segments ) => path.resolve( __dirname, ...segments );

module.exports = {
	...defaultConfig,

	/**
	 * Multiple entry points — one per block (editor + view) plus
	 * the shared block-variations script.
	 *
	 * wp-scripts uses `entry` to determine output filenames:
	 *   key "blocks/marathon-filter/index" → build/blocks/marathon-filter/index.js
	 *
	 * We override the output.path to the theme root so relative paths work.
	 */
	entry: {
		// ── Block: marathon-filter ─────────────────────────────────────────
		'blocks/marathon-filter/build/index': r( 'blocks/marathon-filter/src/index.js' ),
		'blocks/marathon-filter/build/view':  r( 'blocks/marathon-filter/src/view.js' ),

		// ── Block: marathon-info ───────────────────────────────────────────
		'blocks/marathon-info/build/index': r( 'blocks/marathon-info/src/index.js' ),

		// ── Block: featured-marathon ───────────────────────────────────────
		'blocks/featured-marathon/build/index': r( 'blocks/featured-marathon/src/index.js' ),

		// ── Block: training-plan-card ──────────────────────────────────────
		'blocks/training-plan-card/build/index': r( 'blocks/training-plan-card/src/index.js' ),

		// ── Block: stats-highlight ─────────────────────────────────────────
		'blocks/stats-highlight/build/index': r( 'blocks/stats-highlight/src/index.js' ),
		'blocks/stats-highlight/build/view':  r( 'blocks/stats-highlight/src/view.js' ),

		// ── Shared: block variations (loaded in editor) ────────────────────
		'assets/js/build/block-variations': r( 'assets/js/block-variations.js' ),
	},

	output: {
		...defaultConfig.output,
		// Output directly to theme root so block.json relative paths resolve.
		path: r( '.' ),
		// Preserve the entry key directory structure.
		filename: '[name].js',
		// Module output for viewScriptModule entries.
		chunkFilename: 'assets/js/chunks/[name].[contenthash].js',
	},

	module: {
		...defaultConfig.module,
	},

	plugins: [
		...defaultConfig.plugins,
	],
};