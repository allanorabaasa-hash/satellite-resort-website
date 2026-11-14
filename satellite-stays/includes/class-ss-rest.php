<?php
/**
 * Read-only REST endpoint for the guest availability calendar.
 *
 * GET /wp-json/satellite-stays/v1/availability?type=1&from=2026-12-01&days=62
 *
 * Returns, for each night, how many rooms of that type are still free.
 * No guest names or booking details ever leave this endpoint.
 *
 * @package satellite-stays
 */

defined( 'ABSPATH' ) || exit;

class SS_REST {

	const MAX_DAYS = 93;

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		register_rest_route(
			'satellite-stays/v1',
			'/availability',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true', // Public, read-only, counts only.
				'callback'            => array( __CLASS__, 'availability' ),
				'args'                => array(
					'type' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'from' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'days' => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	public static function availability( WP_REST_Request $request ) {
		$type = SS_DB::get_type( (int) $request['type'] );
		if ( ! $type || ! (int) $type->is_active ) {
			return new WP_Error( 'ss_no_type', 'Accommodation not found.', array( 'status' => 404 ) );
		}

		$today = current_time( 'Y-m-d' );
		$from  = (string) $request['from'];
		$d     = DateTime::createFromFormat( '!Y-m-d', $from );
		if ( ! $d || $d->format( 'Y-m-d' ) !== $from || $from < $today ) {
			$from = $today;
		}
		$days = (int) $request['days'];
		$days = $days > 0 ? min( self::MAX_DAYS, $days ) : 62;
		$to   = gmdate( 'Y-m-d', strtotime( $from . ' +' . $days . ' days' ) );

		$nights = SS_DB::nightly_free( (int) $type->id, $from, $to );

		$response = rest_ensure_response(
			array(
				'type'       => (int) $type->id,
				'name'       => $type->name,
				'max_guests' => (int) $type->max_guests,
				'price'      => null === $type->price_per_night ? null : (float) $type->price_per_night,
				'today'      => $today,
				'max_nights' => (int) ss_setting( 'max_nights' ),
				'nights'     => $nights,
			)
		);
		$response->header( 'Cache-Control', 'no-store' );
		return $response;
	}
}
