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
public static function lpac_show_map( $option ){

    $show = get_option($option, true);

    if( $show !== 'yes' ){
        return false;
    }

}

}