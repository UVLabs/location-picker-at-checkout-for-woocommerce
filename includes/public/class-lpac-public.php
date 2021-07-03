<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Lpac
 * @subpackage Lpac/public
 * @author     Uriahs Victor <info@soaringleads.com>
 */
class Lpac_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The Google Maps CDN link.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lpac_google_maps_link    The Google Maps CDN link.
	 */
	private $lpac_google_maps_link;

	/**
	 * The Google Maps API Key.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lpac_google_api_key    The Google Maps API Key.
	 */
	private $lpac_google_api_key;

	/**
	 * The Google Maps parameters.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $lpac_google_maps_params  Additional parameters added to the google maps CDN library.
	 */
	private $lpac_google_maps_params;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct() {
		$this->plugin_name = LPAC_PLUGIN_NAME;
		$this->version     = LPAC_VERSION;

		// TODO change to use constants set in lpac.php
		$this->lpac_google_maps_link = 'https://maps.googleapis.com/maps/api/js?key=';
		$this->lpac_google_api_key   = get_option( 'lpac_google_maps_api_key' );

		$site_locale                   = get_locale();
		$this->lpac_google_maps_params = "&language={$site_locale}&libraries=&v=weekly";

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Lpac_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Lpac_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/lpac-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Lpac_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Lpac_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/lpac-public.js', array( 'jquery' ), $this->version, false );

		$show_on_view_order_page     = Lpac_Functions_Helper::lpac_show_map( 'lpac_display_map_on_view_order_page' );
		$show_on_order_received_page = Lpac_Functions_Helper::lpac_show_map( 'lpac_display_map_on_order_received_page' );

		// Only enqueue the Google Map CDN script on the needed pages
		if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) || is_checkout() ) {

			if ( is_wc_endpoint_url( 'view-order' ) && $show_on_view_order_page === false ) {
				return;
			}

			if ( is_wc_endpoint_url( 'order-received' ) && $show_on_order_received_page === false ) {
				return;
			}

			// Map CDN resource will always load on checkout page since this is the basic functionality of the plugin.
			$lpac_google_maps_resource = $this->lpac_google_maps_link . $this->lpac_google_api_key . $this->lpac_google_maps_params;
			wp_enqueue_script( LPAC_PLUGIN_NAME . '-google-maps-js', $lpac_google_maps_resource, array(), LPAC_VERSION, false );

		}

		// Enqueue our base map and page specific maps
		if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) ) {

			if ( is_wc_endpoint_url( 'view-order' ) && $show_on_view_order_page === false ) {
				return;
			}

			if ( is_wc_endpoint_url( 'order-received' ) && $show_on_order_received_page === false ) {
				return;
			}

			// Map resources will always load on checkout page since this is the basic functionality of the plugin.
			wp_enqueue_script( $this->plugin_name . '-base-map', plugin_dir_url( __FILE__ ) . 'js/maps/base-map.js', '', $this->version, true );

			if ( is_wc_endpoint_url( 'order-received' ) ) {
				wp_enqueue_script( $this->plugin_name . '-order-received-map', plugin_dir_url( __FILE__ ) . 'js/maps/order-received-map.js', array( $this->plugin_name . '-base-map' ), $this->version, true );
			} else {
				wp_enqueue_script( $this->plugin_name . '-order-details-map', plugin_dir_url( __FILE__ ) . 'js/maps/order-details-map.js', array( $this->plugin_name . '-base-map' ), $this->version, true );
			}
		}

		// Enqueue our base map and page specific maps
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {

			wp_enqueue_script( $this->plugin_name . '-base-map', plugin_dir_url( __FILE__ ) . 'js/maps/base-map.js', array( $this->plugin_name . '-google-maps-js' ), $this->version, true );
			/**
			 * This has to be enqueued in the footer so our wp_add_inline_script() function can work.
			 */
			wp_enqueue_script( $this->plugin_name . '-checkout-page-map', plugin_dir_url( __FILE__ ) . 'js/maps/checkout-page-map.js', array( $this->plugin_name . '-base-map' ), $this->version, true );

		}

	}

	/**
	 * Map settings.
	 *
	 * @since    1.0.0
	 */
	public function lpac_get_map_settings() {

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

		$data = array(
			'lpac_map_default_latitude'    => $latitude,
			'lpac_map_default_longitude'   => $longitude,
			'lpac_map_zoom_level'          => $zoom_level,
			'lpac_map_clickable_icons'     => $clickable_icons === 'yes' ? true : false,
			'lpac_map_background_color'    => $background_color,
			'lpac_autofill_billing_fields' => $fill_in_billing_fields === 'yes' ? true : false,

		);

		return apply_filters( 'lpac_map_stored_settings', $data );

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
	public function lpac_is_allowed_woocommerce_pages() {

		if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) || is_checkout() ) {
			return true;
		}

		return false;
	}

}
