<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/public/partials
 */
class Lpac_Public_Display extends Lpac_Public {

	/**
	 * Global map settings for JavaScript.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lpac_global_map_settings_js  Contains the exposed map settings for JS consumption.
	 */
	private $lpac_global_map_settings_js;


	/**
	* Initialize the class and set its properties.
	*
	* @since    1.0.0
	*/
	public function __construct() {
		$this->lpac_expose_map_settings_js();
	}

	/**
	 * Exposes map settings to be used in client-side javascript.
	 *
	 * @since    1.0.0
	 * @param      string    $additional   Additional settings to pass to JS.
	 */
	private function lpac_expose_map_settings_js( $additional = array() ) {

		$js_variables = $this->lpac_get_map_settings();

		$js_variables = array_merge( $js_variables, $additional );

		$map_options = json_encode( $js_variables );

		$global_variables = <<<GLOBALVARS
		// LPAC Map Options
		var map_options = $map_options;
GLOBALVARS;

		$this->lpac_global_map_settings_js = $global_variables;

	}

	/**
	 * Outputs map on the WooCommerce Checkout page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_checkout_page() {

		$lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', 'Detect Current Location' );
		$instuctions_text            = __( 'Click the "Detect Current Location" button then move the red marker to your desired shipping address.', 'lpac' );
		$instuctions_text            = apply_filters( 'lpac_map_instuctions_text', $instuctions_text );

		$markup = <<<MAP
		<div class='woocommerce-shipping-fields__field-wrapper lpac-map'></div>
		<div class='woocommerce-shipping-fields__field-wrapper'>
		<div style="font-size: 10px">$instuctions_text</div>
		<button id='lpac-find-location-btn' type='button'>$lpac_find_location_btn_text</button>
		</div>
MAP;

		echo $markup;

		// Add inline global JS so that we can use data fetched using PHP inside JS
		wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $this->lpac_global_map_settings_js, 'before' );

	}

	/**
	 * Outputs map on the WooCommerce view order page and order received page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_order_details_page() {

		global $woocommerce, $wp;

		// If this isn't the order received page shown after a purchase, or the view order page shown on the user account, then bail.
		if ( ! is_wc_endpoint_url( 'view-order' ) && ! is_wc_endpoint_url( 'order-received' ) ) {
			return;
		}

		$show_on_view_order_page     = Lpac_Functions_Helper::lpac_show_map( 'lpac_display_map_on_view_order_page' );
		$show_on_order_received_page = Lpac_Functions_Helper::lpac_show_map( 'lpac_display_map_on_order_received_page' );

		if ( is_wc_endpoint_url( 'view-order' ) && $show_on_view_order_page === false ) {
			return;
		}

		if ( is_wc_endpoint_url( 'order-received' ) && $show_on_order_received_page === false ) {
			return;
		}

		if ( is_wc_endpoint_url( 'order-received' ) ) {
			$order_id = $wp->query_vars['order-received'];
		}

		if ( is_wc_endpoint_url( 'view-order' ) ) {
			$order_id = $wp->query_vars['view-order'];
		}

		if ( empty( $order_id ) ) {
			return;
		}

		$latitude  = (float) get_post_meta( $order_id, '_lpac_latitude', true );
		$longitude = (float) get_post_meta( $order_id, '_lpac_longitude', true );

		$user_location_collected_during_order = array(
			'lpac_map_order_latitude'  => $latitude,
			'lpac_map_order_longitude' => $longitude,
		);

		$this->lpac_expose_map_settings_js( $user_location_collected_during_order );

		$markup = <<<MAP
		<div class='woocommerce-shipping-fields__field-wrapper lpac-map'></div>
		<div class='woocommerce-shipping-fields__field-wrapper'>
		</div>
MAP;

		echo $markup;

		// Add inline global JS so that we can use data fetched using PHP inside JS
		wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $this->lpac_global_map_settings_js, 'before' );

	}

	/**
	 * Creates the latitude and longitude input fields.
	 *
	 * @since    1.0.0
	 * @param array $fields The fields array.
	 */
	public function lpac_create_lat_and_long_inputs( $fields ) {

		$fields['shipping']['lpac_latitude'] = array(
			'label'       => __( 'Latitude', 'lpac' ),
			'placeholder' => _x( '0000', 'placeholder', 'lpac' ),
			'required'    => false,
			'class'       => array( 'form-row-wide', 'hidden' ),
		);

		$fields['shipping']['lpac_longitude'] = array(
			'label'       => __( 'Longitude', 'lpac' ),
			'placeholder' => _x( '0000', 'placeholder', 'lpac' ),
			'required'    => false,
			'class'       => array( 'form-row-wide', 'hidden' ),
		);

		return $fields;
	}

