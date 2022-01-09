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

use Lpac\Notices\Notice;
use Lpac\Traits\Plugin_Info;

/**
* Class Upsells_Notices.
*/
class Upsells_Notices extends Notice {

	use Plugin_Info;

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
	}

	/**
	 * Create initial pro released notice.
	 *
	 * @return void
	 */
	public function create_pro_released_notice() {

		$days_since_installed = $this->get_days_since_installed();

		if ( $days_since_installed < 3 ) {
			return;
		}

		$content = array(
			'title' => __( 'Location Picker at Checkout PRO Released!', 'map-location-picker-at-checkout-for-woocommerce' ),
			'body'  => __( 'Unlock the full potential of your pickups and deliveries. The PRO version of LPAC is now live and available for purchase! Use Coupon code INIT15 for a 15% discount on your first year subscription! Limited time offer.', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => 'https://lpacwp.com/pricing/?utm_source=banner&utm_medium=lpacnotice&utm_campaign=proupsell',
		);

		echo $this->create_notice_markup( 'initial_pro_launch_notice', $content );
	}
}
