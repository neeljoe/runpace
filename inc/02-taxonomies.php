<?php
/**
 * RunPace – Custom Taxonomies
 *
 * Registers 5 taxonomies:
 *   Marathon:       runpace_distance  | runpace_location  | runpace_event_type
 *   Training Plan:  runpace_difficulty | runpace_goal
 *
 * All are REST-enabled (required for block editor and Interactivity API filters).
 * Slugs are prefixed with "runpace_" to avoid conflicts with other plugins.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Helper ───────────────────────────────────────────────────────────────────

/**
 * Build a minimal but complete labels array for a taxonomy.
 *
 * @param  string $singular Singular label.
 * @param  string $plural   Plural label.
 * @return array<string,string>
 */
function runpace_taxonomy_labels( string $singular, string $plural ): array {
	return [
		/* translators: %s = plural taxonomy name */
		'name'                       => $plural,
		'singular_name'              => $singular,
		/* translators: %s = plural taxonomy name */
		'search_items'               => sprintf( __( 'Search %s', 'runpace' ), $plural ),
		'popular_items'              => sprintf( __( 'Popular %s', 'runpace' ), $plural ),
		'all_items'                  => sprintf( __( 'All %s', 'runpace' ), $plural ),
		'parent_item'                => sprintf( __( 'Parent %s', 'runpace' ), $singular ),
		'parent_item_colon'          => sprintf( __( 'Parent %s:', 'runpace' ), $singular ),
		'edit_item'                  => sprintf( __( 'Edit %s', 'runpace' ), $singular ),
		'view_item'                  => sprintf( __( 'View %s', 'runpace' ), $singular ),
		'update_item'                => sprintf( __( 'Update %s', 'runpace' ), $singular ),
		'add_new_item'               => sprintf( __( 'Add new %s', 'runpace' ), $singular ),
		'new_item_name'              => sprintf( __( 'New %s name', 'runpace' ), $singular ),
		'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'runpace' ), strtolower( $plural ) ),
		'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'runpace' ), strtolower( $plural ) ),
		'choose_from_most_used'      => sprintf( __( 'Choose from most used %s', 'runpace' ), strtolower( $plural ) ),
		'not_found'                  => sprintf( __( 'No %s found.', 'runpace' ), strtolower( $plural ) ),
		'no_terms'                   => sprintf( __( 'No %s', 'runpace' ), strtolower( $plural ) ),
		'items_list_navigation'      => sprintf( __( '%s list navigation', 'runpace' ), $plural ),
		'items_list'                 => sprintf( __( '%s list', 'runpace' ), $plural ),
		'back_to_items'              => sprintf( __( '&larr; Go to %s', 'runpace' ), $plural ),
	];
}

// ─── Marathon: Distance ───────────────────────────────────────────────────────

/**
 * Register the Distance taxonomy (marathon).
 *
 * Terms: 5K, 10K, Half Marathon, Marathon, Ultra.
 * This is a non-hierarchical tag-style taxonomy.
 */
function runpace_register_tax_distance(): void {

	$args = [
		'labels'            => runpace_taxonomy_labels(
			_x( 'Distance', 'Taxonomy singular', 'runpace' ),
			_x( 'Distances', 'Taxonomy plural', 'runpace' )
		),
		'description'       => __( 'Race distance categories: 5K, 10K, Half, Full, Ultra.', 'runpace' ),
		'hierarchical'      => false,
		'public'            => true,
		'publicly_queryable'=> true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rest_base'         => 'distances',
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'show_admin_column' => true,
		'rewrite'           => [
			'slug'         => 'distance',
			'with_front'   => false,
			'hierarchical' => false,
		],
		'query_var'         => true,
		'sort'              => true, // Preserve insertion order for distance sorting.
	];

	register_taxonomy( 'runpace_distance', [ 'marathon' ], $args );
}
add_action( 'init', 'runpace_register_tax_distance' );

// ─── Marathon: Location ───────────────────────────────────────────────────────

/**
 * Register the Location taxonomy (marathon).
 *
 * Hierarchical: Continent > Country > City.
 * e.g. Europe > Germany > Berlin
 */
function runpace_register_tax_location(): void {

	$args = [
		'labels'            => runpace_taxonomy_labels(
			_x( 'Location', 'Taxonomy singular', 'runpace' ),
			_x( 'Locations', 'Taxonomy plural', 'runpace' )
		),
		'description'       => __( 'Geographic location: continent, country, and city.', 'runpace' ),
		'hierarchical'      => true,  // Allows continent > country > city nesting.
		'public'            => true,
		'publicly_queryable'=> true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rest_base'         => 'locations',
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'show_admin_column' => true,
		'rewrite'           => [
			'slug'         => 'location',
			'with_front'   => false,
			'hierarchical' => true,
		],
		'query_var'         => true,
	];

	register_taxonomy( 'runpace_location', [ 'marathon' ], $args );
}
add_action( 'init', 'runpace_register_tax_location' );

// ─── Marathon: Event Type ─────────────────────────────────────────────────────

