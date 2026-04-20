/**
 * RunPace – Block Variations
 *
 * Registers Query Loop variations for the block inserter.
 * Loaded in the block editor via runpace_enqueue_editor_assets().
 */

import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

// ── Upcoming Marathons ────────────────────────────────────────────────────────
registerBlockVariation( 'core/query', {
	name:        'runpace/upcoming-marathons',
	title:       __( 'Upcoming Marathons', 'runpace' ),
	description: __( 'A grid of upcoming marathon events, ordered by race date.', 'runpace' ),
	category:    'runpace',
	keywords:    [ 'marathon', 'race', 'upcoming' ],
	icon:        'location-alt',
	isDefault:   false,

	attributes: {
		query: {
			perPage:   6,
			postType:  'marathon',
			orderBy:   'meta_value',
			metaKey:   '_runpace_race_date',
			order:     'ASC',
			inherit:   false,
		},
		layout: { type: 'constrained' },
	},

	innerBlocks: [
		[
			'core/post-template',
			{ layout: { type: 'grid', columnCount: 3 } },
			[ [ 'runpace/featured-marathon', {} ] ],
		],
		[ 'core/query-pagination', {} ],
		[ 'core/query-no-results', {} ],
	],

	scope: [ 'inserter', 'transform' ],

	isActive: ( { query } ) =>
		query?.postType === 'marathon' &&
		query?.orderBy === 'meta_value' &&
		query?.metaKey === '_runpace_race_date',
} );

// ── Featured Races ────────────────────────────────────────────────────────────
registerBlockVariation( 'core/query', {
	name:        'runpace/featured-races',
	title:       __( 'Featured Races', 'runpace' ),
	description: __( 'Hero cards for marathons marked as featured.', 'runpace' ),
	category:    'runpace',
	keywords:    [ 'marathon', 'featured', 'race' ],
	icon:        'star-filled',
	isDefault:   false,

	attributes: {
		query: {
			perPage:    3,
			postType:   'marathon',
			orderBy:    'meta_value',
			metaKey:    '_runpace_is_featured',
			metaValue:  '1',
			metaCompare:'=',
			order:      'ASC',
			inherit:    false,
		},
		layout: { type: 'constrained' },
	},

	innerBlocks: [
		[
			'core/post-template',
			{ layout: { type: 'grid', columnCount: 3 } },
			[ [ 'runpace/featured-marathon', { showCountdown: true } ] ],
		],
		[ 'core/query-no-results', {} ],
	],

	scope: [ 'inserter', 'transform' ],

	isActive: ( { query } ) =>
		query?.postType === 'marathon' && query?.metaKey === '_runpace_is_featured',
} );

// ── Training Plans ────────────────────────────────────────────────────────────
registerBlockVariation( 'core/query', {
	name:        'runpace/training-plans',
	title:       __( 'Training Plans', 'runpace' ),
	description: __( 'Grid of training plan cards with stats and download CTAs.', 'runpace' ),
	category:    'runpace',
	keywords:    [ 'training', 'plan', 'grid' ],
	icon:        'clipboard',
	isDefault:   false,

	attributes: {
		query: {
			perPage:  6,
			postType: 'training-plan',
			orderBy:  'date',
			order:    'DESC',
			inherit:  false,
		},
		layout: { type: 'constrained' },
	},

	innerBlocks: [
		[
			'core/post-template',
			{ layout: { type: 'grid', columnCount: 3 } },
			[ [ 'runpace/training-plan-card', { colorScheme: 'green' } ] ],
		],
		[ 'core/query-pagination', {} ],
		[ 'core/query-no-results', {} ],
	],

	scope: [ 'inserter', 'transform' ],

	isActive: ( { query } ) => query?.postType === 'training-plan',
} );