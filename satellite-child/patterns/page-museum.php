<?php
/**
 * Title: Satellite — Great Lakes Museum page
 * Slug: satellite-child/page-museum
 * Categories: satellite
 * Post Types: page
 * Block Types: core/post-content
 * Description: The museum, the Tree of Life, statues and gardens, and the gift shop.
 *
 * @package satellite-child
 */

$sat_sights = array(
	array( 'tree-of-life', 'The Tree of Life', 'An emerald-green statue in the grounds, and a striking backdrop for photos.' ),
	array( 'statues', 'Statues', 'Sculptures placed around the gardens, each one a stop on a slow walk through the grounds.' ),
	array( 'gardens', 'Gardens', 'Planted lawns and flowerbeds looking out over the hills. Ideal for wedding photos and quiet afternoons.' ),
);
?>
<!-- wp:group {"align":"full","className":"sat-page-head sat-contours","backgroundColor":"lake","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-page-head sat-contours has-accent-2-color has-lake-background-color has-text-color has-background">
	<!-- wp:heading {"level":1,"textColor":"accent-5"} -->
	<h1 class="wp-block-heading has-accent-5-color has-text-color">The Great Lakes Museum</h1>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"fontSize":"large"} -->
	<p class="has-large-font-size">Artefacts, clans and the history of the tribes of the Great Lakes region, in the hills where that history happened.</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo sat-photo-tall"} -->
			<figure class="wp-block-image size-large sat-photo sat-photo-tall"><img src="<?php echo sat_img( 'museum.jpg' ); ?>" alt="Artefacts on display in the Great Lakes Museum, Kabale"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">History you can walk through</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>The museum brings together artefacts and stories from the communities around Africa's Great Lakes: the clans of the region and the history and traditions of its tribes.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph -->
			<p>It is a good first stop for visitors new to southwestern Uganda, and a rich outing for school trips learning about their own heritage.</p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"sat-muted"} -->
			<p class="sat-muted">Ask us about opening hours, guided tours and group visits.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","backgroundColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section has-accent-2-background-color has-background">
	<!-- wp:heading {"align":"wide","className":"sat-subhead"} -->
	<h2 class="wp-block-heading alignwide sat-subhead">Around the grounds</h2>
	<!-- /wp:heading -->
	<!-- wp:group {"align":"wide","className":"sat-tiles","layout":{"type":"grid","columnCount":3,"minimumColumnWidth":null}} -->
	<div class="wp-block-group alignwide sat-tiles">
		<?php foreach ( $sat_sights as $sat_s ) : ?>
		<!-- wp:group {"className":"sat-tile","layout":{"type":"default"}} -->
		<div class="wp-block-group sat-tile">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
			<figure class="wp-block-image size-large"><img src="<?php echo sat_img( $sat_s[0] . '.jpg' ); ?>" alt="<?php echo esc_attr( $sat_s[1] . ' at Satellite Holiday Resort, Kabale' ); ?>"/></figure>
			<!-- /wp:image -->
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php echo esc_html( $sat_s[1] ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><?php echo esc_html( $sat_s[2] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
		<?php endforeach; ?>
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","className":"sat-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-section">
	<!-- wp:columns {"verticalAlignment":"center","align":"wide"} -->
	<div class="wp-block-columns alignwide are-vertically-aligned-center">
		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:heading -->
			<h2 class="wp-block-heading">The gift shop</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Take a piece of Kabale home. The shop carries original art and work by local artisans:</p>
			<!-- /wp:paragraph -->
			<!-- wp:list {"className":"sat-ticks"} -->
			<ul class="wp-block-list sat-ticks">
				<!-- wp:list-item -->
				<li>Pottery, paintings and photography</li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li>Weavings and carved wood</li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li>Antiques and jewellery</li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li>Clothing</li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","width":"55%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-photo"} -->
			<figure class="wp-block-image size-large sat-photo"><img src="<?php echo sat_img( 'gift-shop.jpg' ); ?>" alt="Local crafts in the Satellite Holiday Resort gift shop"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
