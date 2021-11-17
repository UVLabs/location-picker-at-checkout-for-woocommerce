<?php

/**
 * The admin settings of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @author     Uriahs Victor <info@soaringleads.com>
 */
namespace Lpac\Views;

use  Lpac\Helpers\Functions as Functions_Helper ;
use  Lpac\Controllers\Map_Visibility_Controller ;
class Admin_Settings extends \WC_Settings_Page
{
    /**
     * Setting page id.
     *
     * @var string
     */
    protected  $id ;
    /**
     * Setting page label.
     *
     * @var string
     */
    protected  $label ;
    /**
     * Constructor.
     */
    public function __construct()
    {
        if ( !defined( 'ABSPATH' ) ) {
            exit;
        }
        $this->id = 'lpac_settings';
        $this->label = __( 'Location Picker at Checkout', 'map-location-picker-at-checkout-for-woocommerce' );
        /**
         *  Define all hooks instead of inheriting from parent
         */
        // parent::__construct();
        // Add the tab to the tabs array
        // This method is located in parent class
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 99 );
        // Add sections to our custom tab
        add_action( 'woocommerce_sections_' . $this->id, array( $this, 'lpac_output_settings_sections' ) );
        // Output our different settings
        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'lpac_output_plugin_settings' ) );
        // Save our settings
        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'lpac_save_plugin_settings' ) );
    }
    
    /**
     * Create plugin settings sections.
     *
     * @return array $sections The sections for the custom tab.
     * @since    1.1.0
     */
    public function lpac_create_plugin_settings_sections()
    {
        $sections = array(
            'general'          => __( 'General', 'map-location-picker-at-checkout-for-woocommerce' ),
            'display'          => __( 'Display', 'map-location-picker-at-checkout-for-woocommerce' ),
            'visibility_rules' => __( 'Visibility Rules', 'map-location-picker-at-checkout-for-woocommerce' ),
            'debug'            => __( 'Debug', 'map-location-picker-at-checkout-for-woocommerce' ),
            'premium'          => __( 'Premium', 'map-location-picker-at-checkout-for-woocommerce' ),
        );
        // TODO: Allow this section once development of premium features are futher ahead.
        if ( lpac_fs()->is_free_plan() ) {
            unset( $sections['premium'] );
        }
        return apply_filters( 'woocommerce_get_sections_' . LPAC_PLUGIN_NAME, $sections );
    }
    
    /**
     * Output the settings sections markup.
     *
     * @since    1.1.0
     */
    public function lpac_output_settings_sections()
    {
        global  $current_section ;
        $sections = $this->lpac_create_plugin_settings_sections();
        if ( empty($sections) || 1 === sizeof( $sections ) ) {
            return;
        }
        echo  '<ul class="subsubsub">' ;
        $array_keys = array_keys( $sections );
        foreach ( $sections as $id => $label ) {
            echo  '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . (( $current_section == $id ? 'current' : '' )) . '">' . $label . '</a> ' . (( end( $array_keys ) == $id ? '' : '|' )) . ' </li>' ;
        }
        echo  '</ul><br class="clear" />' ;
    }
    
    /**
     * Create a banner at the top of the page with some information for user..
     *
     * @since    1.2.0
     * @return mixed $markup The markup for the banner.
     */
    private function lpac_create_plugin_settings_banner()
    {
        $here = __( 'HERE', 'map-location-picker-at-checkout-for-woocommerce' );
        $external_icon = '<strong><span style="text-decoration: none" class="dashicons dashicons-external"></span></strong>';
        
        if ( empty(get_option( 'lpac_google_maps_api_key', '' )) ) {
            $no_api_key = __( 'You need an API Key to use Google Maps. Please see this document for how to get it ', 'map-location-picker-at-checkout-for-woocommerce' );
            $no_api_key .= "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/' target='_blank'>{$here}</a>";
            $no_api_key .= $external_icon;
        } else {
            $no_api_key = '';
        }
        
        $title = __( "Use the Options Below to Change the Plugin's Settings", 'map-location-picker-at-checkout-for-woocommerce' );
        $issues = __( 'If you encounter any issues then please open a support ticket ', 'map-location-picker-at-checkout-for-woocommerce' );
        $issues .= "<a href='https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/' target='_blank'>{$here}</a>";
        $issues .= $external_icon;
        $documentation = __( 'Read the documentation ', 'map-location-picker-at-checkout-for-woocommerce' );
        $documentation .= "<a href='https://lpacwp.com/docs/' target='_blank'>{$here}</a>";
        $documentation .= $external_icon;
        $translate_plugin = __( 'Plugin settings not in your Language? Help translate it ', 'map-location-picker-at-checkout-for-woocommerce' );
        $translate_plugin .= "<a href='hhttps://translate.wordpress.org/projects/wp-plugins/map-location-picker-at-checkout-for-woocommerce/' target='_blank'>{$here}</a>";
        $translate_plugin .= $external_icon;
        $markup = <<<MARKUP
\t\t<div style="background: #fff; border-radius: 5px; margin-bottom: 20px; padding: 30px; text-align:center;">
\t\t<h2>{$title}</h2>
\t\t<p>{$no_api_key}</p>
\t\t<p>{$documentation}</p>
\t\t<p>{$issues}</p>
\t\t<p>{$translate_plugin}</p>
\t\t</div>
MARKUP;
        return $markup;
    }
    
    /**
     * House all the plugin settings to do with General.
     *
     * @return array
     */
    public function lpac_create_general_setting_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC General Settings', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_general_settings',
            'type' => 'title',
            'desc' => $this->lpac_create_plugin_settings_banner(),
        );
        $plugin_enabled = get_option( 'lpac_enabled' );
        /* translators: 1: Dashicons outbound link icon*/
        $learn_more = sprintf( __( 'Learn More %s', 'map-location-picker-at-checkout-for-woocommerce' ), '<span style="text-decoration: none" class="dashicons dashicons-external"></span>' );
        /*
         * If the option doesn't exist then this is most likely a new install, so set the checkbox to checked by default.
         * If the option already exists, then use the setting saved in the database.
         */
        
        if ( empty($plugin_enabled) ) {
            $lpac_settings[] = array(
                'name'     => __( 'Enabled', 'map-location-picker-at-checkout-for-woocommerce' ),
                'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
                'desc_tip' => __( 'Enable map on checkout and order details pages.', 'map-location-picker-at-checkout-for-woocommerce' ),
                'id'       => 'lpac_enabled',
                'type'     => 'checkbox',
                'css'      => 'max-width:80px;',
                'value'    => 'yes',
            );
        } else {
            $lpac_settings[] = array(
                'name'     => __( 'Enabled', 'map-location-picker-at-checkout-for-woocommerce' ),
                'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
                'desc_tip' => __( 'Enable map on checkout and order details pages.', 'map-location-picker-at-checkout-for-woocommerce' ),
                'id'       => 'lpac_enabled',
                'type'     => 'checkbox',
                'css'      => 'max-width:80px;',
            );
        }
        
        $lpac_settings[] = array(
            'name'     => __( 'Google Maps API Key', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Enter the API key from Google cloud console.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Enter the API key you copied from the Google Cloud Console. <a href="https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/" target="_blank">Learn More <span style="text-decoration: none" class="dashicons dashicons-external"></span></a>', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_google_maps_api_key',
            'type'     => 'text',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Detect Customer Location on Checkout Page Load', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Enabling this option will have the plugin immediately try to detect the customer location when the checkout page loads. NOTE: This can negatively impact customer experiences. Think carefully before enabling this option. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/#detect-customer-location-on-checkout-page-load' target='_blank'>{$learn_more}</a>" ),
            'id'       => 'lpac_auto_detect_location',
            'type'     => 'checkbox',
            'css'      => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Force Use of Map', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Prevent the customer from checking out until they select a location on the map.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_force_map_use',
            'type'     => 'checkbox',
            'css'      => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Default Coordinates', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the default latitude and logitude that will be fetched every time the map loads.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'        => __( 'Enter the default latitude and logitude that will be fetched every time the map loads. You can find the coordinates for a location <a href="https://www.latlong.net/" target="_blank">here >></a>. Be sure to include the comma when adding your coordinates above.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_map_starting_coordinates',
            'placeholder' => '14.024519,-60.974876',
            'type'        => 'text',
            'css'         => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Default Zoom', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Recommended number is 16.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'        => __( 'Enter the default zoom that will be used every time the map loads.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_general_map_zoom_level',
            'placeholder' => '16',
            'default'     => 3,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Enable Clickable Icons', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Should customers be able to click on icons of different locations that appear on Google Maps? Recommended setting: Disabled', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_allow_clicking_on_map_icons',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Remove Plus Code From Address', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'If you enable this option the plugin will attempt to remove the Plus Code that shows infront addresses returned by Google Maps. Example <code>TMWXH+CW</code>. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/#remove-plus-code-from-address' target='blank'>{$learn_more}</a>" ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_remove_address_plus_code',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Autofill Billing fields', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Should the billing fields be automatically populated with information pulled from the location?', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_autofill_billing_fields',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Show Map on the Order Received Page', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'This option displays a map view on the order received page after an order has been placed by a customer. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/#show-map-on-the-order-received-page' target='blank'> {$learn_more} </a>" ),
            'id'       => 'lpac_display_map_on_order_received_page',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
        );
        $lpac_settings[] = array(
            'name'     => __( 'Show Map on View Order Page', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'This option displays a map view on the order details page in the customer account. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/#show-map-on-view-order-page' target='blank'> {$learn_more} </a>" ),
            'id'       => 'lpac_display_map_on_view_order_page',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
        );
        $lpac_settings[] = array(
            'name'     => __( 'Add Map Link to Order Emails?', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Add either a Button or QR Code that links to Google Maps to the order emails. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/#add-map-link-to-order-emails' target='blank'>{$learn_more}</a>" ),
            'id'       => 'lpac_enable_delivery_map_link_in_email',
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'     => 'checkbox',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Link Type', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Add either a button to Google Maps or a QR Code to the order emails.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'QR Codes are saved to your uploads directory at: <code>/wp-content/uploads/lpac-qr-codes/year/month/day/order_id.jpg</code>', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_email_delivery_map_link_type',
            'type'     => 'select',
            'options'  => array(
            'button'  => __( 'Button', 'map-location-picker-at-checkout-for-woocommerce' ),
            'qr_code' => __( 'QR Code', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Link Location', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_email_delivery_map_link_location',
            'type'    => 'select',
            'options' => array(
            'woocommerce_email_before_order_table' => __( 'Before Order Table', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_email_customer_details'   => __( 'Before Customer Details', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'     => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Select Emails', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class'   => 'wc-enhanced-select',
            'desc'    => __( 'Select the Emails you\'d like this setting to take effect on.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_email_delivery_map_emails',
            'type'    => 'multiselect',
            'options' => array(
            'new_order'                => __( 'New Order', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_on_hold_order'   => __( 'Order on Hold', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_note'            => __( 'Customer Note', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_completed_order' => __( 'Completed Order', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_invoice'         => __( 'Customer Invoice', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'     => 'min-width:300px;height: 100px',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Enable Places Autocomplete Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Allows customers to begin typing an address and receive suggestions from Google. NOTE: This is not as reliable as allowing customers to select their location on the map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/' target='blank'>{$learn_more}</a>" ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_enable_places_autocomplete',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Allowed Places AutoComplete Fields', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'    => __( 'Select the input fields where the places autocomplete should be allowed.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_places_autocomplete_fields',
            'type'    => 'multiselect',
            'options' => array(
            'billing_address_1'  => __( 'Billing Address 1', 'map-location-picker-at-checkout-for-woocommerce' ),
            'shipping_address_1' => __( 'Shipping Address 1', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'     => 'min-width:300px;',
            'class'   => 'wc-enhanced-select',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Hide Map When Using Places Autocomplete', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Hide the map when using the Places Autocomplete feature.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_places_autocomplete_hide_map',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Housekeeping', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Delete all plugin settings on uninstall.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_delete_settings_on_uninstall',
            'type'     => 'checkbox',
        );
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_general_settings_section_end',
        );
        return $lpac_settings;
    }
    
    /**
     * House all the plugin settings to do with Display.
     *
     * @return array
     */
    private function lpac_create_display_setting_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC Display Settings', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_display_settings',
            'type' => 'title',
            'desc' => $this->lpac_create_plugin_settings_banner(),
        );
        $lpac_settings[] = array(
            'name'        => __( 'Background Color (HEX)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'        => __( 'Background color of map container (visible while map is loading).', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_map_background_color',
            'type'        => 'color',
            'placeholder' => '#eeeeee',
            'default'     => '#EEEEEE',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Where Should the Map Appear on the Checkout Page?', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'This option displays a map view on the order received page after an order has been placed by a customer.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_checkout_map_orientation',
            'type'     => 'select',
            'options'  => array(
            'woocommerce_before_checkout_billing_form'  => __( 'Billing Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_after_checkout_billing_form'   => __( 'Billing Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_before_checkout_shipping_form' => __( 'Shipping Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_after_checkout_shipping_form'  => __( 'Shipping Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Checkout Page Map Width (in %)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the width of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_checkout_page_map_width',
            'placeholder' => '100',
            'default'     => 100,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Checkout Page Map Height (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_checkout_page_map_height',
            'placeholder' => '400',
            'default'     => 400,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Order Received Page Map Height (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_order_received_page_map_height',
            'placeholder' => '400',
            'default'     => 400,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Order Received Page Map Width (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the width of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_order_received_page_map_width',
            'placeholder' => '100',
            'default'     => 100,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'View Order Page Map Height (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_view_order_page_map_height',
            'placeholder' => '400',
            'default'     => 400,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'View Order Page Map Width (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_view_order_page_map_width',
            'placeholder' => '100',
            'default'     => 100,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_display_settings_section_end',
        );
        return $lpac_settings;
    }
    
    /**
     * House all the plugin settings to do with map visibility rules.
     *
     * @return array
     */
    private function create_visibility_settings_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC Map Visibility Rules', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_map_visibility_settings',
            'type' => 'title',
            'desc' => $this->lpac_create_plugin_settings_banner(),
        );
        $lpac_settings[] = array(
            'name'     => __( 'Hide map for Guest orders', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Hide the map for customers who aren\'t logged in.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_hide_map_for_guests',
            'type'     => 'checkbox',
            'css'      => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Hide Map for Shipping Methods', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class'   => 'wc-enhanced-select',
            'desc'    => __( 'Hide the map when any of these shipping methods are chosen by the user.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_wc_shipping_methods',
            'type'    => 'multiselect',
            'options' => Functions_Helper::lpac_get_available_shipping_methods(),
            'css'     => 'min-width:300px;height: 100px',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Shipping Classes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class'   => 'wc-enhanced-select',
            'desc'    => __( 'Select shipping classes. NOTE: These settings apply if ANY of the items in the cart meets the condition.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_wc_shipping_classes',
            'type'    => 'multiselect',
            'options' => Functions_Helper::lpac_get_available_shipping_classes(),
            'css'     => 'min-width:300px;height: 100px',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Show or Hide', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'    => sprintf(
            /* translators: 1: Line break HTML 2: opening strong tag 3: closing strong tag*/
            __( 'Should the map be shown or hidden if the order falls within above selected shipping classes? %1$s%1$s Selecting %2$sShow%3$s will display the map %2$sONLY IF%3$s the customer order falls inside the shipping classes selected above. %1$s Selecting %2$sHide%3$s will display the map only if the customer order %2$sDOES NOT%3$s fall inside the shipping classes selected above.', 'map-location-picker-at-checkout-for-woocommerce' ),
            '<br>',
            '<strong>',
            '</strong>'
        ),
            'id'      => 'lpac_wc_shipping_classes_show_hide',
            'type'    => 'radio',
            'options' => array(
            'show' => __( 'Show', 'map-location-picker-at-checkout-for-woocommerce' ),
            'hide' => __( 'Hide', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
        );
        $lpac_settings[] = array(
            'name'    => __( 'Show map for coupons', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class'   => 'wc-enhanced-select',
            'desc'    => __( 'Show the map whenever any of the selected coupons are applied to the order.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_map_show_for_coupons',
            'type'    => 'multiselect',
            'options' => Functions_Helper::get_available_coupons(),
            'css'     => 'min-width:300px;height: 100px',
        );
        return $lpac_settings;
    }
    
    /**
     * House all the plugin settings to do with Debugging.
     *
     * @return array
     */
    private function lpac_create_debug_setting_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC Debug Settings', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_debug_settings',
            'type' => 'title',
            'desc' => $this->lpac_create_plugin_settings_banner(),
        );
        $lpac_settings[] = array(
            'name'     => __( 'Hide checkout notice', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Hide the admin checkout notice.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_hide_troubleshooting_admin_checkout_notice',
            'type'     => 'checkbox',
            'css'      => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Dequeue Google Maps Script in Frontend', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'    => __( 'If you\'re receiving console errors about Google Maps already being loaded by another plugin, then select where the Google Maps script should be removed. Be sure to test the map if you turn this option on.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_dequeue_google_maps',
            'type'    => 'radio',
            'options' => array(
            'none'      => __( 'None', 'map-location-picker-at-checkout-for-woocommerce' ),
            'frontend'  => __( 'Frontend', 'map-location-picker-at-checkout-for-woocommerce' ),
            'dashboard' => __( 'Dashboard', 'map-location-picker-at-checkout-for-woocommerce' ),
            'both'      => __( 'Both', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
        );
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_debug_settings_section_end',
        );
        return $lpac_settings;
    }
    
    /**
     * Output the table to sort the map visibility rules.
     *
     * @return void
     */
    private function output_map_visibility_rules_order()
    {
        $default_visibility_rules = Map_Visibility_Controller::get_map_visibility_rules();
        $rules = get_option( 'lpac_map_visibility_rules_order', $default_visibility_rules );
        /* translators: 1: Dashicons outbound link icon*/
        $learn_more = sprintf( __( 'Learn More %s', 'map-location-picker-at-checkout-for-woocommerce' ), '<span style="text-decoration: none" class="dashicons dashicons-external"></span>' );
        /* If new rules were added that have not been arranged yet then show them in the list at the top. */
        
        if ( count( $rules ) !== count( $default_visibility_rules ) ) {
            $new_rules = array_diff_assoc( $default_visibility_rules, $rules );
            $rules = array_merge( $new_rules, $rules );
            echo  '<style>' ;
            foreach ( $new_rules as $key => $value ) {
                echo  '#' . esc_html( $key ) . '{ background: #FBFF12; }' ;
            }
            echo  '</style>' ;
        }
        
        /* translators: 1: Learn more link*/
        $info_text = sprintf( esc_html( 'Use the table below to arrange the map visibility rules by dragging and dropping. The last rule in the the table decides the final visibility state of the map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/visibility-rules/#rules-order' target='blank'>{$learn_more}</a>" );
        ?>

		<div>
			<table id="lpac-rules" class='wc-shipping-zones'>
				<h4><?php 
        echo  $info_text ;
        ?></h4>
				<p id="lpac-rules-saving" style="display: none; font-weight: 700;"><?php 
        esc_html_e( 'Saving...' );
        ?></p>
				<p id="lpac-rules-saving-success" style="color: #008000; display: none; font-weight: 700;"><?php 
        esc_html_e( 'Saved successfully' );
        ?></p>
				<p id="lpac-rules-saving-failed" style="color: #d63638; display: none; font-weight: 700;"><?php 
        esc_html_e( 'An error occurred while saving rules, please try again...' );
        ?></p>
			<thead>
				<tr>
					<th><span class="woocommerce-help-tip"></span></th>
					<th><?php 
        esc_html_e( 'Rules Order', 'map-location-picker-at-checkout-for-woocommerce' );
        ?></th>
				</tr>
			</thead>

			<tbody>
			
			<?php 
        foreach ( $rules as $rule => $rule_name ) {
            ?>
			<tr data-id="<?php 
            echo  esc_attr( $rule ) ;
            ?>">
			<td width="1%" class="wc-shipping-zone-sort ui-sortable-handle"></td>
			<td id="<?php 
            echo  esc_attr( $rule ) ;
            ?>">
				<?php 
            echo  esc_html( $rule_name ) ;
            ?>
			</td>
			</tr>
			<?php 
        }
        ?>

			</tbody>

		</table>
		</div>

		<?php 
    }
    
    /**
     * Create the setting options for the plugin.
     *
     * @since    1.0.0
     * @param array $settings The WooCommerce settings.
     * @param array $current_section The current settings tab being viewed.
     */
    public function lpac_create_plugin_settings_fields()
    {
        global  $current_section ;
        $lpac_settings = array();
        if ( empty($current_section) || $current_section === 'general' ) {
            $lpac_settings = $this->lpac_create_general_setting_fields();
        }
        if ( $current_section === 'display' ) {
            $lpac_settings = $this->lpac_create_display_setting_fields();
        }
        if ( $current_section === 'debug' ) {
            $lpac_settings = $this->lpac_create_debug_setting_fields();
        }
        
        if ( $current_section === 'visibility_rules' ) {
            $lpac_settings = $this->create_visibility_settings_fields();
            // Add section end lastly
            $lpac_settings = array_merge( $lpac_settings, array(
                'type' => 'sectionend',
                'id'   => 'lpac_map_visibility_settings_section_end',
            ) );
        }
        
        if ( $current_section === 'premium' ) {
        }
        // Custom attributes example
        // https://woocommerce.github.io/code-reference/files/woocommerce-includes-admin-wc-meta-box-functions.html#source-view.146
        // $lpac_settings[] = array(
        // 	'name'     => __( 'Test', 'map-location-picker-at-checkout-for-woocommerce' ),
        // 	'desc_tip' => __( 'Delete all plugin settings on uninstall.', 'map-location-picker-at-checkout-for-woocommerce' ),
        // 	'id'       => 'lpac_delete_settings_on_uninstall',
        // 	'type'     => 'text',
        // 	'custom_attributes' => array(
        // 		'disabled' => 'disabled',
        // 	)
        // );
        // Default checkbox example
        // https://wordpress.stackexchange.com/questions/390270/woocommerce-settings-api-set-checkbox-checked-by-default?noredirect=1#comment567330_390270
        return apply_filters( 'woocommerce_get_settings_' . $this->id, $lpac_settings );
    }
    
    /**
     * Output the plugin's settings markup.
     *
     * @since    1.1.0
     */
    public function lpac_output_plugin_settings()
    {
        $settings = $this->lpac_create_plugin_settings_fields();
        \WC_Admin_Settings::output_fields( $settings );
        global  $current_section ;
        if ( $current_section === 'visibility_rules' ) {
            $this->output_map_visibility_rules_order();
        }
    }
    
    /**
     *  Save our settings.
     *
     */
    public function lpac_save_plugin_settings()
    {
        global  $current_section ;
        $settings = $this->lpac_create_plugin_settings_fields();
        \WC_Admin_Settings::save_fields( $settings );
        if ( $current_section ) {
            do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
        }
    }

}