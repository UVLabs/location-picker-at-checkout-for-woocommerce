<?php

/**
 * The admin settings of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/admin
 * @author     Uriahs Victor <info@soaringleads.com>
 */
class Lpac_Admin_Settings {

	/**
	 * Add settings section to WooCommerce settings page.
	 *
	 * @since    1.0.0
	 * @param array $sections The sections array passed by WooCommerce.
	 */
	public function lpac_add_settings_section( $sections ) {

		$sections['lpac_settings'] = __( 'Location Picker at Checkout', 'lpac' );
		return $sections;

	}

	/**
	 * Create the setting options for the plugin.
	 *
	 * @since    1.0.0
	 * @param array $settings The WooCommerce settings.
	 * @param array $current_section The current settings tab being viewed.
	 */
	public function lpac_plugin_settings( $settings, $current_section ) {

		/**
		 * Check if the current section is what we want
		 **/
		if ( $current_section == 'lpac_settings' ) {

			$lpac_settings = array();

			$lpac_settings[] = array(
				'name' => __( 'LPAC Settings', 'lpac' ),
				'id'   => 'lpac',
				'type' => 'title',
				'desc' => __( 'Use the below options to change the plugin settings.', 'lpac' ),
			);

			$lpac_settings[] = array(
				'name'     => __( 'Google Maps API Key', 'lpac' ),
				'desc_tip' => __( 'Enter the API key from Google cloud console.', 'lpac' ),
				'desc'     => __( 'Enter the API you copied from the Google Cloud Console. <a href="#">Learn More >></a>', 'lpac' ),
				'id'       => 'lpac_google_maps_api_key',
				'type'     => 'text',
				'css'      => 'min-width:300px;',
			);

			$lpac_settings[] = array(
				'name'        => __( 'Default Coordinates', 'lpac' ),
				'desc_tip'    => __( 'Enter the default latitude and logitude that will be fetched every time the map loads.', 'lpac' ),
				'desc'        => __( 'Enter the default latitude and logitude that will be fetched every time the map loads. You can find the coordinates for a location <a href="https://www.latlong.net/" target="_blank">here >></a>. Be sure to include the comma when adding your coordinates above.', 'lpac' ),
				'id'          => 'lpac_map_starting_coordinates',
				'placeholder' => '14.024519,-60.974876',
				'type'        => 'text',
				'css'         => 'min-width:300px;',
			);

			$lpac_settings[] = array(
				'name'        => __( 'Default Zoom', 'lpac' ),
				'desc_tip'    => __( 'Recommended number is 16.', 'lpac' ),
				'desc'        => __( 'Enter the default zoom that will be used every time the map loads.', 'lpac' ),
				'id'          => 'lpac_general_map_zoom_level',
				'placeholder' => '16',
				'default'     => 3,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'     => __( 'Enable Clickable Icons', 'lpac' ),
				'desc_tip' => __( 'Recommended setting: Disabled', 'lpac' ),
				'desc'     => __( 'Should customers be able to click on icons of different locations that appear on Google Maps?', 'lpac' ),
				'id'       => 'lpac_allow_clicking_on_map_icons',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
			);

			// TODO
			// $lpac_settings[] = array(
			// 	'name'     => __( 'Remove Map Buttons', 'lpac' ),
			//     'desc'     => __( 'This will remove all interative buttons from the map such as Zoom and Terrain chooser.', 'lpac' ),
			// 	'id'       => 'lpac_hide_map_buttons',
			// 	'type'     => 'checkbox',
			// 	'css'      => 'min-width:300px;',
			// );

			$lpac_settings[] = array(
				'name'        => __( 'Background Color (HEX)', 'lpac' ),
				'desc'        => __( 'Background color of map container (visible while map is loading).', 'lpac' ),
				'id'          => 'lpac_map_background_color',
				'type'        => 'text',
				'placeholder' => '#EEEEEE',
				'default'     => '#EEEEEE',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'     => __( 'Where Should the Map Appear on the Checkout Page?', 'lpac' ),
				'desc_tip' => __( 'This option displays a map view on the order received page after an order has been placed by a customer.', 'lpac' ),
				'id'       => 'lpac_checkout_map_orientation',
				'type'     => 'select',
				'options'  => array(
					'shipping_address_area_top'    => __( 'Shipping Address Area - Top', 'lpac' ),
					'shipping_address_area_bottom' => __( 'Shipping Address Area - Bottom (recommended)', 'lpac' ),
					'billing_address_area_top'     => __( 'Billing Address Area - Top', 'lpac' ),
					'billing_address_area_bottom'  => __( 'Billing Address Area - Bottom', 'lpac' ),
				),
				'css'      => 'min-width:300px;',
			);

			$lpac_settings[] = array(
				'name'        => __( 'Checkout Page Map Height (in px)', 'lpac' ),
				'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
				'id'          => 'lpac_checkout_page_map_height',
				'placeholder' => '400',
				'default'     => 400,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'        => __( 'Checkout Page Map Width (in %)', 'lpac' ),
				'desc_tip'    => __( 'Enter the width of map you\'d like.', 'lpac' ),
				'id'          => 'lpac_checkout_page_map_width',
				'placeholder' => '100',
				'default'     => 100,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'     => __( 'Show Map on the Order Received Page', 'lpac' ),
				'desc_tip' => __( 'This option displays a map view on the order received page after an order has been placed by a customer.', 'lpac' ),
				'id'       => 'lpac_display_map_on_order_received_page',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable', 'lpac' ),
			);

			$lpac_settings[] = array(
				'name'        => __( 'Order Received Page Map Height (in px)', 'lpac' ),
				'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
				'id'          => 'lpac_order_received_page_map_height',
				'placeholder' => '400',
				'default'     => 400,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'        => __( 'Order Received Page Map Width (in px)', 'lpac' ),
				'desc_tip'    => __( 'Enter the width of map you\'d like.', 'lpac' ),
				'id'          => 'lpac_order_received_page_map_width',
				'placeholder' => '100',
				'default'     => 100,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'     => __( 'Show Map on View Order Page', 'lpac' ),
				'desc_tip' => __( 'This option displays a map view on the order details page in the customer account.', 'lpac' ),
				'id'       => 'lpac_display_map_on_view_order_page',
				'type'     => 'checkbox',
				'css'      => 'min-width:300px;',
				'desc'     => __( 'Enable', 'lpac' ),
			);

			$lpac_settings[] = array(
				'name'        => __( 'View Order Page Map Height (in px)', 'lpac' ),
				'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
				'id'          => 'lpac_view_order_page_map_height',
				'placeholder' => '400',
				'default'     => 400,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'        => __( 'View Order Page Map Width (in px)', 'lpac' ),
				'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
				'id'          => 'lpac_view_order_page_map_width',
				'placeholder' => '100',
				'default'     => 100,
				'type'        => 'number',
				'css'         => 'max-width:80px;',
			);

			$lpac_settings[] = array(
				'name'     => __( 'Housekeeping', 'lpac' ),
				'desc_tip' => __( 'Delete all plugin settings on uninstall.', 'lpac' ),
				'id'       => 'lpac_delete_settings_on_uninstall',
				'type'     => 'checkbox',
			);

			$lpac_settings[] = array(
				'type' => 'sectionend',
				'id'   => 'lpac',
			);

			return $lpac_settings;

		}

	}

}
