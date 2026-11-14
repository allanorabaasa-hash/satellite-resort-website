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
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND ( meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s ) ORDER BY post_id DESC LIMIT 1",
			// Match "cottage.jpg", "cottage.jpeg/.png/.webp" and WordPress's "cottage-scaled.jpg" for large photos.
			'%/' . $wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '.%',
			'%/' . $wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '-scaled.%',
			$wpdb->esc_like( pathinfo( $file, PATHINFO_FILENAME ) ) . '.%'
		)
	);
	$url           = $id ? wp_get_attachment_image_url( $id, $size ) : '';
	$cache[ $key ] = esc_url( $url ? $url : get_stylesheet_directory_uri() . '/assets/images/placeholder.svg' );
	return $cache[ $key ];
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
		esc_url( 'https://wa.me/' . SAT_WHATSAPP . '?text=' . $message ),
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

/** Group the resort page designs in the block inserter. */
add_action(
	'init',
	function () {
		register_block_pattern_category( 'satellite', array( 'label' => __( 'Satellite Resort pages', 'satellite-child' ) ) );
	}
);

/** Don't advertise the WordPress version. */
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );
