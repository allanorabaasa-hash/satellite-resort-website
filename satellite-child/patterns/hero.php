<?php
/**
 * Title: Satellite hero
 * Slug: satellite-child/hero
 * Categories: banner, satellite
 * Keywords: hero, slider, booking
 * Description: Full-width photo slider with headline and the date search bar for booking.
 *
 * Photos: upload hero-1.jpg, hero-2.jpg and hero-3.jpg to the Media Library.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-hero sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-hero sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:group {"className":"sat-slides","layout":{"type":"default"}} -->
	<div class="wp-block-group sat-slides">
		<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
		<figure class="wp-block-image size-full"><img src="<?php echo sat_img( 'hero-1.jpg', 'full' ); ?>" alt="Wooden cottages among the green hills of Kabale at Satellite Holiday Resort"/></figure>
		<!-- /wp:image -->
		<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
		<figure class="wp-block-image size-full"><img src="<?php echo sat_img( 'hero-2.jpg', 'full' ); ?>" alt="Zip-lining over the adventure park at Satellite Holiday Resort"/></figure>
		<!-- /wp:image -->
		<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} -->
		<figure class="wp-block-image size-full"><img src="<?php echo sat_img( 'hero-3.jpg', 'full' ); ?>" alt="The Tree of Life statue and gardens at Satellite Holiday Resort"/></figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"sat-hero-copy","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide sat-hero-copy">
		<!-- wp:group {"className":"sat-hero-meta","layout":{"type":"flex","flexWrap":"wrap"}} -->
		<div class="wp-block-group sat-hero-meta">
			<!-- wp:paragraph {"className":"sat-hero-place"} -->
			<p class="sat-hero-place">Muhanga, on the Kabale–Mbarara Road</p>
			<!-- /wp:paragraph -->
			<!-- wp:html -->
			<p class="sat-weather" data-lat="-1.2486" data-lon="29.9899" hidden><span class="sat-weather-text"></span></p>
			<!-- /wp:html -->
		</div>
		<!-- /wp:group -->
		<!-- wp:heading {"level":1,"className":"sat-hero-title","textColor":"accent-5"} -->
		<h1 class="wp-block-heading sat-hero-title has-accent-5-color has-text-color">Wake up in the hills of Kabale.</h1>
		<!-- /wp:heading -->
		<!-- wp:paragraph {"className":"sat-hero-lead"} -->
		<p class="sat-hero-lead">Wooden cottages, a zip-line over the valley and the Great Lakes Museum, all in one place. Stay for a honeymoon, a family holiday or a night on the road west.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"align":"wide","className":"sat-hero-search","layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide sat-hero-search">
		<!-- wp:shortcode -->
		[ss_availability compact="1" action="/accommodation/"]
		<!-- /wp:shortcode -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
