<?php
/**
* Create plugin shortcodes.
*
* Author:          Uriahs Victor
* Created on:      09/09/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.6.4
* @package Views
*/

namespace Lpac\Views;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Lpac\Helpers\Functions;

/**
* Class Shortcodes.
*
*/
class Shortcodes {

	/**
	 * Class constructor.
	 * @return void
	 */
	public function __construct() {
		 add_shortcode( 'lpac-store-selector', array( $this, 'store_location_shortcode' ) );
	}

	/**
	 * Store Location shortcode markup.
	 * @since 1.6.4
	 * @return string
	 */
	public function store_location_shortcode( $atts ): string {

		$store_locations = Functions::normalize_store_locations();

		if ( empty( $store_locations ) || count( $store_locations ) < 2 ) {
			return __( 'You need to create at least 2 store locations to use this shortcode.', 'map-location-picker-at-checkout-for-woocommerce' );
		}

		$current_preferred_store = get_user_meta( get_current_user_id(), 'lpac_user_preferred_store_location_id', true );

		$default = array(
			'default' => '',
		);

		$attributes = shortcode_atts( $default, $atts );

		$options = "<option value=''>--" . __( 'Please choose an option', 'map-location-picker-at-checkout-for-woocommerce' ) . '--</option>';

		foreach ( $store_locations as $id => $store_location ) {

			if ( $id === $current_preferred_store ) {
				$options .= "<option value='$id' selected>$store_location</option>";
			} elseif ( $id === $attributes['default'] ) {
				$options .= "<option value='$id' selected>$store_location</option>";
			} else {
				$options .= "<option value='$id'>$store_location</option>";
			}
		}

		$field = "<div id='lpac-store-selector-shortcode'><select>" . $options . '</select></div>';
		return $field;
	}

}
