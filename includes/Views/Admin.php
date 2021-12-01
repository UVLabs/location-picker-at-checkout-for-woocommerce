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
 */
namespace Lpac\Views;

use Lpac\Helpers\Functions;

class Admin {

	/**
	 * Add our WooCommerce settings tab
	 *
	 * @param mixed $settings Array of existing WooCommerce settings tab
	 * @return mixed
	 */
	public function lpac_add_settings_tab( $settings ) {
		$settings[] = new \Lpac\Views\Admin_Settings;
		return $settings;
	}

	/**
	 * Displays the view on map button on the admin order page.
	 *
	 * @since    1.0.0
	 * @param object $order The order object.
	 */
	public function lpac_display_lpac_admin_order_meta( $order ) {

		$latitude                 = get_post_meta( $order->get_id(), '_lpac_latitude', true );
		$longitude                = get_post_meta( $order->get_id(), '_lpac_longitude', true );
		$places_autocomplete_used = get_post_meta( $order->get_id(), '_places_autocomplete', true );

		/* translators: 1: Dashicons outbound link icon*/
		$learn_more = sprintf( __( 'Learn More %s', 'map-location-picker-at-checkout-for-woocommerce' ), '<span style="text-decoration: none" class="dashicons dashicons-external"></span>' );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$order_meta_text  = esc_html( __( 'Customer Location', 'map-location-picker-at-checkout-for-woocommerce' ) );
		$view_on_map_text = esc_html( __( 'View on Map', 'map-location-picker-at-checkout-for-woocommerce' ) );

		$places_autocomplete_used_text = '';
		if ( ! empty( $places_autocomplete_used ) ) {
			$places_autocomplete_used_text = sprintf( esc_html( __( 'It looks like this customer used the Places Autocomplete feature. The coordinates on the map might be an approximation. %s' ) ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/#accuracy-of-places-autocomplete' target='_blank'> $learn_more </a>" );
		}

		$map_link = apply_filters( 'lpac_map_provider', "https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}", $latitude, $longitude );

		$markup = <<<HTML
		<p><strong>$order_meta_text:</strong></p>
		<p><a href="$map_link" target="_blank"><button style="cursor:pointer" type='button'>$view_on_map_text</button></a></p>
		<p style="font-size: 12px">$places_autocomplete_used_text</p>
HTML;

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

		add_meta_box( 'lpac_delivery_map_metabox', __( 'Delivery Location', 'map-location-picker-at-checkout-for-woocommerce' ), array( $this, 'lpac_output_custom_order_details_metabox' ), 'shop_order', 'normal', 'high' );
	}

	/**
	 * Outputs the HTML for the metabox
	 *
	 * @since    1.1.2
	 */
	public function lpac_output_custom_order_details_metabox() {

		$id = get_the_ID();

		$latitude           = (float) get_post_meta( $id, '_lpac_latitude', true );
		$longitude          = (float) get_post_meta( $id, '_lpac_longitude', true );
		$shipping_address_1 = get_post_meta( $id, '_shipping_address_1', true );
		$shipping_address_2 = get_post_meta( $id, '_shipping_address_2', true );

		/**
		 * If we have no values for these options bail.
		 */
		if ( empty( $latitude ) || empty( $longitude ) ) {
			return;
		}

		$order_location_details = array(
			'latitude'           => $latitude,
			'longitude'          => $longitude,
			'shipping_address_1' => $shipping_address_1,
			'shipping_address_2' => $shipping_address_2,
		);

		$options = Functions::set_map_options();

		$data = array(
			'lpac_map_default_latitude'    => $options['latitude'],
			'lpac_map_default_longitude'   => $options['longitude'],
			'lpac_map_zoom_level'          => $options['zoom_level'],
			'lpac_map_clickable_icons'     => $options['clickable_icons'] === 'yes' ? true : false,
			'lpac_map_background_color'    => $options['background_color'],
			'lpac_autofill_billing_fields' => $options['fill_in_billing_fields'] === 'yes' ? true : false,

		);

		$order_location_details = json_encode( $order_location_details );
		$map_options            = json_encode( $data );

		$global_variables = <<<JAVASCRIPT
	// Lpac Order Location Details
	var locationDetails = $order_location_details;
	// Lpac Map Settings
	var mapOptions = $map_options;
JAVASCRIPT;

		// Expose JS variables for usage
		wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $global_variables, 'before' );

		$map_container = <<<HTML
			<div id="wrap" style="display: block; text-align: center;">
			<div class="lpac-map" style="display: inline-block; padding 10; border: 1px solid #eee; width: 100%; height:345px;"></div>
			</div>
HTML;

		echo $map_container;

	}

	/**
	 * Create a custom button that can be used on the plugin's settings page.
	 *
	 * @param array $value
	 * @return void
	 */
	public function create_customer_wc_settings_button( $value ) {

		$btn_class   = $value['class'];
		$btn_id      = $value['id'];
		$btn_text    = $value['value'];
		$name        = $value['name'];
		$description = $value['desc'];

		$markup = <<<HTML
				<tr valign='top'>
				<th scope='row' class='titledesc'>$name</th>
				<td>
					<button id="$btn_id" class="$btn_class">$btn_text</button>
					<p class="description">$description</p>
				</td>
				</tr>
HTML;
		echo $markup;

	}

}
