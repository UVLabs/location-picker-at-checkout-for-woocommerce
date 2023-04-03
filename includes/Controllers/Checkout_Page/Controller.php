<?php

/**
 * Handles checkout page related logic.
 *
 * Author:          Uriahs Victor
 * Created on:      06/11/2021 (d/m/y)
 *
 * @link    https://uriahsvictor.com
 * @since   1.3.4
 * @package Lpac
 */

namespace Lpac\Controllers\Checkout_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Lpac\Helpers\Functions;

/**
 * Class Checkout Page Controller.
 */
class Controller {

	/**
	 * Map settings.
	 *
	 * @since    1.0.0
	 */
	public function get_map_options() {

		$options = Functions::set_map_options();

		$data = array(
			'lpac_map_default_latitude'                => $options['latitude'],
			'lpac_map_default_longitude'               => $options['longitude'],
			'lpac_map_zoom_level'                      => $options['zoom_level'],
			'lpac_map_clickable_icons'                 => $options['clickable_icons'] === 'yes' ? true : false,
			'lpac_map_background_color'                => $options['background_color'],
			'lpac_remove_address_plus_code'            => $options['remove_address_plus_code'] === 'yes' ? true : false,
			'lpac_enable_places_autocomplete'          => $options['enable_places_search'] === 'yes' ? true : false,
			'lpac_places_autocomplete_fields'          => $options['places_search_fields'],
			'lpac_places_autocomplete_hide_map'        => $options['places_autocomplete_hide_map'] === 'yes' ? true : false,
			'lpac_places_fill_shipping_fields'         => $options['places_fill_shipping_fields'],
			'lpac_places_fill_billing_fields'          => $options['places_fill_billing_fields'],
			'lpac_auto_detect_location'                => $options['auto_detect_location'] === 'yes' ? true : false,
			'lpac_wc_shipping_destination_setting'     => $options['wc_shipping_destination_setting'],
			'lpac_checkout_page_map_default_type'      => $options['lpac_checkout_page_map_default_type'],
			'lpac_thank_you_page_default_map_type'     => $options['lpac_thank_you_page_default_map_type'],
			'lpac_past_order_page_default_map_type'    => $options['lpac_past_order_page_default_map_type'],
			'lpac_admin_order_screen_default_map_type' => $options['lpac_admin_order_screen_default_map_type'],
			'dissect_customer_address'                 => $options['dissect_customer_address'] === 'yes' ? true : false,
			'disabled_map_controls'                    => $options['disabled_map_controls'],
		);

		return apply_filters( 'lpac_map_stored_public_settings', $data );
	}

	/**
	 * Get the details of the last order places by a customer including the coordinates of the last order.
	 *
	 * @since 1.3.4
	 * @return void|array
	 */
	public function get_last_order_details() {

		if ( ! is_user_logged_in() ) {
			return null;
		}

		$user_id = get_current_user_id();

		// Get the WC_Customer instance Object for the current user
		$customer = new \WC_Customer( $user_id );

		$last_order = $customer->get_last_order();

		if ( empty( $last_order ) ) {
			return null;
		}

		// Backwards compatibility, prior to v1.5.4 we stored location coords as private meta.
		// TODO: Remove backwards compatibility once we're satisfied users have updated the plugin. This was added in v1.5.4
		$latitude  = $last_order->get_meta( 'lpac_latitude', true ) ?: $last_order->get_meta( '_lpac_latitude', true );
		$longitude = $last_order->get_meta( 'lpac_longitude', true ) ?: $last_order->get_meta( '_lpac_longitude', true );

		$user_preferred_store = get_user_meta( $user_id, 'lpac_user_preferred_store_location_id', true );

		// If the user has selected a preferred store...then we should override the last order with the preferred store.
		if ( empty( $user_preferred_store ) ) {
			$store_origin_id = $last_order->get_meta( '_lpac_order__origin_store_id', true ) ?: ''; // Value exists if the customer selected an origin store.
		} else {
			$store_origin_id = $user_preferred_store;
		}

		$address = '';

		if ( $last_order->has_shipping_address() ) {
			$address = apply_filters( 'lpac_last_order_address', $last_order->get_shipping_address_1(), $last_order );
		} else { // Highly likely that the user didnt check the "Shipping to a different address?" option, so shipping fields wouldnt be present.
			$address = apply_filters( 'lpac_last_order_address', $last_order->get_billing_address_1(), $last_order );
		}

		$autocomplete_used = $last_order->get_meta( '_lpac_places_autocomplete' );

		return array(
			'address'                  => $address,
			'latitude'                 => $latitude,
			'longitude'                => $longitude,
			'store_origin_id'          => $store_origin_id,
			'used_places_autocomplete' => $autocomplete_used,
		);

	}

}
