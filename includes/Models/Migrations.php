<?php
/**
 * Handles migrations.
 *
 * When old settings should be moved to new formats to renamed.
 *
 * Author:          Uriahs Victor
 * Created on:      06/08/2022 (d/m/y)
 *
 * @link    https://uriahsvictor.com
 * @since   1.6.2
 * @package Lpac/Models
 */
namespace Lpac\Models;

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Migrations Class.
 */
class Migrations {

	/**
	 * Plugin current version.
	 *
	 * @var string
	 */
	private $plugin_version = LPAC_VERSION;

	/**
	 * Version at which the plugin was installed.
	 *
	 * @var string
	 */
	private $installed_at = '';

	/**
	 * Constructor method
	 *
	 * @return void
	 */
	public function __construct() {
		$this->installed_at = get_option( 'lpac_installed_at_version', '1.0.0' );
	}

	/**
	 * Add new address field to store locations array.
	 *
	 * Remove function when v1.6 usage is depleted.
	 *
	 * @since 1.6.2
	 * @return void
	 */
	public function add_address_field_to_store_locations() : void {

		if ( version_compare( $this->installed_at, '1.6.2', '>=' ) ) {
			return;
		}

		$migrated = get_option( 'lpac_migrated__add_address_to_store_locations' );
		if ( $migrated ) {
			return;
		}

		$store_locations = get_option( 'lpac_store_locations', array() );
		if ( empty( $store_locations ) ) {
			return;
		}

		foreach ( $store_locations as $key => &$store ) {
			$store = array_merge(
				array_slice( $store, 0, 3, true ),
				array( 'store_address_text' => '' ),
				array_slice( $store, 3, null, true )
			);
		}
		unset( $store );

		update_option( 'lpac_store_locations', $store_locations );
		update_option( 'lpac_migrated__add_address_to_store_locations', true );
	}

	/**
	 * Add "Calculate per <unit>" checkbox option to distance range repeater.
	 *
	 * Remove function when v1.6 usage is depleted.
	 *
	 * @return void
	 * @since 1.6.9
	 */
	public function add_should_calculate_per_distance_unit_field() : void {

		if ( version_compare( $this->installed_at, '1.6.9', '>=' ) ) {
			return;
		}

		$migrated = get_option( 'lpac_migrated__add_should_calculate_per_distance_unit_field' );
		if ( $migrated ) {
			return;
		}

		$ranges = get_option( 'lpac_cost_by_distance_range_rangelist', array() );
		if ( empty( $ranges ) ) {
			return;
		}

		foreach ( $ranges as $key => &$range ) {
			$range = array_merge(
				array_slice( $range, 0, 3, true ),
				array( 'should_calculate_per_distance_unit_checkbox' => '' ),
				array_slice( $range, 3, null, true )
			);
		}
		unset( $range );

		update_option( 'lpac_cost_by_distance_range_rangelist', $ranges );
		update_option( 'lpac_migrated__add_should_calculate_per_distance_unit_field', true );
	}

}
