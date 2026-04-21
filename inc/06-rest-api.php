<?php
/**
 * RunPace – Custom REST API Endpoints
 *
 * Registers lightweight read-only endpoints consumed by the
 * marathon-filter block's Interactivity API store when it needs
 * to fetch fresh data (load-more, filter refresh without page reload).
 *
 * Routes registered:
 *   GET /wp-json/runpace/v1/marathons
 *   GET /wp-json/runpace/v1/marathons/<id>
 *   GET /wp-json/runpace/v1/filter-options
 *   GET /wp-json/runpace/v1/training-plans
 *
 * All routes are public (read-only) and rate-limited via
 * the standard WordPress nonce flow for mutation requests.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Registration ─────────────────────────────────────────────────────────────

add_action( 'rest_api_init', 'runpace_register_rest_routes' );

/**
 * Register all RunPace custom REST routes.
 */
function runpace_register_rest_routes(): void {

	$namespace = 'runpace/v1';

	// ── Marathon collection ──────────────────────────────────────────────────

	register_rest_route(
		$namespace,
		'/marathons',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'runpace_rest_get_marathons',
				'permission_callback' => '__return_true', // Public read.
				'args'                => runpace_marathon_query_args(),
			],
		]
	);

	// ── Single marathon ───────────────────────────────────────────────────────

	register_rest_route(
		$namespace,
		'/marathons/(?P<id>[\d]+)',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'runpace_rest_get_marathon',
				'permission_callback' => '__return_true',
				'args'                => [
					'id' => [
						'validate_callback' => static fn( $v ) => is_numeric( $v ),
						'sanitize_callback' => 'absint',
					],
				],
			],
		]
	);

	// ── Filter options (distinct values for dropdowns) ────────────────────────

	register_rest_route(
		$namespace,
		'/filter-options',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'runpace_rest_get_filter_options',
				'permission_callback' => '__return_true',
			],
		]
	);

	// ── Training plans ────────────────────────────────────────────────────────

	register_rest_route(
		$namespace,
		'/training-plans',
		[
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => 'runpace_rest_get_training_plans',
				'permission_callback' => '__return_true',
				'args'                => runpace_training_plan_query_args(),
			],
		]
	);
}

// ─── Argument schemas ─────────────────────────────────────────────────────────

/**
 * Shared argument schema for marathon queries.
 *
 * @return array<string,array<string,mixed>>
 */
function runpace_marathon_query_args(): array {
	return [
		'per_page' => [
			'default'           => 12,
			'type'              => 'integer',
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
		],
		'page' => [
			'default'           => 1,
			'type'              => 'integer',
			'minimum'           => 1,
			'sanitize_callback' => 'absint',
		],
		'distance' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'location' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'event_type' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'date_after' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'date_before' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'featured' => [
			'default'           => false,
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
		],
		'orderby' => [
			'default'           => 'race_date',
			'enum'              => [ 'race_date', 'price', 'title', 'date' ],
			'sanitize_callback' => 'sanitize_key',
		],
		'order' => [
			'default'           => 'asc',
			'enum'              => [ 'asc', 'desc' ],
			'sanitize_callback' => 'sanitize_key',
		],
		'search' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
	];
}

/**
 * Shared argument schema for training plan queries.
 *
 * @return array<string,array<string,mixed>>
 */
function runpace_training_plan_query_args(): array {
	return [
		'per_page' => [
			'default'           => 12,
			'type'              => 'integer',
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
		],
		'page' => [
			'default'           => 1,
			'type'              => 'integer',
			'minimum'           => 1,
			'sanitize_callback' => 'absint',
		],
		'difficulty' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'goal' => [
			'default'           => '',
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_text_field',
		],
		'is_free' => [
			'default'           => null,
			'type'              => [ 'boolean', 'null' ],
		],
	];
}

// ─── Handlers ─────────────────────────────────────────────────────────────────

/**
 * GET /runpace/v1/marathons
 *
 * @param WP_REST_Request $request Incoming request.
 * @return WP_REST_Response
 */
