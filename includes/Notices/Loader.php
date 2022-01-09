<?php
/**
* Load Notices to admin notices hook.
*
* Author:          Uriahs Victor
* Created on:      08/01/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.4.0
* @package Notices
*/

namespace Lpac\Notices;
use Lpac\Notices\Upsells_Notices;

/**
* The Loader class.
*/
class Loader {

	/**
	 * Load our notices.
	 *
	 * @since 1.4.3
	 * @return void
	 */
	public function load_notices() {
		( new Upsells_Notices );
	}
}
