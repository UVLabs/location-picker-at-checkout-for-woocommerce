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
class Lpac_Functions_Helper
{
    /**
     * Shows a feature if the setting is enabled
     *
     * @param string $option the option to act on.
     * @since    1.0.0
     * @since    1.2.0 Added more checks to determine when to show/hide map
     */
    public static function lpac_show_map( $option )
    {
        if ( $option === 'checkout' && lpac_fs()->is_not_paying() ) {
            return true;
        }
        /**
         * If this is the order-recieved or view-order page, then see if the options are set by the admin.
         *
         */
        $show = get_option( $option, 'yes' );
        /**
         * If the admin selected to hide the map on those pages then return false.
         */
        if ( $show !== 'yes' ) {
            return false;
        }
        return true;
    }
    
    /**
     * Create QR Code directory string based on the basedir or baseurl.
     *
     * @return string The qr code resource server path or url path
     * @since    1.1.0
     */
    public static function lpac_get_qr_codes_directory( $base )
    {
        $qr_code_resource_base = wp_upload_dir()[$base];
        $qr_code_resource_locator = $qr_code_resource_base . '/' . 'lpac-qr-codes' . '/' . date( 'Y' ) . '/' . date( 'm' ) . '/' . date( 'd' ) . '/';
        return $qr_code_resource_locator;
    }

}