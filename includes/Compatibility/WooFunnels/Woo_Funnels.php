<?php

/**
* WooFunnels compatibility Class.
*
* Adds compatibility for WooFunnels plugin.
*
* Author:          Uriahs Victor
* Created on:      19/10/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.3.3
* @package Lpac
*/
namespace Lpac\Compatibility\WooFunnels;
/**
* WooFunnels compatibility Class.
*
*/
class Woo_Funnels {

	/**
	 * Check if Lpac has already been added to fieldset.
	 * @var false
	 */
	private $hook_run = false;

	/**
	 * Lpac Map Shown field
	 * @var array
	 */
	private $map_shown_field = array();

	/**
	 * Lpac Latitude field
	 * @var array
	 */
	private $latitude_field = array();

	/**
	 * Lpac Longitude field
	 * @var array
	 */
	private $longitude_field = array();

	/**
	 * Setup needed input fields for Lpac.
	 * @return void
	 */
	public function create_lpac_fields() {

		$this->map_shown_field = array(
			'label'      => __( 'Map Shown', 'map-location-picker-at-checkout-for-woocommerce' ),
			'type'       => 'text',
			'field_type' => 'billing',
			'class'      => ( LPAC_DEBUG ) ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ),
			'clear'      => true,
			'id'         => 'lpac_is_map_shown',
		);

		$this->latitude_field = array(
			'label'      => __( 'Latitude', 'map-location-picker-at-checkout-for-woocommerce' ),
			'type'       => 'text',
			'field_type' => 'billing',
			'class'      => ( LPAC_DEBUG ) ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ),
			'clear'      => true,
			'id'         => 'lpac_latitude',
		);

		$this->longitude_field = array(
			'label'      => __( 'Longitude', 'map-location-picker-at-checkout-for-woocommerce' ),
			'type'       => 'text',
			'field_type' => 'billing',
			'class'      => ( LPAC_DEBUG ) ? array( 'wfacp-col-full' ) : array( 'wfacp-col-full', 'hidden' ),
			'clear'      => true,
			'id'         => 'lpac_longitude',
		);

	}

	/**
	 * Attach needed fields for lpac to WooFunnels checkout fields array.
	 *
	 * @param mixed $fields
	 * @return mixed
	 */
	public function add_lpac_checkout_fields( $fields ) {

		if ( is_array( $fields ) && count( $fields ) > 0 ) {
			$fields['billing']['lpac_is_map_shown'] = $this->map_shown_field;
			$fields['billing']['lpac_latitude']     = $this->latitude_field;
			$fields['billing']['lpac_longitude']    = $this->longitude_field;
		}

		return $fields;
	}

	/**
	 * Add Lpac checkout fields to WooFunnels fieldset
	 *
	 * @param mixed $section
	 * @return mixed
	 */
	public function add_lpac_checkout_fields_to_fieldsets( $section ) {

		// if ( false === $this->hook_run ) {
			$section['single_step'][0]['fields'][] = $this->map_shown_field;
			$section['single_step'][0]['fields'][] = $this->latitude_field;
			$section['single_step'][0]['fields'][] = $this->longitude_field;
		// }

		return $section;
	}

}
