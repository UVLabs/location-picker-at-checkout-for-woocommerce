<?php

/**
* Handles Lpac email related logic.
*
* Author:          Uriahs Victor
* Created on:      03/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.4
* @package Lpac
*/

namespace Lpac\Controllers;

use Lpac\Helpers\QR_Code_Generator;
use Lpac\Traits\Upload_Folders;

/**
* Class emails.
*
* Adds map location details to customer and admin emails.
*
*/
class Emails_Controller {
	use Upload_Folders;

	/**
	 * Outputs a Button or QR Code inside order emails.
	 * @param object $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 * @param object $email
	 * @since    1.1.0
	 * @return void
	 */
	public function add_delivery_location_link_to_email( object $order, bool $sent_to_admin, bool $plain_text, object $email ) {

		$allowed_emails = get_option( 'lpac_email_delivery_map_emails', array() );

		// If the current email ID is not in our list of allowed emails then bail.
		if ( ! in_array( $email->id, $allowed_emails ) ) {
			return;
		}

		// Backwards compatibility, previously we stored location coords as private meta.
		$latitude  = get_post_meta( $order->get_id(), 'lpac_latitude', true ) ?: get_post_meta( $order->get_id(), '_lpac_latitude', true );
		$longitude = get_post_meta( $order->get_id(), 'lpac_longitude', true ) ?: get_post_meta( $order->get_id(), '_lpac_longitude', true );

		// If we have no results return.
		if ( empty( $latitude ) or empty( $longitude ) ) {
			return;
		}

		$map_link = apply_filters( 'lpac_email_map_link_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

		$map_link_type = get_option( 'lpac_email_delivery_map_link_type' );

		if ( $map_link_type === 'button' ) {
			$this->create_delivery_location_link_button( $map_link );
		} elseif ( $map_link_type === 'qr_code' ) {
			$this->create_delivery_location_link_qrcode( $map_link, $order->get_id() );
		} elseif ( $map_link_type === 'static_map' ) {
			$this->create_delivery_location_static_map( $order->get_id(), $map_link, $latitude, $longitude );
		} else {
			$this->create_delivery_location_link_button( $map_link );
		}

	}

	/**
	 * Create map location button link in email.
	 *
	 * @param string $link The link to google maps.
	 * @since    1.1.0
	 */
	private function create_delivery_location_link_button( $link ) {

		$button_text = __( 'Delivery Location', 'map-location-picker-at-checkout-for-woocommerce' );
		$button_text = apply_filters( 'lpac_email_map_location_link_button_text', $button_text );
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
	private function create_delivery_location_link_qrcode( $link, $order_id ) {

		$folder_name = 'qr-codes';

		// TODO allow controlling of these figures
		$options = array(
			'qr_code_data'           => $link,
			'qr_code_foreground_rgb' => '0,0,0',
			'qr_code_background_rgb' => '255,255,255',
		);

		/*
		* Generate and save QR Code
		*/
		( new QR_Code_Generator )->lpac_generate_qr_code( $options, $order_id );

		/*
		* https://example.com/wp-content/uploads/lpac/qr-codes/order_id.jpg
		*/
		$qr_code_link           = $this->get_resource_url( $folder_name, $order_id );
		$delivery_location_text = __( 'Delivery Location', 'map-location-picker-at-checkout-for-woocommerce' );
		$delivery_location_text = apply_filters( 'lpac_email_map_location_link_button_text', $delivery_location_text );

		echo "<div style='text-align: center !important'>
				<a href='$link' target='_blank'><img style='display: block !important; margin: 0 auto !important; text-align: center !important;' src='{$qr_code_link}'/></a>
				 <p style='text-align: center !important; font-size: 20px; margin-bottom: 40px'>{$delivery_location_text}</p>
			</div>";

	}

	/**
	 * Adds a Static Google Map to the order email.
	 *
	 * @param mixed $latitude
	 * @param mixed $longitude
	 * @return void
	 * @since 1.4.0
	 */
	private function create_delivery_location_static_map( $order_id, $map_link, $latitude, $longitude ) {

		$folder_name = 'static-maps';

		$center       = $latitude . ',' . $longitude;
		$center       = sanitize_text_field( apply_filters( 'lpac_email_static_map_center', $center ) );
		$zoom         = sanitize_text_field( apply_filters( 'lpac_email_static_map_zoom', 16 ) );
		$size         = sanitize_text_field( apply_filters( 'lpac_email_static_map_size', '600x300' ) );
		$map_type     = sanitize_text_field( apply_filters( 'lpac_email_static_map_type', 'roadmap' ) );
		$marker_color = sanitize_text_field( apply_filters( 'lpac_email_static_map_marker_color', 'red' ) );
		$marker_label = sanitize_text_field( apply_filters( 'lpac_email_static_map_marker_label', '' ) );
		$api_key      = sanitize_text_field( apply_filters( 'lpac_email_static_map_api_key', get_option( 'lpac_google_maps_api_key', '' ) ) );

		$full_link = sprintf(
			'https://maps.googleapis.com/maps/api/staticmap?center=%1$s&zoom=%2$s&size=%3$s&maptype=%4$s&markers=color:%5$s|label:%6$s|%1$s&key=%7$s',
			$center,
			$zoom,
			$size,
			$map_type,
			$marker_color,
			$marker_label,
			$api_key
		);

		$save_path = $this->create_upload_folder( $folder_name );

		$file_name = $save_path . $order_id . '.jpg';

		$image = file_put_contents( $file_name, file_get_contents( $full_link ) );

		if ( empty( $image ) ) {
			return;
		}

		$image_src = $this->get_resource_url( $folder_name, $order_id );

		$width  = '';
		$height = '';

		if ( ! empty( $size ) ) {
			$size_parts = explode( 'x', $size );
			$width      = $size_parts[0];
			$height     = $size_parts[1];
		}

		$image = "<a href='$map_link' target='_blank'><img style='display: block !important; margin-bottom: 40px !important; margin-left: auto !important; margin-right: auto !important; postition: relative !important;' src='$image_src' width='$width' height='$height'/></a>";
		echo $image;

	}

	/**
	 * Adds store location to order email
	 *
	 * @param object $order
	 * @param bool $sent_to_admin
	 * @param bool $plain_text
	 * @param object $email
	 * @since 1.6.2
	 * @return void
	 */
	public function add_store_location_to_email( object $order, bool $sent_to_admin, bool $plain_text, object $email ): void {

		$show_in_emails = get_option( 'lpac_show_selected_store_in_emails', 'yes' );

		if ( $show_in_emails !== 'yes' ) {
			return;
		}

		$label             = get_option( 'lpac_store_select_label' ) ?: esc_html( 'Deliver from', 'map-location-picker-at-checkout-for-woocommerce' );
		$label             = rtrim( $label, ':' );
		$store_origin_name = get_post_meta( $order->get_id(), '_lpac_order__origin_store_name', true );

		if ( empty( $store_origin_name ) ) {
			return;
		}

		$store_locations = get_option( 'lpac_store_locations', array() );
		$address         = '';

		if ( empty( $store_locations ) ) {
			return;
		}

		$store_names = array_column( $store_locations, 'store_name_text' );
		$key         = array_search( $store_origin_name, $store_names );

		if ( $key !== false ) {
			$address = $store_locations[ $key ]['store_address_text'] ?? '';
			$cords   = $store_locations[ $key ]['store_cords_text'] ?? '';
		}

		$link = ( ! empty( $cords ) ) ? "https://www.google.com/maps/search/?api=1&query=$cords" : '#';

		$markup = "<hr><p>
				   		<span style='font-size: 18px'>$label:</span> <br/><br/>
						<a style='font-weight: bold;' href='$link' target='_blank'>
							$store_origin_name <br/>
							$address <br/>
						</a>
					</p><hr><br/>";
		echo $markup;
	}

}
