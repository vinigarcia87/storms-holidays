<?php
/**
 * Storms Holidays Uninstall
 *
 * Uninstalling Storms Holidays deletes the control tables that keep Holidays parameters
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

function storms_holidays_uninstall() {
	global $wpdb;

	$table = $wpdb->prefix . 'storms_holidays';
	$wpdb->query("DROP TABLE IF EXISTS $table");
	delete_option("storms_holidays_db_version");

	// Clear any cached data that has been removed.
	wp_cache_flush();
}

storms_holidays_uninstall();
