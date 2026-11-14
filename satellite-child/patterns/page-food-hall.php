<?php
/**
 * Title: Satellite — Food and hall page
 * Slug: satellite-child/page-food-hall
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Restaurant and terrace, function hall with an events enquiry.
 *
 * @package satellite-child
 */

$sat_event_msg = rawurlencode( "Hello Satellite Holiday Resort, I would like to ask about the hall.\n\nType of event:\nDate:\nNumber of guests:\nCatering needed (yes/no):" );
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">Food and events</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Meals with a view of the hills, and a hall big enough for the whole family.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'restaurant.jpg' ); ?>" alt="Dining on the terrace at Satellite Holiday Resort, Kabale"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Restaurant and terrace</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Choose a table inside or out on the terrace, where the hills fill the view. The kitchen cooks a wide range of dishes, and serves buffets for groups, events and travellers stopping on the Kabale–Mbarara Road.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"sat-muted"} -->
			<p class="sat-muted">Ask us for today's menu, or tell us about dietary needs when you book.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","backgroundColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section has-accent-2-background-color has-background">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">The function hall</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>A large hall for weddings, introductions, graduation parties, conferences and church or company retreats. Pair it with catering from our kitchen, rooms for your guests and photos in the gardens.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"sat-muted"} -->
			<p class="sat-muted">Send us your date and guest numbers and we will reply with availability, capacity and a quote.</p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"sat-btn-wa"} -->
				<div class="wp-block-button sat-btn-wa"><a class="wp-block-button__link wp-element-button" href="https://wa.me/256772448961?text=<?php echo esc_attr( $sat_event_msg ); ?>">Ask about the hall</a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="mailto:info@satelliteholidayresort.com?subject=<?php echo esc_attr( rawurlencode( 'Hall enquiry' ) ); ?>">Email us</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'hall.jpg' ); ?>" alt="The function hall set up for an event at Satellite Holiday Resort"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
