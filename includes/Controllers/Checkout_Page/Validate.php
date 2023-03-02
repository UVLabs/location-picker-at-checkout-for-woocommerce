<?php
/**
 * Checkout validation related methods.
 *
 * Author:          Uriahs Victor
 * Created on:      15/11/2022 (d/m/y)
 *
 * @link    https://uriahsvictor.com
 * @since   1.6.10
 * @package Controllers
 * @subpackage Controllers/Checkout_Page
 */
namespace Lpac\Controllers\Checkout_Page;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Lpac\Models\Plugin_Settings\General_Settings;
/**
 * Class responsible for checkout page validation.
 *
 * @package Lpac\Controllers\Checkout_Page
 * @since 1.0.0
 */
class Validate {

	/**
	 * Check if the latitude or longitude inputs are filled in.
	 *
	 * @since    1.1.0
	 * @param array  $fields The fields array.
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

		// Don't validate the location fields when local pickup option is used.
		$local_pickup_override = apply_filters( 'lpac_local_pickup_override_map_validation', true, $fields, $errors );

		if ( $local_pickup_override !== false ) {

			if ( function_exists( 'WC' ) ) {
				$chosen_shipping_method = WC()->session->get( 'chosen_shipping_methods' );
				$chosen_shipping_method = $chosen_shipping_method[0] ?? '';

				if ( strpos( $chosen_shipping_method, 'local_pickup' ) !== false ) {
					return;
				}
			}
		}

		/**
		 * Allow users to override this setting
		 */
		$custom_override = apply_filters( 'lpac_override_map_validation', false, $fields, $errors );

		if ( $custom_override === true ) {
			return;
		}

		$error_msg = '<strong>' . __( 'Please select your location using the Map.', 'map-location-picker-at-checkout-for-woocommerce' ) . '</strong>';

		$error_msg = apply_filters( 'lpac_checkout_empty_cords_error_msg', $error_msg );

		$latitude  = $_POST['lpac_latitude'] ?? '';
		$longitude = $_POST['lpac_longitude'] ?? '';

		if ( $latitude === '' || $longitude === '' ) {
			$errors->add( 'validation', $error_msg );
		}

	}

	/**
	 * Check if places autocomplete feature was used at any point during checkout.
	 *
	 * @since    1.6.13
	 * @param array  $fields The fields array.
	 * @param object $errors The errors object.
	 *
	 * @return void
	 */
	public function validate_places_autocomplete( array $fields, object $errors ) :void {

		$shipping_methods = array_map( 'sanitize_text_field', wp_unslash( $_POST['shipping_method'] ?? array() ) );
		$shipping_methods = array_values( $shipping_methods );

		// Don't validate this when local pickup is selected as shipping method.
		foreach ( $shipping_methods as $shipping_method ) {
			if ( false !== strpos( $shipping_method, 'local_pickup' ) ) {
				return;
			}
		}

		$places_autocomplete_used = (bool) sanitize_text_field( wp_unslash( $_POST['lpac_places_autocomplete'] ?? '' ) );

		if ( true === $places_autocomplete_used ) {
			return;
		}

		$force_use = General_Settings::get_force_use_places_autocomplete_setting();

		if ( false === $force_use ) {
			return;
		}

		if ( true === $force_use ) {
			$text = General_Settings::get_force_places_autocomplete_notice_text();
			$errors->add( 'validation', "<strong>$text</strong>" );
		}
	}

	/**
	 * Check if the origin store dropdown has a selected value for Store location selector feature in Shipping Locations settings.
	 *
	 * @since    1.6.0
	 * @param array  $fields The fields array.
	 * @param object $errors The errors object.
	 *
	 * @return void
	 */
	public function validate_store_location_selector_dropdown( array $fields, object $errors ) : void {

		$enable_store_location_selector = get_option( 'lpac_enable_store_location_selector' );
		$enable_store_location_selector = filter_var( $enable_store_location_selector, FILTER_VALIDATE_BOOL );

		if ( empty( $enable_store_location_selector ) ) {
			return;
		}

		/**
		 * The store dropdown visibility might be changed via JS or other conditions
		 * So we need to check if its actually shown before trying to validate
		 *
		 * see changeMapVisibility() in checkout-page-map.js
		 */
		$map_shown = (bool) $_POST['lpac_is_map_shown'] ?? '';

		if ( $map_shown === false ) {
			return;
		}

		$origin_store = $_POST['lpac_order__origin_store'] ?? '';

		$error_msg = '<strong>' . __( 'Please select the store location you would like to order from.', 'map-location-picker-at-checkout-for-woocommerce' ) . '</strong>';

		$error_msg = apply_filters( 'lpac_checkout_empty_origin_store_msg', $error_msg );

		if ( empty( $origin_store ) ) {
			$errors->add( 'validation', $error_msg );
		}

	}

}
