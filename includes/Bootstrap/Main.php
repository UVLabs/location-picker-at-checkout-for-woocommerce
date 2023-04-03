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

if ( !defined( 'WPINC' ) ) {
    die;
}
use  Lpac\Bootstrap\Loader ;
use  Lpac\Bootstrap\I18n ;
use  Lpac\Bootstrap\Admin_Enqueues ;
use  Lpac\Bootstrap\Frontend_Enqueues ;
use  Lpac\Compatibility\Caching\Siteground_Optimizer ;
use  Lpac\Controllers\Emails_Controller ;
use  Lpac\Controllers\Map_Visibility_Controller ;
use  Lpac\Controllers\Admin_Settings_Controller ;
use  Lpac\Controllers\Checkout_Page\Validate as Checkout_Page_Validation ;
use  Lpac\Controllers\Shortcodes as Shortcodes_Controller ;
use  Lpac\Notices\Admin as Admin_Notices ;
use  Lpac\Notices\Notice ;
use  Lpac\Notices\Loader as Notices_Loader ;
use  Lpac\Views\Admin\Admin as Admin_Display ;
use  Lpac\Views\Frontend\Frontend as Frontend_Display ;
use  Lpac\Views\Frontend\Shortcodes ;
use  Lpac\Models\Location_Details ;
use  Lpac\Models\Migrations ;
/**
 * Class Main.
 *
 * Class responsible for firing public and admin hooks.
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
        // TODO these hooks should run depending on if the user is on the admin page or the public site.
        // Right now it runs on both
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->create_shortcodes();
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
        $migrations = new Migrations();
        /**
         * Menus
         */
        $this->loader->add_action( 'admin_menu', $this, 'create_admin_menu' );
        $this->loader->add_action(
            'admin_menu',
            $this,
            'create_submenu',
            11
        );
        /**
         * Make scripts modules
         */
        $this->loader->add_filter(
            'script_loader_tag',
            $plugin_admin,
            'make_scripts_modules',
            10,
            3
        );
        /**
         * Plugin settings migrations
         */
        $this->loader->add_action( 'admin_init', $migrations, 'add_address_field_to_store_locations' );
        $this->loader->add_action( 'admin_init', $migrations, 'add_should_calculate_per_distance_unit_field' );
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
        /* Run tasks related to admin settings */
        $this->loader->add_filter(
            'woocommerce_admin_settings_sanitize_option_lpac_map_starting_coordinates',
            $admin_settings_controller,
            'sanitize_coordinates',
            10,
            3
        );
        $this->loader->add_filter(
            'woocommerce_admin_settings_sanitize_option_lpac_store_locations',
            $admin_settings_controller,
            'generate_store_id',
            10,
            3
        );
        $this->loader->add_filter(
            'woocommerce_admin_settings_sanitize_option_lpac_cost_by_distance_range_rangelist',
            $admin_settings_controller,
            'normalize_cost_by_distance_range_checkbox',
            10,
            3
        );
        /* Custom elements created for WooCommerce settings */
        $this->loader->add_action( 'woocommerce_admin_field_button', $plugin_admin_view, 'create_custom_wc_settings_button' );
        $this->loader->add_action( 'woocommerce_admin_field_hr', $plugin_admin_view, 'create_custom_wc_settings_hr' );
        $this->loader->add_action( 'woocommerce_admin_field_div', $plugin_admin_view, 'create_custom_wc_settings_div' );
        $this->loader->add_action( 'woocommerce_admin_field_repeater', $plugin_admin_view, 'create_custom_wc_settings_repeater' );
        $this->loader->add_action( 'woocommerce_admin_field_info_text', $plugin_admin_view, 'create_custom_wc_settings_info_text' );
        $this->loader->add_action( 'woocommerce_admin_field_upsell_banner', $plugin_admin_view, 'create_custom_wc_settings_upsell_banner' );
        $this->loader->add_action( 'woocommerce_admin_field_lpac_image', $plugin_admin_view, 'create_custom_wc_settings_image' );
        $this->loader->add_filter(
            'plugin_action_links',
            $this,
            'add_plugin_action_links',
            999999,
            2
        );
        // Custom admin order columns
        $this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin_view, 'add_map_btn_admin_list_column' );
        $this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin_view, 'add_map_btn_admin_list_column_content' );
        // We're checking if both "Cost by Distance" and "Cost by Store Distance" is enabled because "Cost by Store Distance" might be turned on while "Cost by Distance" is turned off, which means we would not want to actually show the column because the selector would not be getting outputted on the checkout page.
        
        if ( get_option( 'lpac_enable_shipping_cost_by_distance_feature' ) === 'yes' && get_option( 'lpac_enable_cost_by_store_distance' ) === 'yes' || get_option( 'lpac_enable_cost_by_store_location' ) === 'yes' || get_option( 'lpac_enable_store_location_selector' ) === 'yes' ) {
            $this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin_view, 'add_store_location_admin_list_column' );
            $this->loader->add_action( 'manage_shop_order_posts_custom_column', $plugin_admin_view, 'add_store_location_admin_list_column_content' );
        }
    
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
        $controller_checkout_page_validation = new Checkout_Page_Validation();
        $model_location_details = new Location_Details();
        $controller_shortcodes = new Shortcodes_Controller();
        $compatibility_siteground_optimzer = new Siteground_Optimizer();
        /*
         * If plugin not enabled don't continue
         */
        $plugin_enabled = get_option( 'lpac_enabled', 'yes' );
        if ( $plugin_enabled === 'no' ) {
            return;
        }
        /**
         * Make scripts modules
         */
        $this->loader->add_filter(
            'script_loader_tag',
            $plugin_public,
            'make_scripts_modules',
            10,
            3
        );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'wp_head', $plugin_public_display, 'lpac_output_map_custom_styles' );
        /**
         * Compatibility
         */
        $this->loader->add_filter( 'sgo_js_async_exclude', $compatibility_siteground_optimzer, 'remove_defer_on_gmaps_script' );
        $this->loader->add_filter( 'sgo_js_minify_exclude', $compatibility_siteground_optimzer, 'js_minify_exclude' );
        $this->loader->add_filter( 'sgo_javascript_combine_excluded_inline_content', $compatibility_siteground_optimzer, 'js_combine_exclude_inline_script' );
        $this->loader->add_filter( 'sgo_javascript_combine_exclude', $compatibility_siteground_optimzer, 'js_combine_exclude' );
        /*
         * Output map on checkout page
         */
        add_action( 'plugins_loaded', function () {
            $checkout_page_map_location = get_option( 'lpac_checkout_map_orientation', 'woocommerce_checkout_before_customer_details' );
            $checkout_page_map_location = apply_filters( 'lpac_checkout_map_orientation', $checkout_page_map_location );
            $class = new \Lpac\Views\Frontend\Frontend();
            $priority = apply_filters( 'kikote_checkout_map_orientation_filter_priority', 9 );
            add_action( $checkout_page_map_location, array( $class, 'output_map_on_checkout_page' ), $priority );
        } );
        /*
         * Translated alert strings for checkout page.
         */
        $this->loader->add_action(
            'wp_enqueue_scripts',
            $plugin_public_display,
            'create_checkoutpage_translated_strings',
            99999
        );
        /*
         * Output map on order received and order details pages.
         */
        $this->loader->add_action(
            'woocommerce_order_details_after_order_table',
            $plugin_public_display,
            'lpac_output_map_on_order_details_page',
            10
        );
        /*
         * Origin store name on order details page.
         */
        $this->loader->add_action(
            'woocommerce_order_details_after_order_table',
            $plugin_public_display,
            'output_origin_store_name',
            11
        );
        /*
         * Check if the latitude and longitude fields are filled in based on admin settings.
         */
        $validate_lat_long_fields = get_option( 'lpac_force_map_use', false );
        if ( $validate_lat_long_fields === 'yes' ) {
            // TODO move this check inside the validation method like we did for validate_places_autocomplete().
            $this->loader->add_action(
                'woocommerce_after_checkout_validation',
                $controller_checkout_page_validation,
                'validate_location_fields',
                10,
                2
            );
        }
        $this->loader->add_action(
            'woocommerce_after_checkout_validation',
            $controller_checkout_page_validation,
            'validate_places_autocomplete',
            10,
            2
        );
        /**
         * Validate that a customer has selected a store location from the drop down selector.
         */
        $this->loader->add_action(
            'woocommerce_after_checkout_validation',
            $controller_checkout_page_validation,
            'validate_store_location_selector_dropdown',
            10,
            2
        );
        /*
         * Display map button link, static map or qr code link in order emails email.
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
        
        /**
         * Add selected store location to order emails.
         */
        $this->loader->add_action(
            'woocommerce_email_customer_details',
            $controller_emails,
            'add_store_location_to_email',
            20,
            4
        );
        /*
         * Adds a notice for admin to checkout page
         */
        $this->loader->add_action( 'woocommerce_before_checkout_form', $plugin_public_display, 'add_admin_checkout_notice' );
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
        $this->loader->add_action( 'wp_ajax_lpac_save_selected_store_location', $controller_shortcodes, 'save_selected_store_location' );
        $this->loader->add_action( 'wp_ajax_nopriv_lpac_save_selected_store_location', $controller_shortcodes, 'save_selected_store_location' );
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
     *
     * @param array  $plugin_actions
     * @param string $plugin_file
     * @return array
     */
    public function add_plugin_action_links( $plugin_actions, $plugin_file )
    {
        $new_actions = array();
        
        if ( LPAC_BASE_FILE . '/lpac.php' === $plugin_file ) {
            $new_actions['lpac_wc_settings'] = sprintf( __( '<a href="%s">Settings</a>', 'map-location-picker-at-checkout-for-woocommerce' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=lpac_settings' ) ) );
            if ( !defined( 'LPAC_PLUGIN_PATH_URL_PRO' ) ) {
                $new_actions['lpac_upgrade_link'] = sprintf( __( '%1$sCheck out PRO%2$s', 'map-location-picker-at-checkout-for-woocommerce' ), '<a style="color: green; font-weight: bold" href="https://lpacwp.com/pricing?utm_source=plugin_actions_links&utm_medium=wp_plugins_area" target="_blank">', '</a>' );
            }
        }
        
        return array_merge( $plugin_actions, $new_actions );
    }
    
    /**
     * Create our shortcodes
     *
     * @return void
     */
    public function create_shortcodes() : void
    {
        new Shortcodes();
    }
    
    /**
     * Create our SoaringLeads menu item.
     *
     * @return void
     * @since 1.6.12
     */
    public function create_admin_menu() : void
    {
        $icon = file_get_contents( LPAC_PLUGIN_ASSETS_DIR . 'img/menu-icon.svg' );
        $icon = 'data:image/svg+xml;base64,' . base64_encode( $icon );
        $main_menu = menu_page_url( 'sl-plugins-menu', false );
        
        if ( !empty($main_menu) ) {
            return;
            // Menu already added by another SoarngLeads plugin
        }
        
        add_menu_page(
            __( 'SoaringLeads Plugins', 'map-location-picker-at-checkout-for-woocommerce' ),
            'SoaringLeads',
            'manage_options',
            'sl-plugins-menu',
            array( $this, 'output_root_submenu_upsells' ),
            $icon,
            '57.10'
        );
    }
    
    /**
     * Create custom submenu items for toplevel menu.
     *
     * @return void
     * @since 1.6.12
     */
    public function create_submenu() : void
    {
        add_submenu_page(
            'sl-plugins-menu',
            'Kikote - Location Picker at Checkout',
            'Kikote - Location Picker at Checkout',
            'manage_options',
            'lpac-menu',
            array( $this, 'menu_item_html' ),
            '1'
        );
    }
    
    /**
     * Html for menu item landing page.
     *
     * Not actually outputting anything because we're redirecting the parent page.
     *
     * @since 1.6.12
     */
    public function menu_item_html()
    {
        wp_redirect( site_url( '/wp-admin/admin.php?page=wc-settings&tab=lpac_settings', 'https' ) );
        exit;
    }
    
    /**
     * HTML for root SoaringLeads page.
     *
     * Populate with upsell content.
     *
     * @since 1.6.13
     */
    public function output_root_submenu_upsells()
    {
        ?>
		<h1><?php 
        esc_html_e( 'Check out our available plugins', 'map-location-picker-at-checkout-for-woocommerce' );
        ?></h1>
		<hr style='margin-bottom: 40px'/>
		
		<div style='margin-bottom: 40px'>
		<a href='https://dpswp.com/?utm_source=wpadmin&utm_medium=sl-plugins-page&utm_campaign=plugins-upsell' target='_blank'><img src='<?php 
        echo  esc_attr( LPAC_PLUGIN_ASSETS_PATH_URL . 'admin/img/delivery-and-pickup-scheduling.png' ) ;
        ?>' /></a>
		<p style='font-size: 18px; font-weight: 700;'><?php 
        echo  esc_html( 'Allow customers to set their delivery/pickup date and time during order checkout.', 'map-location-picker-at-checkout-for-woocommerce' ) ;
        ?></p>	
		<a href='https://dpswp.com/?utm_source=wpadmin&utm_medium=sl-plugins-page&utm_campaign=plugins-upsell' target='_blank' class='button-primary'><?php 
        esc_html_e( 'Learn More', 'map-location-picker-at-checkout-for-woocommerce' );
        ?></a>
		</div>

		<div style='margin-bottom: 40px'>
		<a href='https://lpacwp.com/?utm_source=wpadmin&utm_medium=sl-plugins-page&utm_campaign=plugins-upsell' target='_blank'><img src='<?php 
        echo  esc_attr( LPAC_PLUGIN_ASSETS_PATH_URL . 'admin/img/lpac.png' ) ;
        ?>' /></a>
		<p style='font-size: 18px; font-weight: 700;'><?php 
        esc_html_e( 'Let customers choose their shipping or pickup location using a map during checkout.', 'map-location-picker-at-checkout-for-woocommerce' );
        ?></p>	
		<a href='https://lpacwp.com/?utm_source=wpadmin&utm_medium=sl-plugins-page&utm_campaign=plugins-upsell' target='_blank' class='button-primary'><?php 
        esc_html_e( 'Learn More', 'map-location-picker-at-checkout-for-woocommerce' );
        ?></a>
		</div>
		<?php 
    }

}