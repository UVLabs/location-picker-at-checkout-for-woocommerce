<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 */
namespace Lpac\Views;

use  Lpac\Controllers\Map_Visibility_Controller ;
use  Lpac\Controllers\Checkout_Page_Controller ;
use  Lpac\Compatibility\Checkout_Provider ;
class Frontend
{
    /**
     * Exposes map settings to be used in client-side javascript.
     *
     * @since    1.0.0
     * @param      string    $additional   Additional settings to pass to JS.
     */
    private function setup_global_js_vars( $additional = array() )
    {
        $controller_checkout_page = new Checkout_Page_Controller();
        $map_options = $controller_checkout_page->get_map_options();
        $last_order_location = $controller_checkout_page->get_last_order_location();
        $store_locations = $controller_checkout_page->get_store_locations_labels();
        $map_options = array_merge( $map_options, $additional );
        $map_options = json_encode( $map_options );
        $last_order_location = json_encode( $last_order_location );
        $store_locations = json_encode( $store_locations );
        $checkout_provider = ( new Checkout_Provider() )->get_checkout_provider();
        $checkout_provider = json_encode( $checkout_provider );
        $global_variables = <<<JAVASCRIPT
\t\tvar mapOptions = {$map_options};
\t\tvar lpacLastOrder = {$last_order_location};
\t\tvar checkoutProvider = {$checkout_provider};
\t\tvar storeLocations = {$store_locations};
JAVASCRIPT;
        return $global_variables;
    }
    
