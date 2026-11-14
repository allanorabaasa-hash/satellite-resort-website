<?php
/**
 * Runs only when the plugin is deleted from Plugins → Delete.
 * Drops the module's tables (child tables first, because of the foreign keys),
 * removes the Resort Staff role and the settings.
 *
 * @package satellite-stays
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

foreach ( array( 'bookings', 'units', 'accommodation_types' ) as $name ) {
	$table = $wpdb->prefix . 'ss_' . $name;
	$wpdb->query( "DROP TABLE IF EXISTS $table" ); // Built from the prefix, not user input.
}

delete_option( 'ss_db_version' );
delete_option( 'ss_settings' );
remove_role( 'resort_staff' );
$admin = get_role( 'administrator' );
if ( $admin ) {
	$admin->remove_cap( 'ss_manage' );
}
