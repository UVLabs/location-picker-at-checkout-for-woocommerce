<?php

/**
 * Admin Notices.
 *
 * Houses all the notices to show in admin dashboard.
 *
 * @link    https://uriahsvictor.com
 * @since    1.1.0
 *
 * @package    Lpac
 */
namespace Lpac\Notices;

class Admin {

	/**
	 * Detect if site has HTTPS support.
	 *
	 * Geolocation requires that site is running under HTTPS
	 * https://www.chromium.org/Home/chromium-security/prefer-secure-origins-for-powerful-new-features
	 *
	 * @since    1.1.0
	 */
	public function lpac_site_not_https() {

		if ( is_ssl() ) {
			return;
		}

		if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
			return;
		}

		?>

		<div class="notice notice-error is-dismissible">
		<?php
		/* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
		echo sprintf( esc_html__( '%1$s%2$sKikote - Location Picker at Checkout for WooCommerce NOTICE:%3$s HTTPS not detected on this website. The plugin will not work. Please enable HTTPS on this website.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ), '<p>', '<strong>', '</strong>', '</p>' );
		?>
		</div>
		<?php

	}

}
