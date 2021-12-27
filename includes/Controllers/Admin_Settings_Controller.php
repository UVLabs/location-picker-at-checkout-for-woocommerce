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
	 * Sanitize the map default coordinates option before saving.
	 *
	 * @param string $value
	 * @param array $option
	 * @param string $raw_value
	 * @return string
	 */
	public function sanitize_default_map_coordinates( $value, $option, $raw_value ) {

		// Remove letters from input, allow dots and commas
		$value = preg_replace( '/[^0-9,.-]/', '', $value );

		$value = sanitize_text_field( $value );
		$value = trim( $value, ' ,' ); // Remove spaces or commas infront and after value

		return $value;

	}
}
