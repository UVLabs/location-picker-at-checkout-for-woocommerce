<?php
/**
 * Get the general settings of the plugin.
 *
 * Author:          Uriahs Victor
 * Created on:      22/01/2023 (d/m/y)
 *
 * @link    https://uriahsvictor.com
 * @since   1.6.13
 * @package Models
 */

namespace Lpac\Models\Plugin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Lpac\Models\Base_Model;

/**
 * Class responsible for retrieving general settings of plugin.
 *
 * @package Lpac\Models\Plugin_Settings
 * @since 1.6.13
 */
class General_Settings extends Base_Model {

	// ------------- Places Auto Complete -------------/
	/**
	 * Get setting that forces user to make use of the places auto complete feature.
	 *
	 * @return bool|string
	 * @since 1.6.13
	 */
	public static function get_force_use_places_autocomplete_setting(): bool {
		return filter_var( get_option( 'lpac_force_places_autocomplete' ), FILTER_VALIDATE_BOOLEAN );
	}

	public static function get_force_places_autocomplete_notice_text(): string {
		return get_option( 'lpac_force_places_autocomplete_notice_text', '' );
	}

	// ------------- Places Auto Complete -------------/


}
