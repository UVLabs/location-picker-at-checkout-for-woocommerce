<?php
/**
* Class responsible for setting up auth to LPAC SaaS
*
* Author:          Uriahs Victor
* Created on:      28/04/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.6.0
* @package Lpac
*/
namespace Lpac\Traits\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* Auth class.
*
*/
trait Auth {

	/**
	 * Get API Key for SaaS.
	 *
	 * @return mixed
	 */
	private function get_token() {
		return get_option( 'lpac_saas_token' );
	}

	/**
	 * Get API Email for SaaS.
	 *
	 * @return mixed
	 */
	private function get_email() {
		return get_option( 'lpac_saas_email' );
	}

	/**
	 * Get site url.
	 *
	 * @return mixed
	 */
	private function get_site_url() {
		$url  = get_site_url();
		$host = parse_url( $url )['host'] ?? '';
		return $host;
	}

}
