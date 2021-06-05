<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$should_delete_settings = get_option( 'lpac_delete_settings_on_uninstall' );

if ( $should_delete_settings !== 'yes' ) {
	return;
}

$option_keys = array(
	'lpac_google_maps_api_key',
	'lpac_map_starting_coordinates',
	'lpac_general_map_zoom_level',
	'lpac_allow_clicking_on_map_icons',
	'lpac_map_background_color',
	'lpac_checkout_map_orientation',
	'lpac_checkout_page_map_height',
	'lpac_checkout_page_map_width',
	'lpac_display_map_on_order_received_page',
	'lpac_order_received_page_map_height',
	'lpac_order_received_page_map_width',
	'lpac_display_map_on_view_order_page',
	'lpac_view_order_page_map_height',
	'lpac_view_order_page_map_width',
	'lpac_delete_settings_on_uninstall',
);

foreach ( $option_keys as $key ) {
	delete_option( $key );
}
