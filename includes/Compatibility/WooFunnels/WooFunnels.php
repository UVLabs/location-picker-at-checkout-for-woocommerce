<?php

/**
* WooFunnels compatibility Class.
*
* Adds compatibility for WooFunnels plugin.
*
* Author:          Uriahs Victor
* Created on:      19/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.3
* @package Lpac
*/
namespace Lpac\Compatibility\WooFunnels;

/**
* WooFunnels compatibility Class.
*
*/
class WooFunnels
{
    /**
     * Check if Lpac has already been added to fieldset.
     * @var false
     */
    private  $hook_run = false ;
    /**
     * Lpac Map Shown field
     * @var array
     */
    private  $map_shown_field = array() ;
    /**
     * Lpac Latitude field
     * @var array
     */
    private  $latitude_field = array() ;
    /**
     * Lpac Longitude field.
     * @var array
     */
    private  $longitude_field = array() ;
    /**
     * Lpac Places Autocomplete Used field.
     * @var array
     */
    private  $places_autocomplete_used_field = array() ;
    /**
     * Lpac save address checkbox field.
     * @var array
     */
    private  $saved_addresses_checkbox_field = array() ;
    /**
     * Lpac address name input text field.
     * @var array
     */
    private  $saved_addresses_name_input_field = array() ;
    /**
     * Lpac store origin dropdown select field.
     * @var array
     */
    private  $origin_store_dropdown_field = array() ;
    /**
     * Get our currently stored store locations.
     *
     * @since 1.6.0
     * @return array
     */
    private function get_store_locations()
    {
        return $this->normalize_store_locations();
    }
    
    /**
     * Normalize our store locations for displaying in a dropdown.
     * @return array
     */
    private function normalize_store_locations()
    {
        $store_locations = get_option( 'lpac_store_locations', array() );
        $location_ids = array_column( $store_locations, 'store_location_id' );
        $location_names = array_column( $store_locations, 'store_name_text' );
        $store_locations_normalized = array_combine( $location_ids, $location_names );
        return $store_locations_normalized;
    }
    
    /**
     * Get the setting for whether the cost by distance feature is enabled.
     *
     * @return mixed
     */
    private function get_cost_by_distance_setting()
    {
        $enable_cost_by_distance = get_option( 'lpac_enable_shipping_cost_by_distance_feature' );
        return filter_var( $enable_cost_by_distance, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for whether the cost by store distance feature is enabled. Reliant on cost by distance feature.
     *
     * @return mixed
     */
    private function get_cost_by_store_distance_setting()
    {
        $enable_cost_by_distance = get_option( 'lpac_enable_cost_by_store_distance' );
        return filter_var( $enable_cost_by_distance, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for whether the cost by store location feature is enabled.
     *
     * @return mixed
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
     * @return mixed
     */
    private function get_store_location_selector_setting()
    {
        $enable_store_location_selector = get_option( 'lpac_enable_store_location_selector' );
        return filter_var( $enable_store_location_selector, FILTER_VALIDATE_BOOL );
    }
    
    /**
     * Get the setting for store selector label
     *
     * @return mixed
     */
    private function get_store_selector_label_setting()
    {
        return ( get_option( 'lpac_store_select_label' ) ?: __( 'Deliver from', 'map-location-picker-at-checkout-for-woocommerce' ) );
    }
    
    /**
     * Create the store selector field if the option is turned on in "Store Locations".
     *
     * @return void
     */
    private function create_store_location_selector_field() : void
    {
        $enable_cost_by_store_distance = $this->get_cost_by_store_distance_setting();
        $enable_cost_by_store_location = $this->get_cost_by_store_location_setting();
        $enable_store_location_selector_setting = $this->get_store_location_selector_setting();
        
        if ( $enable_cost_by_store_distance === false && $enable_cost_by_store_location === false && $enable_store_location_selector_setting === true ) {
            $store_locations = array_merge( array(
                '' => '--' . __( 'Please choose an option', 'map-location-picker-at-checkout-for-woocommerce' ) . '--',
            ), $this->get_store_locations() );
            $this->origin_store_dropdown_field = array(
                'id'         => 'lpac_order__origin_store',
                'label'      => $this->get_store_selector_label_setting(),
                'type'       => 'select',
                'field_type' => 'billing',
                'clear'      => true,
                'required'   => true,
                'class'      => array( 'wfacp-col-full', 'hidden' ),
                'options'    => $store_locations,
            );
        }
    
    }
    
    /**
     * Setup needed input fields for Lpac.
     * @return void
     */
    public function create_lpac_fields()
    {
        $this->map_shown_field = array(
            'label'      => __( 'Map Shown', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'       => 'text',
            'field_type' => 'billing',
            'class'      => ( LPAC_DEBUG ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ) ),
            'clear'      => true,
            'id'         => 'lpac_is_map_shown',
        );
        $this->latitude_field = array(
            'label'      => __( 'Latitude', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'       => 'text',
            'field_type' => 'billing',
            'class'      => ( LPAC_DEBUG ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ) ),
            'clear'      => true,
            'id'         => 'lpac_latitude',
        );
        $this->longitude_field = array(
            'label'      => __( 'Longitude', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'       => 'text',
            'field_type' => 'billing',
            'class'      => ( LPAC_DEBUG ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ) ),
            'clear'      => true,
            'id'         => 'lpac_longitude',
        );
        $this->places_autocomplete_used_field = array(
            'label'      => __( 'Places Autocomplete Used', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'       => 'text',
            'field_type' => 'billing',
            'clear'      => true,
            'required'   => false,
            'class'      => ( LPAC_DEBUG ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ) ),
            'id'         => 'lpac_places_autocomplete',
        );
        $this->create_store_location_selector_field();
    }
    
    /**
     * Attach needed fields for lpac to WooFunnels checkout fields array.
     *
     * @param mixed $fields
     * @return mixed
     */
    public function add_lpac_checkout_fields( $fields )
    {
        
        if ( is_array( $fields ) && count( $fields ) > 0 ) {
            if ( !empty($this->origin_store_dropdown_field) ) {
                // We do this or else only a blank field would appear
                $fields['billing']['lpac_order__origin_store'] = $this->origin_store_dropdown_field;
            }
            $fields['billing']['lpac_is_map_shown'] = $this->map_shown_field;
            $fields['billing']['lpac_latitude'] = $this->latitude_field;
            $fields['billing']['lpac_longitude'] = $this->longitude_field;
            $fields['billing']['lpac_places_autocomplete'] = $this->places_autocomplete_used_field;
        }
        
        return $fields;
    }
    
    /**
     * Add Lpac checkout fields to WooFunnels fieldset
     *
     * @param mixed $section
     * @return mixed
     */
    public function add_lpac_checkout_fields_to_fieldsets( $section )
    {
        if ( !empty($this->origin_store_dropdown_field) ) {
            $section['single_step'][0]['fields'][] = $this->origin_store_dropdown_field;
        }
        $section['single_step'][0]['fields'][] = $this->map_shown_field;
        $section['single_step'][0]['fields'][] = $this->latitude_field;
        $section['single_step'][0]['fields'][] = $this->longitude_field;
        $section['single_step'][0]['fields'][] = $this->places_autocomplete_used_field;
        // }
        return $section;
    }

}