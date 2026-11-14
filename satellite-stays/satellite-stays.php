<?php
/**
 * Plugin Name:       Satellite Stays
 * Plugin URI:        https://github.com/allanorabaasa-hash/satellite-resort-website
 * Description:       Accommodation booking module for Satellite Holiday Resort, Kabale: accommodation types, rooms/cottages, live availability, booking requests with email alerts, and a staff booking calendar. Full CRUD using prepared statements.
 * Version:           1.0.0
 * Author:            Allan
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 * Text Domain:       satellite-stays
 *
 * @package satellite-stays
 */

defined( 'ABSPATH' ) || exit;

define( 'SS_VERSION', '1.0.0' );
define( 'SS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SS_URL', plugin_dir_url( __FILE__ ) );

require_once SS_PATH . 'includes/class-ss-db.php';
require_once SS_PATH . 'includes/class-ss-public.php';
require_once SS_PATH . 'includes/class-ss-admin.php';
require_once SS_PATH . 'includes/class-ss-rest.php';

/**
 * Module settings with resort defaults (Bookings → Settings).
 *
 * @param string $key notify_email | whatsapp | max_nights | bookings_open | check_in_time | check_out_time
 */
function ss_setting( $key ) {
	$defaults = array(
		'notify_email'   => 'orallan19@gmail.com',
		'whatsapp'       => '256772448961',
		'max_nights'     => 30,
		'bookings_open'  => 1,
		'check_in_time'  => '14:00',
		'check_out_time' => '10:00',
	);
	$saved = get_option( 'ss_settings', array() );
	$all   = wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
	return isset( $all[ $key ] ) ? $all[ $key ] : null;
}

/** Booking reference shown to guests, e.g. SR-0007. */
function ss_reference( $id ) {
	return 'SR-' . str_pad( (string) (int) $id, 4, '0', STR_PAD_LEFT );
}

/** Ugandan number to WhatsApp format: 0772 448 961 -> 256772448961. */
function ss_whatsapp_number( $phone ) {
	$digits = preg_replace( '/\D/', '', (string) $phone );
	if ( 10 === strlen( $digits ) && '0' === $digits[0] ) {
		$digits = '256' . substr( $digits, 1 );
	}
	return $digits;
}

/** "UGX 150,000" or "Rates on request". */
function ss_money( $amount ) {
	return null === $amount || '' === $amount ? 'Rates on request' : 'UGX ' . number_format( (float) $amount );
}

register_activation_hook( __FILE__, array( 'SS_DB', 'install' ) );

add_action(
	'plugins_loaded',
	function () {
		if ( get_option( 'ss_db_version' ) !== SS_VERSION ) {
			SS_DB::install();
		}
		if ( is_admin() ) {
			new SS_Admin();
		}
	}
);

SS_Public::init();
SS_REST::init();