function runpace_rest_get_marathons( WP_REST_Request $request ): WP_REST_Response {

	$args = runpace_build_marathon_query( $request );
	$query = new WP_Query( $args );
	$items = [];

	foreach ( $query->posts as $post ) {
		$items[] = runpace_format_marathon( $post );
	}

	$response = new WP_REST_Response( $items, 200 );
	$response->header( 'X-WP-Total',      (string) $query->found_posts );
	$response->header( 'X-WP-TotalPages', (string) $query->max_num_pages );

	return $response;
}

/**
 * GET /runpace/v1/marathons/<id>
 *
 * @param WP_REST_Request $request Incoming request.
 * @return WP_REST_Response|WP_Error
 */
function runpace_rest_get_marathon( WP_REST_Request $request ): WP_REST_Response|WP_Error {

	$post_id = absint( $request->get_param( 'id' ) );
	$post    = get_post( $post_id );

	if ( ! $post || 'marathon' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error(
			'runpace_not_found',
			__( 'Marathon not found.', 'runpace' ),
			[ 'status' => 404 ]
		);
	}

	return new WP_REST_Response( runpace_format_marathon( $post ), 200 );
}

/**
 * GET /runpace/v1/filter-options
 * Returns distinct values for all filter dropdowns.
 *
 * @return WP_REST_Response
 */
function runpace_rest_get_filter_options(): WP_REST_Response {

	$distances   = runpace_get_term_options( 'runpace_distance' );
	$locations   = runpace_get_term_options( 'runpace_location' );
	$event_types = runpace_get_term_options( 'runpace_event_type' );

	// Distinct countries from meta (supplement taxonomy location).
	global $wpdb;
	$countries = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT DISTINCT meta_value
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND p.post_type = 'marathon'
			   AND p.post_status = 'publish'
			   AND pm.meta_value != ''
			 ORDER BY meta_value ASC",
			'_runpace_country'
		)
	);

	return new WP_REST_Response(
		[
			'distances'   => $distances,
			'locations'   => $locations,
			'eventTypes'  => $event_types,
			'countries'   => array_values( $countries ),
		],
		200
	);
}

/**
 * GET /runpace/v1/training-plans
 *
 * @param WP_REST_Request $request Incoming request.
 * @return WP_REST_Response
 */
