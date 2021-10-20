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
 */
namespace Lpac\Views;

use Lpac\Controllers\Map_Visibility_Controller;
use Lpac\Helpers\Functions;

class Frontend {

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
	 * Map settings.
	 *
	 * @since    1.0.0
	 */
	public function lpac_get_map_settings() {

		$options = Functions::get_map_options();

		$data = array(
			'lpac_map_default_latitude'    => $options['latitude'],
			'lpac_map_default_longitude'   => $options['longitude'],
			'lpac_map_zoom_level'          => $options['zoom_level'],
			'lpac_map_clickable_icons'     => $options['clickable_icons'] === 'yes' ? true : false,
			'lpac_map_background_color'    => $options['background_color'],
			'lpac_autofill_billing_fields' => $options['fill_in_billing_fields'] === 'yes' ? true : false,

		);

		return apply_filters( 'lpac_map_stored_public_settings', $data );

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

		$global_variables = <<<JAVASCRIPT
		// LPAC Map Options
		var map_options = $map_options;
JAVASCRIPT;

		$this->lpac_global_map_settings_js = $global_variables;

	}

	/**
	 * Outputs map on the WooCommerce Checkout page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_checkout_page() {

		// Map div display visibility
		$display = 'block';

		if ( Map_Visibility_Controller::lpac_show_map( 'checkout' ) === false ) {
			$display = 'none';
		}

		$btn_text = __( 'Detect Current Location', 'map-location-picker-at-checkout-for-woocommerce' );

		$lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', $btn_text );
		$instuctions_text            = __( 'Click the "Detect Current Location" button then move the red marker to your desired shipping address.', 'map-location-picker-at-checkout-for-woocommerce' );
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

		// TODO use this filter to create our saved address field
		$after_map_controls_filter = apply_filters( 'lpac_after_map_controls', '', $user_id );
		$after_map_controls_filter = wp_kses_post( $after_map_controls_filter );

		$markup = <<<HTML
		<div style="display: $display" id="lpac-map-container" class='woocommerce-shipping-fields__field-wrapper'>
			$before_map_filter
			<div class='lpac-map'></div>
			$after_map_filter
			<div class='lpac-map-controls'>
			$before_map_controls_filter
			<div id='lpac-map-instructions'>$instuctions_text</div>
			<div id='lpac-find-location-btn-wrapper'><button id='lpac-find-location-btn' class="button btn" type='button'>$lpac_find_location_btn_text</button></div>
			<div id='lpac-saved-addresses'><ul>$saved_addresses_area</ul></div>
			$after_map_controls_filter
			</div>
		</div>
HTML;

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

		$show_on_view_order_page     = Map_Visibility_Controller::lpac_show_map( 'lpac_display_map_on_view_order_page' );
		$show_on_order_received_page = Map_Visibility_Controller::lpac_show_map( 'lpac_display_map_on_order_received_page' );

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

		$latitude           = (float) get_post_meta( $order_id, '_lpac_latitude', true );
		$longitude          = (float) get_post_meta( $order_id, '_lpac_longitude', true );
		$shipping_address_1 = get_post_meta( $order_id, '_shipping_address_1', true );
		$shipping_address_2 = get_post_meta( $order_id, '_shipping_address_2', true );

		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$user_location_collected_during_order = array(
			'lpac_map_order_latitude'           => $latitude,
			'lpac_map_order_longitude'          => $longitude,
			'lpac_map_order_shipping_address_1' => $shipping_address_1,
			'lpac_map_order_shipping_address_2' => $shipping_address_2,
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
			'label'    => __( 'Latitude', 'map-location-picker-at-checkout-for-woocommerce' ),
			'required' => false,
			'class'    => ( LPAC_DEBUG ) ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hidden' ),
		);

		$fields['billing']['lpac_longitude'] = array(
			'label'    => __( 'Longitude', 'map-location-picker-at-checkout-for-woocommerce' ),
			'required' => false,
			'class'    => ( LPAC_DEBUG ) ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hidden' ),
		);

		$fields['billing']['lpac_is_map_shown'] = array(
			'label'    => __( 'Map Shown', 'map-location-picker-at-checkout-for-woocommerce' ),
			'required' => false,
			'class'    => ( LPAC_DEBUG ) ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hidden' ),
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

		$error_msg = '<strong>' . __( 'Please select your location using the Google Map.', 'map-location-picker-at-checkout-for-woocommerce' ) . '</strong>';

		$error_msg = apply_filters( 'lpac_checkout_empty_cords_error_msg', $error_msg );

		if ( empty( $fields['lpac_latitude'] ) || empty( $fields['lpac_longitude'] ) ) {
			$errors->add( 'validation', $error_msg );
		}

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
	 * Show a notice banner to admins on the frontend.
	 *
	 * @since    1.0.0
	 */
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
		$additional  = esc_html__( 'Only Admins on your website can see this notice. You can turn it off in the plugin settings from the "Debug" submenu if everything works fine.' );

		$markup = <<<MARKUP
		<div class="lpac-admin-notice" style="background: #246df3; text-align: center; margin-bottom: 20px; padding: 10px;">
			<p style=" color: #ffffff !important; font-size:14px;"><span style="font-weight: bold">LPAC: </span>
				$notice_text
			</p>
			<p style=" color: #ffffff !important; font-size:12px; font-weight: bold;" >
				$additional
			</p>
		</div>
MARKUP;

		echo $markup;

	}

}
