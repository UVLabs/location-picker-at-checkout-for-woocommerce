<?php
/**
* Orchestrates the Lite admin settings operations.
*
* Author:          Uriahs Victor
* Created on:      19/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.3
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
	 * @param mixed $value
	 * @param mixed $option
	 * @param mixed $raw_value
	 * @return string
	 */
	public function sanitize_default_map_coordinates( $value, $option, $raw_value ) {

		// remove letters from input
		$value = preg_replace( '/[^0-9,]/', '', $value );

		$value = sanitize_text_field( $value );
		$value = trim( $value );

		return $value;

	}
}
