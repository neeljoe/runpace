<?php
/**
 * RunPace – Admin List Table Columns
 *
 * Adds custom columns to the Marathon and Training Plan list tables
 * in wp-admin so editors can scan key meta at a glance.
 *
 * Marathon columns:   Date | City/Country | Distance | Price | Featured
 * Training Plan cols: Duration | Level | Goal | Sessions/week
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Marathon Columns ─────────────────────────────────────────────────────────

/**
 * Define columns for the marathon list table.
 *
 * @param  array<string,string> $columns Default columns.
 * @return array<string,string>
 */
function runpace_marathon_columns( array $columns ): array {

	// Remove the default date column (we'll add a race date instead).
	unset( $columns['date'] );

	return array_merge(
		$columns,
		[
			'race_date'  => __( 'Race date',  'runpace' ),
			'location'   => __( 'Location',   'runpace' ),
			'distance'   => __( 'Distance',   'runpace' ),
			'price'      => __( 'Entry fee',  'runpace' ),
			'featured'   => __( 'Featured',   'runpace' ),
		]
	);
}
add_filter( 'manage_marathon_posts_columns', 'runpace_marathon_columns' );

/**
 * Render cell content for custom marathon columns.
 *
 * @param string $column  Column slug.
 * @param int    $post_id Current post ID.
 */
function runpace_marathon_column_content( string $column, int $post_id ): void {

	switch ( $column ) {

		case 'race_date':
			$date = get_post_meta( $post_id, '_runpace_race_date', true );
			if ( $date ) {
				$timestamp   = strtotime( $date );
				$formatted   = $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : esc_html( $date );
				$is_past     = $timestamp && $timestamp < time();
				$class       = $is_past ? 'style="color:#999"' : '';
				echo '<span ' . $class . '>' . esc_html( $formatted ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				echo '<span style="color:#999">—</span>';
			}
			break;

		case 'location':
			$city    = get_post_meta( $post_id, '_runpace_city',    true );
			$country = get_post_meta( $post_id, '_runpace_country', true );
			$parts   = array_filter( [ $city, $country ] );
			echo $parts ? esc_html( implode( ', ', $parts ) ) : '<span style="color:#999">—</span>';
			break;

		case 'distance':
			$terms = get_the_terms( $post_id, 'runpace_distance' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$names = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $names ) );
			} else {
				echo '<span style="color:#999">—</span>';
			}
			break;

		case 'price':
			$price = get_post_meta( $post_id, '_runpace_price', true );
			if ( $price ) {
				echo esc_html( '$' . number_format( (float) $price, 0 ) );
			} else {
				echo '<span style="color:#999">Free</span>';
			}
			break;

		case 'featured':
			$is_featured = get_post_meta( $post_id, '_runpace_is_featured', true );
			echo $is_featured
				? '<span style="color:#0bda7a;font-size:18px;" title="' . esc_attr__( 'Featured', 'runpace' ) . '">&#9733;</span>'
				: '<span style="color:#ccc;font-size:18px;">&#9734;</span>';
			break;
	}
}
add_action( 'manage_marathon_posts_custom_column', 'runpace_marathon_column_content', 10, 2 );

/**
 * Make the race_date column sortable.
 *
 * @param  array<string,string> $columns Sortable columns.
 * @return array<string,string>
 */
function runpace_marathon_sortable_columns( array $columns ): array {
	$columns['race_date'] = 'race_date';
	$columns['price']     = 'price';
	return $columns;
}
add_filter( 'manage_edit-marathon_sortable_columns', 'runpace_marathon_sortable_columns' );

// ─── Training Plan Columns ────────────────────────────────────────────────────

/**
 * Define columns for the training-plan list table.
 *
 * @param  array<string,string> $columns Default columns.
 * @return array<string,string>
 */
function runpace_training_plan_columns( array $columns ): array {

	unset( $columns['date'] );

	return array_merge(
		$columns,
		[
			'duration'   => __( 'Duration',        'runpace' ),
			'level'      => __( 'Level',           'runpace' ),
			'goal'       => __( 'Goal',            'runpace' ),
			'sessions'   => __( 'Sessions / week', 'runpace' ),
			'is_free'    => __( 'Free?',           'runpace' ),
		]
	);
}
add_filter( 'manage_training-plan_posts_columns', 'runpace_training_plan_columns' );

/**
 * Render cell content for custom training-plan columns.
 *
 * @param string $column  Column slug.
 * @param int    $post_id Current post ID.
 */
function runpace_training_plan_column_content( string $column, int $post_id ): void {

	switch ( $column ) {

		case 'duration':
			$weeks = (int) get_post_meta( $post_id, '_runpace_duration_weeks', true );
			if ( $weeks ) {
				/* translators: %d = number of weeks */
				echo esc_html( sprintf( _n( '%d week', '%d weeks', $weeks, 'runpace' ), $weeks ) );
			} else {
				echo '<span style="color:#999">—</span>';
			}
			break;

		case 'level':
			$terms = get_the_terms( $post_id, 'runpace_difficulty' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				echo esc_html( $terms[0]->name );
			} else {
				$label = get_post_meta( $post_id, '_runpace_level_label', true );
				echo $label ? esc_html( $label ) : '<span style="color:#999">—</span>';
			}
			break;

		case 'goal':
			$terms = get_the_terms( $post_id, 'runpace_goal' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$names = wp_list_pluck( $terms, 'name' );
				echo esc_html( implode( ', ', $names ) );
			} else {
				$label = get_post_meta( $post_id, '_runpace_goal_label', true );
				echo $label ? esc_html( $label ) : '<span style="color:#999">—</span>';
			}
			break;

		case 'sessions':
			$sessions = (int) get_post_meta( $post_id, '_runpace_sessions_per_week', true );
			echo $sessions ? esc_html( (string) $sessions ) : '<span style="color:#999">—</span>';
			break;

		case 'is_free':
			$is_free = get_post_meta( $post_id, '_runpace_is_free', true );
			echo $is_free
				? '<span style="color:#0bda7a;font-weight:600">' . esc_html__( 'Free', 'runpace' ) . '</span>'
				: '<span style="color:#ff5c1a;font-weight:600">' . esc_html__( 'Paid', 'runpace' ) . '</span>';
			break;
	}
}
add_action( 'manage_training-plan_posts_custom_column', 'runpace_training_plan_column_content', 10, 2 );

/**
 * Make the duration column sortable for training plans.
 *
 * @param  array<string,string> $columns Sortable columns.
 * @return array<string,string>
 */
function runpace_training_plan_sortable_columns( array $columns ): array {
	$columns['duration'] = 'duration';
	return $columns;
}
add_filter( 'manage_edit-training-plan_sortable_columns', 'runpace_training_plan_sortable_columns' );

// ─── Handle custom orderby for meta-based sorting ─────────────────────────────

/**
 * Modify the query when sorting by a custom column.
 *
 * @param \WP_Query $query The current query.
 */
function runpace_custom_orderby( \WP_Query $query ): void {

	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	$meta_map = [
		'race_date' => '_runpace_race_date',
		'price'     => '_runpace_price',
		'duration'  => '_runpace_duration_weeks',
	];

	if ( isset( $meta_map[ $orderby ] ) ) {
		$query->set( 'meta_key', $meta_map[ $orderby ] );
		$query->set( 'orderby',  'meta_value' );
	}
}
add_action( 'pre_get_posts', 'runpace_custom_orderby' );
