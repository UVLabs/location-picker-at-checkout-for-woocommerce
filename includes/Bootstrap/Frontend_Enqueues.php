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
 * @author     Uriahs Victor <info@soaringleads.com>
 */
namespace Lpac\Bootstrap;

use  Lpac\Helpers\Functions as Functions_Helper ;
class Frontend_Enqueues
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
     * @since    1.0.0
     * @access   private
     * @var      string    $lpac_google_maps_resource  The google maps url.
     */
    private  $lpac_google_maps_resource ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct()
    {
        $this->plugin_name = LPAC_PLUGIN_NAME;
        $this->version = LPAC_VERSION;
        $this->lpac_google_maps_resource = LPAC_GOOGLE_MAPS_LINK . LPAC_GOOGLE_MAPS_API_KEY . LPAC_GOOGLE_MAPS_PARAMS;
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
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
        wp_enqueue_style(
            $this->plugin_name,
            LPAC_PLUGIN_ASSETS_PATH_URL . 'public/css/lpac-public.css',
            array(),
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
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
        $path = ( LPAC_DEBUG ? '' : 'build/' );
        wp_enqueue_script(
            $this->plugin_name,
            LPAC_PLUGIN_ASSETS_PATH_URL . 'public/js/lpac-public.js',
            array( 'jquery' ),
            $this->version,
            false
        );
        // Only enqueue the Google Map CDN script on the needed pages
        
        if ( is_wc_endpoint_url( 'view-order' ) || is_wc_endpoint_url( 'order-received' ) || is_checkout() ) {
            $show_on_view_order_page = Functions_Helper::lpac_show_map( 'lpac_display_map_on_view_order_page' );
            if ( is_wc_endpoint_url( 'view-order' ) && $show_on_view_order_page === false ) {
                return;
            }
            $show_on_order_received_page = Functions_Helper::lpac_show_map( 'lpac_display_map_on_order_received_page' );
            if ( is_wc_endpoint_url( 'order-received' ) && $show_on_order_received_page === false ) {
                return;
            }
            $show_on_checkout_page = Functions_Helper::lpac_show_map( 'checkout' );
            /**
             * is_checkout() also runs on is_wc_endpoint_url( 'order-received' ) so we need to make this if block doesn't
             * by added the ! conditional
             */
            if ( is_checkout() && !is_wc_endpoint_url( 'order-received' ) && $show_on_checkout_page === false ) {
                return;
            }
            /**
             * Register Google Map Script
             */
            
            if ( get_option( 'lpac_dequeue_google_maps' ) !== 'frontend' && get_option( 'lpac_dequeue_google_maps' ) !== 'both' ) {
                wp_register_script(
                    $this->plugin_name . '-google-maps-js',
                    $this->lpac_google_maps_resource,
                    array(),
                    $this->version,
                    false
                );
                wp_enqueue_script( $this->plugin_name . '-google-maps-js' );
            }
            
            /**
             * The following javascript files have to be enqueued in the footer so our wp_add_inline_script() function can work.
             */
            /**
             * Base Map JS, also enqueues google maps JS automatically.
             */
            wp_enqueue_script(
                $this->plugin_name . '-base-map',
                LPAC_PLUGIN_ASSETS_PATH_URL . 'public/js/maps/' . $path . 'base-map.js',
                array(),
                $this->version,
                true
            );
            // wp_enqueue_script( $this->plugin_name . '-base-map', LPAC_PLUGIN_ASSETS_PATH_URL . 'public/js/maps/base-map.js', array( $this->plugin_name . '-google-maps-js' ), $this->version, true );
            /**
             * Load order received page map
             */
            if ( is_wc_endpoint_url( 'order-received' ) ) {
                wp_enqueue_script(
                    $this->plugin_name . '-order-received-map',
                    LPAC_PLUGIN_ASSETS_PATH_URL . 'public/js/maps/' . $path . 'order-received-map.js',
                    array( $this->plugin_name . '-base-map' ),
                    $this->version,
                    true
                );
            }
            /**
             * Load view order page map (customer)
             */
            if ( is_wc_endpoint_url( 'view-order' ) ) {
                wp_enqueue_script(
                    $this->plugin_name . '-order-details-map',
                    LPAC_PLUGIN_ASSETS_PATH_URL . 'public/js/maps/' . $path . 'order-details-map.js',
                    array( $this->plugin_name . '-base-map' ),
                    $this->version,
                    true
                );
            }
            /**
             * is_checkout() also runs on is_wc_endpoint_url( 'order-received' ) so we need to make this if block doesn't
             * by added the ! conditional
             */
            if ( is_checkout() && !is_wc_endpoint_url( 'order-received' ) ) {
                /**
                 * Load checkout page map
                 */
                wp_enqueue_script(
                    $this->plugin_name . '-checkout-page-map',
                    LPAC_PLUGIN_ASSETS_PATH_URL . 'public/js/maps/' . $path . 'checkout-page-map.js',
                    array( $this->plugin_name . '-base-map' ),
                    $this->version,
                    true
                );
            }
        }
    
    }

}