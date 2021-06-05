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
		$instuctions_text            = __( 'Move the red marker to your desired shipping address.', 'lpac' );
		$instuctions_text            = apply_filters( 'lpac_map_instuctions_text', $instuctions_text );

		$markup = <<<MAP
		<div class='woocommerce-shipping-fields__field-wrapper lpac-map'></div>
		<div class='woocommerce-shipping-fields__field-wrapper'>
		<small>$instuctions_text<small>
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
	public function lpac_long_and_lat_inputs( $fields ) {

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

}
