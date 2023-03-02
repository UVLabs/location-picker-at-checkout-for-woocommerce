<?php
/**
 * Get the store locations settings of the plugin..
 *
 * Author:          Uriahs Victor
 * Created on:      18/02/2023 (d/m/y)
 *
 * @link    https://uriahsvictor.com
 * @since   1.7.0
 * @package Models
 */

namespace Lpac\Models\Plugin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for retrieving Store Locations settings.
 *
 * @package Lpac\Models\Plugin_Settings
 * @since 1.7.0
 */
class Store_Locations {

	/**
	 * Get saved Store Locations.
	 *
	 * @return array
	 * @since 1.7.0
	 */
	public static function get_store_locations(): array {
		return get_option( 'lpac_store_locations', array() );
	}

}
