<?php
/**
 * Marathon Info Block – render.php
 *
 * Server-side rendering for the runpace/marathon-info block.
 * All data comes from post meta registered in inc/03-meta-fields.php.
 *
 * Variables injected by WordPress:
 *   $attributes  (array)    – block attributes
 *   $content     (string)   – inner blocks HTML (unused here)
 *   $block       (WP_Block) – block instance with ->context['postId']
 *
 * @package RunPace
 */

declare( strict_types=1 );

$post_id = $block->context['postId'] ?? get_the_ID();

if ( ! $post_id ) {
	return;
}

// ── Fetch meta ────────────────────────────────────────────────────────────────
$race_date         = get_post_meta( $post_id, '_runpace_race_date',       true );
$city              = get_post_meta( $post_id, '_runpace_city',            true );
$country           = get_post_meta( $post_id, '_runpace_country',         true );
$distance_label    = get_post_meta( $post_id, '_runpace_distance_label',  true );
$registration_url  = get_post_meta( $post_id, '_runpace_registration_url', true );
$price             = (float) get_post_meta( $post_id, '_runpace_price',   true );
$elevation_gain    = (int)   get_post_meta( $post_id, '_runpace_elevation_gain', true );
$difficulty        = (int)   get_post_meta( $post_id, '_runpace_difficulty_rating', true );
$distance_terms    = get_the_terms( $post_id, 'runpace_distance' );
$event_type_terms  = get_the_terms( $post_id, 'runpace_event_type' );

// ── Derived display values ────────────────────────────────────────────────────
$race_timestamp   = $race_date ? strtotime( $race_date ) : false;
$formatted_date   = $race_timestamp ? date_i18n( get_option( 'date_format' ), $race_timestamp ) : '';
$location_string  = implode( ', ', array_filter( [ $city, $country ] ) );
$price_display    = $price > 0 ? '$' . number_format( $price, 0 ) : __( 'Free', 'runpace' );
$distance_display = $distance_label ?: ( $distance_terms && ! is_wp_error( $distance_terms ) ? $distance_terms[0]->name : '' );
$event_type       = $event_type_terms && ! is_wp_error( $event_type_terms ) ? $event_type_terms[0]->name : '';

// ── Countdown ─────────────────────────────────────────────────────────────────
$days_until  = '';
$is_past     = false;
if ( $race_timestamp ) {
	$diff       = $race_timestamp - time();
	$is_past    = $diff < 0;
	$days_until = $is_past ? 0 : (int) ceil( $diff / DAY_IN_SECONDS );
}

// ── Block attributes ──────────────────────────────────────────────────────────
$layout               = $attributes['layout']             ?? 'grid';
$show_countdown       = $attributes['showCountdown']      ?? true;
$show_register_button = $attributes['showRegisterButton'] ?? true;
$register_label       = $attributes['registerLabel']      ?? __( 'Register Now', 'runpace' );

// ── Wrapper ───────────────────────────────────────────────────────────────────
$wrapper_attributes = get_block_wrapper_attributes(
	[
		'class'      => "runpace-marathon-info runpace-marathon-info--{$layout}" . ( $is_past ? ' is-past' : '' ),
		'data-post-id' => (string) $post_id,
	]
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $show_countdown && $race_timestamp && ! $is_past ) : ?>
	<div class="runpace-mi__countdown-badge">
		<span class="runpace-mi__countdown-number"><?php echo esc_html( (string) $days_until ); ?></span>
		<span class="runpace-mi__countdown-label"><?php esc_html_e( 'days to go', 'runpace' ); ?></span>
	</div>
	<?php elseif ( $is_past ) : ?>
	<div class="runpace-mi__past-badge"><?php esc_html_e( 'Past Event', 'runpace' ); ?></div>
	<?php endif; ?>

	<dl class="runpace-mi__grid">

		<?php if ( $formatted_date ) : ?>
		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">📅</span>
				<?php esc_html_e( 'Date', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value"><?php echo esc_html( $formatted_date ); ?></dd>
		</div>
		<?php endif; ?>

		<?php if ( $location_string ) : ?>
		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">📍</span>
				<?php esc_html_e( 'Location', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value"><?php echo esc_html( $location_string ); ?></dd>
		</div>
		<?php endif; ?>

		<?php if ( $distance_display ) : ?>
		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">🏃</span>
				<?php esc_html_e( 'Distance', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value"><?php echo esc_html( $distance_display ); ?></dd>
		</div>
		<?php endif; ?>

		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">💰</span>
				<?php esc_html_e( 'Entry fee', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value"><?php echo esc_html( $price_display ); ?></dd>
		</div>

		<?php if ( $elevation_gain ) : ?>
		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">⛰️</span>
				<?php esc_html_e( 'Elevation', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d = metres of elevation gain */
						__( '%dm gain', 'runpace' ),
						$elevation_gain
					)
				);
				?>
			</dd>
		</div>
		<?php endif; ?>

		<?php if ( $difficulty >= 1 ) : ?>
		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">⭐</span>
				<?php esc_html_e( 'Difficulty', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value runpace-mi__stars" aria-label="<?php echo esc_attr( sprintf( __( 'Difficulty: %d out of 5', 'runpace' ), $difficulty ) ); ?>">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<span class="runpace-mi__star<?php echo $i <= $difficulty ? ' is-filled' : ''; ?>" aria-hidden="true">
						<?php echo $i <= $difficulty ? '★' : '☆'; ?>
					</span>
				<?php endfor; ?>
			</dd>
		</div>
		<?php endif; ?>

		<?php if ( $event_type ) : ?>
		<div class="runpace-mi__item">
			<dt class="runpace-mi__label">
				<span class="runpace-mi__icon" aria-hidden="true">🏷️</span>
				<?php esc_html_e( 'Type', 'runpace' ); ?>
			</dt>
			<dd class="runpace-mi__value">
				<span class="runpace-mi__badge"><?php echo esc_html( $event_type ); ?></span>
			</dd>
		</div>
		<?php endif; ?>

	</dl>

	<?php if ( $show_register_button && $registration_url && ! $is_past ) : ?>
	<div class="runpace-mi__cta">
		<a
			href="<?php echo esc_url( $registration_url ); ?>"
			class="runpace-mi__register-btn wp-element-button"
			target="_blank"
			rel="noopener noreferrer"
		>
			<?php echo esc_html( $register_label ); ?>
			<span class="runpace-mi__btn-arrow" aria-hidden="true">→</span>
		</a>
	</div>
	<?php endif; ?>

</div>