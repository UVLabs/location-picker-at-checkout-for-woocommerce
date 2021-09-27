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

		/**
		 * Shipping methods Admin has decided to hide the map for.
		 */
		$disallowed_shipping_methods = get_option( 'lpac_wc_shipping_methods', array() );
		$disallowed_shipping_methods = json_encode( $disallowed_shipping_methods );

		$global_variables = <<<GLOBALVARS
		// LPAC Map Options
		var map_options = $map_options;
		var saved_disallowed_shipping_methods = $disallowed_shipping_methods;
GLOBALVARS;

		$this->lpac_global_map_settings_js = $global_variables;

	}

	/**
	 * Outputs map on the WooCommerce Checkout page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_checkout_page() {

		if ( Lpac_Functions_Helper::lpac_show_map( 'checkout' ) === false ) {
			return;
		}

		$btn_text = __( 'Detect Current Location', 'lpac' );

		$lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', $btn_text );
		$instuctions_text            = __( 'Click the "Detect Current Location" button then move the red marker to your desired shipping address.', 'lpac' );
		$instuctions_text            = apply_filters( 'lpac_map_instuctions_text', $instuctions_text );

		$user_id = (int) get_current_user_id();

		$saved_addresses_area = apply_filters( 'lpac_saved_addresses', '', $user_id );
		$saved_addresses_area = wp_kses_post( $saved_addresses_area );

		$before_map_filter = apply_filters( 'lpac_before_map', '', $user_id );
		$before_map_filter = wp_kses_post( $before_map_filter );

		$after_map_filter = apply_filters( 'lpac_after_map', '', $user_id );
		$after_map_filter = wp_kses_post( $after_map_filter );

		$before_map_controls_filter = apply_filters( 'lpac_before_map_controls', '', $user_id );
		$before_map_controls_filter = wp_kses_post( $before_map_controls_filter );

		$after_map_controls_filter = apply_filters( 'lpac_after_map_controls', '', $user_id );
		$after_map_controls_filter = wp_kses_post( $after_map_controls_filter );

		$markup = <<<MAP
		<div id="lpac-map-container" class='woocommerce-shipping-fields__field-wrapper'>
			$before_map_filter
			<div class='lpac-map'></div>
			$after_map_filter
			<div class='lpac-map-controls'>
			$before_map_controls_filter
			<div style="font-size: 10px">$instuctions_text</div>
			<button id='lpac-find-location-btn' type='button'>$lpac_find_location_btn_text</button>
			<div id='lpac-saved-addresses'><ul>$saved_addresses_area</ul></div>
			$after_map_controls_filter
			</div>
		</div>
MAP;

		echo apply_filters( 'lpac_map_markup', $markup, $user_id );

		// Add inline global JS so that we can use data fetched using PHP inside JS
		wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $this->lpac_global_map_settings_js, 'before' );

	}

	/**
	 * Outputs map on the WooCommerce view order page and order received page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_order_details_page() {

		global $wp;

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

		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$user_location_collected_during_order = array(
			'lpac_map_order_latitude'  => $latitude,
			'lpac_map_order_longitude' => $longitude,
		);

		$this->lpac_expose_map_settings_js( $user_location_collected_during_order );

		$markup = <<<MAP
		<div id="lpac-map-container" class='woocommerce-shipping-fields__field-wrapper'>
		<div class='lpac-map'></div>
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

		$fields['billing']['lpac_latitude'] = array(
			'label'    => __( 'Latitude', 'lpac' ),
			'required' => false,
			'class'    => array( 'form-row-wide', 'hidden' ),
		);

		$fields['billing']['lpac_longitude'] = array(
			'label'    => __( 'Longitude', 'lpac' ),
			'required' => false,
			'class'    => array( 'form-row-wide', 'hidden' ),
		);

		$fields['billing']['lpac_is_map_shown'] = array(
			'label'    => __( 'Map Shown', 'lpac' ),
			'required' => false,
			'class'    => array( 'form-row-wide', 'hidden' ),
		);

		return $fields;
	}

	/**
	 * Check if the latitude or longitude inputs are filled in.
	 *
	 * @since    1.1.0
	 * @param array $order_id The order id.
	 *
	 * @return void
	 */
	public function lpac_validate_location_fields( $fields, $errors ) {

		/**
		 * The map visibility might be changed via JS or other conditions
		 * So we need to check if its actually shown before trying to validate
		 */
		$map_shown = (bool) $fields['lpac_is_map_shown'];

		if ( $map_shown === false ) {
			return;
		}

		/**
		 * Allow users to override this setting
		 */
		$custom_override = apply_filters( 'lpac_override_map_validation', false, $fields, $errors );

		if ( $custom_override === true ) {
			return;
		}

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

		$latitude  = $_POST['lpac_latitude'] ?? '';
		$longitude = $_POST['lpac_longitude'] ?? '';
		$map_shown = $_POST['lpac_is_map_shown'] ?? '';

		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		// If the map was not shown for this order don't save the coordinates.
		if ( empty( $map_shown ) ) {
			return;
		}

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

	public function lpac_add_admin_checkout_notice() {

		$hide_notice = get_option( 'lpac_hide_troubleshooting_admin_checkout_notice', 'no' );

		if ( $hide_notice === 'yes' ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( empty( $user_id ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notice_text = esc_html__( 'Hi Admin, some websites might have issues with displaying or using the Google Map. If you\'re having issues then please have a look at your browser console for any errors.' );
		$additional  = esc_html__( 'Only Admins on your website can see this notice. You can turn it off in the plugin settings if everything works fine.' );

		$markup = <<<MARKUP
		<div class="lpac-admin-notice" style="background: #246df3; color: #ffffff; text-align: center; margin-bottom: 20px; padding: 10px;">
			<p style="font-size:14px; "><span style="font-weight: bold">LPAC: </span>
				$notice_text
			</p>
			<p style="font-size:12px; font-weight: bold;" >
				$additional
			</p>
		</div>
MARKUP;

		echo $markup;

	}

}
