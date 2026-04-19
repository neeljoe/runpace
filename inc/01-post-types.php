<?php
/**
 * RunPace – Custom Post Types
 *
 * Registers the Marathon and Training Plan CPTs.
 * Both CPTs are block-editor-enabled and REST-API-exposed
 * so Block Bindings and the Interactivity API can consume them.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Marathon CPT ─────────────────────────────────────────────────────────────

/**
 * Register the Marathon post type.
 */
function runpace_register_cpt_marathon(): void {

	$labels = [
		'name'                  => _x( 'Marathons',              'Post type general name', 'runpace' ),
		'singular_name'         => _x( 'Marathon',               'Post type singular name', 'runpace' ),
		'menu_name'             => _x( 'Marathons',              'Admin menu text', 'runpace' ),
		'name_admin_bar'        => _x( 'Marathon',               'Add new on toolbar', 'runpace' ),
		'add_new'               => __( 'Add new',                'runpace' ),
		'add_new_item'          => __( 'Add new marathon',       'runpace' ),
		'new_item'              => __( 'New marathon',           'runpace' ),
		'edit_item'             => __( 'Edit marathon',          'runpace' ),
		'view_item'             => __( 'View marathon',          'runpace' ),
		'view_items'            => __( 'View marathons',         'runpace' ),
		'all_items'             => __( 'All marathons',          'runpace' ),
		'search_items'          => __( 'Search marathons',       'runpace' ),
		'parent_item_colon'     => __( 'Parent marathon:',       'runpace' ),
		'not_found'             => __( 'No marathons found.',    'runpace' ),
		'not_found_in_trash'    => __( 'No marathons in trash.', 'runpace' ),
		'featured_image'        => __( 'Race cover image',       'runpace' ),
		'set_featured_image'    => __( 'Set race cover image',   'runpace' ),
		'remove_featured_image' => __( 'Remove cover image',     'runpace' ),
		'use_featured_image'    => __( 'Use as cover image',     'runpace' ),
		'archives'              => __( 'Marathon archives',      'runpace' ),
		'insert_into_item'      => __( 'Insert into marathon',   'runpace' ),
		'uploaded_to_this_item' => __( 'Uploaded to this marathon', 'runpace' ),
		'filter_items_list'     => __( 'Filter marathons list',  'runpace' ),
		'items_list_navigation' => __( 'Marathons list navigation', 'runpace' ),
		'items_list'            => __( 'Marathons list',         'runpace' ),
	];

	$args = [
		'labels'             => $labels,
		'description'        => __( 'Running events: marathons, half marathons, trail runs and ultras.', 'runpace' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'show_in_rest'       => true,   // Required: block editor + REST API.
		'rest_base'          => 'marathons',
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-location-alt',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'supports'           => [
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'custom-fields', // Required for Block Bindings API (Phase 4).
			'revisions',
		],
		'taxonomies'         => [
			'runpace_distance',
			'runpace_location',
			'runpace_event_type',
		],
		'has_archive'        => 'marathons',
		'rewrite'            => [
			'slug'       => 'marathons',
			'with_front' => false,
			'feeds'      => true,
			'pages'      => true,
		],
		'query_var'          => true,
		'can_export'         => true,
		'delete_with_user'   => false,
	];

	register_post_type( 'marathon', $args );
}
add_action( 'init', 'runpace_register_cpt_marathon' );

// ─── Training Plan CPT ────────────────────────────────────────────────────────

/**
 * Register the Training Plan post type.
 */
function runpace_register_cpt_training_plan(): void {

	$labels = [
		'name'                  => _x( 'Training Plans',                'Post type general name', 'runpace' ),
		'singular_name'         => _x( 'Training Plan',                 'Post type singular name', 'runpace' ),
		'menu_name'             => _x( 'Training Plans',                'Admin menu text', 'runpace' ),
		'name_admin_bar'        => _x( 'Training Plan',                 'Add new on toolbar', 'runpace' ),
		'add_new'               => __( 'Add new',                       'runpace' ),
		'add_new_item'          => __( 'Add new training plan',         'runpace' ),
		'new_item'              => __( 'New training plan',             'runpace' ),
		'edit_item'             => __( 'Edit training plan',            'runpace' ),
		'view_item'             => __( 'View training plan',            'runpace' ),
		'view_items'            => __( 'View training plans',           'runpace' ),
		'all_items'             => __( 'All training plans',            'runpace' ),
		'search_items'          => __( 'Search training plans',         'runpace' ),
		'not_found'             => __( 'No training plans found.',      'runpace' ),
		'not_found_in_trash'    => __( 'No training plans in trash.',   'runpace' ),
		'featured_image'        => __( 'Plan cover image',              'runpace' ),
		'set_featured_image'    => __( 'Set plan cover image',          'runpace' ),
		'remove_featured_image' => __( 'Remove cover image',            'runpace' ),
		'archives'              => __( 'Training plan archives',        'runpace' ),
		'insert_into_item'      => __( 'Insert into plan',              'runpace' ),
		'uploaded_to_this_item' => __( 'Uploaded to this plan',         'runpace' ),
		'filter_items_list'     => __( 'Filter training plans list',    'runpace' ),
		'items_list_navigation' => __( 'Training plans list navigation','runpace' ),
		'items_list'            => __( 'Training plans list',           'runpace' ),
	];

	$args = [
		'labels'             => $labels,
		'description'        => __( 'Structured training plans for all distances and experience levels.', 'runpace' ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_admin_bar'  => true,
		'show_in_rest'       => true,
		'rest_base'          => 'training-plans',
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-clipboard',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'supports'           => [
			'title',
			'editor',
			'thumbnail',
			'excerpt',
			'custom-fields',
			'revisions',
		],
		'taxonomies'         => [
			'runpace_difficulty',
			'runpace_goal',
		],
		'has_archive'        => 'training-plans',
		'rewrite'            => [
			'slug'       => 'training-plans',
			'with_front' => false,
			'feeds'      => false,
			'pages'      => true,
		],
		'query_var'          => true,
		'can_export'         => true,
		'delete_with_user'   => false,
	];

	register_post_type( 'training-plan', $args );
}
add_action( 'init', 'runpace_register_cpt_training_plan' );

// ─── Flush rewrite rules on activation ───────────────────────────────────────

/**
 * Flush rewrite rules when the theme is first activated.
 * This ensures the CPT slugs (marathons/, training-plans/) work immediately.
 */
function runpace_flush_rewrite_rules(): void {
	runpace_register_cpt_marathon();
	runpace_register_cpt_training_plan();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'runpace_flush_rewrite_rules' );
