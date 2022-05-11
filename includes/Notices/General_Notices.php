<?php
/**
* Holds general notices for user.
*
* Author:          Uriahs Victor
* Created on:      11/05/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.5.4
* @package Lpac/Notices
*/

namespace Lpac\Notices;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* General Notices Class.
*/
class General_Notices extends Notice {

	/**
	 * Class constructor
	 * @return void
	 */
	public function __construct() {
		 $this->create_translators_needed_notice();
	}

	/**
	 * Create notice requesting translation help.
	 * @return void
	 */
	public function create_translators_needed_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 4 weeks
		if ( $days_since_installed < 30 ) {
			return;
		}

		// Show this notice only if the review notice has been dismissed
		if ( ! in_array( 'leave_review_notice_1', $this->get_dismissed_notices() ) ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'We Need Your Help', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🙏',
			'body'  => esc_html__( 'Do you speak a language beside English? If so, then please help translate LPAC to your native language; this will help other users who know your native language, but speak little to no English, better navigate and set up the plugin. Plus, you will get a cool "Translation Contributor" badge on your WordPress.org profile', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'cta'   => esc_html__( 'I can help', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://translate.wordpress.org/projects/wp-plugins/map-location-picker-at-checkout-for-woocommerce/' ),
		);

		echo $this->create_notice_markup( 'help_translate_lpac', $content );
	}

}
