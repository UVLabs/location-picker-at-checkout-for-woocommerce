<?php
/**
 * Class responsible for adding compatibility with Siteground Optimizer.
 *
 * Author:          Uriahs Victor
 * Created on:      01/02/2023 (d/m/y)
 *
 * @link https://uriahsvictor.com
 * @link https://wordpress.org/plugins/sg-cachepress/
 * @since 1.6.14
 * @package Compatibility
 */

namespace Lpac\Compatibility\Caching;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for adding support for Siteground optimizer plugin.
 *
 * @package Lpac\Compatibility\Caching
 * @link https://wordpress.org/plugins/sg-cachepress/
 */
class Siteground_Optimizer {

	/**
	 * Remove the defer on Google Maps external script.
	 *
	 * Setting the link as defer causes it to load(execute) when the page is done downloading, it should actually be executed immediately.
	 *
	 * @param array $exclude_list The list of handles to exclude.
	 * @return array
	 * @since 1.6.14
	 */
	public function remove_defer_on_gmaps_script( array $exclude_list ) : array {
		$exclude_list[] = 'lpac-google-maps-js';
		return $exclude_list;
	}

	/**
	 * Exclude scripts from being minified.
	 *
	 * They are already minified.
	 *
	 * @param array $exclude_list The list of handles to exclude.
	 * @return array
	 * @since 1.6.14
	 */
	public function js_minify_exclude( array $exclude_list ) : array {
		$exclude_list[] = 'lpac-base-map';
		$exclude_list[] = 'lpac-checkout-page-map';
		$exclude_list[] = 'lpac-checkout-page-map-pro';
		return $exclude_list;
	}

	/**
	 * Exclude inline scripts from being combined.
	 *
	 * @param array $exclude_list The list of handles to exclude.
	 * @return array
	 * @since 1.6.14
	 */
	public function js_combine_exclude_inline_script( array $exclude_list ) : array {
		$exclude_list[] = 'GMapsScriptLoaded';
		$exclude_list[] = 'Location Picker at Checkout version';
		return $exclude_list;
	}

	/**
	 * Exclude scripts from being combined.
	 *
	 * @param array $exclude_list The list of handles to exclude.
	 * @return array
	 * @since 1.6.14
	 */
	public function js_combine_exclude( array $exclude_list ) : array {
		$exclude_list[] = 'lpac-base-map';
		$exclude_list[] = 'lpac-checkout-page-map';
		$exclude_list[] = 'lpac-checkout-page-map-pro';
		return $exclude_list;
	}

}
