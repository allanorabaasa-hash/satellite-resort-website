<?php
/**
 * Title: Satellite — Review your stay page
 * Slug: satellite-child/page-review
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Private page guests reach from their personal review link.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">How was your stay?</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Thank you for staying with us. Your review helps other travellers choose, and helps us get better.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:group {"className":"sat-card sat-form-card","layout":{"type":"default"}} -->
	<div class="wp-block-group sat-card sat-form-card">
		<!-- wp:shortcode -->
		[ss_review_form]
		<!-- /wp:shortcode -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