	/**
	 * Check if the latitude or longitude inputs are filled in.
	 *
	 * @since    1.1.0
	 * @param array $order_id The order id.
	 */
	public function lpac_validate_location_fields( $fields, $errors ) {

		$error_msg = '<strong>' . __( 'Please select your location using the Google Map.', 'lpac' ) . '</strong>';

		$error_msg = apply_filters( 'lpac_checkout_empty_cords_error_msg', $error_msg );

		if ( empty( $fields['lpac_latitude'] ) || empty( $fields['lpac_longitude'] ) ) {
			$errors->add( 'validation', $error_msg );
		}

	}

	/**
	 * Save the coordinates to the database.
	 *
	 * @since    1.0.0
	 * @param array $order_id The order id.
	 */
	public function lpac_save_cords_order_meta( $order_id ) {
		update_post_meta( $order_id, '_lpac_latitude', sanitize_text_field( $_POST['lpac_latitude'] ) );
		update_post_meta( $order_id, '_lpac_longitude', sanitize_text_field( $_POST['lpac_longitude'] ) );
	}

	/**
	 * Output custom height and width for map set by user in settings.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_custom_styles() {

		$style = '';

		if ( is_wc_endpoint_url( 'order-received' ) ) {

			$order_received_map_height = get_option( 'lpac_order_received_page_map_height', 400 );
			$order_received_map_width  = get_option( 'lpac_order_received_page_map_width', 100 );

			$style = "height: {$order_received_map_height}px !important; width: {$order_received_map_width}% !important; ";

		}

		if ( is_wc_endpoint_url( 'view-order' ) ) {

			$view_order_map_height = get_option( 'lpac_view_order_page_map_height', 400 );
			$view_order_map_width  = get_option( 'lpac_view_order_page_map_width', 100 );

			$style = "height: {$view_order_map_height}px !important; width: {$view_order_map_width}% !important; ";

		}

		// We have to set the condition for !is_wc_endpoint_url() or else this setting would also apply to the order-received page
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {

			$checkout_map_height = get_option( 'lpac_checkout_page_map_height', 400 );
			$checkout_map_width  = get_option( 'lpac_checkout_page_map_width', 100 );

			$style = "height: {$checkout_map_height}px !important; width: {$checkout_map_width}% !important; ";

		}

		$output = <<<CUSTOMCSS
		<style>
			.lpac-map{
				$style
			}
		</style>
		
CUSTOMCSS;

		echo $output;
	}

	/**
	 * Create map location button link in email.
	 *
	 * @param string $link The link to google maps.
	 * @since    1.1.0
	 */
	public function lpac_create_delivery_location_link_button( $link ) {

		$button_text = __( 'Delivery Location', 'lpac' );
		$button_text = apply_filters( 'lpac_map_location_link_button_text', $button_text );
		$base_color  = get_option( 'woocommerce_email_base_color' );
		$text_color  = wc_light_or_dark( $base_color, '#202020', '#ffffff' );

		$button = <<<BUTTON
		<a href="$link" class="btn button" style="background: $base_color; border-radius: 20px; color: $text_color; display: block; margin: 30px auto; padding: 10px; text-decoration: none; text-align: center; width: 150px;" target="_blank">$button_text</a>
BUTTON;
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
		Lpac_Qr_Code_Generator::lpac_generate_qr_code( $options, $order_id );

		/*
		* https://example.com/wp-content/uploads/lpac-qr-codes/Y/m/d/order_id.jpg
		*/
		$qr_code_link = Lpac_Functions_Helper::lpac_get_qr_codes_directory( 'baseurl' ) . $order_id . '.jpg';

		echo "<img style='display: block !important; margin: 30px auto !important; text-align: center !important;' src='{$qr_code_link}'/>";

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
