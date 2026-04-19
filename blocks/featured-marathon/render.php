<?php
/**
 * RunPace – Featured Marathon Block
 *
 * Renders a prominent hero-style card for a single featured marathon.
 * Pulls from either:
 *   1. An explicitly selected post ID (editor-chosen post).
 *   2. Block context (when nested inside a Query Loop).
 *   3. Automatic query for the most recent _runpace_is_featured marathon.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

// ── Resolve post ID ───────────────────────────────────────────────────────────
$post_id = 0;

// Priority 1: explicitly chosen in the editor.
if ( ! empty( $attributes['postId'] ) ) {
	$post_id = absint( $attributes['postId'] );
}

// Priority 2: Query Loop context.
if ( ! $post_id && ! empty( $block->context['postId'] ) ) {
	$post_id = absint( $block->context['postId'] );
}

// Priority 3: auto-query for a featured marathon.
if ( ! $post_id ) {
	$featured_query = new WP_Query( [
		'post_type'      => 'marathon',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery
			[
				'key'   => '_runpace_is_featured',
				'value' => '1',
			],
		],
		'orderby'        => 'meta_value',
		'meta_key'       => '_runpace_race_date', // phpcs:ignore WordPress.DB.SlowDBQuery
		'order'          => 'ASC',
		'no_found_rows'  => true,
	] );

	if ( $featured_query->have_posts() ) {
		$post_id = (int) $featured_query->posts[0]->ID;
	}
}

if ( ! $post_id || 'marathon' !== get_post_type( $post_id ) ) {
	// Editor placeholder only — not shown on front-end.
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		echo '<div class="runpace-featured-marathon runpace-featured-marathon--placeholder">';
		echo '<p>' . esc_html__( 'Featured Marathon: no featured marathon found. Mark a marathon as featured or use the Post Picker in the sidebar.', 'runpace' ) . '</p>';
		echo '</div>';
	}
	return;
}

// ── Post data ─────────────────────────────────────────────────────────────────
$post         = get_post( $post_id );
$title        = get_the_title( $post_id );
$permalink    = get_permalink( $post_id );
$excerpt      = has_excerpt( $post_id )
	? get_the_excerpt( $post_id )
	: wp_trim_words( get_the_content( null, false, $post_id ), 25, '…' );

// ── Meta ──────────────────────────────────────────────────────────────────────
$race_date        = get_post_meta( $post_id, '_runpace_race_date',         true );
$city             = get_post_meta( $post_id, '_runpace_city',              true );
$country          = get_post_meta( $post_id, '_runpace_country',           true );
$registration_url = get_post_meta( $post_id, '_runpace_registration_url',  true );
$price            = get_post_meta( $post_id, '_runpace_price',             true );

// ── Distance taxonomy ─────────────────────────────────────────────────────────
$distance_terms = get_the_terms( $post_id, 'runpace_distance' );
$distance_label = '';
if ( $distance_terms && ! is_wp_error( $distance_terms ) ) {
	$distance_label = implode( ' · ', wp_list_pluck( $distance_terms, 'name' ) );
}
if ( ! $distance_label ) {
	$distance_label = get_post_meta( $post_id, '_runpace_distance_label', true );
}

// ── Date / countdown ─────────────────────────────────────────────────────────
$timestamp      = $race_date ? strtotime( $race_date ) : 0;
$formatted_date = $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : '';
$is_past        = $timestamp && $timestamp < time();
$days_until     = $timestamp && ! $is_past ? (int) ceil( ( $timestamp - time() ) / DAY_IN_SECONDS ) : null;

// ── Featured image ────────────────────────────────────────────────────────────
$image_id  = get_post_thumbnail_id( $post_id );
$image_url = $image_id
	? wp_get_attachment_image_url( $image_id, 'runpace-hero' )
	: '';

// ── Block attributes ──────────────────────────────────────────────────────────
$show_countdown    = ! empty( $attributes['showCountdown'] );
$show_badge        = ! empty( $attributes['showDistanceBadge'] );
$show_excerpt      = ! empty( $attributes['showExcerpt'] );
$show_meta         = ! empty( $attributes['showMeta'] );
$cta_label         = sanitize_text_field( $attributes['ctaLabel']       ?? __( 'Register Now', 'runpace' ) );
$learn_label       = sanitize_text_field( $attributes['learnMoreLabel'] ?? __( 'Learn More',   'runpace' ) );
$overlay_opacity   = min( 100, max( 0, (int) ( $attributes['overlayOpacity'] ?? 55 ) ) );

// ── Location string ───────────────────────────────────────────────────────────
$location_parts = array_filter( [ $city, $country ] );
$location_str   = implode( ', ', $location_parts );

// ── Wrapper ───────────────────────────────────────────────────────────────────
$wrapper_attrs = get_block_wrapper_attributes(
	[
		'class'              => implode( ' ', array_filter( [
			'runpace-featured-marathon',
			$is_past ? 'runpace-featured-marathon--past' : 'runpace-featured-marathon--upcoming',
			$image_url ? '' : 'runpace-featured-marathon--no-image',
		] ) ),
		'style'              => $image_url
			? '--runpace-hero-image: url(' . esc_url( $image_url ) . '); --runpace-overlay-opacity: ' . ( $overlay_opacity / 100 ) . ';'
			: '--runpace-overlay-opacity: ' . ( $overlay_opacity / 100 ) . ';',
		'aria-labelledby'    => 'rp-featured-' . $post_id,
	]
);
?>
<article <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>

	<?php if ( $image_url ) : ?>
		<div class="runpace-featured-marathon__backdrop" aria-hidden="true"></div>
	<?php else : ?>
		<div class="runpace-featured-marathon__backdrop runpace-featured-marathon__backdrop--gradient" aria-hidden="true"></div>
	<?php endif; ?>

	<div class="runpace-featured-marathon__content">

		<header class="runpace-featured-marathon__header">

			<?php if ( $show_badge && $distance_label ) : ?>
				<div class="runpace-featured-marathon__distance-badge">
					<?php echo esc_html( $distance_label ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $is_past ) : ?>
				<div class="runpace-featured-marathon__status runpace-featured-marathon__status--past">
					<?php esc_html_e( 'Past event', 'runpace' ); ?>
				</div>
			<?php endif; ?>

			<h2
				id="rp-featured-<?php echo esc_attr( (string) $post_id ); ?>"
				class="runpace-featured-marathon__title"
			>
				<?php echo esc_html( $title ); ?>
			</h2>

			<?php if ( $location_str ) : ?>
				<p class="runpace-featured-marathon__location">
					<span aria-hidden="true">📍</span>
					<?php echo esc_html( $location_str ); ?>
				</p>
			<?php endif; ?>

		</header>

		<?php if ( $show_excerpt && $excerpt ) : ?>
			<p class="runpace-featured-marathon__excerpt">
				<?php echo esc_html( $excerpt ); ?>
			</p>
		<?php endif; ?>

		<?php if ( $show_meta ) : ?>
			<div class="runpace-featured-marathon__meta">

				<?php if ( $formatted_date ) : ?>
					<div class="runpace-featured-marathon__meta-item">
						<span class="runpace-featured-marathon__meta-icon" aria-hidden="true">📅</span>
						<time
							class="runpace-featured-marathon__meta-value"
							datetime="<?php echo esc_attr( $race_date ); ?>"
						>
							<?php echo esc_html( $formatted_date ); ?>
						</time>
					</div>
				<?php endif; ?>

				<?php if ( $price ) : ?>
					<div class="runpace-featured-marathon__meta-item">
						<span class="runpace-featured-marathon__meta-icon" aria-hidden="true">💳</span>
						<span class="runpace-featured-marathon__meta-value">
							<?php echo esc_html( '$' . number_format( (float) $price, 0 ) ); ?>
						</span>
					</div>
				<?php endif; ?>

			</div>
		<?php endif; ?>

		<?php if ( $show_countdown && null !== $days_until ) : ?>
			<div class="runpace-featured-marathon__countdown" aria-label="<?php
				echo esc_attr( sprintf(
					/* translators: %d = days */
					__( '%d days until the race', 'runpace' ),
					$days_until
				) );
			?>">
				<span class="runpace-featured-marathon__countdown-number">
					<?php echo esc_html( number_format( $days_until ) ); ?>
				</span>
				<span class="runpace-featured-marathon__countdown-label">
					<?php echo esc_html( _n( 'day to go', 'days to go', $days_until, 'runpace' ) ); ?>
				</span>
			</div>
		<?php endif; ?>

		<div class="runpace-featured-marathon__actions">

			<?php if ( $registration_url && ! $is_past ) : ?>
				<a
					href="<?php echo esc_url( $registration_url ); ?>"
					class="runpace-featured-marathon__cta runpace-featured-marathon__cta--primary"
					target="_blank"
					rel="noopener noreferrer"
				>
					<?php echo esc_html( $cta_label ); ?>
					<span class="runpace-btn-arrow" aria-hidden="true">→</span>
				</a>
			<?php endif; ?>

			<a
				href="<?php echo esc_url( $permalink ); ?>"
				class="runpace-featured-marathon__cta runpace-featured-marathon__cta--secondary"
			>
				<?php echo esc_html( $learn_label ); ?>
			</a>

		</div>

	</div>

</article>