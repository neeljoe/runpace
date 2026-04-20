<?php
/**
 * Training Plan Card Block – render.php
 *
 * @package RunPace
 */

declare( strict_types=1 );

$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id ) return;

$weeks        = (int)   get_post_meta( $post_id, '_runpace_duration_weeks',   true );
$peak_km      = (float) get_post_meta( $post_id, '_runpace_peak_weekly_km',   true );
$sessions     = (int)   get_post_meta( $post_id, '_runpace_sessions_per_week', true );
$level_label  = get_post_meta( $post_id, '_runpace_level_label',  true );
$goal_label   = get_post_meta( $post_id, '_runpace_goal_label',   true );
$is_free      = (bool)  get_post_meta( $post_id, '_runpace_is_free',           true );
$download_url = get_post_meta( $post_id, '_runpace_download_url', true );

$diff_terms   = get_the_terms( $post_id, 'runpace_difficulty' );
$goal_terms   = get_the_terms( $post_id, 'runpace_goal' );
$level_name   = ( $diff_terms && ! is_wp_error( $diff_terms ) ) ? $diff_terms[0]->name : ( $level_label ?: '' );
$goal_name    = ( $goal_terms && ! is_wp_error( $goal_terms ) ) ? $goal_terms[0]->name : ( $goal_label ?: '' );
$thumb_url    = get_the_post_thumbnail_url( $post_id, 'runpace-card' );
$permalink    = get_permalink( $post_id );

$color_scheme = $attributes['colorScheme'] ?? 'default';
$show_dl      = $attributes['showDownload'] ?? true;
$show_stats   = $attributes['showStats']    ?? true;

$level_classes = [
	'Beginner'     => 'is-beginner',
	'Intermediate' => 'is-intermediate',
	'Advanced'     => 'is-advanced',
];
$level_class = $level_classes[ $level_name ] ?? '';

$wrapper_attributes = get_block_wrapper_attributes(
	[ 'class' => "runpace-training-card runpace-training-card--{$color_scheme} {$level_class}" ]
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $thumb_url ) : ?>
	<a href="<?php echo esc_url( $permalink ); ?>" class="runpace-tc__img-wrap" tabindex="-1" aria-hidden="true">
		<img
			src="<?php echo esc_url( $thumb_url ); ?>"
			alt=""
			class="runpace-tc__img"
			loading="lazy"
			decoding="async"
		/>
	</a>
	<?php endif; ?>

	<div class="runpace-tc__body">

		<div class="runpace-tc__badges">
			<?php if ( $level_name ) : ?>
			<span class="runpace-tc__level-badge"><?php echo esc_html( $level_name ); ?></span>
			<?php endif; ?>
			<span class="runpace-tc__price-badge<?php echo $is_free ? ' is-free' : ' is-paid'; ?>">
				<?php echo $is_free ? esc_html__( 'Free', 'runpace' ) : esc_html__( 'Paid', 'runpace' ); ?>
			</span>
		</div>

		<h3 class="runpace-tc__title">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			</a>
		</h3>

		<?php if ( $goal_name ) : ?>
		<p class="runpace-tc__goal">
			<span aria-hidden="true">🎯</span>
			<?php echo esc_html( $goal_name ); ?>
		</p>
		<?php endif; ?>

		<?php if ( $show_stats ) : ?>
		<dl class="runpace-tc__stats">

			<?php if ( $weeks ) : ?>
			<div class="runpace-tc__stat">
				<dt><?php esc_html_e( 'Duration', 'runpace' ); ?></dt>
				<dd>
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d = number of weeks */
							_n( '%d week', '%d weeks', $weeks, 'runpace' ),
							$weeks
						)
					);
					?>
				</dd>
			</div>
			<?php endif; ?>

			<?php if ( $sessions ) : ?>
			<div class="runpace-tc__stat">
				<dt><?php esc_html_e( 'Sessions/wk', 'runpace' ); ?></dt>
				<dd><?php echo esc_html( (string) $sessions ); ?></dd>
			</div>
			<?php endif; ?>

			<?php if ( $peak_km > 0 ) : ?>
			<div class="runpace-tc__stat">
				<dt><?php esc_html_e( 'Peak km/wk', 'runpace' ); ?></dt>
				<dd><?php echo esc_html( number_format( $peak_km, 0 ) ); ?></dd>
			</div>
			<?php endif; ?>

		</dl>
		<?php endif; ?>

		<?php if ( $show_dl && $download_url && $is_free ) : ?>
		<a
			href="<?php echo esc_url( $download_url ); ?>"
			class="runpace-tc__download wp-element-button"
			download
		>
			<span aria-hidden="true">⬇</span>
			<?php esc_html_e( 'Download PDF', 'runpace' ); ?>
		</a>
		<?php elseif ( ! $is_free ) : ?>
		<a
			href="<?php echo esc_url( $permalink ); ?>"
			class="runpace-tc__download wp-element-button"
		>
			<?php esc_html_e( 'View plan', 'runpace' ); ?>
		</a>
		<?php endif; ?>

	</div>
</div>