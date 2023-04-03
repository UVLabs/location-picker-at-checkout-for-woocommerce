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

/**
 * Class Upsells_Notices.
 */
class Review_Notices extends Notice {


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

		// Show notice after 38 days
		if ( $days_since_installed < 38 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Has Kikote Helped You?', 'map-location-picker-at-checkout-for-woocommerce' ),
			'body'  => esc_html__( 'Hey! Has the plugin helped your website and/or business? If yes, then would you mind taking a few seconds to leave a kind review? Reviews go a long way and they really help keep me motivated to continue working on the plugin and making it better', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🙏',
			'cta'   => esc_html__( 'Sure', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/reviews/#new-post' ),
		);

		$this->create_notice_markup( 'leave_review_notice_1', $content );
	}


}
