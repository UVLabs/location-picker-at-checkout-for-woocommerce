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
class Lpac_Admin_Settings{

    /**
     * Add settings section to WooCommerce settings page.
     *
     * @since    1.0.0
     * @param array $sections The sections array passed by WooCommerce.
     */
    public function lpac_add_settings_section( $sections ){

        $sections['lpac_settings'] = __( 'Location Picker at Checkout', 'lpac' );
        return $sections;

    }

    /**
     * Create the setting options for the plugin.
     *
     * @since    1.0.0
     * @param array $settings The WooCommerce settings.
     * @param array $current_section The current settings tab being viewed.
     */
    public function lpac_plugin_settings( $settings, $current_section ){

    /**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'lpac_settings' ) {
		
        $lpac_settings = array();

		$lpac_settings[] = array( 'name' => __( 'LPAC Settings', 'lpac' ), 
                                    'id'   => 'lpac',
                                    'type' => 'title', 
                                    'desc' => __( 'Use the below options to change the settings of LPAC', 'lpac' )
                                ); 

        $lpac_settings[] = array(
			'name'     => __( 'Show Map on Order Received Page', 'text-domain' ),
			'desc_tip' => __( 'This option displays a map view on the order received page after an order has been placed by a customer.', 'lpac' ),
			'id'       => 'lpac_display_map_on_order_recieved_page',
			'type'     => 'checkbox',
			'css'      => 'min-width:300px;',
			'desc'     => __( 'Enable', 'lpac' ),
		);

        $lpac_settings[] = array(
			'name'     => __( 'Show Map on Order Details Page', 'text-domain' ),
			'desc_tip' => __( 'This option displays a map view on the order details page in the customer account.', 'lpac' ),
			'id'       => 'lpac_display_map_on_order_details_page',
			'type'     => 'checkbox',
			'css'      => 'min-width:300px;',
			'desc'     => __( 'Enable', 'lpac' ),
		);

        $lpac_settings[] = array( 'type' => 'sectionend', 
                                  'id' => 'lpac' 
                                );

		return $lpac_settings;		

    }

    }

}