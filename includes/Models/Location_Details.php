<?php

/**
* Handles saving of location details to the database.
*
* Author:          Uriahs Victor
* Created on:      16/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   ..
* @package Lpac/Models
*/
namespace Lpac\Models;

use Lpac\Controllers\Map_Visibility_Controller;
use Lpac\Helpers\Functions;

/**
* Location_Details class.
*
* Handles saving of latitude and longitude.
*
*/
class Location_Details {

	/**
	 * Validate the Map's visibility setting to prevent manipulations via the DOM.
	 *
	 * @return void
	 */
	public function validate_map_visibility( $order_id, $data ) {

		$show      = Map_Visibility_Controller::lpac_show_map( 'checkout' );
		$post_data = $_POST;
		$map_shown = $post_data['lpac_is_map_shown'] ?? '';

		if ( $show === false ) {
			return;
		}

		// If we're hiding the map using the places autocomplete feature then we need to ALLOW the coordinates to be saved.
		$places_autocomplete_hidemap = get_option( 'lpac_places_autocomplete_hide_map' );

		if ( empty( $map_shown ) && $places_autocomplete_hidemap !== 'yes' ) {
			return;
		}

		$lat = $post_data['lpac_latitude'] ?? 0.0;
		$lat = Functions::normalize_coordinates( $lat );

		$long = $post_data['lpac_longitude'] ?? 0.0;
		$long = Functions::normalize_coordinates( $long );

		$this->save_order_meta_cords( $order_id, $lat, $long );
		$this->save_order_delivery_origin( $order_id, $post_data );
	}

	/**
	 * Save the coordinates to the database.
	 *
	 * @since    1.0.0
	 * @param int $order_id The order id.
	 */
	public function save_order_meta_cords( int $order_id, float $lat, float $long ) : void {

		if ( empty( $order_id ) || empty( $lat ) || empty( $long ) ) {
			return;
		}

		update_post_meta( $order_id, 'lpac_latitude', sanitize_text_field( $lat ) );
		update_post_meta( $order_id, 'lpac_longitude', sanitize_text_field( $long ) );
	}

	/**
	 * Save whether the Places Autocomplete feature was used.
	 *
	 * The value saved is a 1 or 0. 1 meaning yes and 0 meaning no.
	 *
	 * @param int $order_id
	 * @param array $data
	 * @return void
	 */
	public function save_places_autocomplete( int $order_id, array $data ) : void {
		$places_autocomplete_used = $_POST['lpac_places_autocomplete'] ?? '';
		update_post_meta( $order_id, '_lpac_places_autocomplete', sanitize_text_field( $places_autocomplete_used ) );
	}

	/**
	 * Save the order delivery origin to the DB.
	 *
	 * @param int $order_id
	 * @param array $post_data
	 * @return void
	 */
	private function save_order_delivery_origin( int $order_id, array $post_data ) : void {
		$store_origin_id = $post_data['lpac_order__origin_store'] ?? '';

		if ( ! empty( $store_origin_id ) ) {

			$store_locations    = get_option( 'lpac_store_locations', array() );
			$store_location_ids = array_column( $store_locations, 'store_location_id' );
			$key                = array_search( $store_origin_id, $store_location_ids );
			$store_origin_name  = $store_locations[ $key ]['store_name_text'] ?? '';
			$store_origin_id    = $store_locations[ $key ]['store_location_id'] ?? '';

			update_post_meta( $order_id, '_lpac_order__origin_store_id', sanitize_text_field( $store_origin_id ) );
			update_post_meta( $order_id, '_lpac_order__origin_store_name', sanitize_text_field( $store_origin_name ) );
		}
	}

}
