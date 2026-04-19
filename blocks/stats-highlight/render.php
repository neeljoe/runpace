<?php
/**
 * RunPace – Stats Highlight Block
 *
 * Renders a row/grid of bold numeric stats with labels and optional icons.
 * Supports four visual themes (light, dark, brand, transparent).
 * Uses Intersection Observer via a tiny viewScript for count-up animation.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

// ── Attributes ────────────────────────────────────────────────────────────────
$stats         = $attributes['stats']        ?? [];
$layout        = in_array( $attributes['layout'] ?? 'grid', [ 'grid', 'row', 'stacked' ], true )
	? $attributes['layout']
	: 'grid';
$theme         = in_array( $attributes['theme'] ?? 'dark', [ 'light', 'dark', 'brand', 'transparent' ], true )
	? $attributes['theme']
	: 'dark';
$text_align    = in_array( $attributes['textAlign'] ?? 'center', [ 'left', 'center', 'right' ], true )
	? $attributes['textAlign']
	: 'center';
$show_dividers = ! empty( $attributes['showDividers'] );

// Sanitise stats.
$clean_stats = [];
foreach ( (array) $stats as $stat ) {
	$clean_stats[] = [
		'value' => sanitize_text_field( $stat['value'] ?? '' ),
		'label' => sanitize_text_field( $stat['label'] ?? '' ),
		'icon'  => sanitize_text_field( $stat['icon']  ?? '' ),
	];
}

if ( empty( $clean_stats ) ) {
	return;
}

// ── Wrapper ───────────────────────────────────────────────────────────────────
$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class'            => implode( ' ', array_filter( [
			'runpace-stats-highlight',
			"runpace-stats-highlight--{$theme}",
			"runpace-stats-highlight--{$layout}",
			"runpace-stats-highlight--align-{$text_align}",
			$show_dividers ? 'runpace-stats-highlight--dividers' : '',
		] ) ),
		'data-wp-interactive' => 'runpace/stats-highlight',
	]
);

$count = count( $clean_stats );
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<ul
		class="runpace-stats-highlight__list"
		style="--runpace-stat-count: <?php echo esc_attr( (string) $count ); ?>;"
		role="list"
	>
		<?php foreach ( $clean_stats as $index => $stat ) :
			if ( ! $stat['value'] ) continue;
		?>
			<li
				class="runpace-stats-highlight__item"
				data-wp-init="callbacks.initItem"
				data-stat-value="<?php echo esc_attr( $stat['value'] ); ?>"
			>
				<?php if ( $stat['icon'] ) : ?>
					<span class="runpace-stats-highlight__icon" aria-hidden="true">
						<?php echo esc_html( $stat['icon'] ); ?>
					</span>
				<?php endif; ?>

				<strong
					class="runpace-stats-highlight__value"
					data-wp-text="state.stats[<?php echo esc_attr( (string) $index ); ?>].display"
				>
					<?php echo esc_html( $stat['value'] ); ?>
				</strong>

				<?php if ( $stat['label'] ) : ?>
					<span class="runpace-stats-highlight__label">
						<?php echo esc_html( $stat['label'] ); ?>
					</span>
				<?php endif; ?>

			</li>
		<?php endforeach; ?>
	</ul>

</div>