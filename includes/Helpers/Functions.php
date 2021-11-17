<?php

/**
 * Provide helper static functions.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 */
namespace Lpac\Helpers;

class Functions {

	/**
	 * Determine whether to show the map based on shipping class.
	 *
	 * @param array $selected_shipping_classes_ids the shipping class selected by the admin in settings.
	 *
	 * @return bool whether or not to show the map.
	 */
	public static function lpac_should_show_shipping_class( $selected_shipping_classes_ids ) {

		/**
		 * Get the current order being checkedout zone ID
		 */
		$order_shipping_classes_ids = array_keys( self::lpac_get_order_shipping_classes() );

		/**
		 * Check if any of the shipping classes in this order was selected by the admin in settings.
		 */
		$has_match = array_intersect( $selected_shipping_classes_ids, $order_shipping_classes_ids );

		/**
		 * Get the Show/Hide option selected by the admin for shipping classes.
		 */
		$shown_hidden = get_option( 'lpac_wc_shipping_classes_show_hide' );

		/**
		 * If the order being checked out has a shipping class ID that exists in our list, and the admin
		 * set the option to "Show" from LPAC settings, then show the map and load it's assets.
		 *
		 * Because the admin chose to show the map only for orders that contain the shipping class they selected in the plugin settings.
		 */
		if ( ! empty( $has_match ) && $shown_hidden === 'show' ) {
			return true;
		}

		/**
		 * If the order being checked out has a shipping class ID that doesn't exists in our list, and the admin
		 * set the option to "Hide" from LPAC settings, then show the map and load it's assets.
		 *
		 * Because the admin chose to show the map only for orders that DO NOT contain the shipping class they selected in the plugin settings.
		 */
		if ( empty( $has_match ) && $shown_hidden === 'hide' ) {
			return true;
		}

		/**
		 * Return false(hide the map) in all other situations.
		 */
		return false;

	}

	/**
	 * Create QR Code directory string based on the basedir or baseurl.
	 *
	 * @return string The qr code resource server path or url path
	 * @since    1.1.0
	 */
	public static function lpac_get_qr_codes_directory( $base ) {

		$qr_code_resource_base = wp_upload_dir()[ $base ];

		$qr_code_resource_locator = $qr_code_resource_base . '/' . 'lpac-qr-codes' . '/' . date( 'Y' ) . '/' . date( 'm' ) . '/' . date( 'd' ) . '/';

		return $qr_code_resource_locator;

	}

	/**
	* Normalize available shipping classes for use.
	*
	* Get the list of available shipping classes on the site and get them ready for use in the multiselect settings field of the plugin.
	*
	* @since    1.2.0
	*
	* @return array Array of available shipping classes.
	*/
	public static function lpac_get_available_shipping_classes() {

		if ( ! class_exists( 'WC_Shipping' ) ) {
			error_log( 'Location Picker at Checkout for WooCommerce: WC_Shipping() class not found.' );
			return array();
		}

		$lpac_wc_shipping_classes = ( new \WC_Shipping() )->get_shipping_classes();

		if ( ! is_array( $lpac_wc_shipping_classes ) ) {
			return array();
		}

		$normalized_shipping_classes = array();

		foreach ( $lpac_wc_shipping_classes as $shipping_class_object ) {

			$iterated_shipping_class = array(
				$shipping_class_object->term_id => $shipping_class_object->name,
			);

			/**
			 * We need to keep our term_id as the key so we can later use. array_merge would reset the keys.
			 */
			$normalized_shipping_classes = array_replace( $normalized_shipping_classes, $iterated_shipping_class );

		}

		return $normalized_shipping_classes;
	}

	/**
	* Get current shipping class at checkout.
	*
	* Gets the current shipping class the customer order falls in (based on the shipping class settings in WC settings).
	*
	* @since    1.2.0
	*
	* @return array Array of shipping classes present for this order.
	*/
	public static function lpac_get_order_shipping_classes() {

		$cart = WC()->cart->get_cart();

		if ( ! is_array( $cart ) ) {
			return array();
		}

		$shipping_class_array = array();

		foreach ( $cart as $cart_item ) {

			$shipping_class_id   = $cart_item['data']->get_shipping_class_id();
			$shipping_class_name = $cart_item['data']->get_shipping_class();

			$shipping_class_array[ $shipping_class_id ] = $shipping_class_name;
		}

		return $shipping_class_array;

	}

	/**
	 *
	 * Get all available shipping methods from the shipping zones created by users.
	 *
	 * @return array
	 */
	public static function lpac_get_available_shipping_methods() {

		if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
			error_log( 'Location Picker at Checkout for WooCommerce: WC_Shipping_Zones() class not found.' );
			return array();
		}

		$zones = \WC_Shipping_Zones::get_zones();

