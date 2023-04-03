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
		$this->create_ecommerce_community_notice();
		$this->createPrintusReleaseNotice();
	}

	/**
	 * Create notice requesting translation help.
	 *
	 * @return void
	 */
	private function create_translators_needed_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 66 days
		if ( $days_since_installed < 66 ) {
			return;
		}

		// Show this notice only if the review notice has been dismissed
		if ( ! in_array( 'leave_review_notice_1', $this->get_dismissed_notices() ) ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'We Need Your Help', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🙏',
			'body'  => esc_html__( 'Do you speak a language beside English? If so, then please help translate Kikote to your native language; this will help other users who know your native language, but speak little to no English, better navigate and set up the plugin. Plus, you will get a cool "Translation Contributor" badge on your WordPress.org profile', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'cta'   => esc_html__( 'I can help', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://translate.wordpress.org/projects/wp-plugins/map-location-picker-at-checkout-for-woocommerce/' ),
		);

		$this->create_notice_markup( 'help_translate_lpac', $content );
	}

	/**
	 * SaaS pilot notice.
	 *
	 * @return void
	 */
	private function create_saas_pilot_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 30 days
		if ( $days_since_installed < 30 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Help Shape the Future', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 👀',
			'body'  => sprintf( esc_html__( 'Want a more streamlined delivery/pickup workflow for you or your drivers? Signup for early access to the Kikote Web App pilot; quickly pull up orders and directions from one simplified dashboard. %1$1sLimited spots available%2$2s', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 👾', '<strong>', '</strong>' ),
			'cta'   => esc_html__( 'Learn more', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://lpacwp.com/saas-pilot/' ),
		);

		$this->create_notice_markup( 'saas_pilot', $content );
	}

	/**
	 * Create a notice letting users know about our latest plugin.
	 *
	 * since 1.6.13
	 */
	private function create_dps_released_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 24 days
		if ( $days_since_installed < 24 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Say hello to Delivery & Pickup Scheduling for WooCommerce!', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'body'  => esc_html__( 'Hey! We have a new plugin that helps you further optimize your store— by letting customers select the date and time they\'d like their Delivery or Pickup order. Give it a shot and let me know how it can be improved to better serve you!', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://dpswp.com/' ),
		);

		$this->create_notice_markup( 'dps_released', $content );
	}

	/**
	 * Create notice informing uses of discord server.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function create_ecommerce_community_notice() {

		$days_since_installed = $this->get_days_since_installed();

		// Show notice after 3 days
		if ( $days_since_installed < 3 ) {
			return;
		}

		$content = array(
			'title' => esc_html__( 'Join our E-Commerce Support Discord Community', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'body'  => sprintf( esc_html__( 'Meet E-commerce and chat with store owners like yourself from around the world and discuss on ways to help grow sales, plugin recommendations, tips and tricks and more. %1$1sGrow your store today.%2$2s', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>' ),
			'cta'   => esc_html__( 'Learn more', 'map-location-picker-at-checkout-for-woocommerce' ),
			'link'  => esc_attr( 'https://lpacwp.com/e-commerce-support-community/?utm_source=plugin-notice&utm_medium=wp-dashboard&utm_campaign=ecom-community' ),
		);

		$this->create_notice_markup( 'discord_server', $content );
	}

	/**
	 * Create Printus released notice.
	 *
	 * @return void
	 */
	private function createPrintusReleaseNotice() {

		$content = array(
			'title' => esc_html__( '[NEW] Printus - Cloud Printing Plugin for WooCommerce', 'map-location-picker-at-checkout-for-woocommerce' ) . ' 🚀',
			'body'  => sprintf( esc_html__( 'Print WooCommerce receipts, invoices or package labels to ANY printer as soon as a new order comes in.', 'map-location-picker-at-checkout-for-woocommerce' ), '📈', '<strong>', '</strong>', '%' ),
			'link'  => esc_attr( 'https://printus.cloud/?utm_source=banner&utm_medium=kikotenotice&utm_campaign=cross-sell' ),
		);

		$this->create_notice_markup( 'printus_launch_notice', $content );
	}
}
