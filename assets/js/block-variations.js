/**
 * RunPace – Block Variations
 *
 * Registers custom Query Loop variations for the three RunPace
 * listing contexts:
 *   1. Upcoming Marathons  — future races ordered by race date ASC
 *   2. Featured Races      — _runpace_is_featured = true
 *   3. Related Races       — same distance taxonomy, excluding current post
 *
 * Loaded as editorScript via functions.php (enqueued on block_editor_assets).
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
 */

import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// ── 1. Upcoming Marathons ─────────────────────────────────────────────────────

registerBlockVariation( 'core/query', {
	name:        'runpace/upcoming-marathons',
	title:       __( 'Upcoming Marathons', 'runpace' ),
	description: __( 'A list of future marathon events ordered by race date.', 'runpace' ),
	category:    'runpace',
	icon:        'location-alt',
	keywords:    [ 'marathon', 'race', 'upcoming', 'events' ],
	isActive:    ( blockAttrs ) =>
		blockAttrs?.namespace === 'runpace/upcoming-marathons',

	attributes: {
		namespace: 'runpace/upcoming-marathons',
		query: {
			postType:  'marathon',
			perPage:   6,
			order:     'asc',
			orderBy:   'date',
			inherit:   false,
			// Meta query (race_date >= today) is applied server-side via
			// the pre_get_posts hook in inc/08-query-loop-filters.php.
		},
	},

	// Default inner block template: card layout with featured image, title, meta.
	innerBlocks: [
		[
			'core/post-template',
			{},
			[
				[
					'core/group',
					{
						className: 'runpace-card',
						style: {
							border: { radius: 'var(--wp--custom--radius--lg)' },
							spacing: { padding: { top: '0', right: '0', bottom: '0', left: '0' } },
						},
						backgroundColor: 'surface',
					},
					[
						[
							'core/post-featured-image',
							{ isLink: true, aspectRatio: '16/9' },
						],
						[
							'core/group',
							{
								style: {
									spacing: {
										padding: {
											top:    'var:preset|spacing|5',
											right:  'var:preset|spacing|5',
											bottom: 'var:preset|spacing|5',
											left:   'var:preset|spacing|5',
										},
										blockGap: 'var:preset|spacing|3',
									},
								},
							},
							[
								[ 'core/post-title', { isLink: true } ],
								[ 'core/post-excerpt', { excerptLength: 18 } ],
							],
						],
					],
				],
			],
		],
		[
			'core/query-pagination',
			{ layout: { type: 'flex', justifyContent: 'center' } },
			[
				[ 'core/query-pagination-previous' ],
				[ 'core/query-pagination-numbers' ],
				[ 'core/query-pagination-next' ],
			],
		],
		[ 'core/query-no-results' ],
	],

	scope: [ 'inserter', 'transform' ],
} );

// ── 2. Featured Races ─────────────────────────────────────────────────────────

registerBlockVariation( 'core/query', {
	name:        'runpace/featured-races',
	title:       __( 'Featured Races', 'runpace' ),
	description: __( 'Highlighted marathons marked as featured.', 'runpace' ),
	category:    'runpace',
	icon:        'star-filled',
	keywords:    [ 'featured', 'marathon', 'race', 'highlight' ],
	isActive:    ( blockAttrs ) =>
		blockAttrs?.namespace === 'runpace/featured-races',

	attributes: {
		namespace: 'runpace/featured-races',
		query: {
			postType: 'marathon',
			perPage:  3,
			order:    'asc',
			orderBy:  'date',
			inherit:  false,
			// Meta query (_runpace_is_featured = 1) applied server-side.
		},
	},

	innerBlocks: [
		[
			'core/post-template',
			{ layout: { type: 'grid', columnCount: 3 } },
			[
				[
					'core/group',
					{
						className: 'runpace-card runpace-card--featured',
						style: {
							border: { radius: 'var(--wp--custom--radius--lg)' },
							spacing: { padding: { top: '0', right: '0', bottom: '0', left: '0' } },
						},
						backgroundColor: 'surface',
					},
					[
						[ 'core/post-featured-image', { isLink: true, aspectRatio: '3/2' } ],
						[
							'core/group',
							{
								style: {
									spacing: {
										padding: {
											top: 'var:preset|spacing|5',
											right: 'var:preset|spacing|5',
											bottom: 'var:preset|spacing|5',
											left: 'var:preset|spacing|5',
										},
									},
								},
							},
							[
								[ 'core/post-title', { isLink: true } ],
								[ 'runpace/marathon-info', {} ],
							],
						],
					],
				],
			],
		],
		[ 'core/query-no-results' ],
	],

	scope: [ 'inserter', 'transform' ],
} );

// ── 3. Related Races ──────────────────────────────────────────────────────────

registerBlockVariation( 'core/query', {
	name:        'runpace/related-races',
	title:       __( 'Related Races', 'runpace' ),
	description: __( 'Races in the same distance category, excluding the current post.', 'runpace' ),
	category:    'runpace',
	icon:        'networking',
	keywords:    [ 'related', 'similar', 'marathon', 'race' ],
	isActive:    ( blockAttrs ) =>
		blockAttrs?.namespace === 'runpace/related-races',

	attributes: {
		namespace: 'runpace/related-races',
		query: {
			postType: 'marathon',
			perPage:  3,
			order:    'asc',
			orderBy:  'date',
			inherit:  false,
			// Taxonomy filter (same distance as current post) applied server-side.
		},
	},

	innerBlocks: [
		[
			'core/post-template',
			{ layout: { type: 'grid', columnCount: 3 } },
			[
				[
					'core/group',
					{
						className: 'runpace-card',
						style: {
							border: { radius: 'var(--wp--custom--radius--lg)' },
							spacing: { padding: { top: '0', right: '0', bottom: '0', left: '0' } },
						},
						backgroundColor: 'surface',
					},
					[
						[ 'core/post-featured-image', { isLink: true, aspectRatio: '16/9' } ],
						[
							'core/group',
							{
								style: {
									spacing: {
										padding: {
											top: 'var:preset|spacing|4',
											right: 'var:preset|spacing|5',
											bottom: 'var:preset|spacing|5',
											left: 'var:preset|spacing|5',
										},
									},
								},
							},
							[
								[ 'core/post-title', { isLink: true } ],
								[ 'core/post-excerpt', { excerptLength: 15 } ],
							],
						],
					],
				],
			],
		],
		[ 'core/query-no-results' ],
	],

	scope: [ 'inserter', 'transform' ],
} );