		if ( ! is_array( $zones ) ) {
			return array();
		}

		$shipping_methods = array_column( $zones, 'shipping_methods' );

		$flatten = array_merge( ...$shipping_methods );

		$normalized_shipping_methods = array();

		foreach ( $flatten as $key => $class ) {
			$normalized_shipping_methods[ $class->instance_id ] = $class->title . ' (ID: ' . $class->instance_id . ')';
		}

		return $normalized_shipping_methods;

	}

	/**
	 * Get all available coupons on the website.
	 *
	 * @return mixed
	 */
	public static function get_available_coupons() {

		$coupon_posts = get_posts(
			array(
				'posts_per_page' => 200,
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
			)
		);

		if ( empty( $coupon_posts ) ) {
			return array();
		}

		$coupon_codes = array();

		foreach ( $coupon_posts as $coupon_post ) {
			$coupon_codes[ $coupon_post->post_name ] = $coupon_post->post_title;
		}

		return $coupon_codes;

	}

	/**
	 * Get default map options;
	 *
	 * @return array
	 */
	public static function set_map_options() {

		$starting_coordinates = get_option( 'lpac_map_starting_coordinates', '14.024519,-60.974876' );
		$starting_coordinates = apply_filters( 'lpac_map_starting_coordinates', $starting_coordinates );

		$coordinates_parts = explode( ',', $starting_coordinates );
		$latitude          = ! empty( $coordinates_parts[0] ) ? (float) $coordinates_parts[0] : (float) 14.024519;
		$longitude         = ! empty( $coordinates_parts[1] ) ? (float) $coordinates_parts[1] : (float) -60.974876;

		$zoom_level = (int) get_option( 'lpac_general_map_zoom_level', 16 );
		$zoom_level = apply_filters( 'lpac_general_map_zoom_level', $zoom_level );

		$clickable_icons = get_option( 'lpac_allow_clicking_on_map_icons', 'yes' );
		$clickable_icons = apply_filters( 'lpac_allow_clicking_on_map_icons', $clickable_icons );

		$background_color = get_option( 'lpac_map_background_color', '#eee' );
		$background_color = apply_filters( 'lpac_map_background_color', $background_color );

		$fill_in_billing_fields = get_option( 'lpac_autofill_billing_fields', 'yes' );
		$fill_in_billing_fields = apply_filters( 'lpac_autofill_billing_fields', $fill_in_billing_fields );

		$remove_address_plus_code = get_option( 'lpac_remove_address_plus_code', 'no' );
		$remove_address_plus_code = apply_filters( 'lpac_remove_address_plus_code', $remove_address_plus_code );

		$enable_places_search            = get_option( 'lpac_enable_places_autocomplete', 'no' );
		$places_search_fields            = get_option( 'lpac_places_autocomplete_fields', array() );
		$places_search_fields            = apply_filters( 'lpac_places_autocomplete_fields', $places_search_fields );
		$places_autocomplete_hide_map    = get_option( 'lpac_places_autocomplete_hide_map', 'no' );
		$places_fill_shipping_fields     = apply_filters( 'lpac_places_fill_shipping_fields', true );
		$places_fill_billing_fields      = apply_filters( 'lpac_places_fill_billing_fields', true );
		$wc_shipping_destination_setting = get_option( 'woocommerce_ship_to_destination' );

		$auto_detect_location = get_option( 'lpac_auto_detect_location', 'no' );

		$options = array(
			'latitude'                        => $latitude,
			'longitude'                       => $longitude,
			'zoom_level'                      => $zoom_level,
			'clickable_icons'                 => $clickable_icons,
			'background_color'                => $background_color,
			'fill_in_billing_fields'          => $fill_in_billing_fields,
			'remove_address_plus_code'        => $remove_address_plus_code,
			'enable_places_search'            => $enable_places_search,
			'places_search_fields'            => $places_search_fields,
			'places_autocomplete_hide_map'    => $places_autocomplete_hide_map,
			'places_fill_shipping_fields'     => $places_fill_shipping_fields,
			'places_fill_billing_fields'      => $places_fill_billing_fields,
			'auto_detect_location'            => $auto_detect_location,
			'wc_shipping_destination_setting' => $wc_shipping_destination_setting,
		);

		return apply_filters( 'lpac_map_options', $options );
	}

	/**
	* Detect needed Woocommerce pages.
	*
	* Detect if the page is one of which the map is supposed to show.
	*
	* @since    1.1.0
	*
	* @return bool Whether or not the page is one of our needed pages.
	*/
	public static function is_allowed_woocommerce_pages() {

		if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) || is_checkout() ) {
			return true;
		}

		if ( is_admin() && get_current_screen()->id === 'shop_order' ) {
			return true;
		}

		return false;
	}

}
