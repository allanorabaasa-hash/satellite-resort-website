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

<!-- wp:group {"align":"full","className":"sat-section sat-plan sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section sat-plan sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"align":"wide","textColor":"accent-5"} -->
	<h2 class="wp-block-heading alignwide has-accent-5-color has-text-color">Plan your stay</h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"align":"wide","className":"sat-plan-lead"} -->
	<p class="alignwide sat-plan-lead">Four ways guests make the most of Satellite. Pick one and we will help you shape the rest.</p>
	<!-- /wp:paragraph -->
	<!-- wp:group {"align":"wide","className":"sat-plans","layout":{"type":"grid","columnCount":4,"minimumColumnWidth":null}} -->
	<div class="wp-block-group alignwide sat-plans">
		<?php
		foreach ( array(
			array( 'honeymoon', 'Honeymoon', 'Quiet days for two in the hills.', array( 'A wooden cottage with garden views', 'Dinner on the terrace at sunset', 'Photos at the Tree of Life' ), 'We are planning a honeymoon. Could you help us with a cottage and a special dinner?' ),
			array( 'family', 'Family weekend', 'Two days the children will talk about.', array( 'The guest house, room for everyone', 'Zip-line, pirate ship and kids\' park', 'An afternoon at the Great Lakes Museum' ), 'We are a family planning a weekend. Could you suggest accommodation and activities?' ),
			array( 'stopover', 'Road-trip stopover', 'Break the drive to the lakes and forests.', array( 'Drive straight in from the Kabale–Mbarara Road', 'A hot meal and a good night\'s sleep', 'Back on the road after breakfast' ), 'We are driving through Kabale and need a room for one night.' ),
			array( 'groups', 'Groups and school trips', 'Learning and adventure for big groups.', array( 'Group accommodation and buffets', 'A guided walk through the museum', 'The obstacle course and games' ), 'We are planning a group or school trip. Could you send us options and a quote?' ),
		) as $plan ) :
			?>
		<!-- wp:group {"className":"sat-plan-card sat-plan-<?php echo esc_attr( $plan[0] ); ?>","layout":{"type":"default"}} -->
		<div class="wp-block-group sat-plan-card sat-plan-<?php echo esc_attr( $plan[0] ); ?>">
			<!-- wp:heading {"level":3,"textColor":"accent-5"} -->
			<h3 class="wp-block-heading has-accent-5-color has-text-color"><?php echo esc_html( $plan[1] ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><?php echo esc_html( $plan[2] ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:list {"className":"sat-plan-list"} -->
			<ul class="wp-block-list sat-plan-list">
				<?php foreach ( $plan[3] as $item ) : ?>
				<!-- wp:list-item -->
				<li><?php echo esc_html( $item ); ?></li>
				<!-- /wp:list-item -->
				<?php endforeach; ?>
			</ul>
			<!-- /wp:list -->
			<!-- wp:paragraph {"className":"sat-plan-cta"} -->
			<p class="sat-plan-cta"><a href="<?php echo esc_url( 'https://wa.me/256772448961?text=' . rawurlencode( 'Hello Satellite Holiday Resort. ' . $plan[4] ) ); ?>">Plan it with us on WhatsApp</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
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
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Check your dates</a></div>
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

<?php echo sat_reel(); // Sliding gallery. ?>

<!-- wp:shortcode -->
[ss_reviews class="alignfull sat-section"]
<!-- /wp:shortcode -->

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
