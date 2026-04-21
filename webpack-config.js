/**
 * RunPace – Webpack configuration
 *
 * Extends @wordpress/scripts defaults to build all five custom blocks
 * and the shared block-variations editor script.
 *
 * Each block outputs to its own build/ directory so block.json can
 * reference  "file:./build/index.js"  etc.
 *
 * Compatible with @wordpress/scripts v27–v30+ (webpack-cli v5).
 *
 * Usage:
 *   npm run build    → production build
 *   npm run start    → development watch
 *
 * ── Why we use a custom config ────────────────────────────────────────────
 * The default wp-scripts entry point discovery reads from src/index.js in
 * the project root. We have multiple blocks each with their own src/ and
 * build/ directories, so we override `entry` to list every entry point
 * explicitly and override `output.path` to the theme root so the relative
 * directory structure is preserved.
 */

const path         = require( 'path' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

/**
 * Resolve a path relative to this config file (theme root).
 *
 * @param {...string} segments
 * @returns {string}
 */
const r = ( ...segments ) => path.resolve( __dirname, ...segments );

module.exports = {
	...defaultConfig,

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

		// ── Shared: block variations (editor only) ─────────────────────────
		'assets/js/build/block-variations': r( 'assets/js/block-variations.js' ),
	},

	output: {
		...defaultConfig.output,
		// Output to theme root so entry key paths resolve correctly.
		// e.g. entry key 'blocks/marathon-filter/build/index'
		//   → <theme-root>/blocks/marathon-filter/build/index.js
		path: r( '.' ),
		filename: '[name].js',
		// Async chunks land in assets/js/chunks/ to keep block dirs clean.
		chunkFilename: 'assets/js/chunks/[name].[contenthash].js',
	},
};