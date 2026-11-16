<?php
/**
 * Title: Satellite — Gallery page
 * Slug: satellite-child/page-gallery
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: Sliding gallery and photo grid. Upload gallery-1.jpg to gallery-9.jpg, or replace with your own Gallery block.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">Gallery</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">The cottages, the grounds, the adventure park and the hills around us.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<?php echo sat_reel( '', false ); // Sliding gallery. ?>

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:heading {"align":"wide","className":"sat-subhead"} -->
	<h2 class="wp-block-heading alignwide sat-subhead">More photos</h2>
	<!-- /wp:heading -->
	<!-- wp:group {"align":"wide","className":"sat-gallery","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
	<div class="wp-block-group alignwide sat-gallery">
		<?php for ( $sat_i = 1; $sat_i <= 9; $sat_i++ ) : ?>
		<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
		<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'gallery-' . $sat_i . '.jpg' ); ?>" alt="<?php echo esc_attr( 'Satellite Holiday Resort, Kabale: photo ' . $sat_i ); ?>"/></figure>
		<!-- /wp:image -->
		<?php endfor; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
