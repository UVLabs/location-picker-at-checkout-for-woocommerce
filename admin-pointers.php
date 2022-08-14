<?php

$pointerplus = new PointerPlus( array( 'prefix' => 'lpac-pointers' ) );

// $pointerplus->reset_pointer();

/**
 * Create our onboarding pointers.
 * @param array $pointers
 * @param string $prefix
 * @return array
 */
function create_onboarding_pointers( $pointers, $prefix ) {

	$screen_id = get_current_screen()->id;

	if ( $screen_id === 'admin_page_map-location-picker-at-checkout-for-woocommerce' ) {
		return array();
	}

	$pointers_list = array_merge(
		$pointers,
		array(
			$prefix . '_woocommerce_settings' => array(
				'selector'   => '#toplevel_page_woocommerce',
				'title'      => __( 'Thanks for Installing LPAC!', 'map-location-picker-at-checkout-for-woocommerce' ),
				'text'       => sprintf( __( 'Lets point you to the plugin settings page. Start by going to %1$sWooCommerce->Settings%2$s.', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>', '<br/>' ),
				'icon_class' => 'dashicons-plugins-checked',
				'width'      => 350,
			),
		),
		array(
			$prefix . '_lpac_tab' => array(
				'selector'   => '.woo-nav-tab-wrapper',
				'title'      => __( 'Plugin Settings Tab', 'map-location-picker-at-checkout-for-woocommerce' ),
				'text'       => sprintf( __( 'Click on %1$sLocation Picker at Checkout%2$s to access the plugin settings.', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>', '<br/>' ),
				'icon_class' => 'dashicons-admin-generic',
				'width'      => 250,
			),
		),
		array(
			$prefix . '_lpac_submenu' => array(
				'selector'   => '#lpac-submenu',
				'title'      => __( 'Plugin Settings Menus', 'map-location-picker-at-checkout-for-woocommerce' ),
				'text'       => sprintf( __( 'These are the different setting menus for the plugin. You can check them out %1$slater%2$s, to first get started with LPAC, you need to enter a %1$sGoogle Maps API Key%2$s on this %1$sGeneral Settings%2$s page.', 'map-location-picker-at-checkout-for-woocommerce' ), '<strong>', '</strong>', '<br/>' ),
				'icon_class' => 'dashicons-menu-alt3',
				'width'      => 350,
				'next'       => $prefix . '_google_maps_api_key',
			),
		)
	);

	if ( $screen_id === 'woocommerce_page_wc-settings' ) {
		unset( $pointers_list[ $prefix . '_woocommerce_settings' ] );
	}

	$tab = $_REQUEST['tab'] ?? '';

	if ( $tab !== '' && $tab === 'lpac_settings' ) {
		unset( $pointers_list[ $prefix . '_lpac_tab' ] );
	}

	// We need to only add this pointer on the settings page or else the PHP code attached to the pointer would always run...
	if ( $tab !== '' && $tab === 'lpac_settings' ) {
		$pointers_list = array_merge(
			$pointers_list,
			array(
				$prefix . '_google_maps_api_key' => array(
					'selector'   => '#lpac_google_maps_api_key',
					'title'      => __( 'Enter Your API Key', 'map-location-picker-at-checkout-for-woocommerce' ),
					'text'       => sprintf( __( "The documentation for acquiring your Google Maps API Key can be found %1\$sHERE%2\$s. Once you've entered and saved your API Key; feel free to fine-tune LPAC's settings.%3\$s%3\$s %4\$sNOTE:%5\$s The plugin will NOT work without an API key.", 'map-location-picker-at-checkout-for-woocommerce' ), '<a href="https://lpacwp.com/docs/getting-started/google-cloud-console/getting-your-google-maps-api-key/?utm_source=generaltab&utm_medium=lpacdashboard&utm_campaign=freedocs" target="_blank" style="font-weight:bold;">', '<span style="text-decoration: none" class="dashicons dashicons-external"></span></a>', '</br>', '<strong>', '</strong>' ),
					'icon_class' => 'dashicons-post-status',
					'width'      => 400,
					'jsnext'     => "button = jQuery('<a id=\"pointer-close\" class=\"button action\">" . __( 'Finish' ) . "</a>');
                    button.bind('click.pointer', function () {
                        t.element.pointer('close');
                    });
                    return button;",
					'phpcode'    => hide_initial_onboarding_pointers(),
				),
			)
		);
	}

	return $pointers_list;
}
add_filter( 'lpac-pointers-pointerplus_list', 'create_onboarding_pointers', 10, 2 );

/**
 * Hide initial pointers so that they do not show after we are done displaying the pointers.
 * @return void
 */
function hide_initial_onboarding_pointers() {

	$user_id = get_current_user_id();

	$dismissed_pointers = get_user_meta( $user_id, 'dismissed_wp_pointers', true );

	$dismissed_pointers_array = explode( ',', $dismissed_pointers );

	// Manually dismiss these initial notices incase the user didn't
	$dismissed_pointers_array[] = 'lpac-pointers_woocommerce_settings';
	$dismissed_pointers_array[] = 'lpac-pointers_lpac_tab';
	$dismissed_pointers_array   = array_unique( $dismissed_pointers_array );

	$dismissed_pointers = trim( implode( ',', $dismissed_pointers_array ), ',' );

	update_user_meta( $user_id, 'dismissed_wp_pointers', $dismissed_pointers );
}
