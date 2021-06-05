<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Lpac
 * @subpackage Lpac/includes
 * @author     Uriahs Victor <info@soaringleads.com>
 */
class Lpac {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Lpac_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'LPAC_VERSION' ) ) {
			$this->version = LPAC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'lpac';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Lpac_Loader. Orchestrates the hooks of the plugin.
	 * - Lpac_i18n. Defines internationalization functionality.
	 * - Lpac_Admin. Defines all hooks for the admin area.
	 * - Lpac_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lpac-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-lpac-i18n.php';

		/**
		 * The class responsible for defining all settings of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-lpac-admin-settings.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-lpac-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the admin-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-lpac-admin-display.php';

		/**
		 * The class responsible for defining static helper functions that might
		 * be used in multiple classes.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helpers/class-lpac-functions-helper.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-lpac-public.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-lpac-public-display.php';

		$this->loader = new Lpac_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Lpac_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Lpac_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin          = new Lpac_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_admin_display  = new Lpac_Admin_Display();
		$plugin_admin_settings = new Lpac_Admin_Settings();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		// Display map on order details page
		$this->loader->add_action( 'woocommerce_admin_order_data_after_shipping_address', $plugin_admin_display, 'lpac_display_lpac_admin_order_meta', 10, 1 );

		// WooCommerce
		$this->loader->add_filter( 'woocommerce_get_sections_shipping', $plugin_admin_settings, 'lpac_add_settings_section', 10, 1 );
		$this->loader->add_filter( 'woocommerce_get_settings_shipping', $plugin_admin_settings, 'lpac_plugin_settings', 10, 2 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public         = new Lpac_Public( $this->get_plugin_name(), $this->get_version() );
		$plugin_public_display = new Lpac_Public_Display();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public_display, 'lpac_output_map_custom_styles' );

		// WooCommerce

		$location = get_option( 'lpac_checkout_map_orientation', 'shipping_address_area_bottom' );

		switch ( $location ) {
			case 'billing_address_area_top':
				$location = 'woocommerce_before_checkout_billing_form';
				break;
			case 'billing_address_area_bottom':
				$location = 'woocommerce_after_checkout_billing_form';
				break;
			case 'shipping_address_area_top':
				$location = 'woocommerce_before_checkout_shipping_form';
				break;
			case 'shipping_address_area_bottom':
				$location = 'woocommerce_after_checkout_shipping_form';
				break;
			default:
				$location = 'woocommerce_after_checkout_shipping_form';
				break;
		}

		$location = apply_filters( 'lpac_checkout_map_orientation', $location );

		$this->loader->add_action( $location, $plugin_public_display, 'lpac_output_map_on_checkout_page' );
		$this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public_display, 'lpac_long_and_lat_inputs' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public_display, 'lpac_save_cords_order_meta' );
		$this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public_display, 'lpac_output_map_on_order_details_page' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Lpac_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
