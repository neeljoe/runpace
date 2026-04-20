<?php
/**
 * RunPace – Custom REST API Endpoints
 *
 * Provides lightweight JSON endpoints consumed by the Interactivity API
 * filter block when fetching updated data (e.g. after taxonomy changes).
 *
 * Routes:
 *   GET /wp-json/runpace/v1/marathons           – filterable marathon list
 *   GET /wp-json/runpace/v1/filter-options      – available distances + countries
 *
 * Authentication: public read access (no auth required for published posts).
 * Rate-limit mitigation: results are cached with a short transient.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register REST routes on rest_api_init.
 */
function runpace_register_rest_routes(): void {

	// Marathon list (filterable).
	register_rest_route(
		'runpace/v1',
		'/marathons',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'runpace_rest_get_marathons',
			'permission_callback' => '__return_true',
			'args'                => [
				'distance' => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'location' => [
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'date_filter' => [
					'type'              => 'string',
					'default'           => 'upcoming',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => static function ( string $value ): bool {
						return in_array( $value, [ 'upcoming', 'past', 'all' ], true );
					},
				],
				'per_page' => [
					'type'              => 'integer',
					'default'           => 9,
					'minimum'           => 1,
					'maximum'           => 50,
					'sanitize_callback' => 'absint',
				],
				'page' => [
					'type'              => 'integer',
					'default'           => 1,
					'minimum'           => 1,
					'sanitize_callback' => 'absint',
				],
			],
		]
	);

	// Filter option lists (distances, countries).
	register_rest_route(
		'runpace/v1',
		'/filter-options',
		[
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'runpace_rest_get_filter_options',
			'permission_callback' => '__return_true',
		]
	);
}
add_action( 'rest_api_init', 'runpace_register_rest_routes' );

// ─── Marathon List Endpoint ───────────────────────────────────────────────────

/**
 * Handle GET /runpace/v1/marathons
 *
 * @param  WP_REST_Request $request Incoming request.
 * @return WP_REST_Response|WP_Error
 */
function runpace_rest_get_marathons( WP_REST_Request $request ): WP_REST_Response|WP_Error {

	$distance    = $request->get_param( 'distance' );
	$location    = $request->get_param( 'location' );
	$date_filter = $request->get_param( 'date_filter' );
	$per_page    = (int) $request->get_param( 'per_page' );
	$page        = (int) $request->get_param( 'page' );

	// Build a cache key from the params.
	$cache_key = 'runpace_marathons_' . md5( serialize( compact( 'distance', 'location', 'date_filter', 'per_page', 'page' ) ) );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return rest_ensure_response( $cached );
	}

	// Base query args.
	$args = [
		'post_type'      => 'marathon',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'meta_key'       => '_runpace_race_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		'tax_query'      => [], // phpcs:ignore WordPress.DB.SlowDBQuery
		'meta_query'     => [], // phpcs:ignore WordPress.DB.SlowDBQuery
	];

	// Date filter.
	$today = current_time( 'Y-m-d' );
	if ( 'upcoming' === $date_filter ) {
		$args['meta_query'][] = [
			'key'     => '_runpace_race_date',
			'value'   => $today,
			'compare' => '>=',
			'type'    => 'DATE',
		];
	} elseif ( 'past' === $date_filter ) {
		$args['meta_query'][] = [
			'key'     => '_runpace_race_date',
			'value'   => $today,
			'compare' => '<',
			'type'    => 'DATE',
		];
	}

	// Distance taxonomy filter.
	if ( $distance ) {
		$term = get_term_by( 'name', $distance, 'runpace_distance' );
		if ( $term && ! is_wp_error( $term ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'runpace_distance',
				'field'    => 'term_id',
				'terms'    => $term->term_id,
			];
		}
	}

	// Location (country) meta filter.
	if ( $location ) {
		$args['meta_query'][] = [
			'key'     => '_runpace_country',
			'value'   => $location,
			'compare' => '=',
		];
	}

	// Clean up empty arrays.
	if ( empty( $args['tax_query'] ) )  unset( $args['tax_query'] );
	if ( empty( $args['meta_query'] ) ) unset( $args['meta_query'] );

	$query = new WP_Query( $args );

	$items = [];
	foreach ( $query->posts as $post ) {
		$items[] = runpace_format_marathon_for_rest( $post->ID );
	}

	$response_data = [
		'items'      => $items,
		'total'      => (int) $query->found_posts,
		'totalPages' => (int) $query->max_num_pages,
		'page'       => $page,
	];

	// Cache for 5 minutes.
	set_transient( $cache_key, $response_data, 5 * MINUTE_IN_SECONDS );

	return rest_ensure_response( $response_data );
}

