/**
 * RunPace – webpack.config.js
 *
 * Extends @wordpress/scripts default config.
 * Adds:
 *   - One entry per block (editor script + view module)
 *   - Block variations asset for the editor
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path          = require( 'path' );

/**
 * Collect entry points from blocks directory.
 * Each block contributes:
 *   blocks/<name>/src/index.js  → build/blocks/<name>/index.js  (editor)
 *   blocks/<name>/src/view.js   → build/blocks/<name>/view.js   (frontend module)
 */
const blocksDir = path.resolve( __dirname, 'blocks' );
const blockNames = [
	'marathon-info',
	'featured-marathon',
	'marathon-filter',
	'training-plan-card',
	'stats-highlight',
];

const blockEntries = blockNames.reduce( ( entries, name ) => {
	const srcDir = path.join( blocksDir, name, 'src' );

	// Editor script.
	entries[ `blocks/${ name }/index` ] = path.join( srcDir, 'index.js' );

	// View module (only if view.js exists).
	const viewPath = path.join( srcDir, 'view.js' );
	try {
		require.resolve( viewPath );
		entries[ `blocks/${ name }/view` ] = viewPath;
	} catch {
		// No view.js for this block — skip.
	}

	return entries;
}, {} );

module.exports = {
	...defaultConfig,
	entry: {
		...blockEntries,
		// Block variations for the editor.
		'assets/js/block-variations': path.resolve( __dirname, 'assets/js/block-variations.js' ),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
};