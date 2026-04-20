<?php
/**
 * Featured Marathon Block – render.php
 *
 * @package RunPace
 */

declare( strict_types=1 );

$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id ) return;

$race_date        = get_post_meta( $post_id, '_runpace_race_date', true );
$city             = get_post_meta( $post_id, '_runpace_city', true );
$country          = get_post_meta( $post_id, '_runpace_country', true );
$registration_url = get_post_meta( $post_id, '_runpace_registration_url', true );
$price            = (float) get_post_meta( $post_id, '_runpace_price', true );
$dist_terms       = get_the_terms( $post_id, 'runpace_distance' );
$event_terms      = get_the_terms( $post_id, 'runpace_event_type' );

$race_timestamp   = $race_date ? strtotime( $race_date ) : false;
$formatted_date   = $race_timestamp ? date_i18n( get_option( 'date_format' ), $race_timestamp ) : '';
$is_past          = $race_timestamp && $race_timestamp < time();
$days_to_go       = ( ! $is_past && $race_timestamp ) ? max( 0, (int) ceil( ( $race_timestamp - time() ) / DAY_IN_SECONDS ) ) : 0;
$dist_names       = ( $dist_terms && ! is_wp_error( $dist_terms ) ) ? wp_list_pluck( $dist_terms, 'name' ) : [];
$event_name       = ( $event_terms && ! is_wp_error( $event_terms ) ) ? $event_terms[0]->name : '';
$price_display    = $price > 0 ? '$' . number_format( $price, 0 ) : __( 'Free', 'runpace' );
$thumb_url        = get_the_post_thumbnail_url( $post_id, 'runpace-hero' );
$permalink        = get_permalink( $post_id );

$show_countdown  = $attributes['showCountdown']    ?? true;
$primary_label   = $attributes['primaryLabel']     ?? __( 'Register Now', 'runpace' );
$secondary_label = $attributes['secondaryLabel']   ?? __( 'View Details', 'runpace' );
$overlay_opacity = (float) ( $attributes['overlayOpacity'] ?? 0.55 );

$wrapper_attributes = get_block_wrapper_attributes(
	[ 'class' => 'runpace-featured-marathon' . ( $is_past ? ' is-past' : '' ) ]
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $thumb_url ) : ?>
	<div class="runpace-fm__backdrop">
		<img
			src="<?php echo esc_url( $thumb_url ); ?>"
			alt=""
			class="runpace-fm__backdrop-img"
			loading="eager"
			decoding="async"
		/>
		<div
			class="runpace-fm__overlay"
			style="background: rgba(0,0,0,<?php echo esc_attr( (string) $overlay_opacity ); ?>);"
			aria-hidden="true"
		></div>
	</div>
	<?php endif; ?>

	<div class="runpace-fm__content">

		<div class="runpace-fm__badges">
			<?php if ( $event_name ) : ?>
			<span class="runpace-fm__event-badge"><?php echo esc_html( $event_name ); ?></span>
			<?php endif; ?>
			<?php foreach ( $dist_names as $dist ) : ?>
			<span class="runpace-fm__dist-badge"><?php echo esc_html( $dist ); ?></span>
			<?php endforeach; ?>
		</div>

		<h2 class="runpace-fm__title">
			<a href="<?php echo esc_url( $permalink ); ?>" class="runpace-fm__title-link">
				<?php echo esc_html( get_the_title( $post_id ) ); ?>
			</a>
		</h2>

		<div class="runpace-fm__meta-chips">
			<?php if ( $formatted_date ) : ?>
			<span class="runpace-fm__chip">
				<span aria-hidden="true">📅</span>
				<?php echo esc_html( $formatted_date ); ?>
			</span>
			<?php endif; ?>
			<?php if ( $city || $country ) : ?>
			<span class="runpace-fm__chip">
				<span aria-hidden="true">📍</span>
				<?php echo esc_html( implode( ', ', array_filter( [ $city, $country ] ) ) ); ?>
			</span>
			<?php endif; ?>
			<span class="runpace-fm__chip">
				<span aria-hidden="true">💰</span>
				<?php echo esc_html( $price_display ); ?>
			</span>
		</div>

		<?php if ( $show_countdown && ! $is_past && $days_to_go > 0 ) : ?>
		<div class="runpace-fm__countdown" aria-label="<?php echo esc_attr( sprintf( __( '%d days until race', 'runpace' ), $days_to_go ) ); ?>">
			<span class="runpace-fm__countdown-num"><?php echo esc_html( (string) $days_to_go ); ?></span>
			<span class="runpace-fm__countdown-lbl"><?php esc_html_e( 'days to go', 'runpace' ); ?></span>
		</div>
		<?php elseif ( $is_past ) : ?>
		<div class="runpace-fm__past-label"><?php esc_html_e( 'Past event', 'runpace' ); ?></div>
		<?php endif; ?>

		<div class="runpace-fm__ctas">
			<?php if ( $registration_url && ! $is_past ) : ?>
			<a
				href="<?php echo esc_url( $registration_url ); ?>"
				class="runpace-fm__btn runpace-fm__btn--primary wp-element-button"
				target="_blank"
				rel="noopener noreferrer"
			>
				<?php echo esc_html( $primary_label ); ?>
				<span aria-hidden="true">→</span>
			</a>
			<?php endif; ?>
			<a
				href="<?php echo esc_url( $permalink ); ?>"
				class="runpace-fm__btn runpace-fm__btn--secondary"
			>
				<?php echo esc_html( $secondary_label ); ?>
			</a>
		</div>

	</div>
</div>