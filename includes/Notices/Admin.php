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
	* Detect if WooCommerce is active.
	*
	* Plugin runs off WooCommerce so requires WooCommerce to be active.
	*
	* @since    1.1.0
	*
	*/
	public function lpac_wc_not_active_notice() {

		if ( ! class_exists( 'woocommerce' ) ) {
			?>

			  <div class="notice notice-error is-dismissible">
				<?php
				/* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
				echo sprintf( __( '%1$s%2$sLocation Picker at Checkout for WooCommerce(LPAC) NOTICE:%3$s WooCommerce is not activated, please activate it to use the plugin.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ), '<p>', '<strong>', '</strong>', '</p>' );
				?>
			  </div>
			  <?php

		}

	}

	/**
	* Detect if site has HTTPS support.
	*
	* Geolocation requires that site is running under HTTPS
	* https://www.chromium.org/Home/chromium-security/prefer-secure-origins-for-powerful-new-features
	*
	* @since    1.1.0
	*
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
		echo sprintf( __( '%1$s%2$sLocation Picker at Checkout for WooCommerce(LPAC) NOTICE:%3$s HTTPS not detected on this website. The plugin will not work. Please enable HTTPS on this website.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ), '<p>', '<strong>', '</strong>', '</p>' );
		?>
		</div>
		<?php

	}

	/**
	 * Output a notice when PHP version is below 7.0.
	 *
	 * @return void
	 */
	public function output_php_version_notice() {
		?>

		<div class="notice notice-error is-dismissible">
		<?php
		/* translators: 1: Opening <p> HTML element 2: Opening <strong> HTML element 3: Closing <strong> HTML element 4: Closing <p> HTML element  */
		echo sprintf( __( '%1$s%2$sLocation Picker at Checkout for WooCommerce(LPAC) NOTICE:%3$s PHP version too low to use this plugin. Please change to at least PHP 7.0. You can contact your web host for assistance in updating your PHP version.%4$s', 'map-location-picker-at-checkout-for-woocommerce' ), '<p>', '<strong>', '</strong>', '</p>' );
		?>
		</div>
		<?php

	}

	public function create_pro_version_released_notice() {
		// TODO Create notice helper functions with notice ID in URL and should_show_notice($user_id, $notice_id) method
	}

}
