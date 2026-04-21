<?php
/**
 * RunPace – Query Loop Variation Filters
 *
 * Applies server-side meta/taxonomy filters for the three custom
 * Query Loop variations registered in assets/js/block-variations.js:
 *
 *   runpace/upcoming-marathons  → race_date >= today, ordered by race_date ASC
 *   runpace/featured-races      → _runpace_is_featured = 1
 *   runpace/related-races       → same runpace_distance terms, exclude current post
 *
 * Uses the 'query_loop_block_query_vars' filter (WP 6.1+) which receives
 * the parsed block query and the block instance, making it safe to narrow
 * only our namespaced variations.
 *
 * FIX: The related-races case previously called get_queried_object_id() to
 * find the current post. That function returns the queried object for the
 * current URL — not the post being rendered inside the Query Loop. When a
 * Query Loop is embedded on a page (not a single CPT URL), it would return
 * the page ID, silently filtering against the wrong post. We now read the
 * post ID from $block->context['postId'] which is always the post being
 * rendered by the surrounding post template.
 *
 * @package RunPace
 * @since   1.0.0
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modify Query Loop block queries for RunPace variations.
 *
 * @param  array    $query  WP_Query args to be merged.
 * @param  WP_Block $block  The current block instance.
 * @param  int      $page   Current page number.
 * @return array
 */
function runpace_query_loop_vars( array $query, WP_Block $block, int $page ): array {

	$namespace = $block->context['query']['namespace'] ?? '';

	switch ( $namespace ) {

		// ── Upcoming Marathons ─────────────────────────────────────────────────

		case 'runpace/upcoming-marathons':

			$query['post_type'] = 'marathon';

			// Only show races today or in the future.
			$today        = gmdate( 'Y-m-d' );
			$meta_query   = $query['meta_query'] ?? [];
			$meta_query[] = [
				'key'     => '_runpace_race_date',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			];
			$query['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
			$query['meta_key']   = '_runpace_race_date'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$query['orderby']    = 'meta_value';
			$query['order']      = 'ASC';
			break;

		// ── Featured Races ─────────────────────────────────────────────────────

		case 'runpace/featured-races':

			$query['post_type'] = 'marathon';

			$meta_query   = $query['meta_query'] ?? [];
			$meta_query[] = [
				'key'     => '_runpace_is_featured',
				'value'   => '1',
				'compare' => '=',
			];
			$query['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery
			$query['meta_key']   = '_runpace_race_date'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$query['orderby']    = 'meta_value';
			$query['order']      = 'ASC';
			break;

		// ── Related Races ──────────────────────────────────────────────────────

		case 'runpace/related-races':

			$query['post_type'] = 'marathon';

			/*
			 * FIX: Use $block->context['postId'] instead of get_queried_object_id().
			 *
			 * get_queried_object_id() returns the ID of the URL being viewed — so
			 * when this Query Loop is embedded on a page (not a single marathon URL)
			 * it returns the page ID, not the marathon being rendered. The block
			 * context['postId'] is set by the surrounding post template and always
			 * refers to the specific post currently being rendered, regardless of
			 * what URL the visitor is on.
			 *
			 * Fall back to get_queried_object_id() only if context is unavailable
			 * (e.g. block rendered outside a post template).
			 */
			$current_post_id = ! empty( $block->context['postId'] )
				? (int) $block->context['postId']
				: get_queried_object_id();

			if ( $current_post_id ) {
				// Exclude the current post from results.
				$exclude   = $query['post__not_in'] ?? [];
				$exclude[] = $current_post_id;
				$query['post__not_in'] = array_unique( $exclude );

				// Match same distance taxonomy terms as the current post.
				$distance_terms = get_the_terms( $current_post_id, 'runpace_distance' );
				if ( $distance_terms && ! is_wp_error( $distance_terms ) ) {
					$term_ids = wp_list_pluck( $distance_terms, 'term_id' );

					$tax_query   = $query['tax_query'] ?? [];
					$tax_query[] = [
						'taxonomy' => 'runpace_distance',
						'field'    => 'term_id',
						'terms'    => $term_ids,
					];
					$query['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery
				}
			}

			// Order by upcoming race date.
			$query['meta_key'] = '_runpace_race_date'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$query['orderby']  = 'meta_value';
			$query['order']    = 'ASC';
			break;
	}

	return $query;
}
add_filter( 'query_loop_block_query_vars', 'runpace_query_loop_vars', 10, 3 );