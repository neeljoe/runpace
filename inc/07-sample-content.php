<?php
/**
 * RunPace – Sample Content Seeder
 *
 * Creates sample marathons, training plans, and default taxonomy terms
 * on theme activation (idempotent — runs only once per install).
 *
 * Triggered by: WP-CLI  →  wp eval 'runpace_seed_sample_content();'
 *               Or automatically via the after_switch_theme hook
 *               (gated by a transient so it runs only once).
 *
 * To re-seed (dev only): delete the 'runpace_seeded' option then reactivate.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Gate: only run once ───────────────────────────────────────────────────────

add_action(
	'after_switch_theme',
	static function (): void {
		if ( get_option( 'runpace_content_seeded' ) ) {
			return;
		}
		runpace_seed_sample_content();
		update_option( 'runpace_content_seeded', '1' );
	}
);

// ─── Main seeder ──────────────────────────────────────────────────────────────

/**
 * Entry point — seeds marathons, training plans, and a blog post.
 * Safe to call from WP-CLI; each inner function checks for duplicates.
 */
function runpace_seed_sample_content(): void {

	runpace_seed_marathons();
	runpace_seed_training_plans();
	runpace_seed_blog_post();
}

// ─── Marathons ─────────────────────────────────────────────────────────────────

/**
 * Sample marathon data.
 *
 * @return array<int,array<string,mixed>>
 */
function runpace_sample_marathons(): array {
	return [
		[
			'title'       => 'Berlin Marathon 2026',
			'content'     => '<p>One of the six World Marathon Majors, the Berlin Marathon is renowned for its flat course through the heart of Germany\'s capital. Fast times, incredible atmosphere, and an iconic finish at the Brandenburg Gate make this a bucket-list race for runners worldwide.</p>',
			'excerpt'     => 'Flat, fast, and iconic. One of the World Marathon Majors through the heart of Berlin.',
			'meta'        => [
				'_runpace_race_date'         => '2026-09-27',
				'_runpace_city'              => 'Berlin',
				'_runpace_country'           => 'Germany',
				'_runpace_distance_label'    => '42.195 km',
				'_runpace_registration_url'  => 'https://www.bmw-berlin-marathon.com/',
				'_runpace_website_url'       => 'https://www.bmw-berlin-marathon.com/',
				'_runpace_price'             => 135,
				'_runpace_elevation_gain'    => 88,
				'_runpace_difficulty_rating' => 1,
				'_runpace_is_featured'       => true,
				'_runpace_participant_limit' => 60000,
			],
			'distance'    => 'Marathon',
			'event_type'  => 'Road',
		],
		[
			'title'       => 'Tokyo Marathon 2026',
			'content'     => '<p>Running through the heart of one of the world\'s greatest cities, the Tokyo Marathon offers an unforgettable experience past temples, skyscrapers, and cherry blossom parks. A World Marathon Major with world-class organisation and enthusiastic local support.</p>',
			'excerpt'     => 'World Marathon Major through the heart of Tokyo. Cherry blossoms optional.',
			'meta'        => [
				'_runpace_race_date'         => '2026-03-01',
				'_runpace_city'              => 'Tokyo',
				'_runpace_country'           => 'Japan',
				'_runpace_distance_label'    => '42.195 km',
				'_runpace_registration_url'  => 'https://www.marathon.tokyo/',
				'_runpace_website_url'       => 'https://www.marathon.tokyo/',
				'_runpace_price'             => 120,
				'_runpace_elevation_gain'    => 102,
				'_runpace_difficulty_rating' => 2,
				'_runpace_is_featured'       => true,
				'_runpace_participant_limit' => 38000,
			],
			'distance'    => 'Marathon',
			'event_type'  => 'Road',
		],
		[
			'title'       => 'Cape Town Marathon 2026',
			'content'     => '<p>Set against the backdrop of Table Mountain, the Cape Town Marathon is Africa\'s only World Athletics Platinum Label road race. The scenic out-and-back course hugs the Atlantic seaboard for unforgettable views on every kilometre.</p>',
			'excerpt'     => 'Africa\'s only Platinum Label road race with Table Mountain as your backdrop.',
			'meta'        => [
				'_runpace_race_date'         => '2026-10-18',
				'_runpace_city'              => 'Cape Town',
				'_runpace_country'           => 'South Africa',
				'_runpace_distance_label'    => '42.195 km',
				'_runpace_registration_url'  => 'https://www.capetownmarathon.com/',
				'_runpace_website_url'       => 'https://www.capetownmarathon.com/',
				'_runpace_price'             => 75,
				'_runpace_elevation_gain'    => 185,
				'_runpace_difficulty_rating' => 2,
				'_runpace_is_featured'       => false,
				'_runpace_participant_limit' => 25000,
			],
			'distance'    => 'Marathon',
			'event_type'  => 'Road',
		],
		[
			'title'       => 'UTMB Mont-Blanc 2026',
			'content'     => '<p>The Ultra-Trail du Mont-Blanc is the pinnacle of trail running. This 171 km loop around the Mont-Blanc massif passes through France, Italy, and Switzerland with over 10,000 m of positive elevation. Only the most prepared runners attempt this iconic challenge.</p>',
			'excerpt'     => '171 km around Mont-Blanc through three countries. The ultimate trail challenge.',
			'meta'        => [
				'_runpace_race_date'         => '2026-08-28',
				'_runpace_city'              => 'Chamonix',
				'_runpace_country'           => 'France',
				'_runpace_distance_label'    => '171 km',
				'_runpace_registration_url'  => 'https://utmb.world/',
				'_runpace_website_url'       => 'https://utmb.world/',
				'_runpace_price'             => 690,
				'_runpace_elevation_gain'    => 10000,
				'_runpace_difficulty_rating' => 5,
				'_runpace_is_featured'       => true,
				'_runpace_participant_limit' => 2300,
			],
			'distance'    => 'Ultra',
			'event_type'  => 'Trail',
		],
		[
			'title'       => 'Valencia Half Marathon 2026',
			'content'     => '<p>Valencia is one of the fastest half marathon courses in the world — a flat, point-to-point route finishing in the city centre. Perfect for a personal best attempt on a course that regularly produces world-class times.</p>',
			'excerpt'     => 'One of the world\'s fastest half marathon courses — ideal for a PB attempt.',
			'meta'        => [
				'_runpace_race_date'         => '2026-10-25',
				'_runpace_city'              => 'Valencia',
				'_runpace_country'           => 'Spain',
				'_runpace_distance_label'    => '21.097 km',
				'_runpace_registration_url'  => 'https://valenciamarathon.es/',
				'_runpace_website_url'       => 'https://valenciamarathon.es/',
				'_runpace_price'             => 45,
				'_runpace_elevation_gain'    => 42,
				'_runpace_difficulty_rating' => 1,
				'_runpace_is_featured'       => false,
				'_runpace_participant_limit' => 18000,
			],
			'distance'    => 'Half marathon',
			'event_type'  => 'Road',
		],
		[
			'title'       => 'Comrades Marathon 2026',
			'content'     => '<p>The world\'s largest and oldest ultra marathon — 89 km between Pietermaritzburg and Durban in South Africa. The Comrades is as much a cultural institution as it is a race, drawing over 25,000 runners each year.',
			'excerpt'     => 'The world\'s largest ultra: 89 km through the KwaZulu-Natal Midlands.',
			'meta'        => [
				'_runpace_race_date'         => '2026-06-14',
				'_runpace_city'              => 'Durban',
				'_runpace_country'           => 'South Africa',
				'_runpace_distance_label'    => '89 km',
				'_runpace_registration_url'  => 'https://www.comrades.com/',
				'_runpace_website_url'       => 'https://www.comrades.com/',
				'_runpace_price'             => 95,
				'_runpace_elevation_gain'    => 1850,
				'_runpace_difficulty_rating' => 4,
				'_runpace_is_featured'       => false,
				'_runpace_participant_limit' => 25000,
			],
			'distance'    => 'Ultra',
			'event_type'  => 'Road',
		],
	];
}

