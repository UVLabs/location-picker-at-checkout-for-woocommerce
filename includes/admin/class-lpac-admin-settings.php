<?php

/**
 * The admin settings of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/admin
 * @author     Uriahs Victor <info@soaringleads.com>
 */
class Lpac_Admin_Settings extends WC_Settings_Page
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
        $this->id = 'lpac_settings';
        $this->label = __( 'Location Picker at Checkout', 'lpac' );
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
            ''        => __( 'General', 'lpac' ),
            'premium' => __( 'Premium', 'lpac' ),
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
        // The default tab("General") has no section id, if it does then it's not the General tab
        // And its most likely the "Premium tab"
        
        if ( empty($current_section) ) {
            $lpac_settings[] = array(
                'name' => __( 'LPAC General Settings', 'lpac' ),
                'id'   => 'lpac',
                'type' => 'title',
                'desc' => __( 'Use the below options to change the general plugin settings.', 'lpac' ),
            );
            $plugin_enabled = get_option( 'lpac_enabled' );
            /*
             * If the option doesn't exist then this is most likely a new install, so set the checkbox to checked by default.
             * If the option already exists, then use the setting saved in the database.
             */
            
            if ( empty($plugin_enabled) ) {
                $lpac_settings[] = array(
                    'name'     => __( 'Enabled', 'lpac' ),
                    'desc'     => __( 'Yes', 'lpac' ),
                    'desc_tip' => __( 'Enable map on checkout and order details pages.', 'lpac' ),
                    'id'       => 'lpac_enabled',
                    'type'     => 'checkbox',
                    'css'      => 'max-width:80px;',
                    'value'    => 'yes',
                );
            } else {
                $lpac_settings[] = array(
                    'name'     => __( 'Enabled', 'lpac' ),
                    'desc'     => __( 'Yes', 'lpac' ),
                    'desc_tip' => __( 'Enable map on checkout and order details pages.', 'lpac' ),
                    'id'       => 'lpac_enabled',
                    'type'     => 'checkbox',
                    'css'      => 'max-width:80px;',
                );
            }
            
            $lpac_settings[] = array(
                'name'     => __( 'Google Maps API Key', 'lpac' ),
                'desc_tip' => __( 'Enter the API key from Google cloud console.', 'lpac' ),
                'desc'     => __( 'Enter the API you copied from the Google Cloud Console. <a href="https://github.com/UVLabs/location-picker-at-checkout-for-woocommerce/wiki/Getting-Your-API-Key" target="_blank">Learn More >></a>', 'lpac' ),
                'id'       => 'lpac_google_maps_api_key',
                'type'     => 'text',
                'css'      => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'Default Coordinates', 'lpac' ),
                'desc_tip'    => __( 'Enter the default latitude and logitude that will be fetched every time the map loads.', 'lpac' ),
                'desc'        => __( 'Enter the default latitude and logitude that will be fetched every time the map loads. You can find the coordinates for a location <a href="https://www.latlong.net/" target="_blank">here >></a>. Be sure to include the comma when adding your coordinates above.', 'lpac' ),
                'id'          => 'lpac_map_starting_coordinates',
                'placeholder' => '14.024519,-60.974876',
                'type'        => 'text',
                'css'         => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'Default Zoom', 'lpac' ),
                'desc_tip'    => __( 'Recommended number is 16.', 'lpac' ),
                'desc'        => __( 'Enter the default zoom that will be used every time the map loads.', 'lpac' ),
                'id'          => 'lpac_general_map_zoom_level',
                'placeholder' => '16',
                'default'     => 3,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Enable Clickable Icons', 'lpac' ),
                'desc_tip' => __( 'Should customers be able to click on icons of different locations that appear on Google Maps? Recommended setting: Disabled', 'lpac' ),
                'desc'     => __( 'Yes', 'lpac' ),
                'id'       => 'lpac_allow_clicking_on_map_icons',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'Background Color (HEX)', 'lpac' ),
                'desc'        => __( 'Background color of map container (visible while map is loading).', 'lpac' ),
                'id'          => 'lpac_map_background_color',
                'type'        => 'color',
                'placeholder' => '#eeeeee',
                'default'     => '#EEEEEE',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Where Should the Map Appear on the Checkout Page?', 'lpac' ),
                'desc_tip' => __( 'This option displays a map view on the order received page after an order has been placed by a customer.', 'lpac' ),
                'id'       => 'lpac_checkout_map_orientation',
                'type'     => 'select',
                'options'  => array(
                'woocommerce_before_checkout_shipping_form' => __( 'Shipping Address Area - Top', 'lpac' ),
                'woocommerce_after_checkout_shipping_form'  => __( 'Shipping Address Area - Bottom (recommended)', 'lpac' ),
                'woocommerce_before_checkout_billing_form'  => __( 'Billing Address Area - Top', 'lpac' ),
                'woocommerce_after_checkout_billing_form'   => __( 'Billing Address Area - Bottom', 'lpac' ),
            ),
                'css'      => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Autofill Billing fields', 'lpac' ),
                'desc_tip' => __( 'Should the billing fields be automatically populated with information pulled from the location?', 'lpac' ),
                'desc'     => __( 'Yes', 'lpac' ),
                'id'       => 'lpac_autofill_billing_fields',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'Checkout Page Map Height (in px)', 'lpac' ),
                'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
                'id'          => 'lpac_checkout_page_map_height',
                'placeholder' => '400',
                'default'     => 400,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'Checkout Page Map Width (in %)', 'lpac' ),
                'desc_tip'    => __( 'Enter the width of map you\'d like.', 'lpac' ),
                'id'          => 'lpac_checkout_page_map_width',
                'placeholder' => '100',
                'default'     => 100,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Show Map on the Order Received Page', 'lpac' ),
                'desc_tip' => __( 'This option displays a map view on the order received page after an order has been placed by a customer.', 'lpac' ),
                'id'       => 'lpac_display_map_on_order_received_page',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Enable', 'lpac' ),
            );
            $lpac_settings[] = array(
                'name'        => __( 'Order Received Page Map Height (in px)', 'lpac' ),
                'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
                'id'          => 'lpac_order_received_page_map_height',
                'placeholder' => '400',
                'default'     => 400,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'Order Received Page Map Width (in px)', 'lpac' ),
                'desc_tip'    => __( 'Enter the width of map you\'d like.', 'lpac' ),
                'id'          => 'lpac_order_received_page_map_width',
                'placeholder' => '100',
                'default'     => 100,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Show Map on View Order Page', 'lpac' ),
                'desc_tip' => __( 'This option displays a map view on the order details page in the customer account.', 'lpac' ),
                'id'       => 'lpac_display_map_on_view_order_page',
                'type'     => 'checkbox',
                'css'      => 'min-width:300px;',
                'desc'     => __( 'Enable', 'lpac' ),
            );
            $lpac_settings[] = array(
                'name'        => __( 'View Order Page Map Height (in px)', 'lpac' ),
                'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
                'id'          => 'lpac_view_order_page_map_height',
                'placeholder' => '400',
                'default'     => 400,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'        => __( 'View Order Page Map Width (in px)', 'lpac' ),
                'desc_tip'    => __( 'Enter the height of map you\'d like.', 'lpac' ),
                'id'          => 'lpac_view_order_page_map_width',
                'placeholder' => '100',
                'default'     => 100,
                'type'        => 'number',
                'css'         => 'max-width:80px;',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Add Map Link to Order Emails?', 'lpac' ),
                'desc_tip' => __( 'Add either a Button or QR Code that links to Google Maps to the order emails.', 'lpac' ),
                'id'       => 'lpac_enable_delivery_map_link_in_email',
                'desc'     => __( 'Yes', 'lpac' ),
                'type'     => 'checkbox',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Link Type', 'lpac' ),
                'desc_tip' => __( 'Add either a button to Google Maps or a QR Code to the order emails.', 'lpac' ),
                'desc'     => __( 'QR Codes are saved to your uploads directory at: <code>/wp-content/uploads/lpac-qr-codes/year/month/day/order_id.jpg</code>', 'lpac' ),
                'id'       => 'lpac_email_delivery_map_link_type',
                'type'     => 'select',
                'options'  => array(
                'button'  => __( 'Button', 'lpac' ),
                'qr_code' => __( 'QR Code', 'lpac' ),
            ),
                'css'      => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'    => __( 'Link Location', 'lpac' ),
                'id'      => 'lpac_email_delivery_map_link_location',
                'type'    => 'select',
                'options' => array(
                'woocommerce_email_before_order_table' => __( 'Before Order Table', 'lpac' ),
                'woocommerce_email_customer_details'   => __( 'Before Customer Details', 'lpac' ),
            ),
                'css'     => 'min-width:300px;',
            );
            $lpac_settings[] = array(
                'name'    => __( 'Select Emails', 'lpac' ),
                'class'   => 'select2',
                'desc'    => __( 'Select the Emails you\'d like this setting to take effect on.', 'lpac' ),
                'id'      => 'lpac_email_delivery_map_emails',
                'type'    => 'multiselect',
                'options' => array(
                'new_order'                => __( 'New Order', 'lpac' ),
                'customer_on_hold_order'   => __( 'Order on Hold', 'lpac' ),
                'customer_note'            => __( 'Customer Note', 'lpac' ),
                'customer_completed_order' => __( 'Completed Order', 'lpac' ),
                'customer_invoice'         => __( 'Customer Invoice', 'lpac' ),
            ),
                'css'     => 'min-width:300px;height: 100px',
            );
            $lpac_settings[] = array(
                'name'     => __( 'Housekeeping', 'lpac' ),
                'desc_tip' => __( 'Delete all plugin settings on uninstall.', 'lpac' ),
                'id'       => 'lpac_delete_settings_on_uninstall',
                'type'     => 'checkbox',
            );
            // Custom attributes example
            // https://woocommerce.github.io/code-reference/files/woocommerce-includes-admin-wc-meta-box-functions.html#source-view.146
            // $lpac_settings[] = array(
            // 	'name'     => __( 'Test', 'lpac' ),
            // 	'desc_tip' => __( 'Delete all plugin settings on uninstall.', 'lpac' ),
            // 	'id'       => 'lpac_delete_settings_on_uninstall',
            // 	'type'     => 'text',
            // 	'custom_attributes' => array(
            // 		'disabled' => 'disabled',
            // 	)
            // );
            // TODO default checkbox
            // https://wordpress.stackexchange.com/questions/390270/woocommerce-settings-api-set-checkbox-checked-by-default?noredirect=1#comment567330_390270
            // $lpac_settings[] = array(
            // 	'name'     => __( 'test', 'lpac' ),
            // 	// 'cbvalue' => 'yes',
            // 	// 'value' => 'yes',
            // 	'desc_tip' => __( 'Should customers be able to click on icons of different locations that appear on Google Maps? Recommended setting: Disabled', 'lpac' ),
            // 	'desc'     => __( 'Yes', 'lpac' ),
            // 	'id'       => 'lpac_test_option',
            // 	'type'     => 'checkbox',
            // 	'css'      => 'min-width:300px;',
            // );
            $lpac_settings[] = array(
                'type' => 'sectionend',
                'id'   => 'lpac_general_settings_section_end',
            );
        }
        
        if ( $current_section === 'premium' ) {
        }
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
        WC_Admin_Settings::output_fields( $settings );
    }
    
    /**
     *  Save our settings.
     *
     */
    public function lpac_save_plugin_settings()
    {
        global  $current_section ;
        $settings = $this->lpac_create_plugin_settings_fields();
        WC_Admin_Settings::save_fields( $settings );
        if ( $current_section ) {
            do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
        }
    }

}
new Lpac_Admin_Settings();