    /**
     * Outputs map on the WooCommerce Checkout page.
     *
     * @since    1.0.0
     */
    public function lpac_output_map_on_checkout_page()
    {
        // Map div display visibility
        $display = 'block';
        if ( Map_Visibility_Controller::lpac_show_map( 'checkout' ) === false ) {
            $display = 'none';
        }
        $btn_text = __( 'Detect Current Location', 'map-location-picker-at-checkout-for-woocommerce' );
        $lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', $btn_text );
        $instuctions_text = __( 'Click the "Detect Current Location" button then move the red marker to your desired shipping address.', 'map-location-picker-at-checkout-for-woocommerce' );
        $instuctions_text = apply_filters( 'lpac_map_instuctions_text', $instuctions_text );
        $user_id = (int) get_current_user_id();
        $saved_addresses_area = apply_filters( 'lpac_saved_addresses', '', $user_id );
        $saved_addresses_area = wp_kses_post( $saved_addresses_area );
        $edit_saved_addresses = '';
        
        if ( !empty($saved_addresses_area) ) {
            $account_link = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) . 'edit-address/';
            $edit_saved_addresses = sprintf( esc_html__( '%1$sEdit Saved Addresses%2$s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='{$account_link}' target='_blank'>", '</a>' );
        }
        
        $before_map_filter = apply_filters( 'lpac_before_map', '', $user_id );
        $before_map_filter = wp_kses_post( $before_map_filter );
        $after_map_filter = apply_filters( 'lpac_after_map', '', $user_id );
        $after_map_filter = wp_kses_post( $after_map_filter );
        $before_map_controls_filter = apply_filters( 'lpac_before_map_controls', '', $user_id );
        $before_map_controls_filter = wp_kses_post( $before_map_controls_filter );
        // TODO use this filter to create our saved address field
        $after_map_controls_filter = apply_filters( 'lpac_after_map_controls', '', $user_id );
        $after_map_controls_filter = wp_kses_post( $after_map_controls_filter );
        $markup = <<<HTML
\t\t<div style="display: {$display}" id="lpac-map-container" class='woocommerce-shipping-fields__field-wrapper'>
\t\t\t{$before_map_filter}
\t\t\t<div class='lpac-map'></div>
\t\t\t{$after_map_filter}
\t\t\t<div class='lpac-map-controls'>
\t\t\t{$before_map_controls_filter}
\t\t\t<div id='lpac-map-instructions'>{$instuctions_text}</div>
\t\t\t<div id='lpac-find-location-btn-wrapper'><button id='lpac-find-location-btn' class="button btn" type='button'>{$lpac_find_location_btn_text}</button></div>
\t\t\t<div id='lpac-saved-addresses'>
\t\t\t\t<ul>
\t\t\t\t\t{$saved_addresses_area}
\t\t\t\t</ul>
\t\t\t\t<p id='edit-saved-addresses'>{$edit_saved_addresses}</p>
\t\t\t</div>
\t\t\t{$after_map_controls_filter}
\t\t\t</div>
\t\t</div>
HTML;
        echo  apply_filters( 'lpac_map_markup', $markup, $user_id ) ;
        woocommerce_form_field( 'lpac_latitude', array(
            'label'    => __( 'Latitude', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required' => false,
            'class'    => ( LPAC_DEBUG ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hidden' ) ),
        ) );
        woocommerce_form_field( 'lpac_longitude', array(
            'label'    => __( 'Longitude', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required' => false,
            'class'    => ( LPAC_DEBUG ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hidden' ) ),
        ) );
        woocommerce_form_field( 'lpac_is_map_shown', array(
            'label'    => __( 'Map Shown', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required' => false,
            'class'    => ( LPAC_DEBUG ? array( 'form-row-wide' ) : array( 'form-row-wide', 'hidden' ) ),
        ) );
        woocommerce_form_field( 'lpac_places_autocomplete', array(
            'label'    => __( 'Places Autocomplete', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required' => false,
            'class'    => ( LPAC_DEBUG ? array( 'form-row-wide', 'fc-skip-hide-optional-field' ) : array( 'form-row-wide', 'hidden', 'fc-skip-hide-optional-field' ) ),
        ) );
        do_action( 'lpac_after_map' );
        // Add inline global JS so that we can use data fetched using PHP inside JS
        $global_js_vars = $this->setup_global_js_vars();
        $added = wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $global_js_vars, 'before' );
        // On some websites our basemap might not enqueue in time. In those cases fall back to default wp jquery handle.
        if ( empty($added) ) {
            wp_add_inline_script( 'jquery-core', $global_js_vars, 'before' );
        }
    }
    
    /**
     * Outputs map on the WooCommerce view order page and order received page.
     *
     * @since    1.0.0
     */
    public function lpac_output_map_on_order_details_page()
    {
        global  $wp ;
        // If this isn't the order received page shown after a purchase, or the view order page shown on the user account, then bail.
        if ( !is_wc_endpoint_url( 'view-order' ) && !is_wc_endpoint_url( 'order-received' ) ) {
            return;
        }
        $show_on_view_order_page = Map_Visibility_Controller::lpac_show_map( 'lpac_display_map_on_view_order_page' );
        $show_on_order_received_page = Map_Visibility_Controller::lpac_show_map( 'lpac_display_map_on_order_received_page' );
        if ( is_wc_endpoint_url( 'view-order' ) && $show_on_view_order_page === false ) {
            return;
        }
        if ( is_wc_endpoint_url( 'order-received' ) && $show_on_order_received_page === false ) {
            return;
        }
        if ( is_wc_endpoint_url( 'order-received' ) ) {
            $order_id = $wp->query_vars['order-received'];
        }
        if ( is_wc_endpoint_url( 'view-order' ) ) {
            $order_id = $wp->query_vars['view-order'];
        }
        if ( empty($order_id) ) {
            return;
        }
        // TODO get_post_meta runs everytime when single option is used...
        // We can simply just call it once without a key and only the order id and we'll receive the full array without needing multiple calls to the DB
        // This might not be necessary if the data is cached
        // Backwards compatibility, previously we stored location coords as private meta.
        $latitude = ( (double) get_post_meta( $order_id, 'lpac_latitude', true ) ?: (double) get_post_meta( $order_id, '_lpac_latitude', true ) );
        $longitude = ( (double) get_post_meta( $order_id, 'lpac_longitude', true ) ?: (double) get_post_meta( $order_id, '_lpac_longitude', true ) );
        $shipping_address_1 = get_post_meta( $order_id, '_shipping_address_1', true );
        $shipping_address_2 = get_post_meta( $order_id, '_shipping_address_2', true );
        if ( empty($latitude) || empty($longitude) ) {
            return;
        }
        $user_location_collected_during_order = array(
            'lpac_map_order_latitude'           => $latitude,
            'lpac_map_order_longitude'          => $longitude,
            'lpac_map_order_shipping_address_1' => $shipping_address_1,
            'lpac_map_order_shipping_address_2' => $shipping_address_2,
        );
        $markup = <<<HTML
\t\t<div id="lpac-map-container" class='woocommerce-shipping-fields__field-wrapper'>
\t\t<div class='lpac-map'></div>
\t\t</div>
HTML;
        echo  $markup ;
        // Add inline global JS so that we can use data fetched using PHP inside JS
        $global_js_vars = $this->setup_global_js_vars( $user_location_collected_during_order );
        $added = wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $global_js_vars, 'before' );
        // On some websites our basemap might not enqueue in time. In those cases fall back to default wp jquery handle.
        if ( empty($added) ) {
            wp_add_inline_script( 'jquery-core', $global_js_vars, 'before' );
        }
    }
    
    /**
     * Output custom height and width for map set by user in settings.
     *
     * @since    1.0.0
     */
    public function lpac_output_map_custom_styles()
    {
        $style = '';
        
        if ( is_wc_endpoint_url( 'order-received' ) ) {
            $order_received_map_height = get_option( 'lpac_order_received_page_map_height', 400 );
            $order_received_map_width = get_option( 'lpac_order_received_page_map_width', 100 );
            $style = "height: {$order_received_map_height}px !important; width: {$order_received_map_width}% !important; ";
            $style_mobile = "height: {$order_received_map_height}px !important; width: 100% !important; ";
        }
        
        
        if ( is_wc_endpoint_url( 'view-order' ) ) {
            $view_order_map_height = get_option( 'lpac_view_order_page_map_height', 400 );
            $view_order_map_width = get_option( 'lpac_view_order_page_map_width', 100 );
            $style = "height: {$view_order_map_height}px !important; width: {$view_order_map_width}% !important; ";
            $style_mobile = "height: {$view_order_map_height}px !important; width: 100% !important; ";
        }
        
        // We have to set the condition for !is_wc_endpoint_url() or else this setting would also apply to the order-received page
        
        if ( is_checkout() && !is_wc_endpoint_url( 'order-received' ) ) {
            $checkout_map_height = get_option( 'lpac_checkout_page_map_height', 400 );
            $checkout_map_width = get_option( 'lpac_checkout_page_map_width', 100 );
            $style = "height: {$checkout_map_height}px !important; width: {$checkout_map_width}%; ";
            $style_mobile = "height: {$checkout_map_height}px !important; width: 100% !important; ";
        }
        
        if ( empty($style) ) {
            return;
        }
        $output = <<<HTML
\t\t<style>
\t\t\t.lpac-map{
\t\t\t\t{$style}
\t\t\t}

\t\t\t@media screen and (max-width: 960px ){
\t\t\t\t.lpac-map{
\t\t\t\t\t{$style_mobile}
\t\t\t\t}
\t\t\t}
\t\t</style>
\t\t
HTML;
        echo  $output ;
    }
    
    /**
     * Show a notice banner to admins on the frontend.
     *
     * @since    1.0.0
     */
    public function lpac_add_admin_checkout_notice()
    {
        $hide_notice = get_option( 'lpac_hide_troubleshooting_admin_checkout_notice', 'no' );
        if ( $hide_notice === 'yes' ) {
            return;
        }
        $user_id = get_current_user_id();
        if ( empty($user_id) ) {
            return;
        }
        if ( !current_user_can( 'manage_options' ) ) {
            return;
        }
        $learn_more = esc_html__( 'Learn More', 'map-location-picker-at-checkout-for-woocommerce' );
        $api_key = get_option( 'lpac_google_maps_api_key' );
        $notice_text = esc_html__( 'Hi Admin, some websites might have issues with displaying or using the Google Map. If you\'re having issues then please have a look at your browser console for any errors.' );
        $additional = esc_html__( 'Only Admins on your website can see this notice. You can turn it off in the plugin settings from the "Debug" submenu if everything works fine.' );
        
        if ( empty($api_key) ) {
            $no_api_key = sprintf( esc_html__( 'You have not entered a Google Maps API Key! The plugin will not function how it should until you have entered the key. Please read the following doc for instructions on obtaining a Google Maps API Key %s' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/' target='_blank'>{$learn_more} >></a>" );
            $no_api_key_markup = <<<HTML
\t\t\t<div class="lpac-admin-notice" style="background: #246df3; text-align: center; margin-bottom: 20px; padding: 10px;">
\t\t\t<p style=" color: #ffffff !important; font-size:14px;"><span style="font-weight: bold">Location Picker at Checkout: </span>
\t\t\t\t{$no_api_key}
\t\t\t</p>
\t\t\t</div>
HTML;
            echo  $no_api_key_markup ;
        }
        
        $markup = <<<HTML
\t\t<div class="lpac-admin-notice" style="background: #246df3; text-align: center; margin-bottom: 20px; padding: 10px;">
\t\t\t<p style=" color: #ffffff !important; font-size:14px;"><span style="font-weight: bold">Location Picker at Checkout: </span>
\t\t\t\t{$notice_text}
\t\t\t</p>
\t\t\t<p style=" color: #ffffff !important; font-size:12px; font-weight: bold;" >
\t\t\t\t{$additional}
\t\t\t</p>
\t\t</div>
HTML;
        echo  $markup ;
    }
    
    /**
     * Localize our alert messages to be used by Javascript.
     *
     * @return void
     */
    public function create_checkoutpage_translated_strings()
    {
        $strings = array(
            'geolocation_not_supported' => __( 'Geolocation is not possible on this web browser. Please switch to a different web browser to use our interactive map.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'error_getting_location'    => __( 'Something went wrong while trying to detect your location. Click on the location icon in the address bar and allow our website to detect your location. Please contact us if you need additional assistance.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'no_results_found'          => __( 'No address results found for your location.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'moving_too_quickly'        => __( 'Slow down, you are moving too quickly, use the zoom out button to move the marker across larger distances.', 'map-location-picker-at-checkout-for-woocommerce' ),
        );
        wp_localize_script( 'lpac-checkout-page-map', 'lpacTranslatedAlerts', $strings );
    }

}