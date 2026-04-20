/**
 * Marathon Filter – view.js
 *
 * WordPress Interactivity API store for runpace/marathon-filter.
 * Handles client-side filtering, view toggle, and load-more pagination.
 *
 * Namespace: runpace/marathon-filter
 *
 * State shape (defined & primed server-side in render.php):
 *   allMarathons      – full dataset from server (array of plain objects)
 *   activeDistance    – currently selected distance string ('' = all)
 *   activeLocation    – currently selected country string ('' = all)
 *   activeDateFilter  – 'upcoming' | 'past' | 'all'
 *   viewMode          – 'grid' | 'list'
 *   visibleCount      – how many items to render (grows with loadMore)
 *   pageSize          – increment for load-more
 *   totalFiltered     – derived: total matching items
 */

import { store, getContext, getElement } from '@wordpress/interactivity';

const { state, actions } = store( 'runpace/marathon-filter', {

	state: {

		// ── Derived: filtered + paginated list ────────────────────────────

		/** All marathons that pass the active filters. */
		get filteredMarathons() {
			const { allMarathons, activeDistance, activeLocation, activeDateFilter } = state;

			return allMarathons.filter( ( m ) => {
				// Distance filter.
				if ( activeDistance && ! m.distanceNames.includes( activeDistance ) ) {
					return false;
				}
				// Location filter.
				if ( activeLocation && m.country !== activeLocation ) {
					return false;
				}
				// Date filter.
				if ( activeDateFilter === 'upcoming' && m.isPast ) return false;
				if ( activeDateFilter === 'past' && ! m.isPast ) return false;

				return true;
			} );
		},

		/** Slice of filteredMarathons that is currently visible. */
		get visibleMarathons() {
			return state.filteredMarathons.slice( 0, state.visibleCount );
		},

		/** Whether there are any results. */
		get hasResults() {
			return state.filteredMarathons.length > 0;
		},

		/** Whether all filtered results are already shown. */
		get allLoaded() {
			return state.visibleCount >= state.filteredMarathons.length;
		},

		/** Human-readable results count label. */
		get filteredCountLabel() {
			const total = state.filteredMarathons.length;
			if ( total === 0 ) return '';
			return total === 1 ? '1 race found' : `${ total } races found`;
		},

		/** View mode helpers. */
		get isGridView() { return state.viewMode === 'grid'; },
		get isListView()  { return state.viewMode === 'list';  },

		/** Loading state (optimistic – flipped during DOM updates). */
		isLoading: false,
	},

	actions: {

		// ── Filter setters ─────────────────────────────────────────────────

		setDistance( event ) {
			state.activeDistance = event.target.value;
			state.visibleCount   = state.pageSize;
			actions._syncDOM();
		},

		setLocation( event ) {
			state.activeLocation = event.target.value;
			state.visibleCount   = state.pageSize;
			actions._syncDOM();
		},

		setDateFilter( event ) {
			state.activeDateFilter = event.target.value;
			state.visibleCount     = state.pageSize;
			actions._syncDOM();
		},

		clearDistance() {
			state.activeDistance = '';
			state.visibleCount   = state.pageSize;
			actions._syncDOM();
		},

		clearLocation() {
			state.activeLocation = '';
			state.visibleCount   = state.pageSize;
			actions._syncDOM();
		},

		clearAllFilters() {
			state.activeDistance   = '';
			state.activeLocation   = '';
			state.activeDateFilter = 'upcoming';
			state.visibleCount     = state.pageSize;
			actions._syncDOM();
		},

		// ── View mode ──────────────────────────────────────────────────────

		setGridView() { state.viewMode = 'grid'; },
		setListView()  { state.viewMode = 'list'; },

		// ── Pagination ─────────────────────────────────────────────────────

		loadMore() {
			state.isLoading   = true;
			state.visibleCount = state.visibleCount + state.pageSize;
			actions._syncDOM();

			// Small timeout so the spinner is visible on fast devices.
			setTimeout( () => {
				state.isLoading = false;
			}, 200 );
		},

		// ── DOM sync ───────────────────────────────────────────────────────
		/**
		 * After a filter or pagination change, show/hide existing article
		 * elements to avoid full re-render while JavaScript hydrates the DOM.
		 *
		 * Visible items are determined entirely from state.visibleMarathons.
		 */
		_syncDOM() {
			const { ref } = getElement();
			if ( ! ref ) return;

			const resultsEl = ref.querySelector( '.runpace-mf__results' );
			if ( ! resultsEl ) return;

			const visibleIds = new Set(
				state.visibleMarathons.map( ( m ) => String( m.id ) )
			);
			const filteredIds = new Set(
				state.filteredMarathons.map( ( m ) => String( m.id ) )
			);

			const cards = resultsEl.querySelectorAll( '.runpace-mf__card' );

			cards.forEach( ( card ) => {
				const id = card.dataset.marathonId;
				const shouldShow = visibleIds.has( id );

				if ( shouldShow ) {
					card.hidden = false;
					card.style.display = '';
				} else {
					card.hidden = true;
					card.style.display = 'none';
				}
			} );

			// If JS has loaded new items we don't have DOM nodes for yet,
			// we insert them from allMarathons (simple innerHTML approach).
			const renderedIds = new Set(
				[ ...cards ].map( ( c ) => c.dataset.marathonId )
			);

			for ( const marathon of state.visibleMarathons ) {
				const idStr = String( marathon.id );
				if ( ! renderedIds.has( idStr ) ) {
					resultsEl.insertAdjacentHTML( 'beforeend', actions._cardHTML( marathon ) );
				}
			}
		},

		/**
		 * Generate a card's HTML string from a marathon data object.
		 * Used when load-more needs to add items not in the initial SSR output.
		 *
		 * @param {Object} m Marathon data object.
		 * @returns {string} HTML string.
		 */
		_cardHTML( m ) {
			const distChip = m.distanceNames.length
				? `<span class="runpace-mf__distance-chip">${ m.distanceNames.join( ' · ' ) }</span>`
				: '';

			const daysChip = m.daysToGo > 0
				? `<span class="runpace-mf__days-badge">${ m.daysToGo } day${ m.daysToGo !== 1 ? 's' : '' }</span>`
				: '';

			const dateEl = m.formattedDate
				? `<span class="runpace-mf__date">${ daysChip }${ m.formattedDate }</span>`
				: '';

			const img = m.thumbUrl
				? `<a href="${ m.permalink }" class="runpace-mf__card-img-wrap" tabindex="-1" aria-hidden="true">
						<img src="${ m.thumbUrl }" alt="" class="runpace-mf__card-img" loading="lazy" decoding="async" />
					 </a>`
				: '';

			const location = [ m.city, m.country ].filter( Boolean ).join( ', ' );
			const locationEl = location
				? `<p class="runpace-mf__card-location">${ location }</p>`
				: '';

			const priceEl = m.price > 0
				? `$${ Math.round( m.price ) }`
				: 'Free';

			return `
				<article
					class="runpace-mf__card${ m.isFeatured ? ' is-featured' : '' }${ m.isPast ? ' is-past' : '' }"
					role="listitem"
					data-marathon-id="${ m.id }"
					data-distance="${ m.distanceNames.join( ',' ) }"
					data-country="${ m.country }"
					data-is-past="${ m.isPast }"
				>
					${ img }
					<div class="runpace-mf__card-body">
						<div class="runpace-mf__card-meta">${ distChip }${ dateEl }</div>
						<h3 class="runpace-mf__card-title">
							<a href="${ m.permalink }">${ m.title }</a>
						</h3>
						${ locationEl }
						${ m.excerpt ? `<p class="runpace-mf__card-excerpt">${ m.excerpt }</p>` : '' }
						<div class="runpace-mf__card-footer">
							<span class="runpace-mf__price">${ priceEl }</span>
							<a href="${ m.permalink }" class="runpace-mf__card-link">View race →</a>
						</div>
					</div>
				</article>`;
		},
	},

	callbacks: {
		/**
		 * On first mount, sync the DOM once so the filter controls
		 * reflect the initial state correctly.
		 */
		onInit() {
			const ctx = getContext();
			if ( ! ctx.initialized ) {
				ctx.initialized = true;
				actions._syncDOM();
			}
		},
	},
} );