/**
 * Insert sample marathons (skips any with matching title).
 */
function runpace_seed_marathons(): void {

	foreach ( runpace_sample_marathons() as $data ) {

		// Skip if already exists.
		$existing = get_page_by_title( $data['title'], OBJECT, 'marathon' );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => 'marathon',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_content' => $data['content'],
				'post_excerpt' => $data['excerpt'],
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		// Meta.
		foreach ( $data['meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// Distance taxonomy.
		if ( ! empty( $data['distance'] ) ) {
			wp_set_object_terms( $post_id, $data['distance'], 'runpace_distance' );
		}

		// Event type taxonomy.
		if ( ! empty( $data['event_type'] ) ) {
			wp_set_object_terms( $post_id, $data['event_type'], 'runpace_event_type' );
		}
	}
}

// ─── Training Plans ────────────────────────────────────────────────────────────

/**
 * Sample training plan data.
 *
 * @return array<int,array<string,mixed>>
 */
function runpace_sample_training_plans(): array {
	return [
		[
			'title'      => 'Couch to 5K – 8 Week Plan',
			'content'    => '<p>This beginner-friendly plan takes you from no running background to finishing a 5K in just 8 weeks. Sessions alternate walking and running intervals, gradually building your aerobic base with just 3 sessions per week.</p><p>No equipment needed beyond a good pair of running shoes. Rest days are built in for recovery.</p>',
			'excerpt'    => 'From zero to 5K in 8 weeks. Perfect for complete beginners with just 3 sessions per week.',
			'meta'       => [
				'_runpace_duration_weeks'    => 8,
				'_runpace_peak_weekly_km'    => 20,
				'_runpace_sessions_per_week' => 3,
				'_runpace_level_label'       => 'Beginner',
				'_runpace_goal_label'        => '5K',
				'_runpace_is_free'           => true,
				'_runpace_download_url'      => '',
			],
			'difficulty' => 'Beginner',
			'goal'       => '5K',
		],
		[
			'title'      => 'Sub-4 Hour Marathon – 16 Week Plan',
			'content'    => '<p>A structured 16-week build targeting a sub-4 hour marathon finish. Combines easy runs, tempo intervals, long runs up to 35 km, and one key speed session per week. Requires a comfortable 10K base before starting.</p><p>Peak week hits 70 km. Plan includes a 3-week taper.</p>',
			'excerpt'    => 'Structured 16-week marathon plan targeting a sub-4 finish with 5 sessions per week.',
			'meta'       => [
				'_runpace_duration_weeks'    => 16,
				'_runpace_peak_weekly_km'    => 70,
				'_runpace_sessions_per_week' => 5,
				'_runpace_level_label'       => 'Intermediate',
				'_runpace_goal_label'        => 'Marathon',
				'_runpace_is_free'           => true,
				'_runpace_download_url'      => '',
			],
			'difficulty' => 'Intermediate',
			'goal'       => 'Marathon',
		],
		[
			'title'      => 'Advanced Ultra Trail – 24 Week Plan',
			'content'    => '<p>An elite-level 24-week programme for experienced runners targeting ultra-distance trail races (50 km–100 km). Includes back-to-back long run weekends, vertical gain progression, race-specific nutrition strategies, and a detailed 4-week taper.</p><p>Requires 80+ km/week base. Strength and mobility sessions included.</p>',
			'excerpt'    => '24-week ultra trail plan for experienced runners targeting 50–100 km events.',
			'meta'       => [
				'_runpace_duration_weeks'    => 24,
				'_runpace_peak_weekly_km'    => 130,
				'_runpace_sessions_per_week' => 7,
				'_runpace_level_label'       => 'Advanced',
				'_runpace_goal_label'        => 'Ultra',
				'_runpace_is_free'           => false,
				'_runpace_download_url'      => '',
			],
			'difficulty' => 'Advanced',
			'goal'       => 'Ultra',
		],
		[
			'title'      => 'Half Marathon PB – 12 Week Plan',
			'content'    => '<p>A 12-week plan designed to help runners with a solid half marathon base shave time off their personal best. Alternating threshold runs, race-pace intervals, and long runs up to 22 km keep the training varied and progressive.</p>',
			'excerpt'    => '12-week plan for half marathon runners chasing a personal best. 4 sessions per week.',
			'meta'       => [
				'_runpace_duration_weeks'    => 12,
				'_runpace_peak_weekly_km'    => 55,
				'_runpace_sessions_per_week' => 4,
				'_runpace_level_label'       => 'Intermediate',
				'_runpace_goal_label'        => 'Half marathon',
				'_runpace_is_free'           => true,
				'_runpace_download_url'      => '',
			],
			'difficulty' => 'Intermediate',
			'goal'       => 'Half marathon',
		],
	];
}

/**
 * Insert sample training plans (skips any with matching title).
 */
function runpace_seed_training_plans(): void {

	foreach ( runpace_sample_training_plans() as $data ) {

		$existing = get_page_by_title( $data['title'], OBJECT, 'training-plan' );
		if ( $existing ) {
			continue;
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => 'training-plan',
				'post_status'  => 'publish',
				'post_title'   => $data['title'],
				'post_content' => $data['content'],
				'post_excerpt' => $data['excerpt'],
			],
			true
		);

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		foreach ( $data['meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		if ( ! empty( $data['difficulty'] ) ) {
			wp_set_object_terms( $post_id, $data['difficulty'], 'runpace_difficulty' );
		}

		if ( ! empty( $data['goal'] ) ) {
			wp_set_object_terms( $post_id, $data['goal'], 'runpace_goal' );
		}
	}
}

// ─── Blog Post ─────────────────────────────────────────────────────────────────

/**
 * Insert a sample blog post about marathon training.
 */
function runpace_seed_blog_post(): void {

	$existing = get_page_by_title( 'How to Pick Your First Marathon', OBJECT, 'post' );
	if ( $existing ) {
		return;
	}

	wp_insert_post(
		[
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => 'How to Pick Your First Marathon',
			'post_excerpt' => 'With hundreds of marathons worldwide, choosing your first can be overwhelming. Here\'s a practical guide to finding the race that\'s right for you.',
			'post_content' => '<!-- wp:paragraph -->
<p>Running your first marathon is a life-changing experience, but with thousands of races worldwide, choosing the right one can feel overwhelming. Distance, climate, elevation profile, travel logistics, and crowd support all matter — and the ideal race depends entirely on your goals.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Start with the basics</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Before scrolling through race listings, answer these questions: Is this a bucket-list destination race, or do you want the convenience of a local event? Do you have at least 16 weeks to train from today? And crucially — what is your goal? Just finishing, or chasing a specific time?</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Flat or scenic?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Fast, flat courses like Berlin or Valencia are ideal if you\'re chasing a personal best. More scenic routes — think the UTMB or Coastal Trail races — trade speed for breathtaking landscapes and a different kind of satisfaction.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2 class="wp-block-heading">Allow enough training time</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Most beginner marathon plans require 16–20 weeks. Book your race so that you can start training with a solid base — ideally comfortable with 30–40 km per week before the plan begins. Rushing into a marathon undertrained is the number one cause of DNFs and injury.</p>
<!-- /wp:paragraph -->',
		]
	);
}