/**
 * Register the Event Type taxonomy (marathon).
 *
 * Terms: Road, Trail, Ultra, Track, Virtual.
 */
function runpace_register_tax_event_type(): void {

	$args = [
		'labels'            => runpace_taxonomy_labels(
			_x( 'Event type', 'Taxonomy singular', 'runpace' ),
			_x( 'Event types', 'Taxonomy plural', 'runpace' )
		),
		'description'       => __( 'Type of running event: road, trail, ultra, track, or virtual.', 'runpace' ),
		'hierarchical'      => false,
		'public'            => true,
		'publicly_queryable'=> true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rest_base'         => 'event-types',
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'show_admin_column' => true,
		'rewrite'           => [
			'slug'         => 'event-type',
			'with_front'   => false,
			'hierarchical' => false,
		],
		'query_var'         => true,
	];

	register_taxonomy( 'runpace_event_type', [ 'marathon' ], $args );
}
add_action( 'init', 'runpace_register_tax_event_type' );

// ─── Training Plan: Difficulty ────────────────────────────────────────────────

/**
 * Register the Difficulty taxonomy (training-plan).
 *
 * Terms: Beginner, Intermediate, Advanced.
 */
function runpace_register_tax_difficulty(): void {

	$args = [
		'labels'            => runpace_taxonomy_labels(
			_x( 'Difficulty', 'Taxonomy singular', 'runpace' ),
			_x( 'Difficulties', 'Taxonomy plural', 'runpace' )
		),
		'description'       => __( 'Training plan difficulty level: Beginner, Intermediate, or Advanced.', 'runpace' ),
		'hierarchical'      => false,
		'public'            => true,
		'publicly_queryable'=> true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rest_base'         => 'difficulties',
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'show_admin_column' => true,
		'rewrite'           => [
			'slug'         => 'difficulty',
			'with_front'   => false,
			'hierarchical' => false,
		],
		'query_var'         => true,
		'sort'              => true,
	];

	register_taxonomy( 'runpace_difficulty', [ 'training-plan' ], $args );
}
add_action( 'init', 'runpace_register_tax_difficulty' );

// ─── Training Plan: Goal ──────────────────────────────────────────────────────

/**
 * Register the Goal taxonomy (training-plan).
 *
 * Terms: 5K, 10K, Half Marathon, Marathon, Ultra, General Fitness.
 */
function runpace_register_tax_goal(): void {

	$args = [
		'labels'            => runpace_taxonomy_labels(
			_x( 'Goal', 'Taxonomy singular', 'runpace' ),
			_x( 'Goals', 'Taxonomy plural', 'runpace' )
		),
		'description'       => __( 'Training goal: target race distance or fitness objective.', 'runpace' ),
		'hierarchical'      => false,
		'public'            => true,
		'publicly_queryable'=> true,
		'show_ui'           => true,
		'show_in_menu'      => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'rest_base'         => 'goals',
		'show_tagcloud'     => false,
		'show_in_quick_edit'=> true,
		'show_admin_column' => true,
		'rewrite'           => [
			'slug'         => 'goal',
			'with_front'   => false,
			'hierarchical' => false,
		],
		'query_var'         => true,
	];

	register_taxonomy( 'runpace_goal', [ 'training-plan' ], $args );
}
add_action( 'init', 'runpace_register_tax_goal' );

// ─── Default Terms ────────────────────────────────────────────────────────────

/**
 * Insert default terms on theme activation so the dropdowns are
 * immediately populated in the editor without manual data entry.
 *
 * Uses wp_insert_term() with check_taxonomy_hierarchy = false so
 * it won't error if terms already exist (idempotent).
 */
function runpace_insert_default_terms(): void {

	// Distance terms — ordered by race length.
	$distances = [ '5K', '10K', 'Half marathon', 'Marathon', 'Ultra' ];
	foreach ( $distances as $term ) {
		if ( ! term_exists( $term, 'runpace_distance' ) ) {
			wp_insert_term( $term, 'runpace_distance' );
		}
	}

	// Event type terms.
	$event_types = [ 'Road', 'Trail', 'Ultra', 'Track', 'Virtual' ];
	foreach ( $event_types as $term ) {
		if ( ! term_exists( $term, 'runpace_event_type' ) ) {
			wp_insert_term( $term, 'runpace_event_type' );
		}
	}

	// Difficulty terms.
	$difficulties = [ 'Beginner', 'Intermediate', 'Advanced' ];
	foreach ( $difficulties as $term ) {
		if ( ! term_exists( $term, 'runpace_difficulty' ) ) {
			wp_insert_term( $term, 'runpace_difficulty' );
		}
	}

	// Goal terms (mirrors Distance for consistency).
	$goals = [ '5K', '10K', 'Half marathon', 'Marathon', 'Ultra', 'General fitness' ];
	foreach ( $goals as $term ) {
		if ( ! term_exists( $term, 'runpace_goal' ) ) {
			wp_insert_term( $term, 'runpace_goal' );
		}
	}
}
add_action( 'after_switch_theme', 'runpace_insert_default_terms' );
