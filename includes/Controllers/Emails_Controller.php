<?php

/**
* Handles Lpac email related logic.
*
* Author:          Uriahs Victor
* Created on:      03/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.4.0
* @package Lpac
*/

namespace Lpac\Controllers;

use Lpac\Helpers\Functions as Functions_Helper;
use Lpac\Helpers\QR_Code_Generator as QR_Code_Generator;

/**
* Class emails.
*
* Adds map location details to customer and admin emails.
*
*/
class Emails_Controller {

	/**
	 * Create map location button link in email.
	 *
	 * @param string $link The link to google maps.
	 * @since    1.1.0
	 */
	public function lpac_create_delivery_location_link_button( $link ) {

		$button_text = __( 'Delivery Location', 'map-location-picker-at-checkout-for-woocommerce' );
		$button_text = apply_filters( 'lpac_map_location_link_button_text', $button_text );
		$base_color  = get_option( 'woocommerce_email_base_color' );
		$text_color  = wc_light_or_dark( $base_color, '#202020', '#ffffff' );
		$p_styles    = 'text-align: center; margin: 20px 0 40px 0 !important;';
		$p_styles    = apply_filters( 'lpac_email_btn_p_styles', $p_styles );
		$a_styles    = "background: $base_color; border-radius: 20px; color: $text_color; display: block; margin: 0 auto; padding: 10px; text-decoration: none; width: 150px;";
		$a_styles    = apply_filters( 'lpac_email_btn_a_styles', $a_styles );

		$button = <<<HTML
		<p style="$p_styles"><a href="$link" class="btn button" style="$a_styles" target="_blank">$button_text</a></p>
HTML;
		echo $button;
	}

	/**
	 * Create map location QR Code link in email.
	 *
	 * @param string $link The link to google maps.
	 * @param int $order_id The current order id.
	 * @since    1.1.0
	 */
	public function lpac_create_delivery_location_link_qrcode( $link, $order_id ) {

		$options = array(
			'qr_code_data'           => $link,
			'qr_code_foreground_rgb' => '0,0,0',
			'qr_code_background_rgb' => '255,255,255',
		);

		/*
		* Generate and save QR Code
		*/
		QR_Code_Generator::lpac_generate_qr_code( $options, $order_id );

		/*
		* https://example.com/wp-content/uploads/lpac-qr-codes/Y/m/d/order_id.jpg
		*/
		$qr_code_link = Functions_Helper::lpac_get_qr_codes_directory( 'baseurl' ) . $order_id . '.jpg';

		echo "<p style='text-align: center'><img style='display: block !important; margin: 30px auto !important; text-align: center !important;' src='{$qr_code_link}'/></p>";

	}

	/**
	 * Outputs a Button or QR Code inside order emails.
	 *
	 * @since    1.1.0
	 */
	public function lpac_add_delivery_location_link_to_email( $order, $sent_to_admin, $plain_text, $email ) {

		$allowed_emails = get_option( 'lpac_email_delivery_map_emails', array() );

		if ( ! in_array( $email->id, $allowed_emails ) ) {
			return;
		}

		$latitude  = get_post_meta( $order->get_id(), '_lpac_latitude', true );
		$longitude = get_post_meta( $order->get_id(), '_lpac_longitude', true );
		$map_link  = apply_filters( 'lpac_map_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

		$map_link_type = get_option( 'lpac_email_delivery_map_link_type' );

		if ( $map_link_type === 'button' ) {
			$this->lpac_create_delivery_location_link_button( $map_link );
		} else {
			$this->lpac_create_delivery_location_link_qrcode( $map_link, $order->get_id() );
		}

	}

}
