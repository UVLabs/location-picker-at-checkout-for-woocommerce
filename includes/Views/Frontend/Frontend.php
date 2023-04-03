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
namespace Lpac\Views\Frontend;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

use  Lpac\Controllers\Map_Visibility_Controller ;
use  Lpac\Controllers\Checkout_Page\Controller as Checkout_Page_Controller ;
use  Lpac\Compatibility\Checkout_Provider ;
use  Lpac\Helpers\Functions ;
use  Lpac\Models\Plugin_Settings\Store_Locations ;
class Frontend
{
    /**
     * Exposes map settings to be used in client-side javascript.
     *
     * @param string $additional   Additional settings to pass to JS.
     * @since 1.0.0
     */
    private function setup_global_js_vars( $additional = array() )
    {
        $controller_checkout_page = new Checkout_Page_Controller();
        $map_options = $controller_checkout_page->get_map_options();
        $last_order_location = $controller_checkout_page->get_last_order_details();
        $show_store_locations_on_map = get_option( 'lpac_show_store_locations_on_map' );
        $store_locations = array();
        if ( $show_store_locations_on_map === 'yes' ) {
            $store_locations = Store_Locations::get_store_locations();
        }
        $map_options = array_merge( $map_options, $additional );
        $map_options['fill_in_fields'] = apply_filters( 'lpac_fill_checkout_fields', true );
        $map_options = json_encode( $map_options );
        $last_order_location = json_encode( $last_order_location );
        $store_locations = json_encode( $store_locations );
        $checkout_provider = ( new Checkout_Provider() )->get_checkout_provider();
        $checkout_provider = json_encode( $checkout_provider );
        $global_variables = <<<JAVASCRIPT
\t\t// LPAC JS
\t\tvar mapOptions = {$map_options};
\t\tvar lpacLastOrder = {$last_order_location};
\t\tvar checkoutProvider = {$checkout_provider};
\t\tvar storeLocations = {$store_locations};
JAVASCRIPT;
        return $global_variables;
    }
    
    /**
     * Get our currently stored store locations.
     *
     * @since 1.6.0
     * @return array
     */
    private function get_normalized_store_locations()
    {
        return Functions::normalize_store_locations();
    }
    
