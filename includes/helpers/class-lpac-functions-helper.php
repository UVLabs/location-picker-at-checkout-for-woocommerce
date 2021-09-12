<?php

/**
 * Provide helper static functions.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/includes/helpers
 */
class Lpac_Functions_Helper
{
    /**
     * Shows a feature if the setting is enabled
     *
     * @param string $option the option to act on.
     * @since    1.0.0
     * @since    1.2.0 Added more checks to determine when to show/hide map
     */
    public static function lpac_show_map( $option )
    {
        
        if ( $option === 'checkout' ) {
            $show = true;
            /**
             * Get the shipping classes IDs selected by the admin.
             */
            $selected_shipping_classes_ids = get_option( 'lpac_wc_shipping_classes', array() );
            if ( !empty($selected_shipping_classes_ids) ) {
                $show = self::lpac_should_show_shipping_class( $selected_shipping_classes_ids );
            }
            return $show;
        }
        
        /**
         * If this is the order-recieved or view-order page, then see if the options are set by the admin.
         *
         */
        $show = get_option( $option, 'yes' );
        /**
         * If the admin selected to hide the map on those pages then return false.
         */
        if ( $show !== 'yes' ) {
            return false;
        }
        return true;
    }
    
    /**
     * Determine whether to show the map based on shipping class.
     *
     * @param array $selected_shipping_classes_ids the shipping class selected by the admin in settings.
     *
     * @return bool whether or not to show the map.
     */
    private static function lpac_should_show_shipping_class( $selected_shipping_classes_ids )
    {
        /**
         * Get the current order being checkedout zone ID
         */
        $order_shipping_classes_ids = array_keys( self::lpac_get_order_shipping_classes() );
        /**
         * Check if any of the shipping classes in this order was selected by the admin in settings.
         */
        $has_match = array_intersect( $selected_shipping_classes_ids, $order_shipping_classes_ids );
        /**
         * Get the Show/Hide option selected by the admin for shipping classes.
         */
        $shown_hidden = get_option( 'lpac_wc_shipping_classes_show_hide' );
        /**
         * If the order being checked out has a shipping class ID that exists in our list, and the admin
         * set the option to "Show" from LPAC settings, then show the map and load it's assets.
         *
         * Because the admin chose to show the map only for orders that contain the shipping class they selected in the plugin settings.
         */
        if ( !empty($has_match) && $shown_hidden === 'show' ) {
            return true;
        }
        /**
         * If the order being checked out has a shipping class ID that doesn't exists in our list, and the admin
         * set the option to "Hide" from LPAC settings, then show the map and load it's assets.
         *
         * Because the admin chose to show the map only for orders that DO NOT contain the shipping class they selected in the plugin settings.
         */
        if ( empty($has_match) && $shown_hidden === 'hide' ) {
            return true;
        }
        /**
         * Return false(hide the map) in all other situations.
         */
        return false;
    }
    
    /**
     * Create QR Code directory string based on the basedir or baseurl.
     *
     * @return string The qr code resource server path or url path
     * @since    1.1.0
     */
    public static function lpac_get_qr_codes_directory( $base )
    {
        $qr_code_resource_base = wp_upload_dir()[$base];
        $qr_code_resource_locator = $qr_code_resource_base . '/' . 'lpac-qr-codes' . '/' . date( 'Y' ) . '/' . date( 'm' ) . '/' . date( 'd' ) . '/';
        return $qr_code_resource_locator;
    }
    
    /**
     * Normalize available shipping classes for use.
     *
     * Get the list of available shipping classes on the site and get them ready for use in the multiselect settings field of the plugin.
     *
     * @since    1.2.0
     *
     * @return array Array of available shipping classes.
     */
    public static function lpac_get_available_shipping_classes()
    {
        $normalized_shipping_classes = array();
        
        if ( !class_exists( 'WC_Shipping' ) ) {
            error_log( 'Location Picker at Checkout for WooCommerce: WC_Shipping() class not found.' );
            return $normalized_shipping_classes;
        }
        
        $lpac_wc_shipping_classes = ( new WC_Shipping() )->get_shipping_classes();
        if ( !is_array( $lpac_wc_shipping_classes ) ) {
            return $normalized_shipping_classes;
        }
        foreach ( $lpac_wc_shipping_classes as $shipping_class_object ) {
            $iterated_shipping_class = array(
                $shipping_class_object->term_id => $shipping_class_object->name,
            );
            /**
             * We need to keep our term_id as the key so we can later use. array_merge would reset the keys.
             */
            $normalized_shipping_classes = array_replace( $normalized_shipping_classes, $iterated_shipping_class );
        }
        return $normalized_shipping_classes;
    }
    
    /**
     * Get current shipping class at checkout.
     *
     * Gets the current shipping class the customer order falls in (based on the shipping class settings in WC settings).
     *
     * @since    1.2.0
     *
     * @return array Array of shipping classes present for this order.
     */
    public static function lpac_get_order_shipping_classes()
    {
        $cart = WC()->cart->get_cart();
        $shipping_class_array = array();
        foreach ( $cart as $cart_item ) {
            $shipping_class_id = $cart_item['data']->get_shipping_class_id();
            $shipping_class_name = $cart_item['data']->get_shipping_class();
            $shipping_class_array[$shipping_class_id] = $shipping_class_name;
        }
        return $shipping_class_array;
    }

}