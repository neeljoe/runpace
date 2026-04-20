<?php
/**
 * Pattern: Training Plans Grid
 *
 * @package RunPace
 */

/**
 * Title:       Training Plans Grid
 * Slug:        runpace/training-plans-grid
 * Categories:  runpace-training, query
 * Description: Grid of training plan cards with a section header and CTA.
 * Keywords:    training, plans, grid
 * Viewport Width: 1200
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|9","bottom":"var:preset|spacing|9"}},"color":{"background":"var:preset|color|base"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group has-base-background-color has-background">

	<!-- wp:group {"layout":{"type":"constrained","contentSize":"600px"},"style":{"spacing":{"margin":{"bottom":"var:preset|spacing|8"}}}} -->
	<div class="wp-block-group">

		<!-- wp:paragraph {"textAlign":"center","style":{"color":{"text":"var:preset|color|primary"},"typography":{"fontSize":"var:preset|font-size|xs","fontWeight":"700","letterSpacing":"0.1em","textTransform":"uppercase"}}} -->
		<p class="has-text-align-center has-text-color">TRAINING RESOURCES</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":2,"textAlign":"center","style":{"typography":{"fontFamily":"var:preset|font-family|display","fontSize":"var:preset|font-size|4xl","textTransform":"uppercase"}}} -->
		<h2 class="wp-block-heading has-text-align-center">Train Smarter</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"textAlign":"center","style":{"color":{"text":"var:preset|color|ink-muted"}}} -->
		<p class="has-text-align-center has-text-color">Expert-crafted training plans for every distance and fitness level — free to download.</p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:query {"queryId":1,"query":{"perPage":6,"postType":"training-plan","orderBy":"date","order":"desc","inherit":false},"layout":{"type":"constrained"}} -->
	<div class="wp-block-query">

		<!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:runpace/training-plan-card {"colorScheme":"green","showStats":true,"showDownload":true} /-->
		<!-- /wp:post-template -->

		<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"var:preset|spacing|7"}}}} -->
			<!-- wp:query-pagination-previous /-->
			<!-- wp:query-pagination-numbers /-->
			<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination -->

	</div>
	<!-- /wp:query -->

</div>
<!-- /wp:group -->