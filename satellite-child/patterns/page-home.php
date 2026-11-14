<?php
/**
 * Title: Satellite — Home page
 * Slug: satellite-child/page-home
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Homepage: hero with booking search, places to stay, adventure park, museum, food and hall, getting here.
 *
 * @package satellite-child
 */
?>
<!-- wp:pattern {"slug":"satellite-child/hero"} /-->

<!-- wp:group {"align":"full","className":"sat-section sat-intro","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section sat-intro">
	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"45%"} -->
		<div class="wp-block-column" style="flex-basis:45%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Honeymoons, holidays and stopovers in the hills</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"55%"} -->
		<div class="wp-block-column" style="flex-basis:55%">
			<!-- wp:paragraph {"fontSize":"large"} -->
			<p class="has-large-font-size">Satellite Holiday Resort sits among the green hills of Kabale, right on the road between Mbarara and the southwest. Couples come for quiet cottages and garden views. Families come for the adventure park. Travellers heading to the lakes and forests stop for a meal, a night's rest and a walk through the region's history at the Great Lakes Museum.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">How to find us</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section sat-stay","backgroundColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section sat-stay has-accent-2-background-color has-background">
	<!-- wp:group {"align":"wide","className":"sat-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide sat-section-head">
		<!-- wp:heading -->
		<h2 class="wp-block-heading">Places to stay</h2>
		<!-- /wp:heading -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/stay/' ) ); ?>">Check your dates</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
	<!-- wp:shortcode -->
	[ss_rooms]
	<!-- /wp:shortcode -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:group {"align":"wide","className":"sat-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide sat-section-head">
		<!-- wp:group {"layout":{"type":"default"}} -->
		<div class="wp-block-group">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">The adventure park</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"sat-muted"} -->
			<p class="sat-muted">Something for every age, from a first swing in the kids' park to a zip-line ride across the valley.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/adventure/' ) ); ?>">All activities</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"sat-tiles","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
	<div class="wp-block-group alignwide sat-tiles">
		<?php
		foreach ( array(
			array( 'zip-lining.jpg', 'Zip-lining', 'Fly over the green valley on the resort zip-line.' ),
			array( 'pirate-ship.jpg', 'Pirate ship', 'A swinging ride that children talk about all the way home.' ),
			array( 'obstacle-course.jpg', 'Obstacle course', 'Climb, crawl and balance through a ninja-style course.' ),
			array( 'mountain-biking.jpg', 'Mountain biking', 'Ride the hill tracks around the resort.' ),
			array( 'mountain-climbing.jpg', 'Mountain climbing', 'Hike up for views across the Kabale hills.' ),
			array( 'kids-park.jpg', "Kids' park", 'A play area for the little ones.' ),
		) as $tile ) :
			?>
		<!-- wp:group {"className":"sat-tile","layout":{"type":"default"}} -->
		<div class="wp-block-group sat-tile">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
			<figure class="wp-block-image size-large"><img src="<?php echo sat_img( $tile[0] ); ?>" alt="<?php echo esc_attr( $tile[1] . ' at Satellite Holiday Resort, Kabale' ); ?>"/></figure>
			<!-- /wp:image -->
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php echo esc_html( $tile[1] ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><?php echo esc_html( $tile[2] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section sat-museum sat-contours","backgroundColor":"lake","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section sat-museum sat-contours has-accent-2-color has-lake-background-color has-text-color has-background">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'museum.jpg' ); ?>" alt="Inside the Great Lakes Museum at Satellite Holiday Resort"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%">
			<!-- wp:heading {"textColor":"accent-5"} -->
			<h2 class="wp-block-heading has-accent-5-color has-text-color">The Great Lakes Museum</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Walk through the history of the people of the Great Lakes region: artefacts, the stories of the clans and the traditions of the tribes who have lived in these hills for generations. Then step outside to the Tree of Life statue and the gardens.</p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"backgroundColor":"accent-5","textColor":"lake"} -->
				<div class="wp-block-button"><a class="wp-block-button__link has-lake-color has-accent-5-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url( home_url( '/great-lakes-museum/' ) ); ?>">Visit the museum</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:columns {"align":"wide","className":"sat-pair"} -->
	<div class="wp-block-columns alignwide sat-pair">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'restaurant.jpg' ); ?>" alt="The restaurant terrace at Satellite Holiday Resort"/></figure>
			<!-- /wp:image -->
			<!-- wp:heading {"level":3,"fontSize":"x-large"} -->
			<h3 class="wp-block-heading has-x-large-font-size">Restaurant and terrace</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"sat-muted"} -->
			<p class="sat-muted">Eat indoors or out on the terrace with the hills in view. A wide menu, and buffets for groups and travellers passing through.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'hall.jpg' ); ?>" alt="The function hall at Satellite Holiday Resort"/></figure>
			<!-- /wp:image -->
			<!-- wp:heading {"level":3,"fontSize":"x-large"} -->
			<h3 class="wp-block-heading has-x-large-font-size">Function hall</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"sat-muted"} -->
			<p class="sat-muted">A large space for weddings, introductions, conferences and parties, with catering from our kitchen.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
	<!-- wp:paragraph {"align":"center"} -->
	<p class="has-text-align-center"><a href="<?php echo esc_url( home_url( '/food-and-hall/' ) ); ?>">Food, events and the gift shop</a></p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section sat-visit","backgroundColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section sat-visit has-accent-2-background-color has-background">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"42%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:42%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">Getting here</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>We are at Muhanga, right on the Kabale–Mbarara Road, so you can drive straight in from the highway.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p><strong>Call</strong> <a href="tel:+256772448961">+256 772 448 961</a> or <a href="tel:+256701019003">+256 701 019 003</a><br><strong>Email</strong> <a href="mailto:info@satelliteholidayresort.com">info@satelliteholidayresort.com</a></p>
			<!-- /wp:paragraph -->
			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"sat-btn-wa"} -->
				<div class="wp-block-button sat-btn-wa"><a class="wp-block-button__link wp-element-button" href="https://wa.me/256772448961">Chat on WhatsApp</a></div>
				<!-- /wp:button -->
				<!-- wp:button {"className":"is-style-outline"} -->
				<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="https://www.google.com/maps/search/?api=1&amp;query=Satellite+Holiday+Resort+Kabale">Open in Google Maps</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"58%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:58%">
			<!-- wp:html -->
			<div class="sat-map"><iframe title="Map showing Satellite Holiday Resort, Kabale" src="https://www.google.com/maps?q=Satellite+Holiday+Resort+Kabale&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
			<!-- /wp:html -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
