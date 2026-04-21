/**
 * RunPace – Stats Highlight block view script
 *
 * Handles scroll-triggered count-up animation using the Interactivity API.
 * Uses IntersectionObserver to fire once when the block enters the viewport,
 * then animates each numeric stat from 0 to its target value.
 *
 * @module runpace/stats-highlight
 */

import { store, getContext, getElement } from '@wordpress/interactivity';

// ── Helpers ────────────────────────────────────────────────────────────────────

/**
 * Format a number the same way PHP did (preserving comma-groups etc.).
 * We keep it simple: if the original value had a comma, we re-add grouping.
 *
 * @param {number} value      Current animated value.
 * @param {string} original   The original value string from PHP (e.g. "10,000").
 * @returns {string}
 */
function formatValue( value, original ) {
	const hasComma   = original.includes( ',' );
	const isDecimal  = original.includes( '.' );
	const decimals   = isDecimal ? ( original.split( '.' )[1]?.length ?? 0 ) : 0;

	if ( hasComma ) {
		return Math.round( value ).toLocaleString( 'en-US' );
	}
	if ( isDecimal ) {
		return value.toFixed( decimals );
	}
	return String( Math.round( value ) );
}

/**
 * Easing function: ease-out cubic.
 *
 * @param {number} t  Progress 0–1.
 * @returns {number}
 */
function easeOutCubic( t ) {
	return 1 - Math.pow( 1 - t, 3 );
}

/**
 * Animate a single stat counter from 0 → target over `duration` ms.
 *
 * @param {object}   stat       State stat object (mutated directly).
 * @param {string}   original   Original formatted string from PHP.
 * @param {Function} setState   Function to trigger reactive update.
 * @param {number}   [duration] Animation duration in ms (default 1600).
 */
function animateStat( stat, original, setState, duration = 1600 ) {
	const target = stat.numericTarget;
	if ( target === 0 ) {
		return; // Nothing to animate.
	}

	const start = performance.now();

	function tick( now ) {
		const elapsed  = now - start;
		const progress = Math.min( elapsed / duration, 1 );
		const eased    = easeOutCubic( progress );
		const current  = target * eased;

		setState( () => {
			stat.displayValue = formatValue( current, original );
		} );

		if ( progress < 1 ) {
			requestAnimationFrame( tick );
		} else {
			// Ensure we end on the exact formatted value.
			setState( () => {
				stat.displayValue = original;
				stat.animated     = true;
			} );
		}
	}

	requestAnimationFrame( tick );
}

// ── Store ──────────────────────────────────────────────────────────────────────

const { state } = store( 'runpace/stats-highlight', {

	state: {
		// state.stats, state.animationEnabled, state.hasAnimated
		// are seeded server-side via wp_interactivity_state().
	},

	callbacks: {
		/**
		 * Called once when the block DOM is ready.
		 * Sets up IntersectionObserver to trigger animation on scroll-into-view.
		 */
		onInit() {
			const { ref } = getElement();

			if ( ! state.animationEnabled || state.hasAnimated ) {
				return;
			}

			// Save original formatted strings before we mutate displayValue.
			const originals = state.stats.map( ( s ) => s.value );

			const observer = new IntersectionObserver(
				( entries ) => {
					entries.forEach( ( entry ) => {
						if ( ! entry.isIntersecting || state.hasAnimated ) {
							return;
						}

						// Mark as animated immediately to prevent double-fire.
						state.hasAnimated = true;
						observer.disconnect();

						// Add stagger: each stat starts slightly after the previous.
						state.stats.forEach( ( stat, i ) => {
							const delay = i * 120; // 120 ms stagger between each item
							setTimeout( () => {
								animateStat(
									stat,
									originals[ i ],
									( mutator ) => mutator(), // direct mutation (Interactivity store is reactive)
									1400 + i * 200 // slightly longer duration for later items
								);
							}, delay );
						} );
					} );
				},
				{
					threshold: 0.25, // Trigger when 25% of the block is visible.
					rootMargin: '0px 0px -40px 0px',
				}
			);

			observer.observe( ref );
		},
	},
} );