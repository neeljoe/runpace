<?php
/**
 * Pattern: Hero – Homepage
 *
 * @package RunPace
 */

/**
 * Title:       Homepage Hero
 * Slug:        runpace/hero-homepage
 * Categories:  runpace-hero
 * Description: Full-width hero with headline, CTA buttons, and stat strip.
 * Keywords:    hero, homepage, cta, running
 * Viewport Width: 1200
 */

?>
<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|10","bottom":"var:preset|spacing|9"}},"color":{"background":"var:preset|color|dark-base"}},"layout":{"type":"constrained"},"className":"runpace-hero-pattern"} -->
<div class="wp-block-group runpace-hero-pattern has-dark-base-background-color has-background">

	<!-- wp:group {"layout":{"type":"constrained","contentSize":"820px"},"style":{"spacing":{"padding":{"top":"0","bottom":"0"}}}} -->
	<div class="wp-block-group">

		<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|primary"},"typography":{"fontSize":"var:preset|font-size|sm","fontWeight":"700","letterSpacing":"0.1em","textTransform":"uppercase"},"spacing":{"margin":{"bottom":"var:preset|spacing|4"}}}} -->
		<p class="has-text-color">🏃 THE WORLD'S BEST RUNNING PLATFORM</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":1,"style":{"color":{"text":"var:preset|color|white"},"typography":{"fontFamily":"var:preset|font-family|display","fontSize":"var:preset|font-size|6xl","textTransform":"uppercase","lineHeight":"0.95","letterSpacing":"-0.01em"}}} -->
		<h1 class="wp-block-heading has-white-color has-text-color">Find Your<br/><span style="color:var(--wp--preset--color--primary)">Next Race.</span><br/>Run Your<br/>Best Race.</h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"style":{"color":{"text":"var:preset|color|ink-muted"},"typography":{"fontSize":"var:preset|font-size|xl"},"spacing":{"margin":{"top":"var:preset|spacing|5","bottom":"var:preset|spacing|6"}}}} -->
		<p class="has-text-color">Discover marathons, half marathons, trail runs, and ultra events worldwide. Get expert training plans to crush your goal.</p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"},"style":{"spacing":{"blockGap":"var:preset|spacing|4"}}} -->
		<div class="wp-block-group">

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"backgroundColor":"primary","textColor":"ink","style":{"border":{"radius":"var:custom|radius|pill"},"typography":{"fontSize":"var:preset|font-size|sm","fontWeight":"700","letterSpacing":"0.05em","textTransform":"uppercase"},"spacing":{"padding":{"top":"var:preset|spacing|4","right":"var:preset|spacing|7","bottom":"var:preset|spacing|4","left":"var:preset|spacing|7"}}}} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-primary-background-color has-ink-color has-text-color has-background wp-element-button">Browse Races</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"style":{"border":{"radius":"var:custom|radius|pill","width":"2px","color":"rgba(255,255,255,0.3)"},"color":{"background":"transparent","text":"var:preset|color|white"},"typography":{"fontSize":"var:preset|font-size|sm","fontWeight":"600","letterSpacing":"0.05em","textTransform":"uppercase"},"spacing":{"padding":{"top":"var:preset|spacing|4","right":"var:preset|spacing|7","bottom":"var:preset|spacing|4","left":"var:preset|spacing|7"}}}} -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button">Training Plans</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->

		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->

<!-- wp:runpace/stats-highlight {"theme":"dark","animateOnScroll":true,"stats":[{"value":"200+","label":"Race events","icon":"🏅"},{"value":"10K+","label":"Runners","icon":"👥"},{"value":"50","label":"Countries","icon":"🌍"},{"value":"Free","label":"To join","icon":"✅"}]} /-->