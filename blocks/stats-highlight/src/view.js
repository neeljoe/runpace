/**
 * Stats Highlight – view.js
 * Scroll-reveal animation using IntersectionObserver + Interactivity API.
 */

import { store, getElement } from '@wordpress/interactivity';

store( 'runpace/stats-highlight', {
	state: {
		revealed: false,
	},
	callbacks: {
		onIntersect() {
			const { ref } = getElement();
			if ( ! ref ) return;

			const observer = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							// Flip state — data-wp-class--is-revealed adds CSS class.
							store( 'runpace/stats-highlight' ).state.revealed = true;
							observer.disconnect();
						}
					} );
				},
				{ threshold: 0.25 }
			);

			observer.observe( ref );
		},
	},
} );