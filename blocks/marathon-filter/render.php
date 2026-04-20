<?php
/**
 * Marathon Filter Block – render.php
 *
 * Server-side render for runpace/marathon-filter.
 * Uses the WordPress Interactivity API (data-wp-* directives) for:
 *   – Distance / location / date filtering
 *   – Grid ↔ List view toggle
 *   – Load more pagination
 *
 * Initial state is primed here via wp_interactivity_state() so the
 * server-rendered HTML matches what JavaScript will hydrate.
 *
 * @package RunPace
 */

declare( strict_types=1 );

$posts_per_page         = (int) ( $attributes['postsPerPage']        ?? 9 );
$show_distance_filter   = (bool) ( $attributes['showDistanceFilter'] ?? true );
$show_location_filter   = (bool) ( $attributes['showLocationFilter'] ?? true );
$show_date_filter       = (bool) ( $attributes['showDateFilter']     ?? true );
$show_view_toggle       = (bool) ( $attributes['showViewToggle']     ?? true );
$default_view           = $attributes['defaultView'] ?? 'grid';

// ── Fetch all published marathons ─────────────────────────────────────────────
$query = new WP_Query(
	[
		'post_type'      => 'marathon',
		'post_status'    => 'publish',
		'posts_per_page' => 100,
		'meta_key'       => '_runpace_race_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
	]
);

$all_marathons   = [];
$distance_set    = [];
$location_set    = [];

foreach ( $query->posts as $post ) {
	$id        = $post->ID;
	$race_date = get_post_meta( $id, '_runpace_race_date', true );
	$city      = get_post_meta( $id, '_runpace_city', true );
	$country   = get_post_meta( $id, '_runpace_country', true );

	$dist_terms    = get_the_terms( $id, 'runpace_distance' );
	$loc_terms     = get_the_terms( $id, 'runpace_location' );
	$event_terms   = get_the_terms( $id, 'runpace_event_type' );
	$dist_slugs    = ( $dist_terms && ! is_wp_error( $dist_terms ) ) ? wp_list_pluck( $dist_terms, 'slug' ) : [];
	$dist_names    = ( $dist_terms && ! is_wp_error( $dist_terms ) ) ? wp_list_pluck( $dist_terms, 'name' ) : [];
	$event_names   = ( $event_terms && ! is_wp_error( $event_terms ) ) ? wp_list_pluck( $event_terms, 'name' ) : [];

	$thumb_url   = get_the_post_thumbnail_url( $id, 'runpace-card' );
	$permalink   = get_permalink( $id );
	$race_ts     = $race_date ? strtotime( $race_date ) : 0;
	$is_past     = $race_ts > 0 && $race_ts < time();
	$days_to_go  = ( ! $is_past && $race_ts > 0 ) ? max( 0, (int) ceil( ( $race_ts - time() ) / DAY_IN_SECONDS ) ) : 0;

	$all_marathons[] = [
		'id'           => $id,
		'title'        => get_the_title( $id ),
		'excerpt'      => wp_trim_words( get_the_excerpt( $id ), 15, '…' ),
		'permalink'    => $permalink,
		'thumbUrl'     => $thumb_url ?: '',
		'date'         => $race_date,
		'formattedDate'=> $race_ts ? date_i18n( get_option( 'date_format' ), $race_ts ) : '',
		'city'         => $city,
		'country'      => $country,
		'distanceSlugs'=> $dist_slugs,
		'distanceNames'=> $dist_names,
		'eventTypes'   => $event_names,
		'price'        => (float) get_post_meta( $id, '_runpace_price', true ),
		'isPast'       => $is_past,
		'daysToGo'     => $days_to_go,
		'isFeatured'   => (bool) get_post_meta( $id, '_runpace_is_featured', true ),
	];

	// Build unique distance / location option sets.
	foreach ( $dist_names as $dist ) {
		$distance_set[ $dist ] = $dist;
	}
	if ( $country ) {
		$location_set[ $country ] = $country;
	}
}

ksort( $distance_set );
asort( $location_set );

