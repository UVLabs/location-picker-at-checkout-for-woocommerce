<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/admin/partials
 */
 class Lpac_Admin_Display{


/**
 * Displays the view on map button on the admin order page.
 *
 * @since    1.0.0
 * @param array $order The order object.
 */
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