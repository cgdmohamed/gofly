<?php
/**
 * Removes capabilities, options and the log table. Programs and templates
 * (posts) are kept so no customer work is lost by accident.
 *
 * @package gofly-pb
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once __DIR__ . '/includes/class-capabilities.php';
GFPB_Capabilities::remove();

global $wpdb;
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gofly_pb_log" ); // phpcs:ignore
foreach ( array( 'gfpb_settings', 'gfpb_seq', 'gfpb_seeded', 'gfpb_db_version' ) as $gfpb_option ) {
	delete_option( $gfpb_option );
}
