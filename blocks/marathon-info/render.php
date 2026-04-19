<?php
/**
 * RunPace – Marathon Info Block
 *
 * Server-side render callback. Pulls post meta and taxonomy terms for the
 * current marathon post and outputs a structured info panel.
 *
 * Available variables (injected by WordPress):
 *   $attributes (array)  — Block attributes from block.json.
 *   $content    (string) — InnerBlocks content (unused here).
 *   $block      (object) — WP_Block instance; provides context.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

// ── Resolve post ID from block context (works inside Query Loop) ──────────────
$post_id = absint( $block->context['postId'] ?? get_the_ID() );
if ( ! $post_id ) {
	return;
}

// ── Validate post type ────────────────────────────────────────────────────────
if ( 'marathon' !== get_post_type( $post_id ) ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		echo '<p class="runpace-block-notice">' . esc_html__( 'Marathon Info block: assign this to a marathon post.', 'runpace' ) . '</p>';
	}
	return;
}

// ── Meta retrieval ────────────────────────────────────────────────────────────
$race_date         = get_post_meta( $post_id, '_runpace_race_date',         true );
$city              = get_post_meta( $post_id, '_runpace_city',              true );
$country           = get_post_meta( $post_id, '_runpace_country',           true );
$distance_label    = get_post_meta( $post_id, '_runpace_distance_label',    true );
$registration_url  = get_post_meta( $post_id, '_runpace_registration_url',  true );
$price             = get_post_meta( $post_id, '_runpace_price',             true );
$elevation_gain    = get_post_meta( $post_id, '_runpace_elevation_gain',    true );
$difficulty_rating = (int) get_post_meta( $post_id, '_runpace_difficulty_rating', true );

// ── Taxonomy terms for distance ───────────────────────────────────────────────
$distance_terms = get_the_terms( $post_id, 'runpace_distance' );
$distance_names = ( $distance_terms && ! is_wp_error( $distance_terms ) )
	? implode( ', ', wp_list_pluck( $distance_terms, 'name' ) )
	: $distance_label;

// ── Date formatting ───────────────────────────────────────────────────────────
$formatted_date = '';
$is_past        = false;
if ( $race_date ) {
	$timestamp      = strtotime( $race_date );
	$formatted_date = $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : esc_html( $race_date );
	$is_past        = $timestamp < time();

	// Days until race.
	$days_until = $timestamp ? (int) ceil( ( $timestamp - time() ) / DAY_IN_SECONDS ) : null;
}

// ── Price formatting ──────────────────────────────────────────────────────────
$price_display = $price
	? '$' . number_format( (float) $price, 0 )
	: __( 'Free', 'runpace' );

// ── Difficulty stars ──────────────────────────────────────────────────────────
$difficulty_html = '';
if ( $difficulty_rating >= 1 ) {
	$filled = min( 5, $difficulty_rating );
	for ( $i = 1; $i <= 5; $i++ ) {
		$star_class = $i <= $filled ? 'runpace-star runpace-star--filled' : 'runpace-star';
		$difficulty_html .= '<span class="' . esc_attr( $star_class ) . '" aria-hidden="true">★</span>';
	}
}

// ── Block attributes ──────────────────────────────────────────────────────────
$show_date        = ! empty( $attributes['showDate'] );
$show_location    = ! empty( $attributes['showLocation'] );
$show_distance    = ! empty( $attributes['showDistance'] );
$show_price       = ! empty( $attributes['showPrice'] );
$show_elevation   = ! empty( $attributes['showElevation'] );
$show_difficulty  = ! empty( $attributes['showDifficulty'] );
$show_register    = ! empty( $attributes['showRegisterBtn'] );
$register_label   = sanitize_text_field( $attributes['registerBtnLabel'] ?? __( 'Register Now', 'runpace' ) );
$layout           = in_array( $attributes['layout'] ?? 'card', [ 'card', 'inline', 'compact' ], true )
	? $attributes['layout']
	: 'card';

// ── Wrapper classes & attrs ───────────────────────────────────────────────────
$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class' => implode( ' ', array_filter( [
			'runpace-marathon-info',
			'runpace-marathon-info--' . $layout,
			$is_past ? 'runpace-marathon-info--past' : '',
		] ) ),
	]
);
?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $is_past ) : ?>
		<div class="runpace-marathon-info__badge runpace-marathon-info__badge--past">
			<?php esc_html_e( 'Past event', 'runpace' ); ?>
		</div>
	<?php elseif ( isset( $days_until ) && $days_until <= 30 && $days_until >= 0 ) : ?>
		<div class="runpace-marathon-info__badge runpace-marathon-info__badge--soon">
			<?php
			printf(
				/* translators: %d = number of days */
				esc_html( _n( '%d day to go', '%d days to go', $days_until, 'runpace' ) ),
				(int) $days_until
			);
			?>
		</div>
	<?php endif; ?>

	<dl class="runpace-marathon-info__grid">

		<?php if ( $show_date && $formatted_date ) : ?>
			<div class="runpace-marathon-info__item">
				<dt class="runpace-marathon-info__label">
					<span class="runpace-icon" aria-hidden="true">📅</span>
					<?php esc_html_e( 'Date', 'runpace' ); ?>
				</dt>
				<dd class="runpace-marathon-info__value">
					<time datetime="<?php echo esc_attr( $race_date ); ?>">
						<?php echo esc_html( $formatted_date ); ?>
					</time>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $show_location && ( $city || $country ) ) : ?>
			<div class="runpace-marathon-info__item">
				<dt class="runpace-marathon-info__label">
					<span class="runpace-icon" aria-hidden="true">📍</span>
					<?php esc_html_e( 'Location', 'runpace' ); ?>
				</dt>
				<dd class="runpace-marathon-info__value">
					<?php
					$parts = array_filter( [ $city, $country ] );
					echo esc_html( implode( ', ', $parts ) );
					?>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $show_distance && $distance_names ) : ?>
			<div class="runpace-marathon-info__item">
				<dt class="runpace-marathon-info__label">
					<span class="runpace-icon" aria-hidden="true">🏃</span>
					<?php esc_html_e( 'Distance', 'runpace' ); ?>
				</dt>
				<dd class="runpace-marathon-info__value runpace-marathon-info__value--distance">
					<?php echo esc_html( $distance_names ); ?>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $show_price ) : ?>
			<div class="runpace-marathon-info__item">
				<dt class="runpace-marathon-info__label">
					<span class="runpace-icon" aria-hidden="true">💳</span>
					<?php esc_html_e( 'Entry fee', 'runpace' ); ?>
				</dt>
				<dd class="runpace-marathon-info__value runpace-marathon-info__value--price">
					<?php echo esc_html( $price_display ); ?>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $show_elevation && $elevation_gain ) : ?>
			<div class="runpace-marathon-info__item">
				<dt class="runpace-marathon-info__label">
					<span class="runpace-icon" aria-hidden="true">⛰️</span>
					<?php esc_html_e( 'Elevation gain', 'runpace' ); ?>
				</dt>
				<dd class="runpace-marathon-info__value">
					<?php
					printf(
						/* translators: %s = metres value */
						esc_html__( '%sm', 'runpace' ),
						esc_html( number_format( (int) $elevation_gain ) )
					);
					?>
				</dd>
			</div>
		<?php endif; ?>

		<?php if ( $show_difficulty && $difficulty_html ) : ?>
			<div class="runpace-marathon-info__item">
				<dt class="runpace-marathon-info__label">
					<span class="runpace-icon" aria-hidden="true">⚡</span>
					<?php esc_html_e( 'Difficulty', 'runpace' ); ?>
				</dt>
				<dd class="runpace-marathon-info__value">
					<span class="runpace-difficulty" role="img"
						aria-label="<?php echo esc_attr( sprintf(
							/* translators: %d = difficulty rating 1–5 */
							__( 'Difficulty: %d out of 5', 'runpace' ),
							$difficulty_rating
						) ); ?>">
						<?php echo $difficulty_html; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</span>
				</dd>
			</div>
		<?php endif; ?>

	</dl>

	<?php if ( $show_register && $registration_url ) : ?>
		<div class="runpace-marathon-info__cta">
			<a
				href="<?php echo esc_url( $registration_url ); ?>"
				class="wp-block-button__link runpace-marathon-info__register-btn"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php echo esc_html( $register_label ); ?>
				<span class="runpace-btn-arrow" aria-hidden="true">→</span>
			</a>
		</div>
	<?php endif; ?>

</div>