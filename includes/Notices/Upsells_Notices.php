<?php
/**
* Class responsible for upsell notices.
*
* Author:          Uriahs Victor
* Created on:      08/01/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.4.3
* @package Notices
*/

namespace Lpac\Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* Class Upsells_Notices.
*/
class Upsells_Notices extends Notice {

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {

		# Don't show upsells if user has a valid license
		if ( lpac_fs()->is_paying() ) {
			return;
		}

		$this->create_pro_released_notice();
		$this->create_v160_release_notice();
	}

	/**
	 * Create initial pro released notice.
	 *
	 * @return void
	 */
	public function create_pro_released_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 4 days
		if ( $days_since_installed < 10 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Location Picker at Checkout PRO Released', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			/* translators: 1: Emoji 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: % symbol  */
			'body'  => sprintf( esc_html__( 'Unlock the full potential of your pickups and deliveries %1$s The PRO version of LPAC is now live and available for purchase! Use Coupon code %2$sINIT10%3$s for a 10%4$s discount on your first year subscription! %2$sLimited time offer%3$s.', 'map-location-picker-at-checkout-for-woocommerce' ), '📈', '<strong>', '</strong>', '%' ),
			'link'  => esc_attr( 'https://lpacwp.com/pricing/?utm_source=banner&utm_medium=lpacnotice&utm_campaign=proupsell' ),
		);

		echo $this->create_notice_markup( 'initial_pro_launch_notice', $content );
	}

	/**
	 * Create notice for what's new in v1.6.0 plugin.
	 *
	 * @since 1.6.0
	 * @return void
	 */
	public function create_v160_release_notice() {

		if ( constant( 'LPAC_VERSION' ) !== '1.6.0' ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Welcome to v1.6.0 of LPAC!', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'body'  => esc_html__( 'Some new features have been added to the PRO version of the plugin. These include: Orders Map, Cost by Store Location, Cost by Store Distance and more. Use coupon code INIT10 for a 10% discount at checkout.' ),
			'link'  => esc_attr( 'https://lpacwp.com/pricing/?utm_source=banner&utm_medium=lpacnotice&utm_campaign=proupsell' ),
		);

		echo $this->create_notice_markup( 'v160_release_notice', $content );
	}
}
