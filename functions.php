<?php
/**
 * RunPace Theme – functions.php
 *
 * Core theme setup. Keep this file lean; all feature modules
 * are loaded via the inc/ autoloader at the bottom.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Constants ────────────────────────────────────────────────────────────────

define( 'RUNPACE_VERSION',   '1.0.0' );
define( 'RUNPACE_DIR',       get_template_directory() );
define( 'RUNPACE_URI',       get_template_directory_uri() );
define( 'RUNPACE_ASSETS',    RUNPACE_URI . '/assets' );

// ─── Theme Setup ──────────────────────────────────────────────────────────────

/**
 * Register theme supports and editor settings.
 */
function runpace_setup(): void {

	/*
	 * Translations: load theme text domain.
	 * Translations files live in /languages/.
	 */
	load_theme_textdomain( 'runpace', RUNPACE_DIR . '/languages' );

	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable post thumbnail support.
	add_theme_support( 'post-thumbnails' );

	// Opt in to responsive embeds.
	add_theme_support( 'responsive-embeds' );

	// Output HTML5 markup for core features.
	add_theme_support(
		'html5',
		[
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
			'navigation-widgets',
		]
	);

	// Tell WordPress this is a block theme (full site editing).
	add_theme_support( 'block-templates' );

	// Allow editor styles to load in the block editor iframe.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	// Wide and full alignment support for blocks.
	add_theme_support( 'align-wide' );

	// Disable WordPress core block patterns so our own are the only ones shown.
	remove_theme_support( 'core-block-patterns' );

	// Register custom image sizes.
	add_image_size( 'runpace-card',      600, 400, true );
	add_image_size( 'runpace-card-wide', 900, 500, true );
	add_image_size( 'runpace-hero',     1600, 800, true );
}
add_action( 'after_setup_theme', 'runpace_setup' );

// ─── Assets ───────────────────────────────────────────────────────────────────

/**
 * Enqueue front-end assets.
 *
 * JS is handled per-block via block.json (viewScript / editorScript).
 * Only global theme CSS is enqueued here.
 */
function runpace_enqueue_assets(): void {

	// Global theme styles (resets, utilities not covered by theme.json).
	wp_enqueue_style(
		'runpace-global',
		RUNPACE_ASSETS . '/css/global.css',
		[],
		RUNPACE_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'runpace_enqueue_assets' );

// ─── Block Editor Assets ───────────────────────────────────────────────────────

/**
 * Enqueue assets that load only in the block editor.
 *
 * - block-variations.js: registers custom Query Loop variations
 *   (Upcoming Marathons, Featured Races, Related Races).
 *
 * NOTE: This is the single authoritative registration of runpace-block-variations.
 * inc/05-blocks.php must NOT register this handle — doing so causes a duplicate
 * registration that silently drops one copy and may enqueue a missing file.
 */
function runpace_enqueue_editor_scripts(): void {

	$variations_asset = RUNPACE_DIR . '/assets/js/build/block-variations.asset.php';

	if ( file_exists( $variations_asset ) ) {
		$asset = require $variations_asset;
	} else {
		// Fallback during development before first build.
		$asset = [
			'dependencies' => [ 'wp-blocks', 'wp-i18n', 'wp-block-editor' ],
			'version'      => RUNPACE_VERSION,
		];
	}

	wp_enqueue_script(
		'runpace-block-variations',
		RUNPACE_ASSETS . '/js/build/block-variations.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'runpace_enqueue_editor_scripts' );

// ─── Block Editor ─────────────────────────────────────────────────────────────

/**
 * Register block categories for RunPace custom blocks.
 *
 * @param  array $categories Existing block categories.
 * @return array
 */
function runpace_block_categories( array $categories ): array {

	return array_merge(
		[
			[
				'slug'  => 'runpace',
				'title' => __( 'RunPace', 'runpace' ),
				'icon'  => null,
			],
		],
		$categories
	);
}
add_filter( 'block_categories_all', 'runpace_block_categories' );

/**
 * Register custom block patterns categories.
 */
function runpace_register_pattern_categories(): void {

	register_block_pattern_category(
		'runpace-marathons',
		[ 'label' => __( 'Marathons', 'runpace' ) ]
	);

	register_block_pattern_category(
		'runpace-training',
		[ 'label' => __( 'Training', 'runpace' ) ]
	);

	register_block_pattern_category(
		'runpace-hero',
		[ 'label' => __( 'Heroes', 'runpace' ) ]
	);
}
add_action( 'init', 'runpace_register_pattern_categories' );

// ─── Autoloader ───────────────────────────────────────────────────────────────

/**
 * Load all PHP modules from inc/.
 *
 * Files are loaded alphabetically. Prefix with a number (e.g. 01-)
 * to control load order when dependencies exist.
 */
( function (): void {

	$modules = glob( RUNPACE_DIR . '/inc/*.php' );

	if ( ! $modules ) {
		return;
	}

	foreach ( $modules as $module ) {
		require_once $module;
	}
} )();