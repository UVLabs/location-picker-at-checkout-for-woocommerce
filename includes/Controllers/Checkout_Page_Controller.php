<?php

/**
* Handles checkout page related logic.
*
*
* Author:          Uriahs Victor
* Created on:      06/11/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.4
* @package Lpac
*/

namespace Lpac\Controllers;
use Lpac\Helpers\Functions;

/**
* Class Checkout Page Controller.
*
*/
class Checkout_Page_Controller {

	/**
	 * Map settings.
	 *
	 * @since    1.0.0
	 */
	public function get_map_options() {

		$options = Functions::set_map_options();

		$data = array(
			'lpac_map_default_latitude'            => $options['latitude'],
			'lpac_map_default_longitude'           => $options['longitude'],
			'lpac_map_zoom_level'                  => $options['zoom_level'],
			'lpac_map_clickable_icons'             => $options['clickable_icons'] === 'yes' ? true : false,
			'lpac_map_background_color'            => $options['background_color'],
			'lpac_autofill_billing_fields'         => $options['fill_in_billing_fields'] === 'yes' ? true : false,
			'lpac_remove_address_plus_code'        => $options['remove_address_plus_code'] === 'yes' ? true : false,
			'lpac_enable_places_autocomplete'      => $options['enable_places_search'] === 'yes' ? true : false,
			'lpac_places_autocomplete_fields'      => $options['places_search_fields'],
			'lpac_places_autocomplete_hide_map'    => $options['places_autocomplete_hide_map'] === 'yes' ? true : false,
			'lpac_places_fill_shipping_fields'     => $options['places_fill_shipping_fields'],
			'lpac_places_fill_billing_fields'      => $options['places_fill_billing_fields'],
			'lpac_auto_detect_location'            => $options['auto_detect_location'] === 'yes' ? true : false,
			'lpac_wc_shipping_destination_setting' => $options['wc_shipping_destination_setting'],
		);

		return apply_filters( 'lpac_map_stored_public_settings', $data );

	}

	/**
	 * Get the GPS coordinates of the last order.
	 *
	 * @since 1.3.4
	 * @return void|array
	 */
	public function get_last_order_location() {

		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();

		// Get the WC_Customer instance Object for the current user
		$customer = new \WC_Customer( $user_id );

		$last_order = $customer->get_last_order();

		if ( empty( $last_order ) ) {
			return;
		}

		return array(
			'address'   => $last_order->get_formatted_shipping_address(),
			'latitude'  => $last_order->get_meta( '_lpac_latitude', true ),
			'longitude' => $last_order->get_meta( '_lpac_longitude', true ),
		);

	}

}
