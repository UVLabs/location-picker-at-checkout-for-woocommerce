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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

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

		// $show_on_view_order_page = Lpac_Functions_Helper::lpac_show_map('lpac_display_map_on_view_order_page');
		// $show_on_order_received_page = Lpac_Functions_Helper::lpac_show_map('lpac_display_map_on_order_received_page');

		if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) ) {
			wp_enqueue_script( $this->plugin_name . 'base-map', plugin_dir_url( __FILE__ ) . 'js/base-map.js', '', $this->version, true );
		}

		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) {
			wp_enqueue_script( $this->plugin_name . 'map', plugin_dir_url( __FILE__ ) . 'js/map.js', '', $this->version, true );
		}

	}

	/**
	 * Map settings.
	 *
	 * @since    1.0.0
	 */
	public function lpac_map_settings() {

		$starting_coordinates = get_option( 'lpac_map_starting_coordinates', '14.024519,-60.974876' );
		$starting_coordinates = apply_filters( 'lpac_map_starting_coordinates', $starting_coordinates );

		$coordinates_parts = explode( ',', $starting_coordinates );
		$latitude          = ! empty( $coordinates_parts[0] ) ? (float) $coordinates_parts[0] : (float) 14.024519;
		$longitude         = ! empty( $coordinates_parts[1] ) ? (float) $coordinates_parts[1] : (float) -60.974876;

		$zoom_level = get_option( 'lpac_general_map_zoom_level', 16 );
		$zoom_level = apply_filters( 'lpac_general_map_zoom_level', $zoom_level );

		$clickable_icons = get_option( 'lpac_allow_clicking_on_map_icons', 'yes' );
		$clickable_icons = apply_filters( 'lpac_allow_clicking_on_map_icons', $clickable_icons );

		$data = array(
			'latitude'        => $latitude,
			'longitude'       => $longitude,
			'zoom_level'      => $zoom_level,
			'clickable_icons' => $clickable_icons === 'yes' ? "true" : "false",
		);

		return $data;

	}

}
