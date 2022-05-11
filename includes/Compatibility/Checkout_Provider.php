<?php
/**
* Get the plugin that is controlling the checkout page.
*
* Author:          Uriahs Victor
* Created on:      28/04/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.5.4
* @package Lpac
*/
namespace Lpac\Compatibility;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* Checkout Provider class.
*
*/
class Checkout_Provider {

	/**
	 * Get the plugin controlling the WooCommerce checkout page.
	 * @return string
	 */
	public function get_checkout_provider() {

		$provider = 'wc';

		if ( class_exists( 'WFFN_Core' ) ) {
			$provider = 'woofunnels';
		}

		if ( class_exists( 'FluidCheckout' ) ) {
			$provider = 'fluidcheckout';
		}

		return $provider;
	}

}
