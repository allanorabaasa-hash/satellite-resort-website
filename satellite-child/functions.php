<?php
/**
 * Satellite Holiday Resort child theme functions.
 *
 * @package satellite-child
 */

defined( 'ABSPATH' ) || exit;

/*
 * Resort details from the business's public information.
 * Confirm them with the resort before going live.
 */
define( 'SAT_WHATSAPP', '256772448961' );
define( 'SAT_PHONE_1', '+256 772 448 961' );
define( 'SAT_PHONE_2', '+256 701 019 003' );
define( 'SAT_PHONE_3', '+256 392 001 878' );
define( 'SAT_EMAIL', 'info@satelliteholidayresort.com' );
define( 'SAT_ADDRESS', 'Muhanga, Kabale–Mbarara Road, Kabale, Uganda' );

// One-click site setup (Appearance → Resort setup).
require_once get_stylesheet_directory() . '/inc/setup.php';

/**
 * URL of a photo in the Media Library, found by its file name (e.g. "cottage.jpg").
 * Upload a photo with that name and it appears everywhere it is used, with no code
 * change. Until then a branded placeholder shows.
 *
 * @param string $file File name.
 * @param string $size Image size.
 */
function sat_img( $file, $size = 'large' ) {
	static $cache = array();
	$key = $file . '|' . $size;
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	global $wpdb;
	$id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value REGEXP %s ORDER BY post_id DESC LIMIT 1",
			/*
			 * "cottage.jpg" finds cottage.jpg/.jpeg/.png/.webp, WordPress's cottage-scaled.jpg for large
			 * photos, and cottage-1.jpg when a replacement was uploaded with the same name. The newest wins.
			 */
			'(^|/)' . preg_replace( '/[^A-Za-z0-9_-]/', '', pathinfo( $file, PATHINFO_FILENAME ) ) . '(-[0-9]+)?(-scaled)?[.](jpe?g|png|webp|avif|gif)$'
		)
	);
	$url           = $id ? wp_get_attachment_image_url( $id, $size ) : '';
	$cache[ $key ] = esc_url( $url ? $url : get_stylesheet_directory_uri() . '/assets/images/placeholder.svg' );
	return $cache[ $key ];
}

/**
 * Photos in the sliding gallery, with captions. File name => caption.
 * Change the order or captions here; the photos come from the Media Library.
 */
function sat_reel_photos() {
	return array(
		'hero-1.jpg'          => 'Wooden cottages in the hills',
		'zip-lining.jpg'      => 'The zip-line over the valley',
		'tree-of-life.jpg'    => 'The Tree of Life',
		'museum.jpg'          => 'Inside the Great Lakes Museum',
		'pirate-ship.jpg'     => 'The pirate ship',
		'gardens.jpg'         => 'The gardens',
		'restaurant.jpg'      => 'Restaurant and terrace',
		'kids-park.jpg'       => 'The kids\' park',
		'statues.jpg'         => 'Statues in the grounds',
		'hall.jpg'            => 'The function hall',
		'mountain-biking.jpg' => 'Mountain biking',
		'camping.jpg'         => 'Camping under the hills',
	);
}

/**
 * Sliding gallery markup (block HTML). gallery.js adds the arrows, autoplay and full-screen view.
 *
 * @param string $title   Heading above the reel ('' for none).
 * @param bool   $link    Add a "See the full gallery" link.
 */
function sat_reel( $title = 'Moments at Satellite', $link = true ) {
	$out = '<!-- wp:group {"align":"full","className":"sat-section sat-reel-band sat-contours","backgroundColor":"accent-3","textColor":"accent-2","layout":{"type":"constrained"}} -->' . "\n";
	$out .= '<div class="wp-block-group alignfull sat-section sat-reel-band sat-contours has-accent-2-color has-accent-3-background-color has-text-color has-background">' . "\n";
	if ( $title ) {
		$out .= '<!-- wp:group {"align":"wide","className":"sat-section-head","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->' . "\n";
		$out .= '<div class="wp-block-group alignwide sat-section-head">' . "\n";
		$out .= '<!-- wp:heading {"textColor":"accent-5"} --><h2 class="wp-block-heading has-accent-5-color has-text-color">' . esc_html( $title ) . '</h2><!-- /wp:heading -->' . "\n";
		if ( $link ) {
			$out .= '<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="' . esc_url( home_url( '/gallery/' ) ) . '">See the full gallery</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' . "\n";
		}
		$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	}
	$out .= '<!-- wp:group {"align":"full","className":"sat-reel","layout":{"type":"default"}} -->' . "\n";
	$out .= '<div class="wp-block-group alignfull sat-reel">' . "\n";
	foreach ( sat_reel_photos() as $file => $caption ) {
		$out .= '<!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"sat-reel-item"} -->' . "\n";
		$out .= '<figure class="wp-block-image size-large sat-reel-item"><img src="' . sat_img( $file ) . '" alt="' . esc_attr( $caption . ', Satellite Holiday Resort, Kabale' ) . '" loading="lazy"/><figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption></figure>' . "\n";
		$out .= '<!-- /wp:image -->' . "\n";
	}
	$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	$out .= '</div>' . "\n" . '<!-- /wp:group -->' . "\n";
	return $out;
}

