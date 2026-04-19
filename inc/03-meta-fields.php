<?php
/**
 * RunPace – Post Meta Fields
 *
 * Registers all custom meta fields for Marathon and Training Plan CPTs.
 *
 * Using register_post_meta() with 'show_in_rest' => true is required for:
 *   - Block Bindings API (Phase 4)
 *   - REST API access (Phase 5 Interactivity API filters)
 *   - Block editor sidebar panels
 *
 * Naming convention: _runpace_{field}  (leading underscore = hidden from
 * the legacy Custom Fields meta box, but still accessible via the API).
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Marathon Meta Fields ─────────────────────────────────────────────────────

/**
 * Register meta fields for the marathon post type.
 */
function runpace_register_marathon_meta(): void {

	$post_type = 'marathon';

	// Race date (ISO 8601 string — stored as Y-m-d).
	register_post_meta(
		$post_type,
		'_runpace_race_date',
		[
			'type'              => 'string',
			'description'       => __( 'Date of the race (Y-m-d).', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// City.
	register_post_meta(
		$post_type,
		'_runpace_city',
		[
			'type'              => 'string',
			'description'       => __( 'City where the race takes place.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Country.
	register_post_meta(
		$post_type,
		'_runpace_country',
		[
			'type'              => 'string',
			'description'       => __( 'Country where the race takes place.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Distance label (e.g. "42.195 km" — display string, taxonomy handles filtering).
	register_post_meta(
		$post_type,
		'_runpace_distance_label',
		[
			'type'              => 'string',
			'description'       => __( 'Display label for the race distance.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Registration URL.
	register_post_meta(
		$post_type,
		'_runpace_registration_url',
		[
			'type'              => 'string',
			'description'       => __( 'Official registration link.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Entry price (numeric, in USD — display formatting happens in block render).
	register_post_meta(
		$post_type,
		'_runpace_price',
		[
			'type'              => 'number',
			'description'       => __( 'Entry fee in USD.', 'runpace' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Elevation gain (metres, integer).
	register_post_meta(
		$post_type,
		'_runpace_elevation_gain',
		[
			'type'              => 'integer',
			'description'       => __( 'Total elevation gain in metres.', 'runpace' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Difficulty rating (1–5 scale).
	register_post_meta(
		$post_type,
		'_runpace_difficulty_rating',
		[
			'type'              => 'integer',
			'description'       => __( 'Course difficulty on a 1–5 scale.', 'runpace' ),
			'single'            => true,
			'default'           => 1,
			'sanitize_callback' => static function ( mixed $value ): int {
				return (int) max( 1, min( 5, (int) $value ) );
			},
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Is featured flag (used by the Featured Marathon block).
	register_post_meta(
		$post_type,
		'_runpace_is_featured',
		[
			'type'              => 'boolean',
			'description'       => __( 'Whether this marathon is featured on the homepage.', 'runpace' ),
			'single'            => true,
			'default'           => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Participant limit (0 = unlimited).
	register_post_meta(
		$post_type,
		'_runpace_participant_limit',
		[
			'type'              => 'integer',
			'description'       => __( 'Maximum number of participants. 0 = unlimited.', 'runpace' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Website URL (official race website, distinct from registration URL).
	register_post_meta(
		$post_type,
		'_runpace_website_url',
		[
			'type'              => 'string',
			'description'       => __( 'Official race website URL.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);
}
add_action( 'init', 'runpace_register_marathon_meta' );

// ─── Training Plan Meta Fields ────────────────────────────────────────────────

/**
 * Register meta fields for the training-plan post type.
 */
function runpace_register_training_plan_meta(): void {

	$post_type = 'training-plan';

	// Duration in weeks.
	register_post_meta(
		$post_type,
		'_runpace_duration_weeks',
		[
			'type'              => 'integer',
			'description'       => __( 'Duration of the plan in weeks.', 'runpace' ),
			'single'            => true,
			'default'           => 8,
			'sanitize_callback' => 'absint',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Weekly mileage at peak (km).
	register_post_meta(
		$post_type,
		'_runpace_peak_weekly_km',
		[
			'type'              => 'number',
			'description'       => __( 'Peak weekly mileage in kilometres.', 'runpace' ),
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => static function ( mixed $value ): float {
				return (float) max( 0, (float) $value );
			},
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Sessions per week.
	register_post_meta(
		$post_type,
		'_runpace_sessions_per_week',
		[
			'type'              => 'integer',
			'description'       => __( 'Number of training sessions per week.', 'runpace' ),
			'single'            => true,
			'default'           => 3,
			'sanitize_callback' => static function ( mixed $value ): int {
				return (int) max( 1, min( 14, (int) $value ) );
			},
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Level label (mirrors difficulty taxonomy for quick display).
	register_post_meta(
		$post_type,
		'_runpace_level_label',
		[
			'type'              => 'string',
			'description'       => __( 'Display label for experience level.', 'runpace' ),
			'single'            => true,
			'default'           => 'Beginner',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Goal label (mirrors goal taxonomy for quick display).
	register_post_meta(
		$post_type,
		'_runpace_goal_label',
		[
			'type'              => 'string',
			'description'       => __( 'Target distance or fitness goal.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Is free flag.
	register_post_meta(
		$post_type,
		'_runpace_is_free',
		[
			'type'              => 'boolean',
			'description'       => __( 'Whether this training plan is free.', 'runpace' ),
			'single'            => true,
			'default'           => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);

	// Download URL (for PDF plans).
	register_post_meta(
		$post_type,
		'_runpace_download_url',
		[
			'type'              => 'string',
			'description'       => __( 'Direct download URL for a PDF version of the plan.', 'runpace' ),
			'single'            => true,
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => 'runpace_meta_auth_callback',
			'show_in_rest'      => true,
		]
	);
}
add_action( 'init', 'runpace_register_training_plan_meta' );

// ─── Auth Callback ────────────────────────────────────────────────────────────

/**
 * Auth callback for all RunPace meta fields.
 * Only users who can edit posts may read/write meta via the REST API.
 *
 * @return bool
 */
function runpace_meta_auth_callback(): bool {
	return current_user_can( 'edit_posts' );
}
