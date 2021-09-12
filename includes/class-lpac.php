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
class Lpac
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
    private function load_dependencies()
    {
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
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-lpac-admin.php';
        /**
         * The class responsible for defining all actions that occur in the admin-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-lpac-admin-display.php';
        /**
         * The class responsible for all admin-facing notices
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-lpac-admin-notices.php';
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
        /**
         * The class responsible for generating QR Code using provided data.
         *
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/public/class-lpac-qr-code-generator.php';
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
    private function set_locale()
    {
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
    private function define_admin_hooks()
    {
        $plugin_admin = new Lpac_Admin();
        $plugin_admin_display = new Lpac_Admin_Display();
        $admin_notices = new Lpac_Admin_Notices();
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
        $plugin_public = new Lpac_Public( $this->get_plugin_name(), $this->get_version() );
        $plugin_public_display = new Lpac_Public_Display();
        $functions_helper = new Lpac_Functions_Helper();
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        /*
         * WooCommerce
         */
        $plugin_enabled = get_option( 'lpac_enabled', 'yes' );
        if ( $plugin_enabled === 'no' ) {
            return;
        }
        $this->loader->add_action( 'wp_head', $plugin_public_display, 'lpac_output_map_custom_styles' );
        $checkout_page_map_location = get_option( 'lpac_checkout_map_orientation', 'woocommerce_after_checkout_shipping_form' );
        /*
         *	Update old options.
         */
        //TODO Delete this block when confident most users have updated.
        
        if ( $checkout_page_map_location === 'billing_address_area_top' ) {
            $checkout_page_map_location = 'woocommerce_before_checkout_billing_form';
            update_option( 'lpac_checkout_map_orientation', 'woocommerce_before_checkout_billing_form' );
        } elseif ( $checkout_page_map_location === 'billing_address_area_bottom' ) {
            $checkout_page_map_location = 'woocommerce_after_checkout_billing_form';
            update_option( 'lpac_checkout_map_orientation', 'woocommerce_after_checkout_billing_form' );
        } elseif ( $checkout_page_map_location === 'shipping_address_area_top' ) {
            $checkout_page_map_location = 'woocommerce_before_checkout_shipping_form';
            update_option( 'lpac_checkout_map_orientation', 'woocommerce_before_checkout_shipping_form' );
        } elseif ( $checkout_page_map_location === 'shipping_address_area_bottom' ) {
            $checkout_page_map_location = 'woocommerce_after_checkout_shipping_form';
            update_option( 'lpac_checkout_map_orientation', 'woocommerce_after_checkout_shipping_form' );
        }
        
        /*
         * Output hidden input fields for latitude and longitude.
         */
        $this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public_display, 'lpac_create_lat_and_long_inputs' );
        /*
         * Output map on checkout page
         */
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
                $plugin_public_display,
                'lpac_add_delivery_location_link_to_email',
                20,
                4
            );
        }
    
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