<?php
/**
 * RunPace – Block Registration
 *
 * Registers all custom blocks from the blocks/ directory.
 * Each block is discovered via its block.json file.
 *
 * Also registers the Block Bindings source so core blocks (Paragraph,
 * Heading, Image) can be bound directly to RunPace post meta.
 *
 * NOTE: The runpace-block-variations editor script is registered exclusively
 * in functions.php → runpace_enqueue_editor_scripts(). It must NOT be
 * re-registered here — duplicate handle registration causes one copy to be
 * silently dropped and may enqueue a non-existent file pre-build.
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

// ─── Block Bindings Source Registration ───────────────────────────────────────

/**
 * Register custom block bindings sources for RunPace meta fields.
 *
 * This allows editors to bind core blocks (Paragraph, Heading, Image)
 * directly to post meta via the Block Bindings API without custom render.php.
 *
 * Available keys match the meta fields registered in inc/03-meta-fields.php.
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