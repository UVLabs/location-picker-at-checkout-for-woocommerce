<?php

/**
* Handles saving of location details to the databse.
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
		$map_shown = $_POST['lpac_is_map_shown'] ?? '';

		if ( $show === false ) {
			return;
		}

		// If we're hiding the map using the places autocomplete feature then we need to ALLOW the coordinates to be saved.
		$places_autocomplete_hidemap = get_option( 'lpac_places_autocomplete_hide_map' );

		if ( empty( $map_shown ) && $places_autocomplete_hidemap !== 'yes' ) {
			return;
		}

		$lat  = (float) $_POST['lpac_latitude'] ?? 0.0;
		$long = (float) $_POST['lpac_longitude'] ?? 0.0;

		$this->save_order_meta_cords( $order_id, $lat, $long );

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
		update_post_meta( $order_id, '_places_autocomplete', sanitize_text_field( $places_autocomplete_used ) );
	}

}
