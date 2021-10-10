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
 * @since             1.0.0
 * @package           Lpac
 *
 * @wordpress-plugin
 * Plugin Name:       Location Picker At Checkout For WooCommerce
 * Plugin URI:        https://soaringleads.com
 * Description:       Allow customers to choose their shipping location using a map at checkout.
 * Version:           1.3.2
 * Author:            Uriahs Victor
 * Author URI:        https://uriahsvictor.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       map-location-picker-at-checkout-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 5.7
 * Requires PHP: 7.0
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}
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
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'first-path' => 'plugins.php',
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
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LPAC_VERSION', '1.3.2' );
define( 'LPAC_PLUGIN_NAME', 'lpac' );
define( 'LPAC_PLUGIN_DIR', __DIR__ . '/' );
define( 'LPAC_PLUGIN_ASSETS_DIR', __DIR__ . '/assets/' );
define( 'LPAC_PLUGIN_ASSETS_PATH_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
define( 'LPAC_PLUGIN_PATH_URL', plugin_dir_url( __FILE__ ) );
define( 'LPAC_GOOGLE_MAPS_LINK', 'https://maps.googleapis.com/maps/api/js?key=' );
define( 'LPAC_GOOGLE_MAPS_API_KEY', get_option( 'lpac_google_maps_api_key', '' ) );
$site_locale = get_locale();
define( 'LPAC_GOOGLE_MAPS_PARAMS', "&language={$site_locale}&libraries=&v=weekly" );
$debug = false;
if ( function_exists( 'wp_get_environment_type' ) ) {
    /* File will only exist in local installation */
    if ( wp_get_environment_type() === 'local' && file_exists( LPAC_PLUGIN_ASSETS_DIR . 'public/js/maps/base-map.js' ) ) {
        $debug = true;
    }
}
define( 'LPAC_DEBUG', $debug );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lpac-activator.php
 */
function activate_lpac()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-activator.php';
    Lpac_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lpac-deactivator.php
 */
function deactivate_lpac()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-deactivator.php';
    Lpac_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_lpac' );
register_deactivation_hook( __FILE__, 'deactivate_lpac' );
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