// ── Prime server-side Interactivity state ─────────────────────────────────────
wp_interactivity_state(
	'runpace/marathon-filter',
	[
		'allMarathons'      => $all_marathons,
		'activeDistance'    => '',
		'activeLocation'    => '',
		'activeDateFilter'  => 'upcoming',
		'viewMode'          => $default_view,
		'visibleCount'      => $posts_per_page,
		'pageSize'          => $posts_per_page,
		'totalFiltered'     => count( array_filter( $all_marathons, static fn( $m ) => ! $m['isPast'] ) ),
	]
);

// ── Build a context for the initial visible items ─────────────────────────────
$initial_items = array_slice(
	array_filter( $all_marathons, static fn( $m ) => ! $m['isPast'] ),
	0,
	$posts_per_page
);

$wrapper_attributes = get_block_wrapper_attributes(
	[ 'class' => 'runpace-marathon-filter' ]
);
?>
<div
	<?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	data-wp-interactive="runpace/marathon-filter"
	<?php echo wp_interactivity_data_wp_context( [ 'initialized' => false ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
>

	<!-- ── Filter Controls ─────────────────────────────────────────────── -->
	<div class="runpace-mf__controls" role="search" aria-label="<?php esc_attr_e( 'Filter marathons', 'runpace' ); ?>">

		<div class="runpace-mf__filters">

			<?php if ( $show_distance_filter && $distance_set ) : ?>
			<div class="runpace-mf__filter-group">
				<label class="runpace-mf__filter-label" for="runpace-filter-distance">
					<?php esc_html_e( 'Distance', 'runpace' ); ?>
				</label>
				<select
					id="runpace-filter-distance"
					class="runpace-mf__select"
					data-wp-on--change="actions.setDistance"
					data-wp-bind--value="state.activeDistance"
					aria-label="<?php esc_attr_e( 'Filter by distance', 'runpace' ); ?>"
				>
					<option value=""><?php esc_html_e( 'All distances', 'runpace' ); ?></option>
					<?php foreach ( $distance_set as $dist ) : ?>
						<option value="<?php echo esc_attr( $dist ); ?>">
							<?php echo esc_html( $dist ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>

			<?php if ( $show_location_filter && $location_set ) : ?>
			<div class="runpace-mf__filter-group">
				<label class="runpace-mf__filter-label" for="runpace-filter-location">
					<?php esc_html_e( 'Country', 'runpace' ); ?>
				</label>
				<select
					id="runpace-filter-location"
					class="runpace-mf__select"
					data-wp-on--change="actions.setLocation"
					data-wp-bind--value="state.activeLocation"
					aria-label="<?php esc_attr_e( 'Filter by location', 'runpace' ); ?>"
				>
					<option value=""><?php esc_html_e( 'All countries', 'runpace' ); ?></option>
					<?php foreach ( $location_set as $country ) : ?>
						<option value="<?php echo esc_attr( $country ); ?>">
							<?php echo esc_html( $country ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>

			<?php if ( $show_date_filter ) : ?>
			<div class="runpace-mf__filter-group">
				<label class="runpace-mf__filter-label" for="runpace-filter-date">
					<?php esc_html_e( 'When', 'runpace' ); ?>
				</label>
				<select
					id="runpace-filter-date"
					class="runpace-mf__select"
					data-wp-on--change="actions.setDateFilter"
					data-wp-bind--value="state.activeDateFilter"
					aria-label="<?php esc_attr_e( 'Filter by date', 'runpace' ); ?>"
				>
					<option value="upcoming"><?php esc_html_e( 'Upcoming', 'runpace' ); ?></option>
					<option value="all"><?php esc_html_e( 'All events', 'runpace' ); ?></option>
					<option value="past"><?php esc_html_e( 'Past events', 'runpace' ); ?></option>
				</select>
			</div>
			<?php endif; ?>

		</div>

		<div class="runpace-mf__toolbar">

			<!-- Active filter chips -->
			<div class="runpace-mf__active-filters" aria-live="polite">
				<span
					class="runpace-mf__chip"
					data-wp-bind--hidden="!state.activeDistance"
					hidden
				>
					<span data-wp-text="state.activeDistance"></span>
					<button
						class="runpace-mf__chip-remove"
						data-wp-on--click="actions.clearDistance"
						aria-label="<?php esc_attr_e( 'Clear distance filter', 'runpace' ); ?>"
					>×</button>
				</span>
				<span
					class="runpace-mf__chip"
					data-wp-bind--hidden="!state.activeLocation"
					hidden
				>
					<span data-wp-text="state.activeLocation"></span>
					<button
						class="runpace-mf__chip-remove"
						data-wp-on--click="actions.clearLocation"
						aria-label="<?php esc_attr_e( 'Clear location filter', 'runpace' ); ?>"
					>×</button>
				</span>
			</div>

			<!-- Results count -->
			<p class="runpace-mf__count" aria-live="polite">
				<span data-wp-text="state.filteredCountLabel"></span>
			</p>

			<?php if ( $show_view_toggle ) : ?>
			<!-- Grid / list toggle -->
			<div class="runpace-mf__view-toggle" role="group" aria-label="<?php esc_attr_e( 'View mode', 'runpace' ); ?>">
				<button
					class="runpace-mf__view-btn"
					data-wp-on--click="actions.setGridView"
					data-wp-class--is-active="state.isGridView"
					aria-pressed="true"
					aria-label="<?php esc_attr_e( 'Grid view', 'runpace' ); ?>"
					title="<?php esc_attr_e( 'Grid view', 'runpace' ); ?>"
				>
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
						<rect x="0" y="0" width="7" height="7"/><rect x="9" y="0" width="7" height="7"/>
						<rect x="0" y="9" width="7" height="7"/><rect x="9" y="9" width="7" height="7"/>
					</svg>
				</button>
				<button
					class="runpace-mf__view-btn"
					data-wp-on--click="actions.setListView"
					data-wp-class--is-active="state.isListView"
					aria-pressed="false"
					aria-label="<?php esc_attr_e( 'List view', 'runpace' ); ?>"
					title="<?php esc_attr_e( 'List view', 'runpace' ); ?>"
				>
					<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
						<rect x="0" y="1" width="16" height="2"/><rect x="0" y="6" width="16" height="2"/>
						<rect x="0" y="11" width="16" height="2"/>
					</svg>
				</button>
			</div>
			<?php endif; ?>

		</div>

	</div>
	<!-- /Filter Controls -->

	<!-- ── Marathon Cards ──────────────────────────────────────────────── -->
	<div
		class="runpace-mf__results"
		data-wp-class--is-list="state.isListView"
		data-wp-class--is-grid="state.isGridView"
		role="list"
		aria-label="<?php esc_attr_e( 'Marathon listings', 'runpace' ); ?>"
	>

		<?php if ( $initial_items ) : ?>
			<?php foreach ( $initial_items as $marathon ) : ?>
			<?php
			$card_context = [
				'postId'    => $marathon['id'],
				'isVisible' => true,
			];
			?>
			<article
				class="runpace-mf__card<?php echo $marathon['isFeatured'] ? ' is-featured' : ''; ?><?php echo $marathon['isPast'] ? ' is-past' : ''; ?>"
				role="listitem"
				data-marathon-id="<?php echo esc_attr( (string) $marathon['id'] ); ?>"
				data-distance="<?php echo esc_attr( implode( ',', $marathon['distanceNames'] ) ); ?>"
				data-country="<?php echo esc_attr( $marathon['country'] ); ?>"
				data-is-past="<?php echo $marathon['isPast'] ? 'true' : 'false'; ?>"
			>
				<?php if ( $marathon['thumbUrl'] ) : ?>
				<a href="<?php echo esc_url( $marathon['permalink'] ); ?>" class="runpace-mf__card-img-wrap" tabindex="-1" aria-hidden="true">
					<img
						src="<?php echo esc_url( $marathon['thumbUrl'] ); ?>"
						alt=""
						class="runpace-mf__card-img"
						loading="lazy"
						decoding="async"
					/>
					<?php if ( $marathon['isFeatured'] ) : ?>
					<span class="runpace-mf__featured-badge"><?php esc_html_e( 'Featured', 'runpace' ); ?></span>
					<?php endif; ?>
				</a>
				<?php endif; ?>

				<div class="runpace-mf__card-body">
					<div class="runpace-mf__card-meta">

						<?php if ( $marathon['distanceNames'] ) : ?>
						<span class="runpace-mf__distance-chip">
							<?php echo esc_html( implode( ' · ', $marathon['distanceNames'] ) ); ?>
						</span>
						<?php endif; ?>

						<?php if ( $marathon['formattedDate'] ) : ?>
						<span class="runpace-mf__date">
							<?php if ( $marathon['daysToGo'] > 0 ) : ?>
								<span class="runpace-mf__days-badge">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d = days */
											_n( '%d day', '%d days', $marathon['daysToGo'], 'runpace' ),
											$marathon['daysToGo']
										)
									);
									?>
								</span>
							<?php endif; ?>
							<?php echo esc_html( $marathon['formattedDate'] ); ?>
						</span>
						<?php endif; ?>

					</div>

					<h3 class="runpace-mf__card-title">
						<a href="<?php echo esc_url( $marathon['permalink'] ); ?>">
							<?php echo esc_html( $marathon['title'] ); ?>
						</a>
					</h3>

					<?php if ( $marathon['city'] || $marathon['country'] ) : ?>
					<p class="runpace-mf__card-location">
						<svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" aria-hidden="true">
							<path d="M6 0a4 4 0 0 0-4 4c0 3 4 8 4 8s4-5 4-8a4 4 0 0 0-4-4zm0 5.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/>
						</svg>
						<?php echo esc_html( implode( ', ', array_filter( [ $marathon['city'], $marathon['country'] ] ) ) ); ?>
					</p>
					<?php endif; ?>

					<?php if ( $marathon['excerpt'] ) : ?>
					<p class="runpace-mf__card-excerpt"><?php echo esc_html( $marathon['excerpt'] ); ?></p>
					<?php endif; ?>

					<div class="runpace-mf__card-footer">
						<span class="runpace-mf__price">
							<?php echo $marathon['price'] > 0 ? esc_html( '$' . number_format( $marathon['price'], 0 ) ) : esc_html__( 'Free', 'runpace' ); ?>
						</span>
						<a href="<?php echo esc_url( $marathon['permalink'] ); ?>" class="runpace-mf__card-link">
							<?php esc_html_e( 'View race', 'runpace' ); ?>
							<span aria-hidden="true">→</span>
						</a>
					</div>
				</div>
			</article>
			<?php endforeach; ?>
		<?php else : ?>
			<div class="runpace-mf__empty">
				<p><?php esc_html_e( 'No marathons found. Try adjusting your filters.', 'runpace' ); ?></p>
			</div>
		<?php endif; ?>

	</div>
	<!-- /Marathon Cards -->

	<!-- ── Empty state (shown by JS when no results) ───────────────────── -->
	<div
		class="runpace-mf__no-results"
		data-wp-bind--hidden="state.hasResults"
		hidden
		aria-live="polite"
	>
		<div class="runpace-mf__no-results-inner">
			<span class="runpace-mf__no-results-icon" aria-hidden="true">🏃</span>
			<p><?php esc_html_e( 'No marathons match your filters.', 'runpace' ); ?></p>
			<button class="wp-element-button" data-wp-on--click="actions.clearAllFilters">
				<?php esc_html_e( 'Clear all filters', 'runpace' ); ?>
			</button>
		</div>
	</div>

	<!-- ── Load More ──────────────────────────────────────────────────── -->
	<div class="runpace-mf__load-more-wrap">
		<button
			class="runpace-mf__load-more wp-element-button"
			data-wp-on--click="actions.loadMore"
			data-wp-bind--hidden="state.allLoaded"
			data-wp-class--is-loading="state.isLoading"
			aria-label="<?php esc_attr_e( 'Load more marathons', 'runpace' ); ?>"
		>
			<span class="runpace-mf__load-more-text"><?php esc_html_e( 'Load more', 'runpace' ); ?></span>
			<span class="runpace-mf__load-more-spinner" aria-hidden="true"></span>
		</button>
	</div>

</div>