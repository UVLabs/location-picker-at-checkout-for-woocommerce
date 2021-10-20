<?php

/**
* Handles saving of location details to the databse.
*
* description.
*
* Author:          Uriahs Victor
* Created on:      16/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   ..
* @package package
*/
namespace Lpac\Models;
/**
* Save_Location_Details class.
*
* Handles saving of latitude and longitude.
*
*/
class Save_Location_Details {


	/**
	 * Save the coordinates to the database.
	 *
	 * @since    1.0.0
	 * @param array $order_id The order id.
	 */
	public static function save_order_meta_cords( $order_id, $lat, $long ) {

		if ( empty( $order_id ) || empty( $lat ) || empty( $long ) ) {
			return;
		}

		update_post_meta( $order_id, '_lpac_latitude', sanitize_text_field( $lat ) );
		update_post_meta( $order_id, '_lpac_longitude', sanitize_text_field( $long ) );
	}

}
