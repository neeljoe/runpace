<?php
/**
 * Pattern: Featured Marathons Grid
 *
 * @package RunPace
 */

/**
 * Title:       Featured Marathons
 * Slug:        runpace/featured-marathons-grid
 * Categories:  runpace-marathons, query
 * Description: Grid of featured marathon cards pulled via Query Loop.
 * Keywords:    marathons, featured, grid, cards
 * Viewport Width: 1200
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|9","bottom":"var:preset|spacing|9"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group">

	<!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"center"},"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|7"}}}} -->
	<div class="wp-block-group">

		<!-- wp:heading {"level":2,"style":{"typography":{"fontFamily":"var:preset|font-family|display","fontSize":"var:preset|font-size|4xl","textTransform":"uppercase"}}} -->
		<h2 class="wp-block-heading">Featured Races</h2>
		<!-- /wp:heading -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"style":{"border":{"radius":"var:custom|radius|pill"},"typography":{"fontSize":"var:preset|font-size|xs","fontWeight":"700","letterSpacing":"0.06em","textTransform":"uppercase"}}} -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">View all races →</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->

	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":0,"query":{"perPage":3,"postType":"marathon","orderBy":"meta_value","metaKey":"_runpace_is_featured","metaValue":"1","order":"desc","inherit":false},"layout":{"type":"constrained"}} -->
	<div class="wp-block-query">

		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:runpace/featured-marathon {"showCountdown":true,"primaryLabel":"Register","secondaryLabel":"Details"} /-->
		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->
			<!-- wp:paragraph -->
			<p>No featured races yet. Mark a marathon as "Featured" in the editor sidebar.</p>
			<!-- /wp:paragraph -->
		<!-- /wp:query-no-results -->

	</div>
	<!-- /wp:query -->

</div>
<!-- /wp:group -->