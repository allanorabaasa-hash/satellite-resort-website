<?php
/**
 * Title: Satellite header
 * Slug: satellite-child/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 * Description: Resort header with name, menu and Book a stay button.
 *
 * @package satellite-child
 */
?>
<!-- wp:group {"align":"full","className":"sat-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull sat-header">
	<!-- wp:group {"align":"wide","className":"sat-header-inner","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide sat-header-inner">
		<!-- wp:group {"className":"sat-brand","layout":{"type":"flex","orientation":"vertical"}} -->
		<div class="wp-block-group sat-brand">
			<!-- wp:site-title {"level":0,"className":"sat-brand-name"} /-->
			<!-- wp:paragraph {"className":"sat-brand-sub"} -->
			<p class="sat-brand-sub">Great Lakes Museum, Kabale</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"sat-header-actions","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
		<div class="wp-block-group sat-header-actions">
			<!-- wp:navigation {"overlayBackgroundColor":"base","overlayTextColor":"contrast","className":"sat-nav","layout":{"type":"flex","justifyContent":"right","flexWrap":"nowrap"}} -->
				<!-- wp:navigation-link {"label":"Accommodation","url":"<?php echo esc_url( home_url( '/accommodation/' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"Adventure","url":"<?php echo esc_url( home_url( '/adventure/' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"Museum","url":"<?php echo esc_url( home_url( '/great-lakes-museum/' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"Food &amp; Hall","url":"<?php echo esc_url( home_url( '/food-and-hall/' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"Gallery","url":"<?php echo esc_url( home_url( '/gallery/' ) ); ?>","kind":"custom"} /-->
				<!-- wp:navigation-link {"label":"Contact","url":"<?php echo esc_url( home_url( '/contact/' ) ); ?>","kind":"custom"} /-->
			<!-- /wp:navigation -->

			<!-- wp:buttons {"className":"sat-header-cta"} -->
			<div class="wp-block-buttons sat-header-cta">
				<!-- wp:button -->
				<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/accommodation/' ) ); ?>">Book a stay</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
