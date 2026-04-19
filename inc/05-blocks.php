<?php
/**
 * RunPace – Block Registration
 *
 * Registers all custom RunPace blocks using register_block_type() with
 * block.json metadata. WordPress reads `render`, `style`, `editorStyle`,
 * and `editorScript` automatically from block.json, so this file only
 * needs to call register_block_type() once per block.
 *
 * Load order: called after CPTs (01) and taxonomies (02) are registered,
 * so any render callbacks that query CPT data work on the first load.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all RunPace custom blocks.
 *
 * Each block is registered from its own directory using block.json.
 * WordPress resolves all asset handles (style, editorStyle, editorScript,
 * viewScript, render) relative to the block's directory.
 */
function runpace_register_blocks(): void {

	/**
	 * Block definitions.
	 *
	 * Key   = block directory name (relative to blocks/).
	 * Value = optional args array merged into register_block_type().
	 *         For most blocks, block.json alone is sufficient.
	 */
	$blocks = [

		// ── Marathon Info ─────────────────────────────────────────────────────
		// Displays race metadata pulled from post meta.
		// Works on single marathon pages and inside Query Loop.
		'marathon-info'       => [],

		// ── Featured Marathon ──────────────────────────────────────────────────
		// Hero-style card; auto-queries for a featured marathon or uses
		// a specific post selected via the editor post picker.
		'featured-marathon'   => [],

		// ── Training Plan Card ─────────────────────────────────────────────────
		// Compact card for training-plan posts. Designed for Query Loop grids.
		'training-plan-card'  => [],

		// ── Stats Highlight ────────────────────────────────────────────────────
		// Static stats strip (e.g. "42KM", "10,000+ runners").
		// Uses Interactivity API for scroll-triggered entrance animation.
		'stats-highlight'     => [],

	];

	foreach ( $blocks as $block_name => $extra_args ) {

		$block_dir = RUNPACE_DIR . '/blocks/' . $block_name;

		if ( ! is_dir( $block_dir ) ) {
			// Log a notice in debug mode; don't fatally error in production.
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions
				trigger_error(
					sprintf( 'RunPace: block directory not found: %s', esc_html( $block_dir ) ),
					E_USER_NOTICE
				);
			}
			continue;
		}

		$result = register_block_type( $block_dir, $extra_args );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && false === $result ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions
			trigger_error(
				sprintf( 'RunPace: failed to register block: %s', esc_html( $block_name ) ),
				E_USER_NOTICE
			);
		}
	}
}
add_action( 'init', 'runpace_register_blocks' );

// ─── Query Loop Variations ────────────────────────────────────────────────────

/**
 * Register custom Query Loop block variations for RunPace CPTs.
 *
 * These appear in the Query Loop block's Pattern Chooser as pre-configured
 * starting points.
 *
 * Note: variations are registered client-side (JS) for full block editor
 * support. The filter below registers them server-side so REST responses
 * include the variation names.
 *
 * @param  array $variations Existing variations.
 * @return array
 */
function runpace_query_loop_variations( array $variations ): array {

	$runpace_variations = [

		[
			'name'        => 'runpace-upcoming-marathons',
			'title'       => __( 'Upcoming Marathons', 'runpace' ),
			'description' => __( 'A grid of upcoming marathon events ordered by race date.', 'runpace' ),
			'icon'        => 'location-alt',
			'category'    => 'runpace',
			'isActive'    => [ 'query.postType', 'query.orderBy' ],
			'attributes'  => [
				'query' => [
					'postType'  => 'marathon',
					'orderBy'   => 'meta_value',
					'order'     => 'ASC',
					'perPage'   => 6,
					'metaKey'   => '_runpace_race_date',
					'metaQuery' => [
						'relation' => 'AND',
						[
							'key'     => '_runpace_race_date',
							'value'   => gmdate( 'Y-m-d' ),
							'compare' => '>=',
							'type'    => 'DATE',
						],
					],
					'inherit'  => false,
				],
				'layout' => [
					'type'        => 'grid',
					'columnCount' => 3,
				],
			],
		],

		[
			'name'        => 'runpace-featured-marathons',
			'title'       => __( 'Featured Marathons', 'runpace' ),
			'description' => __( 'Shows marathons marked as featured.', 'runpace' ),
			'icon'        => 'awards',
			'category'    => 'runpace',
			'isActive'    => [ 'query.postType' ],
			'attributes'  => [
				'query' => [
					'postType'  => 'marathon',
					'orderBy'   => 'date',
					'order'     => 'DESC',
					'perPage'   => 3,
					'metaQuery' => [
						[
							'key'   => '_runpace_is_featured',
							'value' => '1',
						],
					],
					'inherit'  => false,
				],
				'layout' => [
					'type'        => 'grid',
					'columnCount' => 3,
				],
			],
		],

		[
			'name'        => 'runpace-training-plans',
			'title'       => __( 'Training Plans Grid', 'runpace' ),
			'description' => __( 'A grid of training plans ordered by duration.', 'runpace' ),
			'icon'        => 'clipboard',
			'category'    => 'runpace',
			'isActive'    => [ 'query.postType' ],
			'attributes'  => [
				'query' => [
					'postType'  => 'training-plan',
					'orderBy'   => 'meta_value_num',
					'order'     => 'ASC',
					'perPage'   => 6,
					'metaKey'   => '_runpace_duration_weeks',
					'inherit'   => false,
				],
				'layout' => [
					'type'        => 'grid',
					'columnCount' => 3,
				],
			],
		],

	];

	return array_merge( $variations, $runpace_variations );
}
add_filter( 'block_type_metadata_settings', static function ( array $settings, array $metadata ): array {
	if ( 'core/query' === ( $metadata['name'] ?? '' ) ) {
		add_filter( 'blocks.registerBlockType', 'runpace_query_loop_client_variations', 10, 2 );
	}
	return $settings;
}, 10, 2 );

/**
 * Enqueue the JS that registers Query Loop variations client-side.
 * This is the correct, supported method for block variations in WP 6.x.
 */
function runpace_enqueue_block_variations(): void {

	wp_register_script(
		'runpace-block-variations',
		RUNPACE_ASSETS . '/js/block-variations.js',
		[ 'wp-blocks', 'wp-dom-ready', 'wp-i18n' ],
		RUNPACE_VERSION,
		true
	);

	wp_enqueue_block_editor_assets( 'runpace-block-variations' );
}
add_action( 'enqueue_block_editor_assets', 'runpace_enqueue_block_variations' );