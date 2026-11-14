<?php
/**
 * Title: Satellite — Stay page
 * Slug: satellite-child/page-stay
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Date search with live availability, and the accommodation cards.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">Stay with us</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Choose your dates to see which cottages and rooms are free. No payment now: we confirm the booking and the price with you first.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:group {"align":"wide","className":"sat-search-block","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide sat-search-block">
		<!-- wp:shortcode -->
		[ss_availability]
		<!-- /wp:shortcode -->
	</div>
	<!-- /wp:group -->

	<!-- wp:heading {"align":"wide","className":"sat-subhead"} -->
	<h2 class="wp-block-heading alignwide sat-subhead">All our accommodation</h2>
	<!-- /wp:heading -->
	<!-- wp:shortcode -->
	[ss_rooms]
	<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->
