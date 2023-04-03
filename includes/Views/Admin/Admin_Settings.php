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
namespace Lpac\Views\Admin;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

use  Lpac\Helpers\Functions as Functions_Helper ;
use  Lpac\Controllers\Map_Visibility_Controller ;
use  Lpac\Views\Admin\RegionList ;
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
     * Learn more text string.
     *
     * @var string
     */
    private static  $learn_more ;
    /**
     * PRO text string.
     *
     * @var string
     */
    private  $pro_label ;
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
        /* translators: 1: Dashicons outbound link icon */
        self::$learn_more = sprintf( __( 'Learn More %s', 'map-location-picker-at-checkout-for-woocommerce' ), '<span style="text-decoration: none" class="dashicons dashicons-external"></span>' );
        $this->pro_label = ( lpac_fs()->is_not_paying() ? ' (' . esc_html__( 'PRO', 'map-location-picker-at-checkout-for-woocommerce' ) . ')' : '' );
        /**
         *  Define all hooks instead of inheriting from parent
         */
        // parent::__construct();
        // Add the tab to the tabs array
        // This method is located in parent class
        add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 99 );
        // Add sections to our custom tab
        add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_settings_sections' ) );
        // Output our different settings
        add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output_plugin_settings' ) );
        // Save our settings
        add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save_plugin_settings' ) );
    }
    
    /**
     * Create plugin settings sections.
     *
     * @return array $sections The sections for the custom tab.
     * @since    1.1.0
     */
    public function create_plugin_settings_sections()
    {
        $sections = array(
            'general'          => __( 'General', 'map-location-picker-at-checkout-for-woocommerce' ),
            'display'          => __( 'Display', 'map-location-picker-at-checkout-for-woocommerce' ),
            'store_locations'  => __( 'Store Locations', 'map-location-picker-at-checkout-for-woocommerce' ),
            'visibility_rules' => __( 'Visibility Rules', 'map-location-picker-at-checkout-for-woocommerce' ),
            'shipping'         => __( 'Shipping', 'map-location-picker-at-checkout-for-woocommerce' ),
            'export'           => __( 'Export', 'map-location-picker-at-checkout-for-woocommerce' ),
            'tools'            => __( 'Tools', 'map-location-picker-at-checkout-for-woocommerce' ),
            'more'             => __( 'More', 'map-location-picker-at-checkout-for-woocommerce' ) . '!',
        );
        return apply_filters( 'woocommerce_get_sections_' . LPAC_PLUGIN_NAME, $sections );
    }
    
    /**
     * Output the settings sections markup.
     *
     * @since    1.1.0
     */
    public function output_settings_sections()
    {
        global  $current_section ;
        $sections = $this->create_plugin_settings_sections();
        if ( empty($sections) || 1 === sizeof( $sections ) ) {
            return;
        }
        echo  '<ul id="lpac-submenu" class="subsubsub">' ;
        $array_keys = array_keys( $sections );
        foreach ( $sections as $id => $label ) {
            echo  '<li><a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) ) . '" class="' . (( $current_section == $id ? 'current' : '' )) . '">' . esc_html( $label ) . '</a> ' . (( end( $array_keys ) == $id ? '' : '|' )) . ' </li>' ;
        }
        echo  '</ul><br class="clear" />' ;
    }
    
    /**
     * Create a banner at the top of the page with some information for user..
     *
     * @since    1.2.0
     * @return mixed $markup The markup for the banner.
     */
    public static function create_plugin_settings_banner()
    {
        $here = __( 'HERE', 'map-location-picker-at-checkout-for-woocommerce' ) . '<strong><span style="text-decoration: none" class="dashicons dashicons-external"></span></strong>';
        
        if ( empty(get_option( 'lpac_google_maps_api_key', '' )) ) {
            $no_api_key = '❌ &nbsp;&nbsp;' . __( 'You need an API Key to use Google Maps. Please see this document for how to get it ', 'map-location-picker-at-checkout-for-woocommerce' );
            $no_api_key .= "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/?utm_source=banner&utm_medium=lpacdashboard&utm_campaign=freedocs' target='_blank'>{$here}</a>";
        } else {
            $no_api_key = '';
        }
        
        $title = esc_html__( "Use the Options Below to Change the Plugin's Settings", 'map-location-picker-at-checkout-for-woocommerce' );
        $issues = esc_html__( 'If you encounter any issues then please open a support ticket', 'map-location-picker-at-checkout-for-woocommerce' );
        $issues .= "&nbsp;<a href='https://wordpress.org/support/plugin/map-location-picker-at-checkout-for-woocommerce/' target='_blank'>{$here}</a>";
        $documentation = esc_html__( 'Read the documentation', 'map-location-picker-at-checkout-for-woocommerce' );
        $documentation .= "&nbsp;<a href='https://lpacwp.com/docs/?utm_source=banner&utm_medium=lpacdashboard&utm_campaign=docshome' target='_blank'>{$here}</a>";
        $pro_demo = esc_html__( 'Try out the PRO version', 'map-location-picker-at-checkout-for-woocommerce' );
        $pro_demo .= "&nbsp;<a href='https://app.instawp.io/launch?t=lpac' target='_blank'>{$here}</a>";
        $dps_upsell = sprintf( esc_html__( 'Let customers choose their date and time for an order. Check out our %1$sDelivery & Pickup Scheduling Plugin%2$s.', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>' );
        $dps_upsell .= "&nbsp;<a href='https://dpswp.com' target='_blank'>{$here}</a>";
        $printus_upsell = sprintf( esc_html__( 'Print Receipts, Invoices or Package labels as soon as an order happens. Check out %1$sPrintus - Cloud Printing Plugin for WooCommerce%2$s.', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>' );
        $printus_upsell .= "&nbsp;<a href='https://printus.cloud' target='_blank'>{$here}</a>";
        $upsells = array( $dps_upsell, $printus_upsell );
        $rand_upsell_key = array_rand( $upsells );
        $translate_plugin = esc_html__( 'Plugin settings not in your Language? Help translate it', 'map-location-picker-at-checkout-for-woocommerce' );
        $translate_plugin .= "&nbsp;<a href='hhttps://translate.wordpress.org/projects/wp-plugins/map-location-picker-at-checkout-for-woocommerce/' target='_blank'>{$here}</a>";
        $markup = <<<HTML
\t\t<div class="lpac-banner">
\t\t<div style="text-align: left">
\t\t<h2>{$title}</h2>
\t\t<p>{$no_api_key}</p>
\t\t<p>📖 &nbsp;&nbsp;{$documentation}</p>
\t\t<p>🌟 &nbsp;&nbsp;{$pro_demo}</p>
\t\t<p>🌟 &nbsp;&nbsp;{$upsells[$rand_upsell_key]}</p>
\t\t<p>🌐 &nbsp;&nbsp;{$translate_plugin}</p>
\t\t<p>✋🏾 &nbsp;&nbsp;{$issues}</p>
\t\t</div>
\t\t</div>
HTML;
        return $markup;
    }
    
    /**
     * House all the plugin settings to do with General.
     *
     * @return array
     */
    public function create_general_setting_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC General Settings', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_general_settings',
            'type' => 'title',
            'desc' => self::create_plugin_settings_banner(),
        );
        $plugin_enabled = get_option( 'lpac_enabled' );
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
            'name'              => __( 'Google Maps API Key', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'Enter the API key from Google cloud console.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Enter the API key you copied from the Google Cloud Console. Without the Google Maps API key the plugin will not work. <a href="https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs" target="blank">Learn More <span style="text-decoration: none" class="dashicons dashicons-external"></span></a>', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_google_maps_api_key',
            'placeholder'       => 'AIzaSyD8seU-lym435g...',
            'type'              => ( LPAC_DEBUG ? 'text' : 'password' ),
            'css'               => 'min-width:300px;',
            'custom_attributes' => array(
            'autocomplete' => 'new-password',
        ),
        );
        $lpac_settings[] = array(
            'name'  => __( 'Map Behaviour', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class' => 'dashicons-before dashicons-admin-generic',
            'type'  => 'hr',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Map Region', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Select your country.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => sprintf( __( 'It\'s fine if your country is not in this list. This feature helps better align the map with your country, but everything will still work fine without it.', 'map-location-picker-at-checkout-for-woocommerce' ) ),
            'id'       => 'lpac_google_map_region',
            'type'     => 'select',
            'options'  => RegionList::createRegionList(),
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Detect Customer Location on Checkout Page Load', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Enabling this option will have the plugin immediately try to detect the customer location when the checkout page loads. NOTE: This can negatively impact customer experiences. Think carefully before enabling this option. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#detect-customer-location-on-checkout-page-load' target='blank'>" . self::$learn_more . '</a>' ),
            'id'       => 'lpac_auto_detect_location',
            'type'     => 'checkbox',
            'css'      => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Force Use of Map', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Prevent the customer from checking out until they select a location on the map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#force-use-of-map' target='blank'>" . self::$learn_more . '</a>' ),
            'id'       => 'lpac_force_map_use',
            'type'     => 'checkbox',
            'css'      => 'max-width:80px;',
            'default'  => 'yes',
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
            'desc_tip' => sprintf( __( 'If you enable this option the plugin will attempt to remove the Plus Code that shows infront addresses returned by Google Maps. Example <code>TMWXH+CW</code>. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#remove-plus-code-from-address' target='blank'>" . self::$learn_more . '</a>' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_remove_address_plus_code',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'default'  => 'yes',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Dissect Street Address', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'By default, the plugin will only show the street address inside the Street Address 1 field. Turn off this option if you want the full address to be entered instead. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#remove-plus-code-from-address' target='blank'>" . self::$learn_more . '</a>' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'kikote_dissect_customer_address',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'default'  => 'yes',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Show Map on the Order Received Page', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'This option displays a map view on the order received page after an order has been placed by a customer. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#show-map-on-the-order-received-page' target='blank'> " . self::$learn_more . ' </a>' ),
            'id'       => 'lpac_display_map_on_order_received_page',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'default'  => 'yes',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Show Map on View Order Page', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'This option displays a map view on the order details page in the customer account. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#show-map-on-view-order-page' target='blank'> " . self::$learn_more . ' </a>' ),
            'id'       => 'lpac_display_map_on_view_order_page',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'default'  => 'yes',
        );
        $lpac_settings[] = array(
            'name'  => __( 'Emails', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class' => 'dashicons-before dashicons-admin-generic',
            'type'  => 'hr',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Add Map Link to Order Emails?', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Add either a Button, QR Code or Static Map image that links to Google Maps to the order emails. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#add-map-link-to-order-emails' target='blank'>" . self::$learn_more . '</a>' ),
            'id'       => 'lpac_enable_delivery_map_link_in_email',
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'     => 'checkbox',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Link Type', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Add either a button to Google Maps, a QR Code or Static Map to the order emails.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => sprintf( __( 'The Static Map option requires enabling a special Google Maps API. Please read the following doc to %1$s %2$s QR Codes are saved to your uploads directory at: <code>/wp-content/uploads/lpac/qr-codes/order_id.jpg</code>', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/enabling-google-static-map-api/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs' target='blank'>" . self::$learn_more . '</a>', '<br/>' ),
            'id'       => 'lpac_email_delivery_map_link_type',
            'type'     => 'select',
            'options'  => array(
            'button'     => __( 'Button', 'map-location-picker-at-checkout-for-woocommerce' ),
            'qr_code'    => __( 'QR Code', 'map-location-picker-at-checkout-for-woocommerce' ),
            'static_map' => __( 'Static Map', 'map-location-picker-at-checkout-for-woocommerce' ),
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
            'new_order'                 => __( 'New Order (Admin)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_processing_order' => __( 'Processing Order', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_on_hold_order'    => __( 'Order on Hold', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_note'             => __( 'Customer Note', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_completed_order'  => __( 'Completed Order', 'map-location-picker-at-checkout-for-woocommerce' ),
            'customer_invoice'          => __( 'Customer Invoice', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'     => 'min-width:300px;height: 100px',
        );
        $lpac_settings[] = array(
            'name'  => __( 'Directions to Customer', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class' => 'dashicons-before dashicons-admin-generic',
            'type'  => 'hr',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Provider', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_map_directions_provider',
            'type'    => 'select',
            'desc'    => __( 'Select whether to open the directions to customer locations in Google Maps or Waze. This option only applies to admin related directions links.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'options' => array(
            'google_maps' => __( 'Google Maps', 'map-location-picker-at-checkout-for-woocommerce' ),
            'waze'        => __( 'Waze', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'     => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'  => __( 'Places Autocomplete', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class' => 'dashicons-before dashicons-admin-generic',
            'type'  => 'hr',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Enable Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Allows customers to begin typing an address and receive suggestions from Google. NOTE: This is not as reliable as allowing customers to select their location on the map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs' target='blank'>" . self::$learn_more . '</a>' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_enable_places_autocomplete',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Force use of Autocomplete feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => sprintf( __( 'Prevent customers from checking out unless they select a location from the google places drop. You should turn this option on if you make use of any of the distance shipping workflows. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs#force-use-of-autocomplete-feature' target='blank'>" . self::$learn_more . '</a>' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_force_places_autocomplete',
            'type'     => 'checkbox',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Notice text', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'    => __( 'Enter the notice message that shows when a customer tries to checkout without making use of the places autocomplete feature.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_force_places_autocomplete_notice_text',
            'default' => __( 'Please select a location from the list of suggested ones that appear while typing your address.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'    => 'textarea',
            'css'     => 'min-width:700px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Allowed Places Autocomplete Fields', 'map-location-picker-at-checkout-for-woocommerce' ),
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
        return $lpac_settings;
    }
    
    /**
     * Create dummy general settings.
     *
     * @return array
     */
    private function create_dummy_general_settings_fields()
    {
        $dummy_general_pro_settings = array();
        $dummy_general_pro_settings[] = array(
            'name'              => __( 'Show A Searchbox Inside the Map', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (PRO)',
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => sprintf( __( 'Enabling this option will add a searchbox inside the checkout page map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=prodocs#show-a-searchbox-inside-the-map' target='_blank'>" . self::$learn_more . '</a>' ),
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $dummy_general_pro_settings[] = array(
            'name'              => __( 'Restrict Places Autocomplete Countries', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (PRO)',
            'desc'              => sprintf( __( 'Select the countries you would like addresses to be pulled from when using the Places Autocomplete feature. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/google-cloud-console/places-autocomplete-feature/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=prodocs#restrict-places-autocomplete-countries' target='blank'>" . self::$learn_more . '</a>' ),
            'desc_tip'          => __( 'Use this feature if you only want to show address results from a specific country or countries.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'multiselect',
            'options'           => array(),
            'css'               => 'height:40px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $dummy_general_pro_settings[] = array(
            'name'              => __( 'Places Autocomplete Type', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (PRO)',
            'desc'              => __( 'Select the type of address you would like the Places Autocomplete API to return.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'select',
            'options'           => array(
            'address' => __( 'Precise Address', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'               => 'max-width:180px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $dummy_general_pro_settings[] = array(
            'name'  => __( 'Saved Addresses', 'map-location-picker-at-checkout-for-woocommerce' ) . '(PRO)',
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'desc'  => sprintf( __( 'Allow customers to save different addresses for later use. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/general-settings/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=prodocs#saved-addresses' target='blank'>" . self::$learn_more . '</a>' ),
            'type'  => 'hr',
            'id'    => 'test',
        );
        $dummy_general_pro_settings[] = array(
            'name'              => __( 'Enable', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        return $dummy_general_pro_settings;
    }
    
    /**
     * House all the plugin settings to do with Display.
     *
     * @return array
     */
    private function create_display_settings_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC Display Settings', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_display_settings',
            'type' => 'title',
            'desc' => self::create_plugin_settings_banner(),
        );
        $lpac_settings[] = array(
            'name'     => __( 'Where Should the Map Appear on the Checkout Page?', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Choose where you would like the map to appear on the checkout page.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_checkout_map_orientation',
            'type'     => 'select',
            'options'  => $this->get_possible_map_locations(),
            'default'  => 'woocommerce_checkout_before_customer_details',
            'css'      => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Map Instructions Text', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'    => __( 'Alter the instructions that appear below the map.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_map_instructions_text',
            'default' => __( 'Click the "Detect Current Location" button then move the red marker to your desired shipping address.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'    => 'textarea',
            'css'     => 'min-width:700px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Default Coordinates', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the default latitude and longitude that will be fetched every time the map loads.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'        => sprintf( __( 'Enter the default latitude and longitude that will be fetched every time the map loads. You can find the coordinates for a location %1$sHere >>%2$s. Be sure to include the comma when adding your coordinates above.', 'map-location-picker-at-checkout-for-woocommerce' ), '<a href="https://www.latlong.net/" target="_blank">', '</a>' ),
            'id'          => 'lpac_map_starting_coordinates',
            'placeholder' => '14.024519,-60.974876',
            'type'        => 'text',
            'css'         => 'min-width:300px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Default Zoom', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Recommended number is 16.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Enter the default zoom that will be used every time the map loads.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_general_map_zoom_level',
            'default'  => 14,
            'type'     => 'number',
            'css'      => 'max-width:80px;',
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
            'name'        => __( 'Checkout Page Map Height (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_checkout_page_map_height',
            'placeholder' => '400',
            'default'     => 400,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
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
            'name'        => __( 'Order Received Page Map Height (in px)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_order_received_page_map_height',
            'placeholder' => '400',
            'default'     => 400,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Order Received Page Map Width (in %)', 'map-location-picker-at-checkout-for-woocommerce' ),
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
            'name'        => __( 'View Order Page Map Width (in %)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'    => __( 'Enter the height of map you\'d like.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_view_order_page_map_width',
            'placeholder' => '100',
            'default'     => 100,
            'type'        => 'number',
            'css'         => 'max-width:80px;',
        );
        $lpac_settings[] = array(
            'name'    => __( 'Checkout Page Default Map Type', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'      => 'lpac_checkout_page_map_default_type',
            'type'    => 'select',
            'options' => array(
            'roadmap'   => __( 'Road Map', 'map-location-picker-at-checkout-for-woocommerce' ),
            'satellite' => __( 'Satellite', 'map-location-picker-at-checkout-for-woocommerce' ),
            'hybrid'    => __( 'Hybrid', 'map-location-picker-at-checkout-for-woocommerce' ),
            'terrain'   => __( 'Terrain', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
        );
        $lpac_settings[] = array(
            'name'    => __( 'Disable Map Controls', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class'   => 'wc-enhanced-select',
            'id'      => 'kikote_disabled_map_controls',
            'type'    => 'multiselect',
            'options' => array(
            'zoom'       => __( 'Zoom Button', 'map-location-picker-at-checkout-for-woocommerce' ),
            'maptype'    => __( 'Map Type Buttons', 'map-location-picker-at-checkout-for-woocommerce' ),
            'fullscreen' => __( 'Fullscreen Button', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
        );
        return $lpac_settings;
    }
    
    /**
     * House all the plugin settings to do with Store Locations.
     *
     * @return array
     */
    private function create_store_locations_settings_fields()
    {
        $lpac_settings[] = array(
            'name' => __( 'LPAC Store Locations', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_store_location_settings',
            'type' => 'title',
            'desc' => self::create_plugin_settings_banner(),
        );
        $dashicon = ( lpac_fs()->is_not_paying() ? "<span class='dashicons-before dashicons-lock'></span>" : '' );
        $lpac_settings[] = array(
            'name'      => __( 'Note', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'        => 'lpac_cost_by_store_location_delivery_prices_row_id',
            'text'      => __( 'Creating store locations is optional if you only have one store location.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'      => 'info_text',
            'is_option' => false,
        );
        $lpac_settings[] = array(
            'name'                   => __( 'Store Locations', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'                   => sprintf(
            __( 'Create your different store locations. A "store" in this context simply means the locations where you do business. Separate latitude and longitude coordinates with a comma. You can use a website like %1$slatlong.net%2$s to get the GPS coordinates for an address. %3$s', 'map-location-picker-at-checkout-for-woocommerce' ),
            "<a href='https://latlong.net' target='_blank'>",
            "<span style='text-decoration: none' class='dashicons dashicons-external'></span></a>",
            "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/store-locations/?utm_source=storelocationstab&utm_medium=lpacdashboard&utm_campaign=freedocs' target='blank'>" . self::$learn_more . '</a>'
        ),
            'id'                     => 'lpac_store_locations',
            'type'                   => 'repeater',
            'current_saved_settings' => get_option( 'lpac_store_locations' ),
            'entity_name'            => __( 'location', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id_field'               => 'store_location_id',
            'table_columns'          => array(
            'store_name_text'    => array(
            'name'        => __( 'Name', 'map-location-picker-at-checkout-for-woocommerce' ),
            'readonly'    => false,
            'placeholder' => __( 'Enter a store name', 'map-location-picker-at-checkout-for-woocommerce' ),
            'required'    => true,
        ),
            'store_cords_text'   => array(
            'name'        => __( 'Coordinates', 'map-location-picker-at-checkout-for-woocommerce' ),
            'readonly'    => false,
            'placeholder' => '13.856098,-61.057016',
            'required'    => true,
        ),
            'store_address_text' => array(
            'name'        => __( 'Address', 'map-location-picker-at-checkout-for-woocommerce' ),
            'readonly'    => false,
            'placeholder' => '#9 LPAC Street',
        ),
            'store_icon_text'    => array(
            'name'        => $dashicon . __( 'Icon URL', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'readonly'    => ( lpac_fs()->is_not_paying() ? true : false ),
            'placeholder' => 'https://example.com/wp-content/.../icon.png',
        ),
        ),
        );
        $lpac_settings[] = array(
            'name'     => __( 'Show On Map', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Should we display your store locations as pins on the checkout page map?', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_show_store_locations_on_map',
            'type'     => 'checkbox',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Display Store Selector on Checkout Page', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Turning on this option adds a store selector dropdown field in your checkout page. The selected store would show on the order details for customers and on the admin order page.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_enable_store_location_selector',
            'type'     => 'checkbox',
        );
        $lpac_settings[] = array(
            'name'        => __( 'Store Selector Label', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'        => __( 'Enter the label you would like to use for the Store Selector field in the checkout page.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'          => 'lpac_store_select_label',
            'placeholder' => __( 'Deliver from', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'        => 'text',
            'default'     => __( 'Deliver from', 'map-location-picker-at-checkout-for-woocommerce' ),
            'css'         => 'max-width:200px;',
        );
        $lpac_settings[] = array(
            'name'     => __( 'Show Selected Store in Order Emails', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'     => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Checking this option will add the Store name and address inside the WooCommerce order emails.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_show_selected_store_in_emails',
            'type'     => 'checkbox',
            'default'  => 'yes',
            'css'      => 'max-width:200px;',
        );
        $lpac_settings[] = array(
            'name'      => __( 'Shortcode', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'        => 'lpac_store_selector Shortcode',
            'text'      => sprintf(
            esc_html__( 'You can use the shortcode: %1$s[kikote_store_selector]%2$s anywhere on your website to allow customers (including guests) to set their preferred store to order from. The selected store will automatically be chosen at checkout.  %3$s', 'map-location-picker-at-checkout-for-woocommerce' ),
            '<code>',
            '</code>',
            '<a href="https://lpacwp.com/docs/getting-started/plugin-settings/store-locations/?utm_source=storelocationstab&utm_medium=lpacdashboard&utm_campaign=freedocs#shortcode" target="_blank">' . self::$learn_more . '</a>'
        ),
            'type'      => 'info_text',
            'is_option' => false,
        );
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_store_location_settings_section_end',
        );
        return $lpac_settings;
    }
    
    /**
     * Create dummy Display Pro fields.
     *
     * @return array
     */
    private function create_dummy_display_settings_fields()
    {
        $lpac_dummy_display_pro_settings[] = array(
            'name'  => __( 'Map ID', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'type'  => 'hr',
            'desc'  => sprintf( __( 'Set the Map ID for the respective Maps. You can create a custom map for each entry. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/display-settings/?utm_source=displaytab&utm_medium=lpacdashboard&utm_campaign=prodocs#map-id' target='blank'>" . self::$learn_more . '</a>' ),
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'              => __( 'Checkout Page Map ID', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'The Map ID to use for your Checkout page for styling.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => 'cfceab16...',
            'type'              => 'text',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'              => __( 'Order Received Page Map ID', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'The Map ID to use for your "Order Received" page for styling.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => 'cfceab16...',
            'type'              => 'text',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'              => __( 'View Order Page Map ID', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'The Map ID to use for your "View Order" page for styling.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => 'cfceab16...',
            'type'              => 'text',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'              => __( 'Admin Dashboard View Order Page Map ID', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'The Map ID to use for your the "View Order" page inside the WordPress admin Dashboard.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => 'cfceab16...',
            'type'              => 'text',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'  => __( 'Marker Icon', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'desc'  => sprintf( __( 'Set a custom icon to be used for the main map marker; the marker that customers move around on the map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/display-settings/?utm_source=displaytab&utm_medium=lpacdashboard&utm_campaign=prodocs#marker-icon' target='blank'>" . self::$learn_more . '</a>' ),
            'type'  => 'hr',
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'              => __( 'Link to Icon', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'The icon to use as the map marker.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Enter the URL to the icon that should be used as the custom map marker.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'url',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_display_pro_settings[] = array(
            'name'              => __( 'Marker Anchor Points', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'The anchor point for the marker in X,Y values. Used to show customer where exactly they\'re moving the marker to. The X value is usually half of the image width, the Y is usually the height of the image + 3. Be sure to test the map marker after setting these values to ensure the anchor works well.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => '15, 33',
            'type'              => 'text',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        return $lpac_dummy_display_pro_settings;
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
            'desc' => self::create_plugin_settings_banner(),
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
            'desc'    => sprintf( __( 'Hide the map when any of these shipping methods are chosen by the user. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/visibility-rules/?utm_source=visibilityrulestab&utm_medium=lpacdashboard&utm_campaign=freedocs#hide-map-for-shipping-methods' target='blank'>" . self::$learn_more . '</a>' ),
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
        if ( lpac_fs()->is_not_paying() ) {
            $lpac_settings = $this->create_dummy_visibility_settings_fields( $lpac_settings );
        }
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_map_visibility_settings_section_end',
        );
        return $lpac_settings;
    }
    
    /**
     * Create Dummy Visibility Rules Pro fields.
     *
     * @param array $lpac_visibility_settings An array of Live fields to merge the dummy ones into.
     * @return array
     */
    private function create_dummy_visibility_settings_fields( array $lpac_visibility_settings )
    {
        $lpac_dummy_visibility_pro_settings = array();
        $lpac_dummy_visibility_pro_settings[] = array(
            'name'  => __( 'Get More With PRO', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'type'  => 'hr',
            'desc'  => sprintf( __( 'The Shipping Zones, Minimum Cart Subtotal and Maximum Cart Subtotal features are available in the PRO version. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/pricing/?utm_source=visibilityrulestab&utm_medium=lpacdashboard&utm_campaign=proupsell' target='_blank'>" . self::$learn_more . '</a>' ),
        );
        $lpac_dummy_visibility_pro_settings[] = array(
            'name'              => __( 'Shipping Zones', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class'             => 'wc-enhanced-select',
            'desc'              => sprintf( __( 'Select the Shipping Zones. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/visibility-rules/?utm_source=visibilityrulestab&utm_medium=lpacdashboard&utm_campaign=prodocs#shipping-zones-pro-feature' target='blank'>" . self::$learn_more . '</a>' ),
            'type'              => 'multiselect',
            'options'           => array(),
            'css'               => 'height:40px',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_visibility_pro_settings[] = array(
            'name'              => __( 'Show or Hide', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => sprintf(
            /* translators: 1: Line break HTML 2: opening strong tag 3: closing strong tag*/
            __( 'Should the map be shown or hidden if the order falls within above selected shipping zones? %1$s%1$s Selecting %2$sShow%3$s will display the map %2$sONLY IF%3$s the customer order falls inside the shipping zones selected above. %1$s Selecting %2$sHide%3$s will display the map only if the customer order %2$sDOES NOT%3$s fall inside the shipping zones selected above.', 'map-location-picker-at-checkout-for-woocommerce' ),
            '<br>',
            '<strong>',
            '</strong>'
        ),
            'type'              => 'radio',
            'options'           => array(
            'show' => __( 'Show', 'map-location-picker-at-checkout-for-woocommerce' ),
            'hide' => __( 'Hide', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_visibility_pro_settings[] = array(
            'name'              => __( 'Minimum Cart Subtotal', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'The minimum amount the cart total should be before showing the checkout page map. NOTE: Coupons and Shipping Cost are not taken into account when calculating the cart subtotal.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'text',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_visibility_pro_settings[] = array(
            'name'              => __( 'Maximum Cart Subtotal', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'The maximum amount the cart total can be before hiding the checkout page map. NOTE: Coupons and Shipping Cost are not taken into account when calculating the cart subtotal.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'text',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $lpac_dummy_visibility_pro_settings[] = array(
            'name' => '',
            'type' => 'hr',
        );
        return array_merge( $lpac_visibility_settings, $lpac_dummy_visibility_pro_settings );
    }
    
    /**
     *  Create Dummy Export Pro fields.
     *
     * @return array
     */
    private function create_dummy_export_settings_fields()
    {
        $dummy_export_pro_settings = array();
        $dummy_export_pro_settings[] = array(
            'name' => __( 'Get More With PRO', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_premium',
            'type' => 'title',
        );
        $dummy_export_pro_settings[] = array(
            'type' => 'upsell_banner',
        );
        $dummy_export_pro_settings[] = array(
            'name'  => __( 'Export', 'map-location-picker-at-checkout-for-woocommerce' ),
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'type'  => 'hr',
            'desc'  => sprintf( __( 'The following features are available in the PRO version. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/pricing/?utm_source=exporttab&utm_medium=lpacdashboard&utm_campaign=proupsell' target='_blank'>" . self::$learn_more . '</a>' ),
        );
        $dummy_export_pro_settings[] = array(
            'name'      => __( 'Orders Map View', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'      => 'div',
            'class'     => 'lpac-map',
            'desc'      => __( 'The above is a dummy map. Plotted orders feature is available in the PRO version of LPAC.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'css'       => 'height: 480px;',
            'is_option' => false,
        );
        $dummy_export_pro_settings[] = array(
            'name'              => __( 'Date From', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'date',
            'desc'              => __( 'Set START date from which you want to start exporting orders.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $dummy_export_pro_settings[] = array(
            'name'              => __( 'Date To', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'date',
            'desc'              => __( 'Set END date from which you want to start exporting orders.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
        );
        $dummy_export_pro_settings[] = array(
            'name'      => __( 'Export to CSV', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'      => 'button',
            'id'        => 'lpac_export_btn',
            'link'      => LPAC_PLUGIN_ASSETS_PATH_URL . 'files/sample_export.csv',
            'value'     => __( 'Download sample export', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'      => sprintf(
            __( 'A .CSV file with Order ID, Customer Name, Customer Email, Order Date, Map Link and Phone Number will be downloaded.%1$s Files are saved to: %2$s %3$s %4$s', 'map-location-picker-at-checkout-for-woocommerce' ),
            '<br>',
            '<code>',
            '/wp-content/uploads/lpac/order-exports/',
            '</code>'
        ),
            'is_option' => false,
        );
        $dummy_export_pro_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_export_section_end',
        );
        return $dummy_export_pro_settings;
    }
    
    /**
     * Create dummy shipping setting fields to show in Lite plugin.
     *
     * @return array
     */
    private function create_dummy_shipping_setting_fields()
    {
        $lpac_dummy_pro_settings = array();
        $lpac_dummy_pro_settings[] = array(
            'name' => __( 'Get More With PRO', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_premium',
            'type' => 'title',
        );
        $lpac_dummy_pro_settings[] = array(
            'type' => 'upsell_banner',
        );
        $lpac_dummy_pro_settings[] = array(
            'name'  => __( 'Cost by Region', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'desc'  => sprintf( __( 'Use the map to draw regions and set shipping costs for customers who fall within those regions.  %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=prodocs#cost-by-region' target='_blank'>" . self::$learn_more . '</a>' ),
            'type'  => 'hr',
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Enable Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_shipping_cost_by_region_enabled',
            'type'              => 'checkbox',
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'css'               => 'height: 400px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Default Region Color (HEX)', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Default background color for drawn regions on the frontend of the website.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_shipping_regions_default_background_color',
            'type'              => 'color',
            'placeholder'       => '#ff0000',
            'default'           => '#FF0000',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'      => __( 'Draw Shipping Regions', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'      => 'div',
            'class'     => 'lpac-map',
            'desc'      => __( 'The above is a dummy map. Drawing of shipping regions is available in the PRO version of LPAC.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'css'       => 'height: 480px;',
            'is_option' => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Shipping Methods', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_shipping_regions_shipping_methods',
            'class'             => 'wc-enhanced-select',
            'desc'              => sprintf( __( 'Select the Shipping Method(s) this feature applies to. If there is a cost already set on the shipping method, then that base cost will be added to the cost set for the region. NOTE: You need to have at least ONE created Shipping Zone with Shipping Methods attached to it. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=prodocs#cost-by-region' target='_blank'>" . self::$learn_more . '</a>' ),
            'type'              => 'multiselect',
            'css'               => 'min-width:300px;height: 100px',
            'options'           => array(),
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Ship Only to Drawn Regions.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'If checked, a customer WILL NOT be able to place an order unless they choose a location that falls within any of your drawn regions. Pair this setting with the "Force Use of Map" general option for best results.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_ship_only_to_drawn_regions',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'No Shipping Method Available Text', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Enter the text that displays when a customer\'s location does not fall within any drawn shipping region.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_no_shipping_method_available_text',
            'default'           => __( 'Unfortunately, we do not currently ship to your region.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'textarea',
            'css'               => 'min-width:700px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'No Shipping Method Error', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Enter the text that displays when a customer tries to checkout without choosing a location within a drawn region.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_no_shipping_method_selected_error',
            'default'           => __( 'We do not currently ship to your selected location. Please choose a different location on the map then try placing your order again. Please contact us if you need any help.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'textarea',
            'css'               => 'min-width:700px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Show Shipping Regions on Checkout Map', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_show_shipping_regions_on_checkout_map',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Display Shipping Regions Label', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_show_shipping_regions_name_on_checkout_map',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Display Shipping Regions Cost', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_show_shipping_regions_cost_on_checkout_map',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'                          => __( 'Shipping Restrictions', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                            => 'lpac_regions_min_max_order_total',
            'row_id'                        => 'lpac_regions_min_max_order_total_row_id',
            'desc'                          => sprintf( __( 'Set the minimum and maximum order total a customer needs to have based on the region they are trying to checkout from. If a region only has a minimum order total, then simply leave the maximum order total field blank. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=prodocs#cost-by-region' target='_blank'>" . self::$learn_more . '</a>' ),
            'type'                          => 'repeater',
            'current_saved_settings'        => array(),
            'entity_name'                   => __( 'entry', 'map-location-picker-at-checkout-for-woocommerce' ),
            'table_columns'                 => array(
            'region_name_select'          => array(
            'name' => __( 'Select region', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'region_min_order_total_text' => array(
            'name' => __( 'Minimum order total', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
        ),
            'region_max_order_total_text' => array(
            'name' => __( 'Max order total', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
        ),
        ),
            'select_field_dropdown_options' => array( array(
            'name' => '',
        ) ),
            'is_option'                     => false,
            'fields_disabled'               => true,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Order Total Insufficient Notice', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => sprintf(
            __( "Enter the text for the notice that displays when a customer current order total is lower than what you have set for their detected region's %1\$sMinimum order total%2\$s. %3\$sNOTE: The minimum amount needed is automatically appended to the text on the checkout page.%4\$s", 'map-location-picker-at-checkout-for-woocommerce' ),
            '<strong>',
            '</strong>',
            '<code>',
            '</code>'
        ),
            'id'                => 'lpac_order_total_insufficient_text',
            'default'           => __( 'Order total for your selected region needs to be at least', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'textarea',
            'css'               => 'min-width:700px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Order Total Beyond Limit Notice', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => sprintf(
            __( "Enter the text for the notice that displays when a customer current order total is beyond what you have set for their detected region's %1\$sMaximum order total%2\$s. %3\$sNOTE: The maximum allowed order total is automatically appended to the text on the checkout page.%4\$s", 'map-location-picker-at-checkout-for-woocommerce' ),
            '<strong>',
            '</strong>',
            '<code>',
            '</code>'
        ),
            'id'                => 'lpac_order_total_limit_passed_text',
            'default'           => __( 'Order total for your selected region needs to be at most', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'textarea',
            'css'               => 'min-width:700px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'  => __( 'Cost by Distance', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'desc'  => sprintf( __( 'Charge customers based on the distance between your store and their location. Be sure to test this before committing to the changes. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=prodocs#cost-by-distance' target='blank'>" . self::$learn_more . '</a>' ),
            'type'  => 'hr',
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Enable Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_shipping_cost_by_region_enabled',
            'type'              => 'checkbox',
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'css'               => 'height: 400px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Distance Matrix API Key', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_matrix_api_key',
            'desc'              => __( 'This is a specific API key created just for usage of Google\'s Distance Matrix API. The key should have no referrer restrictions set on it.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => 'AIzaSyD8seU-lym435g...',
            'type'              => 'password',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Shipping Methods', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_matrix_shipping_methods',
            'class'             => 'wc-enhanced-select',
            'desc'              => sprintf( __( 'Select the Shipping Method(s) this feature applies to. If there is a cost already set on the shipping method, then that base cost will be added to the cost set for the region. NOTE: You need to have at least ONE created Shipping Zone with Shipping Methods attached to it. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=prodocs#cost-by-distance' target='_blank'>" . self::$learn_more . '</a>' ),
            'type'              => 'multiselect',
            'options'           => array(),
            'css'               => 'height:40px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Origin Coordinates', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_matrix_store_origin_cords',
            'desc'              => sprintf( __( 'Enter the coordinates of the location from which the delivery/pickup will begin. This might be the coordinates for your physical store or business. If you have multiple origin locations (example multiple stores) then enter the coordinates for the preferred one. You can find the coordinates for a location %1$sHere >>%2$s', 'map-location-picker-at-checkout-for-woocommerce' ), '<a href="https://www.latlong.net/" target="blank">', '</a>' ),
            'placeholder'       => '14.024519,-60.974876',
            'type'              => 'text',
            'css'               => 'max-width:180px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Cost per Unit', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'id'                => 'lpac_distance_matrix_cost_per_unit',
            'desc'              => __( 'Enter the price you wish to charge per Kilometer/Mile. The default store currency will be used.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => '0.50',
            'type'              => 'text',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Distance Unit', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_matrix_distance_unit',
            'desc'              => __( 'Select your preferred unit. By Default the Distance Matrix API returns values in Metric Units. If Miles is selected then Kilometers will be converted into Miles where 1 Kilometer is equivalent to 0.621371 Miles.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'select',
            'options'           => array(
            'km'   => __( 'Kilometers', 'map-location-picker-at-checkout-for-woocommerce' ),
            'mile' => __( 'Miles', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'               => 'max-width:120px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Display Cost & Unit in Shipping Label', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'Enabling this option will append the "Cost Per Unit Distance" and "Distance Unit" to the end of the Shipping Method name. E.g: Flat Rate ($1.50/Km).', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_show_distance_unit_cost_in_checkout',
            'type'              => 'checkbox',
            'css'               => 'max-width:120px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Travel Mode', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_matrix_travel_mode',
            'desc'              => __( 'Enter the travel mode you will be using. Though multiple options are provided, you might always want to use "driving" for best results.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'placeholder'       => '2.50',
            'type'              => 'select',
            'options'           => array(
            'driving'   => __( 'Driving', 'map-location-picker-at-checkout-for-woocommerce' ),
            'bicycling' => __( 'Bicycling', 'map-location-picker-at-checkout-for-woocommerce' ),
            'walking'   => __( 'Walking', 'map-location-picker-at-checkout-for-woocommerce' ),
        ),
            'css'               => 'max-width:120px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Limit Shipping Distance', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip'          => __( 'If checked, you can limit the maximum distance you will ship to. The distance will be calculated in the unit you set above.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_limit_shipping_distance',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Maximum Distance', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Set the maximum distance you will ship to. The unit used will be the same as the unit you set above.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_max_shipping_distance',
            'placeholder'       => '24',
            'type'              => 'text',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Free Shipping for Distance', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'If a customer distance falls within the range you set above, then their shipping cost will be free. The unit used will be the same as the unit you set above.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_free_shipping_max_distance',
            'placeholder'       => '24',
            'type'              => 'text',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'No Shipping Method Available Text', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Enter the text that displays when a customer\'s location is beyond your max shipping distance.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_cost_no_shipping_method_available_text',
            'default'           => __( 'Unfortunately, we do not currently ship this far.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'textarea',
            'css'               => 'min-width:700px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'No Shipping Method Error', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Enter the text that displays when a customer tries to checkout with a location that is beyond your max shipping distance.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_distance_cost_no_shipping_method_selected_error',
            'default'           => __( 'We do not currently ship to your selected location. Please choose a location closer to our main store then try placing your order again. Please contact us if you need any help.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'type'              => 'textarea',
            'css'               => 'min-width:700px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'  => __( 'Cost by Distance Range', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'type'  => 'hr',
            'id'    => 'lpac_cost_by_distance_range_hr',
            'desc'  => sprintf( __( 'Create your distance ranges and set the Price. It is highly recommended that you read the official documentation for this feature to fully understand how it works. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=prodocs#cost-by-distance-range' target='_blank'>" . self::$learn_more . '</a>' ),
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Enable Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_enable_cost_by_distance_range',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $distance_unit = get_option( 'lpac_distance_matrix_distance_unit', 'km' );
        $lpac_dummy_pro_settings[] = array(
            'name'                   => __( 'Create Ranges', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                     => 'lpac_cost_by_distance_range_rangelist',
            'row_id'                 => 'lpac_cost_by_distance_range_row_id',
            'type'                   => 'repeater',
            'current_saved_settings' => get_option( 'lpac_cost_by_distance_range_rangelist', array() ),
            'entity_name'            => __( 'range', 'map-location-picker-at-checkout-for-woocommerce' ),
            'table_columns'          => array(
            'start_range_text'                            => array(
            'name'     => __( 'Start range', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . $distance_unit . ')',
            'readonly' => false,
        ),
            'end_range_text'                              => array(
            'name'     => __( 'End range', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . $distance_unit . ')',
            'readonly' => false,
        ),
            'range_price_text'                            => array(
            'name'     => __( 'Price', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'readonly' => false,
        ),
            'should_calculate_per_distance_unit_checkbox' => array(
            'name'     => __( 'Calculate per', 'map-location-picker-at-checkout-for-woocommerce' ) . ' ' . $distance_unit,
            'readonly' => false,
        ),
        ),
            'fields_disabled'        => true,
            'is_option'              => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'  => __( 'Cost by Store Distance', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'id'    => 'lpac_cost_by_store_distance_pricing_hr',
            'type'  => 'hr',
            'desc'  => sprintf( __( 'Select your store location(s) and set the unit price for shipping from that location. The shipping cost for the order will be calculated automagically when a customer selects their desired store at checkout. This setting will override the "Origin Coordinates" and "Cost Per Unit Distance" you set above. If making use of this feature, be sure to set the details for your main store. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=freedocs#cost-by-store-distance' target='_blank'>" . self::$learn_more . '</a>' ),
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Enable Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_enable_cost_by_store_distance',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $saved_store_locations = get_option( 'lpac_store_locations', array() );
        $saved_cost_by_store_distance_delivery_prices = get_option( 'lpac_cost_by_store_distance_delivery_prices', array() );
        // If this option is empty then it means the user hasn't used this feature before.
        
        if ( empty($saved_cost_by_store_distance_delivery_prices) ) {
            $saved_cost_by_store_distance_delivery_prices = array( array(
                'store_location_id_select' => 'store_location_0',
                'store_price_text'         => '2',
            ) );
            $saved_store_locations = array( array(
                'store_location_id' => 'store_location_0',
                'store_name_text'   => 'Main Store',
            ) );
        }
        
        $lpac_dummy_pro_settings[] = array(
            'name'                          => __( 'Store Locations', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'                          => __( 'This setting is simply a demo of adding store locations and pricing. It does not actually do anything in the free version of the plugin.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                            => 'lpac_store_location_dummy',
            'type'                          => 'repeater',
            'current_saved_settings'        => $saved_cost_by_store_distance_delivery_prices,
            'id_field'                      => 'store_location_id',
            'entity_name'                   => __( 'entry', 'map-location-picker-at-checkout-for-woocommerce' ),
            'table_columns'                 => array(
            'store_name_select' => array(
            'name'     => __( 'Select store', 'map-location-picker-at-checkout-for-woocommerce' ),
            'readonly' => false,
        ),
            'store_price_text'  => array(
            'name'        => __( 'Set cost per unit distance', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'readonly'    => false,
            'placeholder' => '0.00',
        ),
        ),
            'select_field_dropdown_options' => $saved_store_locations,
            'option_element_id'             => 'store_location_id',
            'option_element_value'          => 'store_name_text',
            'select_element_id'             => 'store_location_id_select',
            'fields_disabled'               => true,
            'is_option'                     => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'  => __( 'Cost by Store Location', 'map-location-picker-at-checkout-for-woocommerce' ) . $this->pro_label,
            'class' => 'dashicons-before dashicons-lock premium-dummy-subsection',
            'type'  => 'hr',
            'id'    => 'lpac_cost_by_store_location_hr',
            'desc'  => sprintf( __( 'Select your store location(s) and set the price for shipping from that store. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=freedocs#cost-by-store-location' target='_blank'>" . self::$learn_more . '</a>' ),
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Enable Feature', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc'              => __( 'Yes', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_enable_cost_by_store_location',
            'type'              => 'checkbox',
            'css'               => 'max-width:80px;',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'name'              => __( 'Shipping Methods', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                => 'lpac_cost_by_store_location_shipping_methods',
            'class'             => 'wc-enhanced-select',
            'desc'              => sprintf( __( 'Select the Shipping Method(s) this feature applies to. If there is a cost already set on the shipping method, then that base cost will be added to the cost set for the region. NOTE: You need to have at least ONE created Shipping Zone with Shipping Methods attached to it. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/shipping/?utm_source=shippingtab&utm_medium=lpacdashboard&utm_campaign=freedocs#cost-by-store-location' target='_blank'>" . self::$learn_more . '</a>' ),
            'type'              => 'multiselect',
            'options'           => array(),
            'css'               => 'min-width:300px;height: 100px',
            'custom_attributes' => array(
            'disabled' => 'disabled',
        ),
            'is_option'         => false,
        );
        $saved_store_locations = get_option( 'lpac_store_locations', array() );
        $saved_cost_by_store_location_delivery_prices = get_option( 'lpac_cost_by_store_location_delivery_prices', array() );
        // If this option is empty then it means the user hasn't used this feature before.
        
        if ( empty($saved_cost_by_store_location_delivery_prices) ) {
            $saved_cost_by_store_location_delivery_prices = array( array(
                'store_location_id_select' => 'store_location_0',
                'store_price_text'         => '2',
            ) );
            $saved_store_locations = array( array(
                'store_location_id' => 'store_location_0',
                'store_name_text'   => 'Smiths Street',
            ) );
        }
        
        $lpac_dummy_pro_settings[] = array(
            'name'                          => __( 'Store Locations', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'                            => 'lpac_cost_by_store_location_delivery_prices',
            'row_id'                        => 'lpac_cost_by_store_location_delivery_prices_row_id',
            'type'                          => 'repeater',
            'current_saved_settings'        => $saved_cost_by_store_location_delivery_prices,
            'id_field'                      => 'store_location_id',
            'entity_name'                   => __( 'entry', 'map-location-picker-at-checkout-for-woocommerce' ),
            'table_columns'                 => array(
            'store_name_select' => array(
            'name'     => __( 'Select store', 'map-location-picker-at-checkout-for-woocommerce' ),
            'readonly' => false,
        ),
            'store_price_text'  => array(
            'name'        => __( 'Location shipping price', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'readonly'    => false,
            'placeholder' => '0.00',
        ),
        ),
            'select_field_dropdown_options' => $saved_store_locations,
            'option_element_id'             => 'store_location_id',
            'option_element_value'          => 'store_name_text',
            'select_element_id'             => 'store_location_id_select',
            'fields_disabled'               => true,
            'is_option'                     => false,
        );
        $lpac_dummy_pro_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_dummy_premium_settings_section_end',
        );
        return $lpac_dummy_pro_settings;
    }
    
    /**
     * House all the plugin settings to do with Debugging.
     *
     * @return array
     */
    private function create_tools_setting_fields()
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'LPAC Tools', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_tools_settings',
            'type' => 'title',
            'desc' => self::create_plugin_settings_banner(),
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
            'name'     => __( 'Housekeeping', 'map-location-picker-at-checkout-for-woocommerce' ),
            'desc_tip' => __( 'Delete all plugin settings on uninstall.', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'       => 'lpac_delete_settings_on_uninstall',
            'type'     => 'checkbox',
        );
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_debug_settings_section_end',
        );
        return $lpac_settings;
    }
    
    /**
     * Create LPAC's more page entries.
     *
     * @return array
     */
    private function create_more_page() : array
    {
        $lpac_settings = array();
        $lpac_settings[] = array(
            'name' => __( 'More', 'map-location-picker-at-checkout-for-woocommerce' ),
            'id'   => 'lpac_more',
            'type' => 'title',
        );
        $lpac_settings[] = array(
            'type'      => 'lpac_image',
            'src'       => LPAC_PLUGIN_ASSETS_PATH_URL . 'admin/img/delivery-and-pickup-scheduling.png',
            'desc'      => __( 'Allow customers to set their delivery/pickup date and time during order checkout.', 'map-location-picker-at-checkout-for-woocommerce' ) . " <a href='https://wordpress.org/plugins/delivery-and-pickup-scheduling-for-woocommerce/' target='_blank'>" . self::$learn_more . '</a>',
            'height'    => '280px',
            'url'       => 'https://wordpress.org/plugins/delivery-and-pickup-scheduling-for-woocommerce/',
            'is_option' => false,
        );
        $lpac_settings[] = array(
            'type'      => 'lpac_image',
            'src'       => LPAC_PLUGIN_ASSETS_PATH_URL . 'admin/img/discord-banner.png',
            'desc'      => __( 'Join E-Commerce store owners like yourself from around the world in a community tailored at helping your business grow.', 'map-location-picker-at-checkout-for-woocommerce' ) . " <a href='https://lpacwp.com/e-commerce-support-community/?utm_source=plugin-notice&utm_medium=wp-dashboard&utm_campaign=ecom-community' target='_blank'>" . self::$learn_more . '</a>',
            'height'    => '280px',
            'url'       => 'https://lpacwp.com/e-commerce-support-community/?utm_source=plugin-notice&utm_medium=wp-dashboard&utm_campaign=ecom-community',
            'is_option' => false,
        );
        $lpac_settings[] = array(
            'type' => 'sectionend',
            'id'   => 'lpac_more_section_end',
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
        $info_text = sprintf( esc_html( 'Use the table below to arrange the map visibility rules by dragging and dropping. The last rule in the the table decides the final visibility state of the map. %s', 'map-location-picker-at-checkout-for-woocommerce' ), "<a href='https://lpacwp.com/docs/getting-started/plugin-settings/visibility-rules/?utm_source=visibilityrulestab&utm_medium=lpacdashboard&utm_campaign=freedocs#rules-order' target='blank'>" . self::$learn_more . '</a>' );
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
    public function create_plugin_settings_fields()
    {
        global  $current_section ;
        /**
         * Redirect to the Map Builder post type.
         */
        
        if ( $current_section === 'map_builder' ) {
            wp_safe_redirect( site_url( 'wp-admin/edit.php?post_type=kikote-maps' ) );
            exit;
        }
        
        $lpac_settings = array();
        
        if ( empty($current_section) || $current_section === 'general' ) {
            $lpac_settings = $this->create_general_setting_fields();
            if ( lpac_fs()->is_not_paying() ) {
                $lpac_settings = array_merge( $lpac_settings, $this->create_dummy_general_settings_fields() );
            }
            $lpac_settings[] = array(
                'type' => 'sectionend',
                'id'   => 'lpac_general_settings_section_end',
            );
        }
        
        
        if ( $current_section === 'display' ) {
            $lpac_settings = $this->create_display_settings_fields();
            if ( lpac_fs()->is_not_paying() ) {
                $lpac_settings = array_merge( $lpac_settings, $this->create_dummy_display_settings_fields() );
            }
            $lpac_settings[] = array(
                'type' => 'sectionend',
                'id'   => 'lpac_display_settings_section_end',
            );
        }
        
        if ( $current_section === 'store_locations' ) {
            $lpac_settings = $this->create_store_locations_settings_fields();
        }
        if ( $current_section === 'visibility_rules' ) {
            $lpac_settings = $this->create_visibility_settings_fields();
        }
        // Add fields to Export tab
        if ( $current_section === 'export' ) {
            if ( lpac_fs()->is_not_paying() ) {
                $lpac_settings = $this->create_dummy_export_settings_fields();
            }
        }
        if ( $current_section === 'shipping' ) {
            if ( lpac_fs()->is_not_paying() ) {
                $lpac_settings = array_merge( $lpac_settings, $this->create_dummy_shipping_setting_fields() );
            }
        }
        if ( $current_section === 'tools' ) {
            $lpac_settings = $this->create_tools_setting_fields();
        }
        if ( $current_section === 'more' ) {
            $lpac_settings = $this->create_more_page();
        }
        return apply_filters( 'woocommerce_get_settings_' . $this->id, $lpac_settings );
    }
    
    /**
     * Output the plugin's settings markup.
     *
     * @since    1.1.0
     */
    public function output_plugin_settings()
    {
        $settings = $this->create_plugin_settings_fields();
        \WC_Admin_Settings::output_fields( $settings );
        global  $current_section ;
        if ( $current_section === 'visibility_rules' ) {
            $this->output_map_visibility_rules_order();
        }
    }
    
    /**
     *  Save our settings.
     */
    public function save_plugin_settings()
    {
        global  $current_section ;
        $settings = $this->create_plugin_settings_fields();
        \WC_Admin_Settings::save_fields( $settings );
        if ( $current_section ) {
            do_action( 'woocommerce_update_options_' . $this->id . '_' . $current_section );
        }
    }
    
    /**
     * Get the possible locations that the map can be rendered on the checkout page.
     *
     * @return array
     */
    private function get_possible_map_locations()
    {
        $locations = array(
            ''                                             => __( 'Select', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_checkout_before_customer_details' => __( 'Checkout Form - Top', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_before_checkout_shipping_form'    => __( 'Shipping Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_after_checkout_shipping_form'     => __( 'Shipping Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_before_checkout_billing_form'     => __( 'Billing Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_after_checkout_billing_form'      => __( 'Billing Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ),
            'woocommerce_review_order_before_payment'      => __( 'Before Payment Options', 'map-location-picker-at-checkout-for-woocommerce' ),
        );
        /*
         * Fluid Checkout compatibility
         */
        
        if ( class_exists( 'FluidCheckout' ) ) {
            $locations = array(
                ''                                             => __( 'Select', 'map-location-picker-at-checkout-for-woocommerce' ),
                'woocommerce_checkout_before_customer_details' => __( 'Checkout Form - Top', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (Fluid Checkout)',
                'fc_checkout_before_step_shipping_fields'      => __( 'Shipping Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (Fluid Checkout)',
            );
            $current_set_location = get_option( 'lpac_checkout_map_orientation', '' );
            if ( !array_key_exists( $current_set_location, $locations ) ) {
                update_option( 'lpac_checkout_map_orientation', 'woocommerce_checkout_before_customer_details' );
            }
        }
        
        /*
         * FunnelKit compatibility
         */
        
        if ( class_exists( 'WFFN_Core', false ) || class_exists( 'WFACP_core', false ) ) {
            $locations = array(
                ''                                             => __( 'Select', 'map-location-picker-at-checkout-for-woocommerce' ),
                'woocommerce_checkout_before_customer_details' => __( 'Checkout Form - Top', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (FunnelKit)',
                'wfacp_divider_shipping'                       => __( 'Shipping Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (FunnelKit)',
                'wfacp_divider_shipping_end'                   => __( 'Shipping Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (FunnelKit)',
                'wfacp_divider_billing'                        => __( 'Billing Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (FunnelKit)',
                'wfacp_divider_billing_end'                    => __( 'Billing Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (FunnelKit)',
            );
            $current_set_location = get_option( 'lpac_checkout_map_orientation', '' );
            if ( !array_key_exists( $current_set_location, $locations ) ) {
                update_option( 'lpac_checkout_map_orientation', 'woocommerce_checkout_before_customer_details' );
            }
        }
        
        /*
         * CheckoutWC compatibility
         */
        
        if ( defined( 'CFW_NAME' ) ) {
            $locations = array(
                ''                                     => __( 'Select', 'map-location-picker-at-checkout-for-woocommerce' ),
                'cfw_start_shipping_address_container' => __( 'Shipping Address Area - Top', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (CheckoutWC)',
                'cfw_end_shipping_address_container'   => __( 'Shipping Address Area - Bottom', 'map-location-picker-at-checkout-for-woocommerce' ) . ' (CheckoutWC)',
            );
            $current_set_location = get_option( 'lpac_checkout_map_orientation', '' );
            if ( !array_key_exists( $current_set_location, $locations ) ) {
                update_option( 'lpac_checkout_map_orientation', 'cfw_start_shipping_address_container' );
            }
        }
        
        $locations = apply_filters( 'kikote_possible_checkout_map_locations', $locations );
        return $locations;
    }

}