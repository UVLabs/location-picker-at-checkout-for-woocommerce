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
class Lpac_Public_Display {

	private $lpac_google_maps_link;

	private $lpac_google_api_key;

	private $lpac_google_maps_options;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct(){
		$this->lpac_google_maps_link = 'https://maps.googleapis.com/maps/api/js?key=';
		$this->lpac_google_api_key = get_option( 'lpac_google_maps_api_key' ); 
		$this->lpac_google_maps_options = '&callback=initMap&libraries=&v=weekly';
	}

	/**
	 * Outputs map on the WooCommerce Checkout page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_checkout_page(){

		$lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', 'Detect Current Location' );
		
		$markup = <<<MAP
		<div class='woocommerce-shipping-fields__field-wrapper lpac-map'></div>
		<div class='woocommerce-shipping-fields__field-wrapper'>
		<button id='lpac-find-location-btn' type='button'>$lpac_find_location_btn_text</button>
		</div>
MAP;

		echo $markup;

		$lpac_google_maps_resource = $this->lpac_google_maps_link . $this->lpac_google_api_key .  $this->lpac_google_maps_options;

		wp_enqueue_script( LPAC_PLUGIN_NAME . 'google-maps-js', $lpac_google_maps_resource, '', LPAC_VERSION, false );

	}

	/**
	 * Outputs map on the WooCommerce view order page and order received page.
	 *
	 * @since    1.0.0
	 */
	public function lpac_output_map_on_order_details_page(){

		global $woocommerce, $wp;

		// If this isn't the order received page shown after a purchase, or the view order page shown on the user account, then bail.
		if( ! is_wc_endpoint_url( 'view-order' ) && ! is_wc_endpoint_url( 'order-received' ) ){
			return;
		}

		$show_on_view_order_page = Lpac_Functions_Helper::lpac_show_map('lpac_display_map_on_view_order_page');
		$show_on_order_received_page = Lpac_Functions_Helper::lpac_show_map('lpac_display_map_on_order_received_page');

		if( is_wc_endpoint_url( 'view-order' ) && $show_on_view_order_page === false ){
			return;
		}

		if( is_wc_endpoint_url( 'order-received' ) && $show_on_order_received_page === false ){
			return;
		}

		if( is_wc_endpoint_url( 'order-received' ) ){
			$order_id = $wp->query_vars['order-received'];
		}
		
		if( is_wc_endpoint_url( 'view-order' ) ){
			$order_id = $wp->query_vars['view-order'];
		}
			
		if( empty($order_id) ){
			return;
		}
		
		$latitude = (float) get_post_meta( $order_id, '_lpac_latitude', true );
		$longitude = (float) get_post_meta( $order_id, '_lpac_longitude', true );

		$data = array(
			'latitude' => $latitude,
			'longitude' => $longitude,
		);
		
		$markup = <<<MAP
		<div class='woocommerce-shipping-fields__field-wrapper lpac-map'></div>
		<div class='woocommerce-shipping-fields__field-wrapper'>
		</div>
MAP;
		
		echo $markup;
		
		$lpac_google_maps_resource = $this->lpac_google_maps_link . $this->lpac_google_api_key .  $this->lpac_google_maps_options;
		
		wp_add_inline_script( LPAC_PLUGIN_NAME . 'base-map', 'const saved_coordinates=' . json_encode($data), 'before' );
		wp_enqueue_script( LPAC_PLUGIN_NAME . 'base-google-maps-js', $lpac_google_maps_resource, '', LPAC_VERSION, false );

	}

	/**
	 * Creates the latitude and longitude input fields.
	 *
	 * @since    1.0.0
	 * @param array $fields The fields array.
	 */
	public function lpac_long_and_lat_inputs( $fields ) {
		
		$fields['shipping']['lpac_latitude'] = array(
			'label'     => __('Latitude', 'lpac'),
			'placeholder'   => _x('0000', 'placeholder', 'lpac'),
			'required'  => false,
			'class'     => array('form-row-wide', 'hidden'),
		);
		
		$fields['shipping']['lpac_longitude'] = array(
			'label'     => __('Longitude', 'lpac'),
			'placeholder'   => _x('0000', 'placeholder', 'lpac'),
			'required'  => false,
			'class'     => array('form-row-wide', 'hidden'),
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
		

		if( is_wc_endpoint_url( 'order-received' ) ){

			$order_received_map_height = get_option( 'lpac_order_received_page_map_height' );
			$order_received_map_width = get_option( 'lpac_order_received_page_map_width' );

			$style = "height: {$order_received_map_height}px !important; width: {$order_received_map_width}% !important; ";

		}

		if( is_wc_endpoint_url( 'view-order' ) ){

			$view_order_map_height = get_option( 'lpac_view_order_page_map_height' );
			$view_order_map_width = get_option( 'lpac_view_order_page_map_width' );

			$style = "height: {$view_order_map_height}px !important; width: {$view_order_map_width}% !important; ";

		}

		// We have to set the condition for !is_wc_endpoint_url() or else this setting would also apply to the order-received page
		if( is_checkout() && !is_wc_endpoint_url( 'order-received' ) ){

			$checkout_map_height = get_option( 'lpac_checkout_page_map_height' );
			$checkout_map_width = get_option( 'lpac_checkout_page_map_width' );

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