/**
 * Format a single marathon post for the REST response.
 *
 * @param  int $post_id Post ID.
 * @return array<string, mixed>
 */
function runpace_format_marathon_for_rest( int $post_id ): array {

	$race_date   = get_post_meta( $post_id, '_runpace_race_date', true );
	$race_ts     = $race_date ? strtotime( $race_date ) : 0;
	$is_past     = $race_ts > 0 && $race_ts < time();
	$days_to_go  = ( ! $is_past && $race_ts > 0 )
		? max( 0, (int) ceil( ( $race_ts - time() ) / DAY_IN_SECONDS ) )
		: 0;

	$dist_terms  = get_the_terms( $post_id, 'runpace_distance' );
	$dist_names  = ( $dist_terms && ! is_wp_error( $dist_terms ) ) ? wp_list_pluck( $dist_terms, 'name' ) : [];

	return [
		'id'            => $post_id,
		'title'         => get_the_title( $post_id ),
		'excerpt'       => wp_trim_words( get_the_excerpt( $post_id ), 15, '…' ),
		'permalink'     => get_permalink( $post_id ),
		'thumbUrl'      => get_the_post_thumbnail_url( $post_id, 'runpace-card' ) ?: '',
		'date'          => $race_date,
		'formattedDate' => $race_ts ? date_i18n( get_option( 'date_format' ), $race_ts ) : '',
		'city'          => get_post_meta( $post_id, '_runpace_city', true ),
		'country'       => get_post_meta( $post_id, '_runpace_country', true ),
		'distanceNames' => $dist_names,
		'price'         => (float) get_post_meta( $post_id, '_runpace_price', true ),
		'isPast'        => $is_past,
		'daysToGo'      => $days_to_go,
		'isFeatured'    => (bool) get_post_meta( $post_id, '_runpace_is_featured', true ),
	];
}

// ─── Filter Options Endpoint ──────────────────────────────────────────────────

/**
 * Handle GET /runpace/v1/filter-options
 *
 * Returns all unique distances and countries currently in the database.
 *
 * @return WP_REST_Response
 */
function runpace_rest_get_filter_options(): WP_REST_Response {

	$cache_key = 'runpace_filter_options';
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return rest_ensure_response( $cached );
	}

	// Distances from taxonomy.
	$dist_terms = get_terms(
		[
			'taxonomy'   => 'runpace_distance',
			'hide_empty' => true,
		]
	);

	$distances = [];
	if ( $dist_terms && ! is_wp_error( $dist_terms ) ) {
		$distances = array_values( wp_list_pluck( $dist_terms, 'name' ) );
	}

	// Countries from meta (unique values).
	global $wpdb;
	$countries = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT DISTINCT meta_value
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND pm.meta_value != ''
			   AND p.post_type = 'marathon'
			   AND p.post_status = 'publish'
			 ORDER BY meta_value ASC",
			'_runpace_country'
		)
	);

	$data = [
		'distances' => $distances,
		'countries' => $countries ?: [],
	];

	set_transient( $cache_key, $data, 15 * MINUTE_IN_SECONDS );

	return rest_ensure_response( $data );
}

// ─── Cache invalidation ───────────────────────────────────────────────────────

/**
 * Clear marathon-related REST caches when a marathon post is saved or deleted.
 *
 * @param int $post_id Post ID.
 */
function runpace_invalidate_marathon_cache( int $post_id ): void {

	if ( 'marathon' !== get_post_type( $post_id ) ) {
		return;
	}

	// Delete the filter options cache (country list may have changed).
	delete_transient( 'runpace_filter_options' );

	// We can't enumerate all marathon list transient keys here, so use a global
	// flush instead (fine for sites with a reasonable number of marathons).
	global $wpdb;
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_runpace_marathons_%'"
	);
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_runpace_marathons_%'"
	);
}
add_action( 'save_post',   'runpace_invalidate_marathon_cache' );
add_action( 'delete_post', 'runpace_invalidate_marathon_cache' );