    /**
     * Get the setting for whether the cost by distance feature is enabled.
     *
     * @since 1.6.0
     * @return bool
     */
    private function get_cost_by_distance_setting()
    {
        $enable_cost_by_distance = get_option( 'lpac_enable_shipping_cost_by_distance_feature' );
        return filter_var( $enable_cost_by_distance, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for whether the cost by store distance feature is enabled, reliant on the cost by distance feature.
     *
     * @since 1.6.0
     * @return bool
     */
    private function get_cost_by_store_distance_setting()
    {
        $enable_cost_by_distance = get_option( 'lpac_enable_cost_by_store_distance' );
        return filter_var( $enable_cost_by_distance, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for whether the cost by store location feature is enabled.
     *
     * @since 1.6.0
     * @return bool
     */
    private function get_cost_by_store_location_setting()
    {
        $enable_cost_by_store_location = get_option( 'lpac_enable_cost_by_store_location' );
        return filter_var( $enable_cost_by_store_location, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for whether the store location selector should be shown on the checkout page.
     *
     * This setting is set on the Store Locations settings page.
     *
     * @since 1.6.0
     * @return bool
     */
    private function get_store_location_selector_setting()
    {
        $enable_store_location_selector = get_option( 'lpac_enable_store_location_selector' );
        return filter_var( $enable_store_location_selector, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for store selector label
     *
     * @since 1.6.0
     * @return string
     */
    private function get_store_selector_label_setting()
    {
        return ( get_option( 'lpac_store_select_label' ) ?: __( 'Deliver from', 'map-location-picker-at-checkout-for-woocommerce' ) );
    }
    
    /**
     * Create the store selector field if the option is turned on in "Store Locations".
     *
     * @since 1.6.0
     * @return void
     */
    private function maybe_create_store_location_selector_fields() : void
    {
        /**
         * Checking for get_cost_by_distance_setting() instead of get_cost_by_store_distance_setting because
         * the latter can be turned on while the former is turned off resulting in unexpected results.
         */
        
        if ( ($this->get_cost_by_distance_setting() === false || $this->get_cost_by_store_distance_setting() === false) && $this->get_cost_by_store_location_setting() === false && $this->get_store_location_selector_setting() === true ) {
            $store_locations = array_merge( array(
                '' => '--' . __( 'Please choose an option', 'map-location-picker-at-checkout-for-woocommerce' ) . '--',
            ), $this->get_normalized_store_locations() );
            woocommerce_form_field( 'lpac_order__origin_store', array(
                'type'     => 'select',
                'label'    => $this->get_store_selector_label_setting(),
                'required' => true,
                'class'    => array( 'form-row-wide', 'hidden' ),
                'options'  => $store_locations,
            ), '' );
        }
    
    }
    
    /**
     * Create our custom checkout fields.
     *
     * @return void
     */
    private function create_lpac_checkout_fields() : void
    {
        $this->maybe_create_store_location_selector_fields();
        woocommerce_form_field( 'lpac_latitude', array(
            'label'             => __( 'Latitude', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required'          => false,
            'class'             => ( LPAC_DEBUG ? array( 'form-row-wide', 'fc-skip-hide-optional-field' ) : array( 'form-row-wide', 'hidden' ) ),
            'custom_attributes' => array(
            'readonly' => true,
        ),
        ) );
        woocommerce_form_field( 'lpac_longitude', array(
            'label'             => __( 'Longitude', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required'          => false,
            'class'             => ( LPAC_DEBUG ? array( 'form-row-wide', 'fc-skip-hide-optional-field' ) : array( 'form-row-wide', 'hidden' ) ),
            'custom_attributes' => array(
            'readonly' => true,
        ),
        ) );
        woocommerce_form_field( 'lpac_is_map_shown', array(
            'label'             => __( 'Map Shown', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required'          => false,
            'class'             => ( LPAC_DEBUG ? array( 'form-row-wide', 'fc-skip-hide-optional-field' ) : array( 'form-row-wide', 'hidden' ) ),
            'custom_attributes' => array(
            'readonly' => true,
        ),
            'default'           => 0,
        ) );
        woocommerce_form_field( 'lpac_places_autocomplete', array(
            'label'             => __( 'Places Autocomplete', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required'          => false,
            'class'             => ( LPAC_DEBUG ? array( 'form-row-wide', 'fc-skip-hide-optional-field' ) : array( 'form-row-wide', 'hidden' ) ),
            'custom_attributes' => array(
            'readonly' => true,
        ),
            'default'           => 0,
        ) );
    }
    
    /**
     * Outputs map on the WooCommerce Checkout page.
     *
     * @since    1.0.0
     */
    public function output_map_on_checkout_page()
    {
        $maps_api_key = get_option( 'lpac_google_maps_api_key' );
        if ( empty($maps_api_key) ) {
            return;
        }
        // Map div display visibility
        $display = 'block';
        if ( Map_Visibility_Controller::lpac_show_map( 'checkout' ) === false ) {
            $display = 'none';
        }
        $btn_text = __( 'Detect Current Location', 'map-location-picker-at-checkout-for-woocommerce' );
        $lpac_find_location_btn_text = apply_filters( 'lpac_find_location_btn_text', $btn_text );
        $instuctions_text = get_option( 'lpac_map_instructions_text' );
        if ( empty($instuctions_text) ) {
            $instuctions_text = __( 'Click the "Detect Current Location" button then move the red marker to your desired shipping address.', 'map-location-picker-at-checkout-for-woocommerce' );
        }
        $instuctions_text = apply_filters( 'lpac_map_instuctions_text', $instuctions_text );
        $user_id = (int) get_current_user_id();
        do_action( 'lpac_before_checkout_map_container', '', $user_id );
        ?>
		<?php 
        ?>
		<div style='display: <?php 
        echo  esc_attr( $display ) ;
        ?>' id='lpac-map-container' class='woocommerce-shipping-fields__field-wrapper'>
			<?php 
        do_action( 'lpac_before_checkout_map', '', $user_id );
        ?>
			<div class='lpac-map'></div>
			<?php 
        do_action( 'lpac_after_checkout_map', '', $user_id );
        ?>
			<div class='lpac-map-controls'>
			<?php 
        do_action( 'lpac_before_checkout_map_controls', '', $user_id );
        ?>
			<div id='lpac-map-instructions'> <?php 
        echo  esc_html( $instuctions_text ) ;
        ?></div>
			<?php 
        do_action( 'lpac_before_detect_location_btn', '', $user_id );
        ?>
			<div id='lpac-find-location-btn-wrapper'><button id='lpac-find-location-btn' class='button btn' type='button'><?php 
        echo  esc_html( $lpac_find_location_btn_text ) ;
        ?></button></div>
			<?php 
        do_action( 'lpac_after_detect_location_btn', '', $user_id );
        ?>
			<div id='lpac-saved-addresses'>
				<?php 
        do_action( 'lpac_before_saved_addresses', '', $user_id );
        ?>
				<ul>
					<?php 
        do_action( 'lpac_saved_addresses', '', $user_id );
        ?>
				</ul>
				<?php 
        do_action( 'lpac_after_saved_addresses', '', $user_id );
        ?>
			</div>
			<?php 
        do_action( 'lpac_after_checkout_map_controls', '', $user_id );
        ?>
			</div>
		</div>
		<?php 
        do_action( 'lpac_after_checkout_map_container', '', $user_id );
        // Create our fields that hold data such as gps cordinates etc.
        $this->create_lpac_checkout_fields();
        // Add inline global JS so that we can use data fetched using PHP inside JS
        $global_js_vars = $this->setup_global_js_vars();
        $added = wp_add_inline_script( LPAC_PLUGIN_NAME . '-base-map', $global_js_vars, 'before' );
        // On some websites our basemap might not enqueue in time. In those cases fall back to default wp jquery handle.
        if ( empty($added) ) {
            wp_add_inline_script( 'jquery-core', $global_js_vars, 'before' );
        }
    }
    
    /**
     * Add origin store name on order details page.
     *
     * @return void
     */
    public function output_origin_store_name()
    {
        if ( $this->get_store_location_selector_setting() === false && $this->get_cost_by_store_distance_setting() === false && $this->get_cost_by_store_location_setting() === false ) {
            return;
        }
        // If this isn't the order received page shown after a purchase, or the view order page shown on the user account, then bail.
        if ( !is_wc_endpoint_url( 'view-order' ) && !is_wc_endpoint_url( 'order-received' ) ) {
            return;
        }
        global  $wp ;
        if ( is_wc_endpoint_url( 'order-received' ) ) {
            $order_id = $wp->query_vars['order-received'];
        }
        if ( is_wc_endpoint_url( 'view-order' ) ) {
            $order_id = $wp->query_vars['view-order'];
        }
        if ( empty($order_id) ) {
            return;
        }
        $store_origin_name = get_post_meta( $order_id, '_lpac_order__origin_store_name', true );
        if ( empty($store_origin_name) ) {
            return;
        }
        $store_origin_name_label = apply_filters( 'lpac_order_details_deliver_from_text', esc_html__( 'Order origin', 'map-location-picker-at-checkout-for-woocommerce' ) );
        ?>
			<br/>
			<h2 class='woocommerce-order-details__title'><?php 
        echo  esc_html( $store_origin_name_label ) ;
        ?></h2>
			<p class='lpac_order_details_deliver_from_text' style='font-size: 20px; font-weight: bold'><?php 
        echo  esc_html( $store_origin_name ) ;
        ?></p>
		<?php 
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
        $order = wc_get_order( $order_id );
        // Backwards compatibility, prior to v1.5.4 we stored location coords as private meta.
        $latitude = ( (double) $order->get_meta( 'lpac_latitude' ) ?: (double) $order->get_meta( '_lpac_latitude' ) );
        $longitude = ( (double) $order->get_meta( 'lpac_longitude' ) ?: (double) $order->get_meta( '_lpac_longitude' ) );
        
        if ( $order->has_shipping_address() ) {
            $shipping_address_1 = $order->get_shipping_address_1();
            $shipping_address_2 = $order->get_shipping_address_2();
        } else {
            // Highly likely that the user didnt check the "Shipping to a different address?" option, so shipping fields wouldnt be present.
            $shipping_address_1 = $order->get_billing_address_1();
            $shipping_address_2 = $order->get_billing_address_2();
        }
        
        if ( empty($latitude) || empty($longitude) ) {
            return;
        }
        $collected_during_order = array(
            'lpac_map_order_latitude'           => $latitude,
            'lpac_map_order_longitude'          => $longitude,
            'lpac_map_order_shipping_address_1' => $shipping_address_1,
            'lpac_map_order_shipping_address_2' => $shipping_address_2,
        );
        $user_id = (int) get_current_user_id();
        $label = __( 'Location', 'map-location-picker-at-checkout-for-woocommerce' );
        do_action( 'lpac_before_order_details_map_container', '', $user_id );
        ?>
		<div id="lpac-map-container" class='woocommerce-shipping-fields__field-wrapper'>
			<?php 
        do_action( 'lpac_before_order_details_map', '', $user_id );
        ?>
			<h2 class='woocommerce-order-details__title'><?php 
        echo  esc_html( $label ) ;
        ?></h2>
			<div class='lpac-map'></div>
			<?php 
        do_action( 'lpac_after_order_details_map', '', $user_id );
        ?>
		</div>
		<?php 
        do_action( 'lpac_after_order_details_map_container', '', $user_id );
        // Add inline global JS so that we can use data fetched using PHP inside JS
        $global_js_vars = $this->setup_global_js_vars( $collected_during_order );
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
        ?>
		<style>
			.lpac-map{ <?php 
        echo  esc_attr( $style ) ;
        ?> }
			@media screen and (max-width: 960px ){
				.lpac-map{ <?php 
        echo  esc_attr( $style_mobile ) ;
        ?> }
			}
		</style>
		
		<?php 
    }
    
    /**
     * Show a notice banner to admins on the frontend.
     *
     * @since    1.0.0
     */
    public function add_admin_checkout_notice()
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
        $notice_text = esc_html__( 'Hi Admin, some websites might have issues with displaying or using the Google Map. If you\'re having issues then please have a look at your browser console for any errors.', 'map-location-picker-at-checkout-for-woocommerce' );
        $additional = esc_html__( 'Only Admins on your website can see this notice. You can turn it off in the plugin settings from the "Tools" submenu if everything works fine.', 'map-location-picker-at-checkout-for-woocommerce' );
        
        if ( empty($api_key) ) {
            $no_api_key = sprintf( esc_html__( 'You have not entered a Google Maps API Key! The plugin will not function how it should until you have entered the key. Please read the following doc for instructions on obtaining a Google Maps API Key %s' ), "<a style='color: blue !important' href='https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/' target='_blank'>{$learn_more} >></a>" );
            ?>
			<div class="lpac-admin-notice" style="background: red; text-align: center; margin-bottom: 20px; padding: 10px; font-weight: bold">
			<p style=" color: #ffffff !important; font-size:14px;"><span style="font-weight: bold">Location Picker at Checkout: </span>
				<?php 
            echo  wp_kses_post( $no_api_key ) ;
            ?>
			</p>
			</div>
			<?php 
        }
        
        ?>
		<div class="lpac-admin-notice" style="background: #246df3; text-align: center; margin-bottom: 20px; padding: 10px;">
			<p style=" color: #ffffff !important; font-size:14px;"><span style="font-weight: bold">Location Picker at Checkout: </span>
				<?php 
        echo  esc_html( $notice_text ) ;
        ?>
			</p>
			<p style=" color: #ffffff !important; font-size:12px; font-weight: bold;" >
				<?php 
        echo  esc_html( $additional ) ;
        ?>
			</p>
		</div>
		<?php 
    }
    
    /**
     * Localize our alert messages to be used by Javascript.
     *
     * @return void
     */
    public function create_checkoutpage_translated_strings()
    {
        $strings = array(
            'geolocation_not_supported'  => __( 'Geolocation is not possible on this web browser. Please switch to a different web browser to use our interactive map.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'manually_select_location'   => __( 'Please select your location manually by clicking on the map then moving the marker to your desired location.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'no_results_found'           => __( 'No address results found for your location.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'moving_too_quickly'         => __( 'Slow down, you are moving too quickly, use the zoom out button to move the marker across larger distances.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'generic_error'              => __( 'An error occurred while trying to detect your location. Please try again after the page has refreshed.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'generic_last_order_address' => apply_filters( 'lpac_empty_last_order_address_default_text', __( 'Previous Address', 'map-location-picker-at-checkout-for-woocommerce' ) ),
        );
        wp_localize_script( 'lpac-checkout-page-map', 'lpacTranslatedJsStrings', $strings );
    }

}