function runpace_rest_get_training_plans( WP_REST_Request $request ): WP_REST_Response {

	$per_page  = absint( $request->get_param( 'per_page' ) );
	$page      = absint( $request->get_param( 'page' ) );
	$difficulty = sanitize_text_field( (string) $request->get_param( 'difficulty' ) );
	$goal       = sanitize_text_field( (string) $request->get_param( 'goal' ) );

	$args = [
		'post_type'      => 'training-plan',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page ?: 12,
		'paged'          => $page ?: 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	$tax_query = [];

	if ( $difficulty ) {
		$tax_query[] = [
			'taxonomy' => 'runpace_difficulty',
			'field'    => 'slug',
			'terms'    => $difficulty,
		];
	}

	if ( $goal ) {
		$tax_query[] = [
			'taxonomy' => 'runpace_goal',
			'field'    => 'slug',
			'terms'    => $goal,
		];
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	// is_free filter.
	$is_free = $request->get_param( 'is_free' );
	if ( null !== $is_free ) {
		$args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
			[
				'key'     => '_runpace_is_free',
				'value'   => rest_sanitize_boolean( $is_free ) ? '1' : '0',
				'compare' => '=',
			],
		];
	}

	$query = new WP_Query( $args );
	$items = [];

	foreach ( $query->posts as $post ) {
		$items[] = runpace_format_training_plan( $post );
	}

	$response = new WP_REST_Response( $items, 200 );
	$response->header( 'X-WP-Total',      (string) $query->found_posts );
	$response->header( 'X-WP-TotalPages', (string) $query->max_num_pages );

	return $response;
}

// ─── Query builders ───────────────────────────────────────────────────────────

/**
 * Build WP_Query args from marathon REST request parameters.
 *
 * @param WP_REST_Request $request Incoming request.
 * @return array<string,mixed>
 */
function runpace_build_marathon_query( WP_REST_Request $request ): array {

	$per_page  = absint( $request->get_param( 'per_page' ) ) ?: 12;
	$page      = absint( $request->get_param( 'page' ) ) ?: 1;
	$distance  = sanitize_text_field( (string) $request->get_param( 'distance' ) );
	$location  = sanitize_text_field( (string) $request->get_param( 'location' ) );
	$event_type = sanitize_text_field( (string) $request->get_param( 'event_type' ) );
	$date_after  = sanitize_text_field( (string) $request->get_param( 'date_after' ) );
	$date_before = sanitize_text_field( (string) $request->get_param( 'date_before' ) );
	$featured  = rest_sanitize_boolean( $request->get_param( 'featured' ) );
	$orderby   = sanitize_key( (string) $request->get_param( 'orderby' ) );
	$order     = strtoupper( sanitize_key( (string) $request->get_param( 'order' ) ) );
	$search    = sanitize_text_field( (string) $request->get_param( 'search' ) );

	$args = [
		'post_type'      => 'marathon',
		'post_status'    => 'publish',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'order'          => in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'ASC',
	];

	if ( $search ) {
		$args['s'] = $search;
	}

	// ── Taxonomy filters ─────────────────────────────────────────────────────

	$tax_query = [];

	if ( $distance ) {
		$tax_query[] = [
			'taxonomy' => 'runpace_distance',
			'field'    => 'slug',
			'terms'    => $distance,
		];
	}

	if ( $location ) {
		$tax_query[] = [
			'taxonomy' => 'runpace_location',
			'field'    => 'slug',
			'terms'    => $location,
		];
	}

	if ( $event_type ) {
		$tax_query[] = [
			'taxonomy' => 'runpace_event_type',
			'field'    => 'slug',
			'terms'    => $event_type,
		];
	}

	if ( ! empty( $tax_query ) ) {
		$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	// ── Meta filters ─────────────────────────────────────────────────────────

	$meta_query = [];

	if ( $date_after ) {
		$meta_query[] = [
			'key'     => '_runpace_race_date',
			'value'   => sanitize_text_field( $date_after ),
			'compare' => '>=',
			'type'    => 'DATE',
		];
	}

	if ( $date_before ) {
		$meta_query[] = [
			'key'     => '_runpace_race_date',
			'value'   => sanitize_text_field( $date_before ),
			'compare' => '<=',
			'type'    => 'DATE',
		];
	}

	if ( $featured ) {
		$meta_query[] = [
			'key'     => '_runpace_is_featured',
			'value'   => '1',
			'compare' => '=',
		];
	}

	if ( ! empty( $meta_query ) ) {
		$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
	}

	// ── Orderby ───────────────────────────────────────────────────────────────

	$meta_orderby_map = [
		'race_date' => '_runpace_race_date',
		'price'     => '_runpace_price',
	];

	if ( isset( $meta_orderby_map[ $orderby ] ) ) {
		$args['meta_key'] = $meta_orderby_map[ $orderby ]; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['orderby']  = 'meta_value';
	} elseif ( 'title' === $orderby ) {
		$args['orderby'] = 'title';
	} else {
		$args['orderby'] = 'date';
	}

	return $args;
}

// ─── Formatters ───────────────────────────────────────────────────────────────

/**
 * Format a marathon post into a REST-safe array.
 *
 * @param  WP_Post $post Marathon post object.
 * @return array<string,mixed>
 */
function runpace_format_marathon( WP_Post $post ): array {

	$id         = $post->ID;
	$race_date  = get_post_meta( $id, '_runpace_race_date', true );
	$timestamp  = $race_date ? strtotime( (string) $race_date ) : 0;

	// Thumbnail.
	$thumbnail_id  = (int) get_post_thumbnail_id( $id );
	$thumbnail_src = $thumbnail_id
		? wp_get_attachment_image_url( $thumbnail_id, 'runpace-card' )
		: '';

	// Taxonomies.
	$distances   = runpace_get_post_term_names( $id, 'runpace_distance' );
	$locations   = runpace_get_post_term_names( $id, 'runpace_location' );
	$event_types = runpace_get_post_term_names( $id, 'runpace_event_type' );

	return [
		'id'               => $id,
		'title'            => get_the_title( $post ),
		'excerpt'          => wp_strip_all_tags( get_the_excerpt( $post ) ),
		'permalink'        => get_permalink( $post ),
		'thumbnail'        => $thumbnail_src ?: '',
		'raceDate'         => $race_date,
		'raceDateFormatted'=> $timestamp ? date_i18n( get_option( 'date_format' ), $timestamp ) : '',
		'timestamp'        => $timestamp ?: 0,
		'isPast'           => $timestamp && $timestamp < time(),
		'city'             => get_post_meta( $id, '_runpace_city',             true ),
		'country'          => get_post_meta( $id, '_runpace_country',          true ),
		'distanceLabel'    => get_post_meta( $id, '_runpace_distance_label',   true ),
		'registrationUrl'  => get_post_meta( $id, '_runpace_registration_url', true ),
		'price'            => (float) get_post_meta( $id, '_runpace_price',   true ),
		'elevationGain'    => (int)   get_post_meta( $id, '_runpace_elevation_gain', true ),
		'difficultyRating' => (int)   get_post_meta( $id, '_runpace_difficulty_rating', true ),
		'isFeatured'       => (bool)  get_post_meta( $id, '_runpace_is_featured', true ),
		'websiteUrl'       => get_post_meta( $id, '_runpace_website_url',      true ),
		'distances'        => $distances,
		'locations'        => $locations,
		'eventTypes'       => $event_types,
	];
}

/**
 * Format a training plan post into a REST-safe array.
 *
 * @param  WP_Post $post Training plan post object.
 * @return array<string,mixed>
 */
function runpace_format_training_plan( WP_Post $post ): array {

	$id            = $post->ID;
	$thumbnail_id  = (int) get_post_thumbnail_id( $id );
	$thumbnail_src = $thumbnail_id
		? wp_get_attachment_image_url( $thumbnail_id, 'runpace-card' )
		: '';

	$difficulties = runpace_get_post_term_names( $id, 'runpace_difficulty' );
	$goals        = runpace_get_post_term_names( $id, 'runpace_goal' );

	return [
		'id'             => $id,
		'title'          => get_the_title( $post ),
		'excerpt'        => wp_strip_all_tags( get_the_excerpt( $post ) ),
		'permalink'      => get_permalink( $post ),
		'thumbnail'      => $thumbnail_src ?: '',
		'durationWeeks'  => (int)   get_post_meta( $id, '_runpace_duration_weeks',    true ),
		'peakWeeklyKm'   => (float) get_post_meta( $id, '_runpace_peak_weekly_km',    true ),
		'sessionsPerWeek'=> (int)   get_post_meta( $id, '_runpace_sessions_per_week', true ),
		'levelLabel'     => get_post_meta( $id, '_runpace_level_label',  true ),
		'goalLabel'      => get_post_meta( $id, '_runpace_goal_label',   true ),
		'isFree'         => (bool)  get_post_meta( $id, '_runpace_is_free',           true ),
		'downloadUrl'    => get_post_meta( $id, '_runpace_download_url', true ),
		'difficulties'   => $difficulties,
		'goals'          => $goals,
	];
}

// ─── Term helpers ─────────────────────────────────────────────────────────────

/**
 * Get term names for a post in a given taxonomy.
 *
 * @param  int    $post_id  Post ID.
 * @param  string $taxonomy Taxonomy slug.
 * @return string[]
 */
function runpace_get_post_term_names( int $post_id, string $taxonomy ): array {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return [];
	}
	return wp_list_pluck( $terms, 'name' );
}

/**
 * Get all terms of a taxonomy as slug => name pairs.
 *
 * @param  string $taxonomy Taxonomy slug.
 * @return array<string,string>[]  Array of { slug, name } objects.
 */
function runpace_get_term_options( string $taxonomy ): array {
	$terms = get_terms(
		[
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		]
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	return array_map(
		static fn( WP_Term $t ): array => [
			'slug' => $t->slug,
			'name' => $t->name,
		],
		$terms
	);
}