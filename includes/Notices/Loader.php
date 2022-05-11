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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Lpac\Notices\Upsells_Notices;
use Lpac\Notices\Review_Notices;

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
		( new Review_Notices );
		( new General_Notices );
	}
}
