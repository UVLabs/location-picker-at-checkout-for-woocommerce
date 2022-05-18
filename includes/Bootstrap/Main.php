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
use  Lpac\Controllers\Map_Visibility_Controller ;
use  Lpac\Controllers\Admin_Settings_Controller ;
use  Lpac\Controllers\Checkout_Page_Controller ;
use  Lpac\Views\Admin as Admin_Display ;
use  Lpac\Notices\Admin as Admin_Notices ;
use  Lpac\Notices\Notice ;
use  Lpac\Notices\Loader as Notices_Loader ;
use  Lpac\Views\Frontend as Frontend_Display ;
use  Lpac\Compatibility\WooFunnels\Woo_Funnels ;
use  Lpac\Models\Location_Details ;
use  Lpac\Controllers\API\Order as API_Order ;
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
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        
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
        $plugin_admin_view = new Admin_Display();
        $notice = new Notice();
        $notices_loader = new Notices_Loader();
        $admin_notices = new Admin_Notices();
        $admin_settings_controller = new Admin_Settings_Controller();
        $controller_map_visibility = new Map_Visibility_Controller();
        $api_orders = new API_Order();
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        // Notices
        $this->loader->add_action( 'admin_notices', $admin_notices, 'lpac_site_not_https' );
        $this->loader->add_action( 'admin_notices', $notices_loader, 'load_notices' );
        /* Notices Ajax dismiss method */
        $this->loader->add_action( 'wp_ajax_lpac_dismiss_notice', $notice, 'dismiss_notice' );
        // Display map on order details page
        $this->loader->add_action(
            'woocommerce_admin_order_data_after_shipping_address',
            $plugin_admin_view,
            'lpac_display_lpac_admin_order_meta',
            10,
            1
        );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin_view, 'lpac_create_custom_order_details_metabox' );
        $this->loader->add_action( 'woocommerce_get_settings_pages', $plugin_admin_view, 'lpac_add_settings_tab' );
        /* Handle map visibility rules ordering table ajax requests in admin settings  */
        $this->loader->add_action( 'wp_ajax_lpac_map_visibility_rules_order', $controller_map_visibility, 'checkout_map_rules_order_ajax_handler' );
        /* Sanitize default map coordinates */
        $this->loader->add_filter(
            'woocommerce_admin_settings_sanitize_option_lpac_map_starting_coordinates',
            $admin_settings_controller,
            'sanitize_default_map_coordinates',
            10,
            3
        );
        /* Custom elements created for WooCommerce settings */
        $this->loader->add_action( 'woocommerce_admin_field_button', $plugin_admin_view, 'create_custom_wc_settings_button' );
        $this->loader->add_action( 'woocommerce_admin_field_hr', $plugin_admin_view, 'create_custom_wc_settings_hr' );
        $this->loader->add_action( 'woocommerce_admin_field_div', $plugin_admin_view, 'create_custom_wc_settings_div' );
        $this->loader->add_filter(
            'plugin_action_links',
            $this,
            'add_plugin_action_links',
            999999,
            2
        );
        // We need both of these hooks to be able to get our coordinates and build our payload
        $this->loader->add_action(
            'woocommerce_checkout_update_order_meta',
            $api_orders,
            'prepare_order_checkout',
            99999,
            2
        );
        $this->loader->add_action(
            'woocommerce_process_shop_order_meta',
            $api_orders,
            'prepare_order_admin',
            99999,
            2
        );
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
        $controller_map_visibility = new Map_Visibility_Controller();
        $controller_checkout_page = new Checkout_Page_Controller();
        $model_location_details = new Location_Details();
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
         * Output map on checkout page
         */
        $checkout_page_map_location = get_option( 'lpac_checkout_map_orientation', 'woocommerce_before_checkout_billing_form' );
        $checkout_page_map_location = apply_filters( 'lpac_checkout_map_orientation', $checkout_page_map_location );
        $this->loader->add_action( $checkout_page_map_location, $plugin_public_display, 'lpac_output_map_on_checkout_page' );
        /*
         * WooFunnels compatibility
         */
        
        if ( class_exists( 'WFFN_Core' ) ) {
            $woofunnels_compatibility = new Woo_Funnels();
            $this->loader->add_action( 'after_setup_theme', $woofunnels_compatibility, 'create_lpac_fields' );
            $this->loader->add_filter(
                'wfacp_get_checkout_fields',
                $woofunnels_compatibility,
                'add_lpac_checkout_fields',
                8
            );
            $this->loader->add_filter(
                'wfacp_get_fieldsets',
                $woofunnels_compatibility,
                'add_lpac_checkout_fields_to_fieldsets',
                7
            );
            // Remove map from default position and set it to above the customer information fields.
            
            if ( $checkout_page_map_location !== 'woocommerce_checkout_before_customer_details' ) {
                remove_action( $checkout_page_map_location, 'lpac_output_map_on_checkout_page' );
                $this->loader->add_action( 'woocommerce_checkout_before_customer_details', $plugin_public_display, 'lpac_output_map_on_checkout_page' );
            }
        
        }
        
        /*
         * Output map on order received and order details pages.
         */
        $this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public_display, 'lpac_output_map_on_order_details_page' );
        /*
         * Check if the latitude and longitude fields are filled in based on admin settings.
         */
        $validate_lat_long_fields = get_option( 'lpac_force_map_use', false );
        if ( $validate_lat_long_fields === 'yes' ) {
            $this->loader->add_action(
                'woocommerce_after_checkout_validation',
                $controller_checkout_page,
                'validate_location_fields',
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
                'add_delivery_location_link_to_email',
                20,
                4
            );
        }
        
        /*
         * Adds a notice for admin to checkout page
         */
        $this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public_display, 'lpac_add_admin_checkout_notice' );
        /*
         * Handles showing or hiding of map. Fires everytime the checkout page is updated.
         */
        $this->loader->add_action( 'wp_ajax_nopriv_lpac_checkout_map_visibility', $controller_map_visibility, 'checkout_map_visibility_ajax_handler' );
        $this->loader->add_action( 'wp_ajax_lpac_checkout_map_visibility', $controller_map_visibility, 'checkout_map_visibility_ajax_handler' );
        /*
         * Validate checkout map details and then add the latitude and longitude to the order meta.
         */
        $this->loader->add_action(
            'woocommerce_checkout_update_order_meta',
            $model_location_details,
            'validate_map_visibility',
            10,
            2
        );
        /*
         * Add places autocomplete order meta.
         */
        $this->loader->add_action(
            'woocommerce_checkout_update_order_meta',
            $model_location_details,
            'save_places_autocomplete',
            10,
            2
        );
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
    
    /**
     * Add action Links for plugin
     * @param array $plugin_actions
     * @param string $plugin_file
     * @return array
     */
    public function add_plugin_action_links( $plugin_actions, $plugin_file )
    {
        $new_actions = array();
        if ( LPAC_BASE_FILE . '/map-location-picker-at-checkout-for-woocommerce.php' === $plugin_file ) {
            $new_actions['lpac_wc_settings'] = sprintf( __( '<a href="%s">Settings</a>', 'map-location-picker-at-checkout-for-woocommerce' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=lpac_settings' ) ) );
        }
        return array_merge( $new_actions, $plugin_actions );
    }

}