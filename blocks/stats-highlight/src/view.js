/**
 * RunPace – Stats Highlight view.js
 *
 * Interactivity API store:
 * - Triggers "is-visible" class via IntersectionObserver for CSS animation.
 * - No heavy count-up library; keeps it <1KB.
 *
 * Namespace: runpace/stats-highlight
 */

import { store, getElement } from '@wordpress/interactivity';

store( 'runpace/stats-highlight', {
	callbacks: {
		/**
		 * Called once per stat item when it enters the viewport.
		 * Adds the CSS class that drives the slide-in transition defined in style.css.
		 */
		initItem() {
			const { ref } = getElement();
			if ( ! ref ) return;

			// Skip if user prefers reduced motion.
			const prefersReduced = window.matchMedia(
				'(prefers-reduced-motion: reduce)'
			).matches;

			if ( prefersReduced ) {
				ref.classList.add( 'is-visible' );
				return;
			}

			const observer = new IntersectionObserver(
				( entries, obs ) => {
					entries.forEach( ( entry ) => {
						if ( entry.isIntersecting ) {
							entry.target.classList.add( 'is-visible' );
							obs.unobserve( entry.target );
						}
					} );
				},
				{ threshold: 0.2 }
			);

			observer.observe( ref );
		},
	},
} );