/**
 * Assets.
 */
function sat_enqueue_assets() {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'sat-child', get_stylesheet_uri(), array(), $version );
	wp_add_inline_style( 'sat-child', ':root{--sat-contours:url(' . esc_url( get_stylesheet_directory_uri() . '/assets/images/contours.svg' ) . ');}' );
	wp_enqueue_script(
		'sat-slider',
		get_stylesheet_directory_uri() . '/assets/js/slider.js',
		array(),
		$version,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	if ( is_front_page() || is_page( 'gallery' ) ) {
		wp_enqueue_script(
			'sat-gallery',
			get_stylesheet_directory_uri() . '/assets/js/gallery.js',
			array(),
			$version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
	if ( is_front_page() ) {
		wp_enqueue_script(
			'sat-weather',
			get_stylesheet_directory_uri() . '/assets/js/weather.js',
			array(),
			$version,
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'sat_enqueue_assets' );

/** Keep the private review page out of search engines. */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_page( 'review-your-stay' ) ) {
			$robots['noindex'] = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);

/**
 * Floating "Chat on WhatsApp" button on every page.
 */
function sat_whatsapp_button() {
	$message = rawurlencode( 'Hello Satellite Holiday Resort, I would like to ask about…' );
	printf(
		'<a class="sat-whatsapp" href="%1$s" target="_blank" rel="noopener" aria-label="%2$s">%3$s<span>%2$s</span></a>',
		esc_url( 'https://wa.me/' . ( function_exists( 'ss_whatsapp_number' ) ? ss_whatsapp_number() : SAT_WHATSAPP ) . '?text=' . $message ),
		esc_html__( 'Chat on WhatsApp', 'satellite-child' ),
		'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8s-.4-.1-.6.1-.7.8-.8 1-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.3-.4.7-1.4a.5.5 0 0 0 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.1 5.1 0 0 0 1.1 2.7 11.7 11.7 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6a2.7 2.7 0 0 0 1.8-1.2 2.2 2.2 0 0 0 .1-1.2c0-.1-.2-.2-.4-.3Z"/></svg>'
	);
}
add_action( 'wp_footer', 'sat_whatsapp_button' );

/** Booking module: show the branded placeholder until a room photo is uploaded. */
add_filter(
	'ss_image_fallback',
	function () {
		return get_stylesheet_directory_uri() . '/assets/images/placeholder.svg';
	}
);

/** Shortcodes inside header/footer template parts. */
add_filter( 'render_block_core/shortcode', 'do_shortcode' );

/** Editor: label the two sliders so the client knows the stacked photos rotate on the site. */
add_action(
	'after_setup_theme',
	function () {
		add_editor_style( 'assets/css/editor.css' );
	}
);

/** Group the resort page designs in the block inserter. */
add_action(
	'init',
	function () {
		register_block_pattern_category( 'satellite', array( 'label' => __( 'Satellite Resort pages', 'satellite-child' ) ) );
	}
);

/** Browser tab icon: the resort crest, until a Site Icon is set in Settings → General. */
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" type="image/svg+xml" href="%s">' . "\n", esc_url( get_stylesheet_directory_uri() . '/assets/images/brand/crest-green.svg' ) );
		}
	}
);

/** Old links to /stay/ (before it was renamed Accommodation) still work. */
add_action(
	'template_redirect',
	function () {
		$path = untrailingslashit( (string) wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ) );
		if ( is_404() && 'stay' === basename( $path ) ) {
			wp_safe_redirect( home_url( '/accommodation/' ), 301 );
			exit;
		}
	}
);

/** Don't advertise the WordPress version. */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
