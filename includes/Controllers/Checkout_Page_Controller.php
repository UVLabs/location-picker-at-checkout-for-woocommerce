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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Lpac\Helpers\Functions;

/**
* Class Checkout Page Controller.
*
*/
class Checkout_Page_Controller {

	/**
	 * Check if the latitude or longitude inputs are filled in.
	 *
	 * @since    1.1.0
	 * @param array $fields The fields array.
	 * @param object $errors The errors object.
	 *
	 * @return void
	 */
	public function validate_location_fields( array $fields, object $errors ) :void {

		/**
		 * The map visibility might be changed via JS or other conditions
		 * So we need to check if its actually shown before trying to validate
		 */
		$map_shown = (bool) $_POST['lpac_is_map_shown'] ?? '';

		if ( $map_shown === false ) {
			return;
		}

		/**
		 * Allow users to override this setting
		 */
		$custom_override = apply_filters( 'lpac_override_map_validation', false, $fields, $errors );

		if ( $custom_override === true ) {
			return;
		}

		$error_msg = '<strong>' . __( 'Please select your location using the Google Map.', 'map-location-picker-at-checkout-for-woocommerce' ) . '</strong>';

		$error_msg = apply_filters( 'lpac_checkout_empty_cords_error_msg', $error_msg );

		if ( empty( $_POST['lpac_latitude'] ) || empty( $_POST['lpac_longitude'] ) ) {
			$errors->add( 'validation', $error_msg );
		}

	}

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

		// Backwards compatibility, previously we stored location coords as private meta.
		// TODO: Remove backwards compatibility once we're satisfied users have updated the plugin. This was added in v1.5.4
		$latitude        = $last_order->get_meta( 'lpac_latitude', true ) ?: $last_order->get_meta( '_lpac_latitude', true );
		$longitude       = $last_order->get_meta( 'lpac_longitude', true ) ?: $last_order->get_meta( '_lpac_longitude', true );
		$store_origin_id = $last_order->get_meta( '_lpac_order__origin_store_id', true ) ?: ''; // Value exists if the customer selected an origin store.

		return array(
			'address'         => $last_order->get_formatted_shipping_address(),
			'latitude'        => $latitude,
			'longitude'       => $longitude,
			'store_origin_id' => $store_origin_id,
		);

	}

	/**
	 * Combine store locations with their labels.
	 *
	 * @since 1.5.7
	 * @since 1.6.0 use new store locations array
	 * @return array
	 */
	public function get_store_locations() {
		return get_option( 'lpac_store_locations', array() );
	}

}
