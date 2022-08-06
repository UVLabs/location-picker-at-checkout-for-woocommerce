<?php
/**
* Orchestrates the Lite admin settings operations.
*
* Author:          Uriahs Victor
* Created on:      19/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.4
* @package Lpac
*/

namespace Lpac\Controllers;

/**
* The Admin_Settings_Controller class.
*
*/
class Admin_Settings_Controller {

	/**
	 * Sanitizes coordinates before saving.
	 *
	 * @param string $values
	 * @param array $option
	 * @param string $raw_value
	 * @return string
	 */
	public function sanitize_coordinates( $value, $option = '', $raw_value = '' ) {

		// Remove letters from input, allow dots, commas and dashes
		$value = preg_replace( '/[^0-9,.-]/', '', $value );

		$value = sanitize_text_field( $value );
		$value = trim( $value, ' ,' ); // Remove spaces or commas infront and after value

		return $value;

	}

	/**
	 * Sanitize price input to drop letters and only accept numbers and fullstops.
	 *
	 * @param array $values
	 * @param array $option
	 * @param array $raw_value
	 * @return array
	 */
	public function sanitize_pricing_inputs( array $values, array $option, array $raw_value ) : array {

		foreach ( $values as $key => &$store_details ) {
			$store_details['store_price_text'] = sanitize_text_field( preg_replace( '/[^0-9.]/', '', $store_details['store_price_text'] ) );
		}
		unset( $store_details );

		return $values;
	}

	/**
	 * Generate a store ID for store locations and sanitize fields.
	 *
	 * @param array $values
	 * @param array $option
	 * @param array $raw_value
	 * @return array
	 */
	public function generate_store_id( array $values, array $option, array $raw_value ) : array {

		foreach ( $values as $key => &$store_details ) {

			if ( empty( $store_details['store_name_text'] ) ) {
				unset( $values[ $key ] ); //prevent adding of blank store locations
				continue;
			}

			$store_location_id                 = 'store_location_' . $key;
			$store_details['store_name_text']  = sanitize_text_field( $store_details['store_name_text'] );
			$store_details['store_cords_text'] = $this->sanitize_coordinates( $store_details['store_cords_text'] );
			$store_details['store_icon_text']  = esc_url_raw( $store_details['store_icon_text'] ?? '' ); // users can still edit this via page elements though its a pro feature...
			$store_details                     = array( 'store_location_id' => $store_location_id ) + $store_details;
		}

			unset( $store_details );

		$values = array_unique( $values, SORT_REGULAR );

		return $values;
	}

}
