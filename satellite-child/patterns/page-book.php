<?php
/**
 * Title: Satellite — Book your stay page
 * Slug: satellite-child/page-book
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Booking request form with WhatsApp and phone alternatives.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">Book your stay</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Send us your dates and we will hold your room while we confirm the booking and the price.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:columns {"align":"wide","className":"sat-book-grid"} -->
	<div class="wp-block-columns alignwide sat-book-grid">
		<!-- wp:column {"width":"62%"} -->
		<div class="wp-block-column" style="flex-basis:62%">
			<!-- wp:group {"className":"sat-card sat-form-card","layout":{"type":"default"}} -->
			<div class="wp-block-group sat-card sat-form-card">
				<!-- wp:shortcode -->
				[ss_booking_form]
				<!-- /wp:shortcode -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"38%"} -->
		<div class="wp-block-column" style="flex-basis:38%">
			<!-- wp:group {"className":"sat-card","layout":{"type":"default"}} -->
			<div class="wp-block-group sat-card">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">Prefer to talk?</h3>
				<!-- /wp:heading -->
				<!-- wp:paragraph -->
				<p>Book on WhatsApp or by phone. We add every booking to the same calendar, so your room is just as safe.</p>
				<!-- /wp:paragraph -->
				<!-- wp:buttons {"className":"sat-stack"} -->
				<div class="wp-block-buttons sat-stack">
					<!-- wp:button {"className":"sat-btn-wa"} -->
					<div class="wp-block-button sat-btn-wa"><a class="wp-block-button__link wp-element-button" href="https://wa.me/256772448961?text=Hello%20Satellite%20Holiday%20Resort%2C%20I%20would%20like%20to%20book%20a%20stay.">Book on WhatsApp</a></div>
					<!-- /wp:button -->
					<!-- wp:button {"className":"is-style-outline"} -->
					<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="tel:+256772448961">Call +256 772 448 961</a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
				<!-- wp:paragraph {"fontSize":"small"} -->
				<p class="has-small-font-size">Email: <a href="mailto:info@satelliteholidayresort.com">info@satelliteholidayresort.com</a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
			<!-- wp:group {"className":"sat-card","layout":{"type":"default"}} -->
			<div class="wp-block-group sat-card">
				<!-- wp:heading {"level":3} -->
				<h3 class="wp-block-heading">Good to know</h3>
				<!-- /wp:heading -->
				<!-- wp:list {"className":"sat-ticks"} -->
				<ul class="wp-block-list sat-ticks">
					<!-- wp:list-item -->
					<li>Your room is held as soon as you send the request.</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>We confirm the price and check-in time with you before you travel.</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Tell us about honeymoons, birthdays or special diets in your request.</li>
					<!-- /wp:list-item -->
					<!-- wp:list-item -->
					<li>Adventure park activities and museum visits can be arranged during your stay.</li>
					<!-- /wp:list-item -->
				</ul>
				<!-- /wp:list -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
