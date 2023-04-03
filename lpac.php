<?php

/**
 * Kikote - Location Picker at Checkout Plugin for WooCommerce.
 *
 * @link              https://uriahsvictor.com
 * @link              https://github.com/UVLabs/location-picker-at-checkout-for-woocommerce
 * @since             1.0.0
 * @package           Lpac
 *
 * @wordpress-plugin
 * Plugin Name:       Kikote - Location Picker at Checkout for WooCommerce
 * Plugin URI:        https://lpacwp.com
 * Description:       Allow customers to choose their shipping or pickup location using a map at checkout.
 * Version:           1.7.4-lite
 * Requires at least: 5.7
 * Author:            Uriahs Victor
 * Author URI:        https://lpacwp.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       map-location-picker-at-checkout-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 7.5
 * Requires PHP: 7.4
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !defined( 'LPAC_VERSION' ) ) {
    define( 'LPAC_VERSION', '1.7.4' );
}
/**
 * Check PHP version
 */
if ( function_exists( 'phpversion' ) ) {
    
    if ( version_compare( phpversion(), '7.4', '<' ) ) {
        add_action( 'admin_notices', function () {
            echo  "<div class='notice notice-error is-dismissible'>" ;
            /* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
            echo  sprintf(
                esc_html__( '%1$s%2$sKikote - Location Picker at Checkout for WooCommerce NOTICE:%3$s PHP version too low to use this plugin. Please change to at least PHP 7.4. You can contact your web host for assistance in updating your PHP version.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ),
                '<p>',
                '<strong>',
                '</strong>',
                '</p>'
            ) ;
            echo  '</div>' ;
        } );
        return;
    }

}
/**
 * Check PHP versions
 */
if ( defined( 'PHP_VERSION' ) ) {
    
    if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
        add_action( 'admin_notices', function () {
            echo  "<div class='notice notice-error is-dismissible'>" ;
            /* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
            echo  sprintf(
                esc_html__( '%1$s%2$sKikote - Location Picker at Checkout for WooCommerce NOTICE:%3$s PHP version too low to use this plugin. Please change to at least PHP 7.4. You can contact your web host for assistance in updating your PHP version.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ),
                '<p>',
                '<strong>',
                '</strong>',
                '</p>'
            ) ;
            echo  '</div>' ;
        } );
        return;
    }

}
/**
 * Check that WooCommerce is active.
 *
 * This needs to happen before freemius does any work.
 *
 * @since 1.0.0
 */

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
    add_action( 'admin_notices', function () {
        echo  "<div class='notice notice-error is-dismissible'>" ;
        /* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
        echo  sprintf(
            esc_html__( '%1$s%2$sKikote - Location Picker at Checkout for WooCommerce NOTICE:%3$s WooCommerce is not activated, please activate it to use the plugin.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ),
            '<p>',
            '<strong>',
            '</strong>',
            '</p>'
        ) ;
        echo  '</div>' ;
    } );
    return;
}


if ( function_exists( 'lpac_fs' ) ) {
    lpac_fs()->set_basename( false, __FILE__ );
} else {
    // Setup Freemius.
    
    if ( !function_exists( 'lpac_fs' ) ) {
        /**
         * Create a helper function for easy SDK access.
         *
         * @return mixed
         * @throws Freemius_Exception Freemius Exception.
         * @since 1.0.0
         */
        function lpac_fs()
        {
            global  $lpac_fs ;
            
            if ( !isset( $lpac_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
                $lpac_fs = fs_dynamic_init( array(
                    'id'              => '8507',
                    'slug'            => 'map-location-picker-at-checkout-for-woocommerce',
                    'premium_slug'    => 'map-location-picker-at-checkout-for-woocommerce-pro',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_da07de47a2bdd9391af9020cc646d',
                    'is_premium'      => false,
                    'premium_suffix'  => 'PRO',
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                    'slug'   => 'lpac-menu',
                    'parent' => array(
                    'slug' => 'sl-plugins-menu',
                ),
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $lpac_fs;
        }
        
        // Init Freemius.
        lpac_fs();
        /**
         * Signal that SDK was initiated.
         *
         * @since 1.0.1
         */
        do_action( 'lpac_fs_loaded' );
    }
    
    /**
     * Composer autoload. DO NOT PLACE THIS LINE BEFORE FREEMIUS SDK RUNS.
     *
     * Doing that will cause the plugin to throw an error when trying to activate PRO when the Free version is active or vice versa.
     * This is because both PRO and Free are generated from the same codebase, meaning composer autoloader file would already be
     * present and throw an error when trying to be redefined.
     */
    require_once dirname( __FILE__ ) . '/vendor/autoload.php';
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-lpac-activator.php
     */
    if ( !function_exists( 'activate_lpac' ) ) {
        /**
         * Code that runs when the plugin is activated.
         *
         * @return void
         * @since 1.0.0
         */
        function activate_lpac()
        {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-activator.php';
            Lpac_Activator::activate();
        }
    
    }
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-lpac-deactivator.php
     */
    if ( !function_exists( 'deactivate_lpac' ) ) {
        /**
         * Code that runs when the plugin is deactivated.
         *
         * @return void
         * @since 1.0.0
         */
        function deactivate_lpac()
        {
            require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-deactivator.php';
            Lpac_Deactivator::deactivate();
        }
    
    }
    register_activation_hook( __FILE__, 'activate_lpac' );
    register_deactivation_hook( __FILE__, 'deactivate_lpac' );
    /**
     * Move this code to the main plugin file and then run it in an action hook when the SDK is initialized: lpac_fs_loaded.
     */
    function lpac_redirect_to_map_builder()
    {
        wp_safe_redirect( admin_url( 'edit.php?post_type=kikote-maps' ) );
        exit;
    }
    
    require __DIR__ . '/class-lpac-uninstall.php';
    require __DIR__ . '/admin-pointers.php';
    lpac_fs()->add_action( 'after_uninstall', array( new Lpac_Uninstall(), 'remove_plugin_settings' ) );
    lpac_fs()->add_filter( 'show_deactivation_subscription_cancellation', '__return_false' );
    lpac_fs()->add_filter( 'plugin_icon', function () {
        return dirname( __FILE__ ) . '/assets/img/logo.png';
    } );
    define( 'LPAC_BASE_FILE', basename( plugin_dir_path( __FILE__ ) ) );
    define( 'LPAC_PLUGIN_NAME', 'lpac' );
    define( 'LPAC_PLUGIN_DIR', __DIR__ . '/' );
    define( 'LPAC_PLUGIN_ASSETS_DIR', __DIR__ . '/assets/' );
    define( 'LPAC_PLUGIN_ASSETS_PATH_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
    define( 'LPAC_PLUGIN_PATH_URL', plugin_dir_url( __FILE__ ) );
    define( 'LPAC_INSTALLED_AT_VERSION', get_option( 'lpac_installed_at_version', constant( 'LPAC_VERSION' ) ) );
    define( 'LPAC_IS_PREMIUM_VERSION', lpac_fs()->is_premium() );
    define( 'LPAC_GOOGLE_MAPS_API_LINK', 'https://maps.googleapis.com/maps/api/js?key=' );
    define( 'LPAC_GOOGLE_MAPS_API_KEY', get_option( 'lpac_google_maps_api_key', '' ) );
    define( 'LPAC_GOOGLE_MAPS_DIRECTIONS_LINK', 'https://maps.google.com/maps?daddr=' );
    define( 'LPAC_WAZE_DIRECTIONS_LINK', 'https://waze.com/ul?ll=' );
    $debug = false;
    if ( function_exists( 'wp_get_environment_type' ) ) {
        /* File will only exist in local installation */
        if ( wp_get_environment_type() === 'local' && file_exists( LPAC_PLUGIN_ASSETS_DIR . 'public/js/maps/base-map.js' ) ) {
            $debug = true;
        }
    }
    define( 'LPAC_DEBUG', $debug );
    $version = ( LPAC_DEBUG ? 'weekly' : 'quarterly' );
    $google_params = array( "v={$version}" );
    $libraries = array();
    $places_autocomplete = get_option( 'lpac_enable_places_autocomplete', 'no' );
    if ( 'no' !== $places_autocomplete ) {
        array_push( $libraries, 'places' );
    }
    
    if ( !empty($libraries) ) {
        $libraries = implode( ',', $libraries );
        array_push( $google_params, "libraries={$libraries}" );
    }
    
    // Map Region.
    $region = get_option( 'lpac_google_map_region' );
    if ( !empty($region) ) {
        $google_params[] = "region={$region}";
    }
    // Callback parameter is required even though we're not making use of it.
    $google_params[] = 'callback=GMapsScriptLoaded';
    // Bring our parameters together.
    $google_params = implode( '&', $google_params );
    define( 'LPAC_GOOGLE_MAPS_PARAMS', $google_params );
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    $main_plugin = new \Lpac\Bootstrap\Main();
    $main_plugin->run();
}
