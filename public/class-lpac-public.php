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
		$this->version = $version;

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
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/map.js', '', $this->version, false );
		wp_enqueue_script( $this->plugin_name . 'map', plugin_dir_url( __FILE__ ) . 'js/map.js', '', $this->version, false );

   
	//   $lpac_google_maps_link = 'https://maps.googleapis.com/maps/api/js?key=';
	//   $lpac_google_api_key = 'AIzaSyDyXUSNP_7YOsfE9DuJhCj-ssA-XHXoBuE';
	//   $lpac_google_maps_options = '&callback=initMap&libraries=&v=weekly';

	//   $lpac_google_maps_resource = $lpac_google_maps_link . $lpac_google_api_key .  $lpac_google_maps_options;

	//   wp_enqueue_script( $this->plugin_name . 'google-maps-js', $lpac_google_maps_resource, $this->plugin_name . 'map', $this->version, false );

	}

}
