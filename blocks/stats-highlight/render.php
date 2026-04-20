<?php
/**
 * Stats Highlight Block – render.php
 *
 * @package RunPace
 */

declare( strict_types=1 );

$stats           = $attributes['stats']           ?? [];
$theme           = $attributes['theme']           ?? 'dark';
$animate         = $attributes['animateOnScroll'] ?? true;

if ( empty( $stats ) ) {
	return;
}

// Prime Interactivity state for scroll-reveal.
if ( $animate ) {
	wp_interactivity_state(
		'runpace/stats-highlight',
		[ 'revealed' => false ]
	);
}

$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class'                 => "runpace-stats runpace-stats--{$theme}",
		'data-wp-interactive'   => $animate ? 'runpace/stats-highlight' : null,
		'data-wp-class--is-revealed' => $animate ? 'state.revealed' : null,
	]
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $animate ) : ?>
	<div
		class="runpace-stats__observer-target"
		data-wp-on--intersect="callbacks.onIntersect"
		aria-hidden="true"
	></div>
	<?php endif; ?>

	<ul class="runpace-stats__list" role="list">
		<?php foreach ( $stats as $index => $stat ) :
			$value = $stat['value'] ?? '';
			$label = $stat['label'] ?? '';
			$icon  = $stat['icon']  ?? '';
			if ( ! $value && ! $label ) continue;
		?>
		<li
			class="runpace-stats__item"
			style="--item-index: <?php echo esc_attr( (string) $index ); ?>;"
		>
			<?php if ( $icon ) : ?>
			<span class="runpace-stats__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
			<?php endif; ?>
			<span class="runpace-stats__value"><?php echo esc_html( $value ); ?></span>
			<span class="runpace-stats__label"><?php echo esc_html( $label ); ?></span>
		</li>
		<?php endforeach; ?>
	</ul>

</div>