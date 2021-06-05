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
 * Version:           1.0.0
 * Author:            Uriahs Victor
 * Author URI:        https://uriahsvictor.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       lpac
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 5.3
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LPAC_VERSION', '1.0.0' );
define( 'LPAC_PLUGIN_NAME', 'lpac' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-lpac-activator.php
 */
function activate_lpac() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-activator.php';
	Lpac_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-lpac-deactivator.php
 */
function deactivate_lpac() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-lpac-deactivator.php';
	Lpac_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_lpac' );
register_deactivation_hook( __FILE__, 'deactivate_lpac' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-lpac.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_lpac() {

	$plugin = new Lpac();
	$plugin->run();

}
run_lpac();
