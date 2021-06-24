<?php

/**
 * Provide helper static functions.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.0.0
 *
 * @package    Lpac
 * @subpackage Lpac/includes/helpers
 */

class Lpac_Functions_Helper {

	/**
	 * Shows a feature if the setting is enabled
	 *
	 * @since    1.0.0
	 */
	public static function lpac_show_map( $option ) {

		$show = get_option( $option, true );

		if ( $show !== 'yes' ) {
			return false;
		}

	}

	/**
	 * Create QR Code directory string based on the basedir or baseurl.
	 *
	 * @return string The qr code resource server path or url path
	 * @since    1.1.0
	 */
	public static function lpac_get_qr_codes_directory( $base ) {

		$qr_code_resource_base = wp_upload_dir()[ $base ];

		$qr_code_resource_locator = $qr_code_resource_base . '/' . 'lpac-qr-codes' . '/' . date( 'Y' ) . '/' . date( 'm' ) . '/' . date( 'd' ) . '/';

		return $qr_code_resource_locator;

	}

}
