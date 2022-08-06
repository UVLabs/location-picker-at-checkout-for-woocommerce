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

		/**
		 * If the free version and PRO version exist then don't delete the settings.
		 * This ensures that users do not accidentally delete their settings when installing PRO plugin.
		 */
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		if ( array_key_exists( 'map-location-picker-at-checkout-for-woocommerce/lpac.php', $plugins ) && array_key_exists( 'map-location-picker-at-checkout-for-woocommerce-pro/lpac.php', $plugins ) ) {
			return;
		}

		$should_delete_settings = get_option( 'lpac_delete_settings_on_uninstall' );

		if ( $should_delete_settings !== 'yes' ) {
			return;
		}

		$option_keys = array(
			'lpac_enabled',
			'lpac_google_maps_api_key',
			'lpac_force_map_use',
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
			'lpac_store_locations_cords',
			'lpac_store_locations_labels',
			'lpac_store_locations_icons',
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
			'lpac_wc_shipping_zones',
			'lpac_wc_shipping_zones_show_hide',
			'lpac_wc_shipping_classes',
			'lpac_wc_shipping_classes_show_hide',
			'lpac_enable_save_address_feature',
			'lpac_wc_shipping_methods',
			'lpac_hide_troubleshooting_admin_checkout_notice',
			'lpac_dequeue_google_maps',
			'lpac_hide_map_for_guests',
			'lpac_wc_shipping_methods',
			'lpac_map_min_cart_amount',
			'lpac_map_max_cart_amount',
			'lpac_map_show_for_coupons',
			'lpac_map_anchor_points',
			'lpac_admin_view_order_map_id',
			'lpac_installed_at_version',
			'lpac_first_install_date',
			'lpac_remove_address_plus_code',
			'lpac_enable_places_autocomplete',
			'lpac_places_autocomplete_fields',
			'lpac_places_autocomplete_hide_map',
			'lpac_auto_detect_location',
			'lpac_export_date_from',
			'lpac_export_date_to',
			'lpac_places_autocomplete_country_restrictions',
			'lpac_places_autocomplete_type',
			'lpac_enable_shipping_cost_by_distance_feature',
			'lpac_distance_matrix_api_key',
			'lpac_distance_matrix_store_origin_cords',
			'lpac_distance_matrix_cost_per_unit',
			'lpac_distance_matrix_distance_unit',
			'lpac_distance_matrix_travel_mode',
			'lpac_distance_matrix_shipping_methods',
			'lpac_shipping_cost_by_region_enabled',
			'lpac_shipping_regions',
			'lpac_shipping_regions_shipping_methods',
			'lpac_show_shipping_regions_on_checkout_map',
			'lpac_show_shipping_regions_cost_on_checkout_map',
			'lpac_show_shipping_regions_name_on_checkout_map',
			'lpac_shipping_regions_default_background_color',
			'lpac_ship_only_to_drawn_regions',
			'lpac_no_shipping_method_available_text',
			'lpac_no_shipping_method_selected_error',
			'lpac_limit_shipping_distance',
			'lpac_max_shipping_distance',
			'lpac_max_free_shipping_distance',
			'lpac_distance_cost_no_shipping_method_available_text',
			'lpac_distance_cost_no_shipping_method_selected_error',
			'lpac_saas_email',
			'lpac_saas_token',
			'lpac_store_locations',
			'lpac_show_store_locations_on_map',
			'lpac_enable_store_location_selector',
			'lpac_store_select_label',
			'lpac_enable_cost_by_store_distance',
			'lpac_enable_cost_by_store_location',
			'lpac_cost_by_store_distance_delivery_prices',
			'lpac_cost_by_store_location_delivery_prices',
			'lpac_cost_by_store_location_shipping_methods',
			'lpac_migrated__add_address_to_store_locations',
			'lpac_show_selected_store_in_emails',
		);

		foreach ( $option_keys as $key ) {
			delete_option( $key );
		}

		delete_metadata( 'user', 0, 'lpac_dismissed_notices', '', true );

		$lpac_directory = wp_upload_dir()['basedir'] . '/lpac';

		( new WP_Filesystem_Direct( '' ) )->delete( $lpac_directory, true, 'd' );

	}

}
