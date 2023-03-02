<?php
/**
 * Get the general settings of the plugin.
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

use Lpac\Models\Base_Model;

/**
 * Class for retrieving plugin's display settings.
 *
 * @package Lpac\Models\Plugin_Settings
 * @since 1.7.0
 */
class Display_Settings extends Base_Model {

	/**
	 * Get the map id for the view order edit screen map.
	 *
	 * @return mixed
	 */
	public static function get_admin_view_order_map_id() {
		return get_option( 'lpac_admin_view_order_map_id', '' );
	}

}
