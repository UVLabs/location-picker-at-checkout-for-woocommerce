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
namespace Lpac\Bootstrap;

use  Lpac\Bootstrap\Loader ;
use  Lpac\Bootstrap\I18n ;
use  Lpac\Bootstrap\Admin_Enqueues ;
use  Lpac\Bootstrap\Frontend_Enqueues ;
use  Lpac\Controllers\Emails_Controller ;
use  Lpac\Views\Admin as Admin_Display ;
use  Lpac\Notices\Admin as Admin_Notices ;
use  Lpac\Views\Frontend as Frontend_Display ;
/**
* Class Main.
*
* Class responsible for firing public and admin hooks.
*
*/
class Main
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Lpac_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        
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
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        $this->loader = new Loader();
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
    private function set_locale()
    {
        $plugin_i18n = new I18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Admin_Enqueues();
        $plugin_admin_display = new Admin_Display();
        $admin_notices = new Admin_Notices();
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Notices
        $this->loader->add_action( 'admin_notices', $admin_notices, 'lpac_wc_not_active_notice' );
        $this->loader->add_action( 'admin_notices', $admin_notices, 'lpac_site_not_https' );
        // Display map on order details page
        $this->loader->add_action(
            'woocommerce_admin_order_data_after_shipping_address',
            $plugin_admin_display,
            'lpac_display_lpac_admin_order_meta',
            10,
            1
        );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin_display, 'lpac_create_custom_order_details_metabox' );
        $this->loader->add_action( 'woocommerce_get_settings_pages', $plugin_admin_display, 'lpac_add_settings_tab' );
    }
    
    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Frontend_Enqueues();
        $plugin_public_display = new Frontend_Display();
        $controller_emails = new Emails_Controller();
        /*
         * If plugin not enabled don't continue
         */
        $plugin_enabled = get_option( 'lpac_enabled', 'yes' );
        if ( $plugin_enabled === 'no' ) {
            return;
        }
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_head', $plugin_public_display, 'lpac_output_map_custom_styles' );
        /*
         * Output hidden input fields for latitude and longitude.
         */
        $this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public_display, 'lpac_create_lat_and_long_inputs' );
        /*
         * Output map on checkout page
         */
        $checkout_page_map_location = get_option( 'lpac_checkout_map_orientation', 'woocommerce_before_checkout_billing_form' );
        $checkout_page_map_location = apply_filters( 'lpac_checkout_map_orientation', $checkout_page_map_location );
        $this->loader->add_action( $checkout_page_map_location, $plugin_public_display, 'lpac_output_map_on_checkout_page' );
        /*
         * Output map on order received and order details pages.
         */
        $this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public_display, 'lpac_output_map_on_order_details_page' );
        /*
         * Save location coordinates to order meta.
         */
        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public_display, 'lpac_save_cords_order_meta' );
        /*
         * Validate latitude and longitude fields.
         */
        $validate_lat_long_fields = get_option( 'lpac_force_map_use', false );
        if ( $validate_lat_long_fields === 'yes' ) {
            $this->loader->add_action(
                'woocommerce_after_checkout_validation',
                $plugin_public_display,
                'lpac_validate_location_fields',
                10,
                2
            );
        }
        /*
         * Display map button link or qr code link in email.
         */
        $enable_map_link_in_email = get_option( 'lpac_enable_delivery_map_link_in_email' );
        
        if ( $enable_map_link_in_email === 'yes' ) {
            $email_map_link_location = get_option( 'lpac_email_delivery_map_link_location' );
            $email_map_link_location = apply_filters( 'lpac_email_delivery_map_link_location', $email_map_link_location );
            $this->loader->add_action(
                $email_map_link_location,
                $controller_emails,
                'lpac_add_delivery_location_link_to_email',
                20,
                4
            );
        }
        
        $this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public_display, 'lpac_add_admin_checkout_notice' );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Lpac_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}