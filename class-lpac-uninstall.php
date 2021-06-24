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

class Lpac_Uninstall {

	/**
	 * Remove plugin settings.
	 *
	 * @since    1.0.0
	 * @since    1.1.0 Turned into class to support freemius.
	*/
	public static function remove_plugin_settings() {

		$should_delete_settings = get_option( 'lpac_delete_settings_on_uninstall' );

		if ( $should_delete_settings !== 'yes' ) {
			return;
		}

		$option_keys = array(
			'lpac_enabled',
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
			'lpac_order_received_page_map_id',
			'lpac_view_order_page_map_id',
			'lpac_checkout_page_map_id',
			'lpac_autofill_billing_fields',
			'lpac_email_delivery_map_emails',
			'lpac_email_delivery_map_link_location',
			'lpac_email_delivery_map_link_type',
			'lpac_enable_delivery_map_link_in_email',
			'lpac_delete_settings_on_uninstall',
		);

		foreach ( $option_keys as $key ) {
			delete_option( $key );
		}

		$lpac_qr_codes_directory = wp_upload_dir()['basedir'] . '/' . 'lpac-qr-codes';

		( new WP_Filesystem_Direct( '' ) )->delete( $lpac_qr_codes_directory, true, 'd' );

	}

}
