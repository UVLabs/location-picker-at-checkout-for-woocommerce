<?php

/**
* Review Notices.
*
* Notices to review the plugin.
*
* Author:          Uriahs Victor
* Created on:      23/04/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.5.2
* @package Notices
*/

namespace Lpac\Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Lpac\Notices\Notice;
use Lpac\Traits\Plugin_Info;

/**
* Class Upsells_Notices.
*/
class Review_Notices extends Notice {

	use Plugin_Info;

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct() {
		$this->create_review_plugin_notice();
	}

	/**
	 * Create leave review for plugin notice.
	 *
	 * @return void
	 */
	public function create_review_plugin_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 3 weeks
		if ( $days_since_installed < 21 ) {
			return;
		}

		$content = array(
			'title' => __( 'Has LPAC Helped You?', 'map-location-picker-at-checkout-for-woocommerce' ),
			'body'  => __( 'Hey! its Uriahs, Sole Developer working on Location Picker at Checkout for WooCommerce(LPAC). Has the plugin benefitted your website? If yes, then would you mind taking a few seconds to leave a kind review? Reviews go a long way and they really help keep me motivated to continue working on the plugin and making it better.', 'map-location-picker-at-checkout-for-woocommerce' ),
			'cta'   => __( 'Sure!', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => 'https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/reviews/#new-post',
		);

		echo $this->create_notice_markup( 'leave_review_notice_1', $content );
	}


}
