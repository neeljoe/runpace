<?php
/**
 * RunPace – Block Registration
 *
 * Registers all custom blocks from the blocks/ directory.
 * Each block is discovered via its block.json file.
 *
 * Also registers Query Loop variations on the server so they
 * appear in the editor without a client-side build step.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Register Custom Blocks ───────────────────────────────────────────────────

/**
 * Register all blocks in the /blocks directory.
 * Each sub-directory must contain a valid block.json.
 */
function runpace_register_blocks(): void {

	$blocks_dir = RUNPACE_DIR . '/blocks';
	$block_dirs = glob( $blocks_dir . '/*/block.json' );

	if ( ! $block_dirs ) {
		return;
	}

	foreach ( $block_dirs as $block_json ) {
		register_block_type( dirname( $block_json ) );
	}
}
add_action( 'init', 'runpace_register_blocks' );

// ─── Query Loop Variations ────────────────────────────────────────────────────

/**
 * Enqueue the block variations script in the block editor.
 * This registers client-side Query Loop variations for the inserter.
 */
function runpace_enqueue_editor_assets(): void {

	$asset_file = RUNPACE_DIR . '/assets/js/block-variations.asset.php';
	$version    = file_exists( $asset_file )
		? require $asset_file
		: [ 'dependencies' => [ 'wp-blocks', 'wp-element', 'wp-i18n' ], 'version' => RUNPACE_VERSION ];

	wp_enqueue_script(
		'runpace-block-variations',
		RUNPACE_ASSETS . '/js/block-variations.js',
		$version['dependencies'] ?? [ 'wp-blocks', 'wp-element', 'wp-i18n' ],
		$version['version'] ?? RUNPACE_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'runpace_enqueue_editor_assets' );

// ─── Interactivity API: Marathon Filter State ─────────────────────────────────

/**
 * Prime the server-side state for the marathon filter block.
 * Called during the render phase so wp_interactivity_state() is available.
 */
function runpace_prime_filter_state(): void {

	// Fetch all published marathons with their key meta for the JS store.
	$marathons = get_posts(
		[
			'post_type'      => 'marathon',
			'post_status'    => 'publish',
			'posts_per_page' => 100,
			'orderby'        => 'meta_value',
			'meta_key'       => '_runpace_race_date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		]
	);

	$items = [];
	foreach ( $marathons as $id ) {
		$items[] = [
			'id'       => $id,
			'date'     => get_post_meta( $id, '_runpace_race_date', true ),
			'city'     => get_post_meta( $id, '_runpace_city', true ),
			'country'  => get_post_meta( $id, '_runpace_country', true ),
			'price'    => (float) get_post_meta( $id, '_runpace_price', true ),
			'distance' => wp_get_post_terms( $id, 'runpace_distance', [ 'fields' => 'names' ] ),
		];
	}

	wp_interactivity_state(
		'runpace/marathon-filter',
		[
			'allMarathons'     => $items,
			'activeDistance'   => '',
			'activeLocation'   => '',
			'activeDateFilter' => 'upcoming',
			'viewMode'         => 'grid',
			'visibleCount'     => 9,
			'pageSize'         => 9,
		]
	);
}

// ─── Block Bindings Source Registration ───────────────────────────────────────

/**
 * Register custom block bindings sources for RunPace meta fields.
 *
 * This allows editors to bind core blocks (Paragraph, Heading, Image)
 * directly to post meta via the Block Bindings API without custom render.php.
 */
function runpace_register_bindings(): void {

	if ( ! function_exists( 'register_block_bindings_source' ) ) {
		return; // WordPress < 6.5 fallback.
	}

	register_block_bindings_source(
		'runpace/meta',
		[
			'label'              => __( 'RunPace Meta', 'runpace' ),
			'get_value_callback' => static function ( array $source_args, \WP_Block $block ): string {
				$post_id = $block->context['postId'] ?? get_the_ID();
				$key     = $source_args['key'] ?? '';

				if ( ! $post_id || ! $key ) {
					return '';
				}

				$value = get_post_meta( $post_id, $key, true );

				// Format specific fields for display.
				if ( '_runpace_price' === $key ) {
					return $value ? '$' . number_format( (float) $value, 0 ) : __( 'Free', 'runpace' );
				}

				if ( '_runpace_race_date' === $key ) {
					$ts = strtotime( (string) $value );
					return $ts ? date_i18n( get_option( 'date_format' ), $ts ) : (string) $value;
				}

				if ( '_runpace_duration_weeks' === $key ) {
					$weeks = (int) $value;
					/* translators: %d = number of weeks */
					return $weeks ? sprintf( _n( '%d week', '%d weeks', $weeks, 'runpace' ), $weeks ) : '';
				}

				return esc_html( (string) $value );
			},
			'uses_context'       => [ 'postId', 'postType' ],
		]
	);
}
add_action( 'init', 'runpace_register_bindings' );