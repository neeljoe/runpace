<?php
/**
 * RunPace – Sample Content Seeder
 *
 * Creates sample Marathons and Training Plans on theme activation
 * so the site has something to show immediately after install.
 *
 * Only runs when WP_DEBUG is true OR when explicitly triggered via
 * the admin action ?runpace_seed=1 (admin-only, nonce-protected).
 *
 * Safe to run multiple times — checks post existence before inserting.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Admin trigger ────────────────────────────────────────────────────────────

/**
 * Allow manual seeding from wp-admin via ?runpace_seed=1.
 */
function runpace_seed_admin_trigger(): void {

	if ( ! isset( $_GET['runpace_seed'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	check_admin_referer( 'runpace_seed' );

	runpace_create_sample_content();

	wp_safe_redirect( admin_url( 'edit.php?post_type=marathon&seeded=1' ) );
	exit;
}
add_action( 'admin_init', 'runpace_seed_admin_trigger' );

/**
 * Show a seed notice + button in the admin.
 */
function runpace_seed_notice(): void {

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, [ 'marathon', 'training-plan' ], true ) ) {
		return;
	}

	if ( isset( $_GET['seeded'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		echo '<div class="notice notice-success is-dismissible"><p>✅ RunPace sample content created!</p></div>';
		return;
	}

	$seed_url = wp_nonce_url(
		add_query_arg( 'runpace_seed', '1', admin_url( 'admin.php' ) ),
		'runpace_seed'
	);

	echo '<div class="notice notice-info"><p>';
	printf(
		/* translators: %s = seed link */
		esc_html__( 'RunPace: No content yet? %s', 'runpace' ),
		'<a href="' . esc_url( $seed_url ) . '">' . esc_html__( 'Create sample content →', 'runpace' ) . '</a>'
	);
	echo '</p></div>';
}
add_action( 'admin_notices', 'runpace_seed_notice' );

// ─── Seeder ───────────────────────────────────────────────────────────────────

/**
 * Create all sample posts.
 */
function runpace_create_sample_content(): void {
	runpace_seed_marathons();
	runpace_seed_training_plans();
}

/**
 * Insert sample marathons.
 */
function runpace_seed_marathons(): void {

	$marathons = [
		[
			'title'        => 'Berlin Marathon',
			'city'         => 'Berlin',
			'country'      => 'Germany',
			'race_date'    => date( 'Y-m-d', strtotime( '+120 days' ) ),
			'price'        => 120,
			'elevation'    => 80,
			'difficulty'   => 2,
			'distance'     => 'Marathon',
			'event_type'   => 'Road',
			'reg_url'      => 'https://www.bmw-berlin-marathon.com',
			'featured'     => true,
			'excerpt'      => 'One of the world\'s fastest and most iconic city marathons. The flat course through Berlin\'s historic streets makes it a favourite for PBs.',
		],
		[
			'title'        => 'Boston Marathon',
			'city'         => 'Boston',
			'country'      => 'United States',
			'race_date'    => date( 'Y-m-d', strtotime( '+200 days' ) ),
			'price'        => 215,
			'elevation'    => 450,
			'difficulty'   => 4,
			'distance'     => 'Marathon',
			'event_type'   => 'Road',
			'reg_url'      => 'https://www.baa.org',
			'featured'     => true,
			'excerpt'      => 'The world\'s oldest annual marathon and one of the six World Marathon Majors. Qualification required.',
		],
		[
			'title'        => 'Tokyo Marathon',
			'city'         => 'Tokyo',
			'country'      => 'Japan',
			'race_date'    => date( 'Y-m-d', strtotime( '+90 days' ) ),
			'price'        => 180,
			'elevation'    => 100,
			'difficulty'   => 2,
			'distance'     => 'Marathon',
			'event_type'   => 'Road',
			'reg_url'      => 'https://www.tokyomarathon.jp',
			'featured'     => true,
			'excerpt'      => 'Run through the heart of Tokyo past landmarks like Sensō-ji Temple and Tokyo Tower.',
		],
		[
			'title'        => 'Ultra Trail du Mont-Blanc',
			'city'         => 'Chamonix',
			'country'      => 'France',
			'race_date'    => date( 'Y-m-d', strtotime( '+300 days' ) ),
			'price'        => 350,
			'elevation'    => 10000,
			'difficulty'   => 5,
			'distance'     => 'Ultra',
			'event_type'   => 'Trail',
			'reg_url'      => 'https://utmbmontblanc.com',
			'featured'     => false,
			'excerpt'      => '170km around the Mont Blanc massif through France, Italy, and Switzerland. The ultimate mountain ultra.',
		],
		[
			'title'        => 'London Half Marathon',
			'city'         => 'London',
			'country'      => 'United Kingdom',
			'race_date'    => date( 'Y-m-d', strtotime( '+60 days' ) ),
			'price'        => 65,
			'elevation'    => 120,
			'difficulty'   => 2,
			'distance'     => 'Half marathon',
			'event_type'   => 'Road',
			'reg_url'      => 'https://www.londonmarathon.co.uk',
			'featured'     => false,
			'excerpt'      => 'A scenic half marathon through London\'s iconic streets and parklands. Perfect for those stepping up from 10K.',
		],
		[
			'title'        => 'Sydney 10K',
			'city'         => 'Sydney',
			'country'      => 'Australia',
			'race_date'    => date( 'Y-m-d', strtotime( '+45 days' ) ),
			'price'        => 55,
			'elevation'    => 60,
			'difficulty'   => 1,
			'distance'     => '10K',
			'event_type'   => 'Road',
			'reg_url'      => 'https://www.sydneyrunningfestival.com.au',
			'featured'     => false,
			'excerpt'      => 'Run past the Opera House and Harbour Bridge on one of the world\'s most beautiful running routes.',
		],
	];

	foreach ( $marathons as $data ) {
		// Skip if already exists.
		$existing = get_page_by_title( $data['title'], OBJECT, 'marathon' ); // phpcs:ignore WordPress.WP.DeprecatedFunctions
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => 'marathon',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_excerpt' => $data['excerpt'],
				'post_content' => sprintf(
					'<!-- wp:paragraph --><p>%s is a world-class running event. Check the official website for full details and registration.</p><!-- /wp:paragraph -->',
					esc_html( $data['title'] )
				),
			]
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		// Meta.
		update_post_meta( $post_id, '_runpace_race_date',       $data['race_date'] );
		update_post_meta( $post_id, '_runpace_city',            $data['city'] );
		update_post_meta( $post_id, '_runpace_country',         $data['country'] );
		update_post_meta( $post_id, '_runpace_price',           $data['price'] );
		update_post_meta( $post_id, '_runpace_elevation_gain',  $data['elevation'] );
		update_post_meta( $post_id, '_runpace_difficulty_rating', $data['difficulty'] );
		update_post_meta( $post_id, '_runpace_registration_url', $data['reg_url'] );
		update_post_meta( $post_id, '_runpace_is_featured',     $data['featured'] );
		update_post_meta( $post_id, '_runpace_distance_label',  $data['distance'] );

		// Taxonomies.
		$dist_term = get_term_by( 'name', $data['distance'], 'runpace_distance' );
		if ( $dist_term ) {
			wp_set_post_terms( $post_id, [ $dist_term->term_id ], 'runpace_distance' );
		}

		$event_term = get_term_by( 'name', $data['event_type'], 'runpace_event_type' );
		if ( $event_term ) {
			wp_set_post_terms( $post_id, [ $event_term->term_id ], 'runpace_event_type' );
		}
	}
}

/**
 * Insert sample training plans.
 */
function runpace_seed_training_plans(): void {

	$plans = [
		[
			'title'        => 'Couch to 5K – 8 Week Plan',
			'level'        => 'Beginner',
			'goal'         => '5K',
			'weeks'        => 8,
			'sessions'     => 3,
			'peak_km'      => 20,
			'is_free'      => true,
			'excerpt'      => 'Go from zero to 5K in just 8 weeks. Walk/run intervals make this accessible to complete beginners.',
		],
		[
			'title'        => 'Half Marathon 12-Week Plan',
			'level'        => 'Intermediate',
			'goal'         => 'Half marathon',
			'weeks'        => 12,
			'sessions'     => 4,
			'peak_km'      => 55,
			'is_free'      => true,
			'excerpt'      => 'Build your base and run your first (or fastest) half marathon with this structured 12-week plan.',
		],
		[
			'title'        => 'Marathon 16-Week Advanced Plan',
			'level'        => 'Advanced',
			'goal'         => 'Marathon',
			'weeks'        => 16,
			'sessions'     => 6,
			'peak_km'      => 90,
			'is_free'      => false,
			'excerpt'      => 'High-volume marathon prep with speed work, long runs, and race-pace training for sub-3:30 athletes.',
		],
		[
			'title'        => '10K Beginner 6-Week Plan',
			'level'        => 'Beginner',
			'goal'         => '10K',
			'weeks'        => 6,
			'sessions'     => 3,
			'peak_km'      => 30,
			'is_free'      => true,
			'excerpt'      => 'A simple 6-week plan to complete your first 10K. Run three times per week and cross the finish line feeling strong.',
		],
	];

	foreach ( $plans as $data ) {
		$existing = get_page_by_title( $data['title'], OBJECT, 'training-plan' ); // phpcs:ignore WordPress.WP.DeprecatedFunctions
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => 'training-plan',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_excerpt' => $data['excerpt'],
				'post_content' => sprintf(
					'<!-- wp:paragraph --><p>%s</p><!-- /wp:paragraph -->',
					esc_html( $data['excerpt'] )
				),
			]
		);

		if ( is_wp_error( $post_id ) || ! $post_id ) {
			continue;
		}

		update_post_meta( $post_id, '_runpace_duration_weeks',    $data['weeks'] );
		update_post_meta( $post_id, '_runpace_sessions_per_week', $data['sessions'] );
		update_post_meta( $post_id, '_runpace_peak_weekly_km',    $data['peak_km'] );
		update_post_meta( $post_id, '_runpace_level_label',       $data['level'] );
		update_post_meta( $post_id, '_runpace_goal_label',        $data['goal'] );
		update_post_meta( $post_id, '_runpace_is_free',           $data['is_free'] );

		$diff_term = get_term_by( 'name', $data['level'], 'runpace_difficulty' );
		if ( $diff_term ) {
			wp_set_post_terms( $post_id, [ $diff_term->term_id ], 'runpace_difficulty' );
		}

		$goal_term = get_term_by( 'name', $data['goal'], 'runpace_goal' );
		if ( $goal_term ) {
			wp_set_post_terms( $post_id, [ $goal_term->term_id ], 'runpace_goal' );
		}
	}
}