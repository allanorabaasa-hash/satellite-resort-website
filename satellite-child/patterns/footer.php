<?php
/**
 * Title: Satellite footer
 * Slug: satellite-child/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 * Description: Dark footer with contour lines, contacts and page links.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-footer sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-footer sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">
	<!-- wp:columns {"align":"wide"} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"36%"} -->
		<div class="wp-block-column" style="flex-basis:36%">
			<!-- wp:image {"width":"170px","sizeSlug":"full","className":"sat-footer-logo"} -->
			<figure class="wp-block-image size-full is-resized sat-footer-logo"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/brand/logo-on-dark.svg' ); ?>" alt="Satellite Holiday Resort and Great Lakes Museum, Kabale" style="width:170px"/></figure>
			<!-- /wp:image -->
			<!-- wp:paragraph -->
			<p>Wooden cottages, an adventure park and the Great Lakes Museum in the hills of Kabale. Made for honeymoons, family holidays and stopovers on the road west.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"className":"sat-footer-title"} -->
			<h3 class="wp-block-heading sat-footer-title">Find us</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p>Muhanga, Kabale–Mbarara Road<br>Kabale, Uganda</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"className":"sat-footer-title"} -->
			<h3 class="wp-block-heading sat-footer-title">Talk to us</h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><a href="tel:+256772448961">+256 772 448 961</a><br><a href="tel:+256701019003">+256 701 019 003</a><br><a href="https://wa.me/256772448961">Chat on WhatsApp</a><br><a href="mailto:info@satelliteholidayresort.com">Email us</a></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3,"className":"sat-footer-title"} -->
			<h3 class="wp-block-heading sat-footer-title">Explore</h3>
			<!-- /wp:heading -->
			<!-- wp:list {"className":"sat-footer-links"} -->
			<ul class="wp-block-list sat-footer-links">
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/stay/' ) ); ?>">Stay and book</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/adventure/' ) ); ?>">Adventure park</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/great-lakes-museum/' ) ); ?>">Great Lakes Museum</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/food-and-hall/' ) ); ?>">Food and events hall</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/gallery/' ) ); ?>">Gallery</a></li>
				<!-- /wp:list-item -->
				<!-- wp:list-item -->
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact and directions</a></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
	<!-- wp:group {"align":"wide","className":"sat-footer-bottom","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide sat-footer-bottom">
		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Satellite Holiday Resort, Kabale</p>
		<!-- /wp:paragraph -->
		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size"><a href="https://www.facebook.com/Satellite-Holiday-Resort-2226548580904130/" rel="noopener">Facebook</a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
