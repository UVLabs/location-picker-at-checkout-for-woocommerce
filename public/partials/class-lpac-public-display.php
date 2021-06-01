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
	$this->lpac_google_api_key = 'AIzaSyDyXUSNP_7YOsfE9DuJhCj-ssA-XHXoBuE';
	$this->lpac_google_maps_options = '&callback=initMap&libraries=&v=weekly';
}

/**
 * Outputs map on the WooCommerce Checkout page.
 *
 * @since    1.0.0
 */
public function lpac_output_map_checkout_page(){

	$lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', 'Detect Current Location' );
	
	$markup = <<<MAP
	<div class='woocommerce-shipping-fields__field-wrapper' id='lpac-map'></div>
	<div class='woocommerce-shipping-fields__field-wrapper'>
	<button id='lpac-find-location-btn' type='button'>$lpac_find_location_btn_text</button>
	</div>
MAP;
	// echo "<div class='woocommerce-shipping-fields__field-wrapper' id='lpac-map'></div>";
		
	// echo "<button id='submit' type='button'>Detect Current Location</button>";
	  echo $markup;

	  $lpac_google_maps_resource = $this->lpac_google_maps_link . $this->lpac_google_api_key .  $this->lpac_google_maps_options;

	  wp_enqueue_script( LPAC_PLUGIN_NAME . 'google-maps-js', $lpac_google_maps_resource, '', LPAC_VERSION, false );

}

/**
 * Outputs map on the WooCommerce past order details page.
 *
 * @since    1.0.0
 */
public function lpac_output_map_past_order_page(){

	global $woocommerce, $wp;

	if( ! is_wc_endpoint_url( 'view-order' ) && ! is_wc_endpoint_url( 'order-received' ) ){
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
	<div class='woocommerce-shipping-fields__field-wrapper' id='lpac-map'></div>
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
 * Displays the view on map button on the admin order page.
 *
 * @since    1.0.0
 * @param array $order The order object.
 */
// TODO Move this to admin display
public function lpac_display_lpac_admin_order_meta($order){
	
	$order_meta_text = __('Customer Location', 'lpac');
	$view_on_map_text = __('View on Map', 'lpac');
	$latitude = get_post_meta( $order->get_id(), '_lpac_latitude', true );
	$longitude = get_post_meta( $order->get_id(), '_lpac_longitude', true );
	$map_link = apply_filters('lpac_map_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

	$markup = <<<LOCATIONMETA
	<p><strong>$order_meta_text:</strong></p>
	<p><a href="$map_link" target="_blank"><button style="cursor:pointer" type='button'>$view_on_map_text</button></a></p>
LOCATIONMETA;

echo $markup;
}

}