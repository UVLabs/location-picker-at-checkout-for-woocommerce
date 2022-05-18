<?php
/**
* Class responsible for sending order details to app.lpac.com
*
* Author:          Uriahs Victor
* Created on:      28/04/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.6.0
* @package Lpac
*/
namespace Lpac\Controllers\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
* Send Orders class.
*
*/
class Order {

	use \Lpac\Traits\API\Auth;

	/**
	 * Prepare the order payload when order is created via Checkout.
	 *
	 * @param int $order_id
	 * @param array $order_data
	 * @return void
	 */
	public function prepare_order_checkout( int $order_id, array $order_data ) {

		if ( ! defined( 'LPAC_SAAS_EARLY_ACCESS' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		$payload = array(
			'host'       => $this->get_site_url(),
			'order_id'   => $order_id,
			'order_date' => $order->get_date_created(),
			'customer'   => array(
				'id'                => $order->get_customer_id(),
				'name'              => $order_data['shipping_first_name'] . ' ' . $order_data['shipping_last_name'], //TODO handle when only billing fields are used
				'email'             => $order_data['billing_email'],
				'phone'             => $order_data['billing_phone'],
				'formatted_address' => $order_data['shipping_address_1'] . ' ' . $order_data['shipping_address_2'], //TODO handle when only billing fields are used, format this how WooCommerce is doing it in their get_formatted_address method
			),
			'total'      => $order->get_total(), // TODO show more totals to better list out amounts
			'cords'      => array(
				'lat'  => $_POST['lpac_latitude'] ?? '',
				'long' => $_POST['lpac_longitude'] ?? '',
			), // TODO show more totals to better list out amounts
		);

		$json = json_encode( $payload );

		$this->send_order( $json );

	}

	/**
	 * Prepare the order payload when order is created via Admin screen.
	 *
	 * @param int $order_id
	 * @param object $post
	 * @return void
	 */
	public function prepare_order_admin( int $order_id, object $post ) {

		if ( ! defined( 'LPAC_SAAS_EARLY_ACCESS' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );

		//TODO handle guests
		$payload = array(
			'host'       => $this->get_site_url(),
			'order_id'   => $order_id,
			'order_date' => $order->get_date_created(),
			'customer'   => array(
				'id'                => $order->get_customer_id(),
				'name'              => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(), //TODO handle when only billing fields are used
				'email'             => $order->get_billing_email(),
				'phone'             => $order->get_shipping_phone(),
				'formatted_address' => $order->get_formatted_shipping_address(), //TODO handle when only billing fields are used
			),
			'total'      => $order->get_total(), // TODO show more totals to better list out amounts
			'cords'      => array(
				'lat'  => $order->get_meta( 'lpac_latitude' ),
				'long' => $order->get_meta( 'lpac_longitude' ),
			), // TODO show more totals to better list out amounts
		);

		$json = json_encode( $payload );
		$this->send_order( $json );

	}

	/**
	 * Send an order to LPAC SaaS
	 *
	 * @param array $order
	 * @return void
	 */
	public function send_order( string $json ) {

		$app_url  = LPAC_SAAS_URL;
		$endpoint = $app_url . '/wp-json/bridge/v1/new-order';

		$payload = array(
			'method'      => 'POST',
			'timeout'     => 45,
			'redirection' => 5,
			// 'httpversion' => '1.0',
			// 'blocking'    => true,
			'headers'     => array(
				'Content-Type'  => 'application/json; charset=UTF-8',
				'Cache-Control' => 'no-cache',
				'Token'         => base64_encode( $this->get_token() ),
				'Email'         => base64_encode( $this->get_email() ),
			),
			'body'        => $json,
		);

		if ( LPAC_DEBUG ) {
			$payload['sslverify'] = false;
		}

		$response = wp_remote_post( $endpoint, $payload );

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo "Something went wrong: $error_message";
		} else {
			// TODO Handle response
		}

	}

}
