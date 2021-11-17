<?php

/**
* Orchestrates the map visibility communication between JavaScript and PHP.
*
* Author:          Uriahs Victor
* Created on:      16/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.3
* @package Lpac
*/
namespace Lpac\Controllers;

/**
* Class Map Visibility Controller.
*
*/
class Map_Visibility_Controller
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
        //TODO Rather than this one method handling both the checkout on order-received and view-order page, we should possibly split it into another method just for the order-received and view-order pages.
        
        if ( $option === 'checkout' ) {
            $show = true;
            $rules_order = get_option( 'lpac_map_visibility_rules_order', self::get_map_visibility_rules() );
            foreach ( $rules_order as $rule => $rule_name ) {
                $show = self::$rule( $show );
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
     * Handler for map rules order.
     *
     * Fires when the order of the rules are changed.
     *
     * @return void
     */
    public function checkout_map_rules_order_ajax_handler()
    {
        try {
            $items = $_REQUEST['rulesOrder'] ?? '';
            if ( empty($items) ) {
                wp_send_json_error( false );
            }
            $items_assoc = array();
            $visibility_rules = self::get_map_visibility_rules();
            foreach ( $items as $key ) {
                $items_assoc[$key] = sanitize_text_field( $visibility_rules[$key] );
            }
            update_option( 'lpac_map_visibility_rules_order', $items_assoc );
            wp_send_json_success( true );
        } catch ( \Throwable $th ) {
            wp_send_json_error( false );
        }
    }
    
    /**
     * Handler for checkout map Ajax call.
     *
     * Fires on checkout page update event.
     *
     * @return void
     */
    public function checkout_map_visibility_ajax_handler()
    {
        try {
            $show = self::lpac_show_map( 'checkout' );
            wp_send_json_success( (bool) $show );
        } catch ( \Throwable $th ) {
            wp_send_json_error( false );
        }
    }
    
    /**
     * Default map visibility rules and order.
     *
     * @return array
     */
    public static function get_map_visibility_rules()
    {
        $rules = array(
            'guests_orders'    => __( 'Guest Orders', 'map-location-picker-at-checkout-for-woocommerce' ),
            'shipping_methods' => __( 'Shipping Methods', 'map-location-picker-at-checkout-for-woocommerce' ),
            'shipping_classes' => __( 'Shipping Classes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'coupon'           => __( 'Coupons', 'map-location-picker-at-checkout-for-woocommerce' ),
        );
        return $rules;
    }
    
    /**
     * Orchestrate the showing and hiding of the checkout map based on the shipping methods selected by admin.
     *
     * @return bool
     */
    private static function hide_map_by_shipping_method()
    {
        $shipping_methods = get_option( 'lpac_wc_shipping_methods', array() );
        $checkout_shipping_method = WC()->session->get( 'chosen_shipping_methods' )[0] ?? '';
        if ( empty($checkout_shipping_method) ) {
            return true;
        }
        $installed_at = get_option( 'lpac_installed_at_version' );
        $present = '';
        /**
         * Backwards compatibility with old way of storing shipping methods.
         * Way of storing shipping methods changed in v1.3.3 to using instance ids so we can grab all shipping methods on site.
         */
        
        if ( empty($installed_at) || !is_numeric( $shipping_methods[0] ) ) {
            $present = array_filter( $shipping_methods, function ( $shipping_method ) use( $checkout_shipping_method ) {
                if ( strpos( $checkout_shipping_method, $shipping_method ) !== false ) {
                    return $shipping_method;
                }
            } );
        } else {
            $parts = explode( ':', $checkout_shipping_method );
            $instance_id = $parts[1] ?? 0;
            if ( in_array( $instance_id, $shipping_methods ) ) {
                $present = true;
            }
        }
        
        /*
         * If the shipping method chosen at checkout is present in our list of shipping methods
         * by the admin, then return false to hide the map.
         */
        if ( !empty($present) ) {
            return false;
        }
        return true;
    }
    
    /**
     * Check if to show the map based on the shipping method.
     *
     * @param bool $show
     * @return bool
     */
    private static function shipping_methods( $show )
    {
        /* Get shipping methods admin has chosen to hide the map for. */
        $hidden_shipping_methods = get_option( 'lpac_wc_shipping_methods' );
        if ( !empty($hidden_shipping_methods) ) {
            $show = self::hide_map_by_shipping_method();
        }
        return $show;
    }
    
    /**
     * Check if to show the map based on the shipping classes.
     *
     * @param bool $show
     * @return bool
     */
    private static function shipping_classes( $show )
    {
        /*
         * Get the shipping classes IDs selected by the admin.
         */
        $selected_shipping_classes_ids = get_option( 'lpac_wc_shipping_classes', array() );
        if ( !empty($selected_shipping_classes_ids) ) {
            $show = \Lpac\Helpers\Functions::lpac_should_show_shipping_class( $selected_shipping_classes_ids );
        }
        return $show;
    }
    
    /**
     * Check if to hide the map for guest orders.
     *
     * @param bool $show
     * @return bool
     */
    private static function guests_orders( $show )
    {
        /*
         * Get the shipping classes IDs selected by the admin.
         */
        $hide_for_guests = get_option( 'lpac_hide_map_for_guests', 'no' );
        if ( $hide_for_guests === 'yes' ) {
            $show = false;
        }
        return $show;
    }
    
    /**
     * Check if to show the map based on the shipping zones.
     *
     * @param bool $show
     * @return bool
     */
    private static function shipping_zones( $show )
    {
        return $show;
    }
    
    /**
     * Check if to show the map based on the cart total.
     *
     * @param bool $show
     * @return bool
     */
    private static function cart_total( $show )
    {
        return $show;
    }
    
    /**
     * Show the map based on the coupon applied at checkout.
     *
     * @param bool $show
     * @return bool
     */
    private static function coupon( $show )
    {
        $show_for_coupons = get_option( 'lpac_map_show_for_coupons', array() );
        if ( empty($show_for_coupons) ) {
            return $show;
        }
        $applied_coupons = WC()->cart->applied_coupons;
        if ( empty($applied_coupons) ) {
            return $show;
        }
        foreach ( $applied_coupons as $coupon ) {
            
            if ( in_array( $coupon, $show_for_coupons ) ) {
                $show = true;
                break;
            }
        
        }
        return $show;
    }

}