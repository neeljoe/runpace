<?php
/**
 * RunPace – Training Plan Card Block
 *
 * Renders a styled card summarising a training plan post.
 * Works standalone on a single training-plan page and inside Query Loop.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

// ── Resolve post ID ───────────────────────────────────────────────────────────
$post_id = absint( $block->context['postId'] ?? get_the_ID() );
if ( ! $post_id ) {
	return;
}

if ( 'training-plan' !== get_post_type( $post_id ) ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		echo '<p class="runpace-block-notice">' . esc_html__( 'Training Plan Card: assign this to a training-plan post.', 'runpace' ) . '</p>';
	}
	return;
}

// ── Post data ─────────────────────────────────────────────────────────────────
$title        = get_the_title( $post_id );
$permalink    = get_permalink( $post_id );
$excerpt      = has_excerpt( $post_id )
	? get_the_excerpt( $post_id )
	: wp_trim_words( get_the_content( null, false, $post_id ), 18, '…' );

// ── Meta ──────────────────────────────────────────────────────────────────────
$duration_weeks    = (int) get_post_meta( $post_id, '_runpace_duration_weeks',    true );
$peak_weekly_km    = (float) get_post_meta( $post_id, '_runpace_peak_weekly_km',  true );
$sessions_per_week = (int) get_post_meta( $post_id, '_runpace_sessions_per_week', true );
$level_label       = get_post_meta( $post_id, '_runpace_level_label',             true );
$goal_label        = get_post_meta( $post_id, '_runpace_goal_label',              true );
$is_free           = (bool) get_post_meta( $post_id, '_runpace_is_free',          true );
$download_url      = get_post_meta( $post_id, '_runpace_download_url',            true );

// ── Taxonomy fallbacks ────────────────────────────────────────────────────────
$difficulty_terms = get_the_terms( $post_id, 'runpace_difficulty' );
$difficulty_label = ( $difficulty_terms && ! is_wp_error( $difficulty_terms ) )
	? $difficulty_terms[0]->name
	: $level_label;

$goal_terms = get_the_terms( $post_id, 'runpace_goal' );
$goal_str   = ( $goal_terms && ! is_wp_error( $goal_terms ) )
	? implode( ', ', wp_list_pluck( $goal_terms, 'name' ) )
	: $goal_label;

// ── Level → visual class map ──────────────────────────────────────────────────
$level_class_map = [
	'Beginner'     => 'runpace-level--beginner',
	'Intermediate' => 'runpace-level--intermediate',
	'Advanced'     => 'runpace-level--advanced',
];
$level_class = $level_class_map[ $difficulty_label ] ?? '';

// ── Featured image ────────────────────────────────────────────────────────────
$image_id  = get_post_thumbnail_id( $post_id );
$image_url = $image_id
	? wp_get_attachment_image_url( $image_id, 'runpace-card' )
	: '';

// ── Block attributes ──────────────────────────────────────────────────────────
$show_duration  = ! empty( $attributes['showDuration'] );
$show_level     = ! empty( $attributes['showLevel'] );
$show_goal      = ! empty( $attributes['showGoal'] );
$show_sessions  = ! empty( $attributes['showSessions'] );
$show_peak_km   = ! empty( $attributes['showPeakKm'] );
$show_free_badge= ! empty( $attributes['showFreeBadge'] );
$show_download  = ! empty( $attributes['showDownload'] );
$cta_label      = sanitize_text_field( $attributes['ctaLabel']       ?? __( 'View Plan',     'runpace' ) );
$download_label = sanitize_text_field( $attributes['downloadLabel']  ?? __( 'Download PDF',  'runpace' ) );
$color_scheme   = in_array( $attributes['colorScheme'] ?? 'auto', [ 'auto', 'light', 'dark', 'accent' ], true )
	? $attributes['colorScheme']
	: 'auto';

// ── Wrapper ───────────────────────────────────────────────────────────────────
$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', array_filter( [
			'runpace-training-plan-card',
			"runpace-training-plan-card--{$color_scheme}",
			$image_url ? '' : 'runpace-training-plan-card--no-thumb',
		] ) ),
	]
);
?>
<article <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<?php if ( $image_url ) : ?>
		<a href="<?php echo esc_url( $permalink ); ?>" class="runpace-training-plan-card__thumb-link" tabindex="-1" aria-hidden="true">
			<div class="runpace-training-plan-card__thumb">
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'runpace-card',
					false,
					[
						'class'   => 'runpace-training-plan-card__thumb-img',
						'loading' => 'lazy',
						'alt'     => '',
					]
				);
				?>
			</div>
		</a>
	<?php endif; ?>

	<div class="runpace-training-plan-card__body">

		<header class="runpace-training-plan-card__header">

			<div class="runpace-training-plan-card__badges">
				<?php if ( $show_level && $difficulty_label ) : ?>
					<span class="runpace-level-badge <?php echo esc_attr( $level_class ); ?>">
						<?php echo esc_html( $difficulty_label ); ?>
					</span>
				<?php endif; ?>

				<?php if ( $show_free_badge ) : ?>
					<span class="runpace-price-badge <?php echo $is_free ? 'runpace-price-badge--free' : 'runpace-price-badge--paid'; ?>">
						<?php echo $is_free ? esc_html__( 'Free', 'runpace' ) : esc_html__( 'Premium', 'runpace' ); ?>
					</span>
				<?php endif; ?>
			</div>

			<h3 class="runpace-training-plan-card__title">
				<a href="<?php echo esc_url( $permalink ); ?>">
					<?php echo esc_html( $title ); ?>
				</a>
			</h3>

			<?php if ( $show_goal && $goal_str ) : ?>
				<p class="runpace-training-plan-card__goal">
					<span aria-hidden="true">🎯</span>
					<?php echo esc_html( $goal_str ); ?>
				</p>
			<?php endif; ?>

		</header>

		<?php if ( $excerpt ) : ?>
			<p class="runpace-training-plan-card__excerpt">
				<?php echo esc_html( $excerpt ); ?>
			</p>
		<?php endif; ?>

		<!-- Stats grid -->
		<dl class="runpace-training-plan-card__stats">

			<?php if ( $show_duration && $duration_weeks ) : ?>
				<div class="runpace-training-plan-card__stat">
					<dd class="runpace-training-plan-card__stat-value"><?php echo esc_html( (string) $duration_weeks ); ?></dd>
					<dt class="runpace-training-plan-card__stat-label"><?php echo esc_html( _n( 'week', 'weeks', $duration_weeks, 'runpace' ) ); ?></dt>
				</div>
			<?php endif; ?>

			<?php if ( $show_sessions && $sessions_per_week ) : ?>
				<div class="runpace-training-plan-card__stat">
					<dd class="runpace-training-plan-card__stat-value"><?php echo esc_html( (string) $sessions_per_week ); ?>×</dd>
					<dt class="runpace-training-plan-card__stat-label"><?php esc_html_e( 'per week', 'runpace' ); ?></dt>
				</div>
			<?php endif; ?>

			<?php if ( $show_peak_km && $peak_weekly_km ) : ?>
				<div class="runpace-training-plan-card__stat">
					<dd class="runpace-training-plan-card__stat-value"><?php echo esc_html( number_format( $peak_weekly_km, 0 ) ); ?></dd>
					<dt class="runpace-training-plan-card__stat-label"><?php esc_html_e( 'peak km/wk', 'runpace' ); ?></dt>
				</div>
			<?php endif; ?>

		</dl>

		<div class="runpace-training-plan-card__actions">
			<a
				href="<?php echo esc_url( $permalink ); ?>"
				class="runpace-training-plan-card__cta"
			>
				<?php echo esc_html( $cta_label ); ?>
			</a>

			<?php if ( $show_download && $download_url ) : ?>
				<a
					href="<?php echo esc_url( $download_url ); ?>"
					class="runpace-training-plan-card__download"
					target="_blank"
					rel="noopener noreferrer"
					download
				>
					<span aria-hidden="true">⬇</span>
					<?php echo esc_html( $download_label ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>

</article>