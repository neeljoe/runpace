<?php
/**
 * RunPace – Stats Highlight Block
 *
 * Server-renders the stats strip and seeds the Interactivity API state
 * for the scroll-triggered count-up animation.
 *
 * @package RunPace
 * @since   1.0.0
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Inner blocks HTML (unused – no inner blocks).
 * @var WP_Block $block      Block instance.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Attribute extraction ──────────────────────────────────────────────────────

$stats             = $attributes['stats']             ?? [];
$animation_enabled = $attributes['animationEnabled']  ?? true;
$columns           = max( 1, min( 6, (int) ( $attributes['columns'] ?? 3 ) ) );

if ( empty( $stats ) ) {
	return;
}

// ── Prepare stats for Interactivity API state ─────────────────────────────────

/**
 * Parse a formatted value string into its numeric base for animation.
 * "10,000" → 10000  |  "42" → 42  |  "3.5" → 3.5
 */
$parsed_stats = array_map(
	static function ( array $stat ): array {
		$raw     = isset( $stat['value'] ) ? (string) $stat['value'] : '0';
		$numeric = (float) str_replace( [ ',', ' ' ], '', $raw );
		return [
			'value'          => $stat['value']  ?? '',
			'suffix'         => $stat['suffix'] ?? '',
			'label'          => $stat['label']  ?? '',
			'numericTarget'  => $numeric,
			'displayValue'   => $stat['value']  ?? '0', // initial = final (SSR)
			'animated'       => false,
		];
	},
	$stats
);

// Seed server state — JS will pick this up on hydration.
wp_interactivity_state(
	'runpace/stats-highlight',
	[
		'stats'            => $parsed_stats,
		'animationEnabled' => $animation_enabled,
		'hasAnimated'      => false,
	]
);

// ── Wrapper attributes ────────────────────────────────────────────────────────

$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class'               => 'runpace-stats',
		'data-wp-interactive' => 'runpace/stats-highlight',
		'data-wp-init'        => 'callbacks.onInit',
		'data-columns'        => (string) $columns,
	]
);

?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<ul class="runpace-stats__grid" style="--stats-columns:<?php echo esc_attr( (string) $columns ); ?>">

		<?php foreach ( $parsed_stats as $index => $stat ) :
			$context = wp_interactivity_data_wp_context( [ 'index' => $index ] );
		?>
		<li class="runpace-stats__item"
			<?php echo $context; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			data-wp-class--is-animated="context.index >= 0">

			<span class="runpace-stats__number" aria-hidden="true">
				<span class="runpace-stats__value"
					data-wp-text="state.stats[context.index].displayValue"><?php
						echo esc_html( $stat['value'] );
					?></span><?php
					if ( ! empty( $stat['suffix'] ) ) :
					?><span class="runpace-stats__suffix"><?php echo esc_html( $stat['suffix'] ); ?></span><?php
					endif;
			?></span>

			<?php if ( ! empty( $stat['label'] ) ) : ?>
			<span class="runpace-stats__label"><?php echo esc_html( $stat['label'] ); ?></span>
			<?php endif; ?>

			<?php
			// Visually hidden but accessible number for screen readers.
			printf(
				'<span class="screen-reader-text">%s%s — %s</span>',
				esc_html( $stat['value'] ),
				esc_html( $stat['suffix'] ),
				esc_html( $stat['label'] )
			);
			?>
		</li>
		<?php endforeach; ?>

	</ul>

</div>