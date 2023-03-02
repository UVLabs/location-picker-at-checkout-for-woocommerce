<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @author     Uriahs Victor <info@soaringleads.com>
 */
namespace Lpac\Bootstrap;

use  Lpac\Models\Plugin_Settings\Display_Settings ;
use  Lpac\Pro\Helpers\Functions ;
class Admin_Enqueues
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $version ;
    /**
     * The full google maps resource with all needed params.
     *
     * @since    1.1.2
     * @access   private
     * @var      string    $lpac_google_maps_resource   The google maps url.
     */
    private  $lpac_google_maps_resource ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string $plugin_name       The name of this plugin.
     * @param      string $version    The version of this plugin.
     */
    public function __construct()
    {
        $this->plugin_name = LPAC_PLUGIN_NAME;
        $this->version = LPAC_VERSION;
        $this->lpac_google_maps_resource = LPAC_GOOGLE_MAPS_API_LINK . LPAC_GOOGLE_MAPS_API_KEY . '&' . LPAC_GOOGLE_MAPS_PARAMS;
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        wp_enqueue_style(
            $this->plugin_name,
            LPAC_PLUGIN_ASSETS_PATH_URL . 'admin/css/lpac-admin.css',
            array(),
            $this->version,
            'all'
        );
        wp_enqueue_style(
            $this->plugin_name . '-notices',
            LPAC_PLUGIN_ASSETS_PATH_URL . 'admin/css/notices.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $query_string = $_SERVER['QUERY_STRING'];
        $lite_assets_path_url = constant( 'LPAC_PLUGIN_ASSETS_PATH_URL' );
        $path = ( LPAC_DEBUG ? '' : 'build/' );
        $is_lpac_settings = strpos( $query_string, 'wc-settings&tab=lpac_settings' );
        // Only load the admin scripts on the WooCommerce settings page of LPAC
        
        if ( $is_lpac_settings ) {
            wp_enqueue_script(
                $this->plugin_name . '-jquery-repeater-js',
                $lite_assets_path_url . 'lib/jquery.repeater.js',
                array( 'jquery' ),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name . '-repeater-setup-js',
                $lite_assets_path_url . 'admin/js/repeater-setup.js',
                array( 'jquery' ),
                $this->version,
                false
            );
            wp_enqueue_script(
                $this->plugin_name,
                $lite_assets_path_url . 'admin/js/lpac-admin.js',
                array( 'jquery' ),
                $this->version,
                false
            );
        }
        
        /**
         * Register Google Map Script
         */
        wp_register_script(
            $this->plugin_name . '-google-maps-js',
            $this->lpac_google_maps_resource,
            array(),
            $this->version,
            false
        );
        wp_enqueue_script( $this->plugin_name . '-google-maps-js' );
        // Callback function. The callback parameter is *required* by google maps. It throws a console error if not present.
        wp_add_inline_script( $this->plugin_name . '-google-maps-js', "\n\t\t\t\tfunction GMapsScriptLoaded(){\n\t\t\t\tconsole.log('Location Picker at Checkout: Maps API Script loaded');\n\t\t\t\t}\n\t\t\t", 'before' );
        // Pass assets folder path to JS
        $global_variables = <<<JAVASCRIPT
\t\tvar lpacAssetsFolderPath = "{$lite_assets_path_url}";
JAVASCRIPT;
        // Expose JS variables for usage.
        wp_add_inline_script( $this->plugin_name, $global_variables, 'before' );
        wp_register_script(
            $this->plugin_name . '-base-map',
            $lite_assets_path_url . 'public/js/maps/' . $path . 'base-map.js',
            array(),
            $this->version,
            true
        );
        /**
         * This has to be enqueued in the footer so our wp_add_inline_script() function can work.
         * Only run this code on shop order(order details) page in admin area.
         */
        
        if ( get_current_screen()->id === 'shop_order' ) {
            wp_enqueue_script(
                $this->plugin_name . '-order-map',
                $lite_assets_path_url . 'admin/js/' . $path . 'order-map.js',
                array( $this->plugin_name . '-base-map' ),
                $this->version,
                true
            );
            $google_map_id = json_encode( Display_Settings::get_admin_view_order_map_id() );
            wp_add_inline_script( $this->plugin_name . '-order-map', "\n\t\t\t\tvar googleMapID = {$google_map_id}\n\t\t\t\t", 'before' );
        }
    
    }
    
    /**
     * Turn a script into a module so that we can make use of JS components.
     *
     * @param mixed $tag
     * @param mixed $handle
     * @param mixed $src
     * @return mixed
     * @since 1.7.0
     */
    public function make_scripts_modules( $tag, $handle, $src )
    {
        $modules_handlers = array( $this->plugin_name . '-order-map' );
        if ( !in_array( $handle, $modules_handlers, true ) ) {
            return $tag;
        }
        $id = $handle . '-js';
        $parts = explode( '</script>', $tag );
        // Break up our string
        foreach ( $parts as $key => $part ) {
            if ( false !== strpos( $part, $src ) ) {
                // Make sure we're only altering the tag for our module script.
                $parts[$key] = '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $id ) . '">';
            }
        }
        $tags = implode( '</script>', $parts );
        // Bring everything back together
        return $tags;
    }

}