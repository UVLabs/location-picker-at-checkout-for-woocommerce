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
 */
class Checkout_Provider {

	/**
	 * Get the plugin controlling the WooCommerce checkout page.
	 *
	 * @return string
	 */
	public function get_checkout_provider() {

		$provider = 'wc';

		if ( class_exists( 'WFFN_Core', false ) || class_exists( 'WFACP_core', false ) ) {
			/**
			* We need a better way to know when the checkout is actually overridden.
			* $settings               = get_option( '_wfacp_global_settings', array() );
			* $enable_custom_checkout = $settings['override_checkout_page_id'] ?? '';

			* When the option is turned on it sets the custom checkout ID, when it's not it sets it to 0
			* if ( ! empty( $enable_custom_checkout ) ) {
			* $provider = 'funnelkit';
			* }
			*/
			$provider = 'funnelkit';
		}

		if ( class_exists( 'FluidCheckout', false ) ) {
			$provider = 'fluidcheckout';
		}

		if ( defined( 'CFW_NAME' ) ) {
			$provider = 'checkoutwc';
		}

		if ( class_exists( 'Orderable_Pro', false ) ) {
			$settings               = get_option( 'orderable_settings', array() );
			$enable_custom_checkout = $settings['checkout_general_override_checkout'] ?? '';
			if ( $enable_custom_checkout ) {
				$provider = 'orderable';
			}
		}

		return $provider;
	}

}
