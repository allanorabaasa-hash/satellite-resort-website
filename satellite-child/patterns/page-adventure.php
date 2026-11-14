<?php
/**
 * Title: Satellite — Adventure page
 * Slug: satellite-child/page-adventure
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Every adventure park activity with a photo and short description.
 *
 * @package satellite-child
 */

$sat_activities = array(
	array( 'zip-lining', 'Zip-lining', 'Clip in and fly across the valley with the Kabale hills spread out below you. It is the ride most guests remember first.' ),
	array( 'pirate-ship', 'Pirate ship', 'A big swinging ship that rocks higher with every pass. A favourite with children and the young at heart.' ),
	array( 'obstacle-course', 'Obstacle course', 'A ninja-style course of climbs, crawls, nets and balance beams. Race your friends or take it slow.' ),
	array( 'mountain-biking', 'Mountain biking', 'Follow the tracks around the resort and into the surrounding hills on two wheels.' ),
	array( 'mountain-climbing', 'Mountain climbing', 'Hike the hillside for wide views over the terraced slopes of Kabale. Bring good shoes and water.' ),
	array( 'kids-park', "Kids' park", 'A play area for younger children, close enough that you can relax nearby while they play.' ),
	array( 'games', 'Games', 'Outdoor and indoor games for groups, school trips and families who want to spend the afternoon together.' ),
);
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">The adventure park</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Zip-lining, a pirate ship, an obstacle course and hill trails, right next to your cottage. Ask us about day visits.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section sat-rows","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section sat-rows">
	<?php foreach ( $sat_activities as $sat_a ) : ?>
	<!-- wp:columns {"verticalAlignment":"center","align":"wide","className":"sat-row"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center sat-row">
		<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( $sat_a[0] . '.jpg' ); ?>" alt="<?php echo esc_attr( $sat_a[1] . ' at Satellite Holiday Resort adventure park, Kabale' ); ?>"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading"><?php echo esc_html( $sat_a[1] ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"fontSize":"large","className":"sat-muted"} -->
			<p class="sat-muted has-large-font-size"><?php echo esc_html( $sat_a[2] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
	<?php endforeach; ?>

	<!-- wp:group {"className":"sat-card sat-note","layout":{"type":"constrained"}} -->
	<div class="wp-block-group sat-card sat-note">
		<!-- wp:heading {"level":3} -->
		<h3 class="wp-block-heading">Before you ride</h3>
		<!-- /wp:heading -->
		<!-- wp:paragraph -->
		<p>Ask our team about opening times, prices, safety rules and any age or height limits for each activity. Groups and school trips should book ahead on WhatsApp so we can have guides ready.</p>
		<!-- /wp:paragraph -->
		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"sat-btn-wa"} -->
			<div class="wp-block-button sat-btn-wa"><a class="wp-block-button__link wp-element-button" href="https://wa.me/256772448961?text=Hello%20Satellite%20Holiday%20Resort%2C%20I%20would%20like%20to%20ask%20about%20the%20adventure%20park.">Ask on WhatsApp</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
