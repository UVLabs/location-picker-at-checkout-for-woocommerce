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
class Lpac_Admin_Display extends Lpac_Admin {

	/**
	 * Displays the view on map button on the admin order page.
	 *
	 * @since    1.0.0
	 * @param array $order The order object.
	 */
	public function lpac_display_lpac_admin_order_meta( $order ) {

		$latitude  = get_post_meta( $order->get_id(), '_lpac_latitude', true );
		$longitude = get_post_meta( $order->get_id(), '_lpac_longitude', true );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$order_meta_text  = __( 'Customer Location', 'lpac' );
		$view_on_map_text = __( 'View on Map', 'lpac' );

		$map_link = apply_filters( 'lpac_map_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

		$markup = <<<LOCATIONMETA
	<p><strong>$order_meta_text:</strong></p>
	<p><a href="$map_link" target="_blank"><button style="cursor:pointer" type='button'>$view_on_map_text</button></a></p>
LOCATIONMETA;

		echo $markup;
	}

	/**
	 * Create the metabox for holding the map view in admin order details.
	 *
	 * @since    1.1.2
	 */
	public function lpac_create_custom_order_details_metabox() {

		$latitude  = (float) get_post_meta( get_the_ID(), '_lpac_latitude', true );
		$longitude = (float) get_post_meta( get_the_ID(), '_lpac_longitude', true );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		add_meta_box( 'lpac_delivery_map_metabox', __( 'Delivery Location', 'lpac' ), array( $this, 'lpac_output_custom_order_details_metabox' ), 'shop_order', 'normal', 'high' );
	}

	/**
	 * Outputs the HTML for the metabox
	 *
	 * @since    1.1.2
	 */
	public function lpac_output_custom_order_details_metabox() {

		$latitude  = (float) get_post_meta( get_the_ID(), '_lpac_latitude', true );
		$longitude = (float) get_post_meta( get_the_ID(), '_lpac_longitude', true );

		$order_coordinates = array(
			'latitude'  => $latitude,
			'longitude' => $longitude,
		);

		$order_coordinates = json_encode( $order_coordinates );

		$global_variables = <<<GLOBALVARS
	// LPAC Order delivery coordinates
	var coordinates = $order_coordinates;
GLOBALVARS;

		wp_add_inline_script( LPAC_PLUGIN_NAME . '-order-map', $global_variables, 'before' );

		$map_container = <<<DIV
			<div id="wrap" style="display: block; text-align: center;">
			<div id="lpac-map" style="display: inline-block; padding 10; border: 1px solid #eee; width: 100%; height:345px;"></div>
			</div>
DIV;

		echo $map_container;

	}

}
