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
	 *
	 * @return void
	 */
	public function __construct() {
		$this->create_translators_needed_notice();
		$this->create_saas_pilot_notice();
		$this->create_dps_released_notice();
		$this->create_discord_server_notice();
	}

	/**
	 * Create notice requesting translation help.
	 *
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

	/**
	 * SaaS pilot notice.
	 *
	 * @return void
	 */
	public function create_saas_pilot_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 10 days
		if ( $days_since_installed < 10 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Help Shape the Future', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 👀',
			'body'  => sprintf( esc_html__( 'Want a more streamlined delivery/pickup workflow for you or your drivers? Signup for early access to the LPAC Web App pilot; quickly pull up orders and directions from one simplified dashboard. %1$1sLimited spots available%2$2s', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 👾', '<strong>', '</strong>' ),
			'cta'   => esc_html__( 'Learn more', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://lpacwp.com/saas-pilot/' ),
		);

		echo $this->create_notice_markup( 'saas_pilot', $content );
	}

	/**
	 * Create a notice letting users know about our latest plugin.
	 *
	 * since 1.6.13
	 */
	public function create_dps_released_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 5 days
		if ( $days_since_installed < 5 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Say hello to Delivery & Pickup Scheduling for WooCommerce!', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'body'  => esc_html__( 'Hey! I\'ve just released a new plugin that helps you further optimize your store, by letting customers select the date and time they\'d like their Delivery or Pickup order. Give it a shot and let me know how it can be improved to better serve you!', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://dpswp.com/' ),
		);

		echo $this->create_notice_markup( 'dps_released', $content );
	}

	/**
	 * Create notice informing uses of discord server.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function create_discord_server_notice() {

		$content = array(
			'title' => esc_html__( 'Join our E-Commerce Support Discord Community', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'body'  => sprintf( esc_html__( 'Meet E-commerce and chat with store owners like yourself from around the world and discuss on ways to help grow sales, plugin recommendations, tips and tricks and more. %1$1sGrow your store today.%2$2s', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>' ),
			'cta'   => esc_html__( 'Let me in', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://discord.gg/d8TsBv8D' ),
		);

		echo $this->create_notice_markup( 'discord_server', $content );
	}

}
