<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://uriahsvictor.com
 * @link              https://github.com/UVLabs/location-picker-at-checkout-for-woocommerce
 * @since             1.0.0
 * @package           Lpac
 *
 * @wordpress-plugin
 * Plugin Name:       Location Picker At Checkout For WooCommerce
 * Plugin URI:        https://lpacwp.com
 * Description:       Allow customers to choose their shipping or pickup location using a map at checkout.
 * Version:           1.5.3-lite
 * Author:            Uriahs Victor
 * Author URI:        https://uriahsvictor.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       map-location-picker-at-checkout-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 6.0
 * Requires PHP: 7.3
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( !defined( 'LPAC_VERSION' ) ) {
    define( 'LPAC_VERSION', '1.5.2' );
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lpac-activator.php
 */
if ( !function_exists( 'activate_lpac' ) ) {
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
    function deactivate_lpac()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-deactivator.php';
        Lpac_Deactivator::deactivate();
    }

}
register_activation_hook( __FILE__, 'activate_lpac' );
register_deactivation_hook( __FILE__, 'deactivate_lpac' );
/*
* Check if the Free version is installed and show notice about deactivating it.
* We're checking the plugin folder so this can only be ran for the PRO plugin.
*/
$plugin_folder = basename( dirname( __FILE__ ) );

if ( $plugin_folder === 'map-location-picker-at-checkout-for-woocommerce-pro' ) {
    /**
     * Deactivate free version if active.
     */
    if ( !function_exists( 'get_plugins' ) || !function_exists( 'deactivate_plugins' ) ) {
        include ABSPATH . '/wp-admin/includes/plugin.php';
    }
    $plugins = get_plugins();
    
    if ( array_key_exists( 'map-location-picker-at-checkout-for-woocommerce/lpac.php', $plugins ) && array_key_exists( 'map-location-picker-at-checkout-for-woocommerce-pro/lpac.php', $plugins ) ) {
        add_action( 'admin_notices', function () {
            ?>
				<div class="notice notice-error is-dismissible">
					<?php 
            /* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
            echo  sprintf(
                __( '%1$s%2$sLocation Picker at Checkout for WooCommerce(LPAC) NOTICE:%3$s You need to deactivate and DELETE the free version of the plugin before using the PRO version. Your current settings will remain in place.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ),
                '<p>',
                '<strong>',
                '</strong>',
                '</p>'
            ) ;
            ?>
				</div>
				<?php 
        } );
        deactivate_plugins( 'map-location-picker-at-checkout-for-woocommerce/lpac.php', true );
        return;
    }

}

// Composer autoload
require dirname( __FILE__ ) . '/vendor/autoload.php';

if ( !function_exists( 'lpac_fs' ) ) {
    // Create a helper function for easy SDK access.
    function lpac_fs()
    {
        global  $lpac_fs ;
        
        if ( !isset( $lpac_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
            $lpac_fs = fs_dynamic_init( array(
                'id'             => '8507',
                'slug'           => 'map-location-picker-at-checkout-for-woocommerce',
                'type'           => 'plugin',
                'public_key'     => 'pk_da07de47a2bdd9391af9020cc646d',
                'is_premium'     => false,
                'premium_suffix' => 'PRO',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'first-path' => 'admin.php?page=wc-settings&tab=lpac_settings',
            ),
                'is_live'        => true,
            ) );
        }
        
        return $lpac_fs;
    }
    
    // Init Freemius.
    lpac_fs();
    // Signal that SDK was initiated.
    do_action( 'lpac_fs_loaded' );
}

include __DIR__ . '/class-lpac-uninstall.php';

if ( function_exists( 'lpac_fs' ) ) {
    lpac_fs()->add_action( 'after_uninstall', array( new Lpac_Uninstall(), 'remove_plugin_settings' ) );
    lpac_fs()->add_filter( 'show_deactivation_subscription_cancellation', '__return_false' );
}

/**
 * Check that WooCommerce is active.
 */

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', array( new Lpac\Notices\Admin(), 'lpac_wc_not_active_notice' ) );
    return;
}

/**
 * Check PHP version
 */
if ( function_exists( 'phpversion' ) ) {
    
    if ( version_compare( phpversion(), '7.3', '<' ) ) {
        add_action( 'admin_notices', array( new Lpac\Notices\Admin(), 'output_php_version_notice' ) );
        return;
    }

}
/**
 * Check PHP versions
 */
if ( defined( 'PHP_VERSION' ) ) {
    
    if ( version_compare( PHP_VERSION, '7.3', '<' ) ) {
        add_action( 'admin_notices', array( new Lpac\Notices\Admin(), 'output_php_version_notice' ) );
        return;
    }

}
define( 'LPAC_BASE_FILE', basename( plugin_dir_path( __FILE__ ) ) );
define( 'LPAC_PLUGIN_NAME', 'lpac' );
define( 'LPAC_PLUGIN_DIR', __DIR__ . '/' );
define( 'LPAC_PLUGIN_ASSETS_DIR', __DIR__ . '/assets/' );
define( 'LPAC_PLUGIN_ASSETS_PATH_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'LPAC_PLUGIN_PATH_URL', plugin_dir_url( __FILE__ ) );
define( 'LPAC_GOOGLE_MAPS_LINK', 'https://maps.googleapis.com/maps/api/js?key=' );
define( 'LPAC_GOOGLE_MAPS_API_KEY', get_option( 'lpac_google_maps_api_key', '' ) );
$debug = false;
if ( function_exists( 'wp_get_environment_type' ) ) {
    /* File will only exist in local installation */
    if ( wp_get_environment_type() === 'local' && file_exists( LPAC_PLUGIN_ASSETS_DIR . 'public/js/maps/base-map.js' ) ) {
        $debug = true;
    }
}
define( 'LPAC_DEBUG', $debug );
$site_locale = get_locale();
$version = ( LPAC_DEBUG ? 'weekly' : 'quarterly' );
$google_params = array( "language={$site_locale}", "v={$version}" );
$libraries = array();
$places_autocomplete = get_option( 'lpac_enable_places_autocomplete', 'no' );
if ( $places_autocomplete !== 'no' ) {
    array_push( $libraries, 'places' );
}

if ( !empty($libraries) ) {
    $libraries = implode( ',', $libraries );
    array_push( $google_params, "libraries={$libraries}" );
}

$google_params = '&' . implode( '&', $google_params );
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
use  Lpac\Bootstrap\Main as Plugin ;
$plugin = new Plugin();
$plugin->run();