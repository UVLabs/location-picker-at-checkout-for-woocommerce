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

public function lpac_output_map(){

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

	  $lpac_google_maps_link = 'https://maps.googleapis.com/maps/api/js?key=';
	  $lpac_google_api_key = 'AIzaSyDyXUSNP_7YOsfE9DuJhCj-ssA-XHXoBuE';
	  $lpac_google_maps_options = '&callback=initMap&libraries=&v=weekly';

	  $lpac_google_maps_resource = $lpac_google_maps_link . $lpac_google_api_key .  $lpac_google_maps_options;

	  wp_enqueue_script( LPAC_PLUGIN_NAME . 'google-maps-js', $lpac_google_maps_resource, '', LPAC_VERSION, false );

}

public function lpac_long_and_lat_inputs( $fields ) {
	
	$fields['shipping']['lpac_latitude'] = array(
		'label'     => __('Latitude', 'lpac'),
		'placeholder'   => _x('0000', 'placeholder', 'lpac'),
		'required'  => false,
		'class'     => array('form-row-wide'),
	);
	
	$fields['shipping']['lpac_longitude'] = array(
		'label'     => __('Longitude', 'lpac'),
		'placeholder'   => _x('0000', 'placeholder', 'lpac'),
		'required'  => false,
		'class'     => array('form-row-wide'),
	);

	return $fields;
}

public function lpac_save_cords_order_meta( $order_id ) {
    update_post_meta( $order_id, '_lpac_latitude', sanitize_text_field( $_POST['lpac_latitude'] ) );
    update_post_meta( $order_id, '_lpac_longitude', sanitize_text_field( $_POST['lpac_longitude'] ) );
}

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