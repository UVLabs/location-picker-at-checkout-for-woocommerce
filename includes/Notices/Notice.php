<?php
/**
* Class responsible for creating notices markup.
*
* Author:          Uriahs Victor
* Created on:      08/01/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.4.3
* @package Notices
*/
namespace Lpac\Notices;

/**
* Class Notice.
*/
class Notice {

	/**
	 * Get the current user id
	 *
	 * @return int
	 */
	private function get_user_id() {
		return get_current_user_id();
	}

	/**
	 * Get the notice ids that have been dismissed by user.
	 * @return mixed
	 */
	private function get_dismissed_notices() {
		return get_user_meta( $this->get_user_id(), 'lpac_dismissed_notices', true );
	}

	/**
	 * Create the dismiss URL for a notice.
	 * @param string $notice_id The ID of the particular notice.
	 * @return string
	 */
	protected function create_dismiss_url( string $notice_id ) {

		if ( ! function_exists( 'wp_create_nonce' ) ) {
			require_once( ABSPATH . 'wp-includes/pluggable.php' );
		}
		$nonce = wp_create_nonce( 'lpac_notice_nonce_value' );

		return admin_url( 'admin-ajax.php?action=lpac_dismiss_notice&lpac_notice_id=' . $notice_id . '&lpac_notice_nonce=' . $nonce );

	}

	/**
	 * Create the markup for a notice
	 * @param string $notice_id The ID of the particular notice.
	 * @param array $content The content to add to the notice.
	 * @return string
	 */
	protected function create_notice_markup( string $notice_id, array $content ) {

		# Only show the Notice to Admins
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$dismissed_notices = $this->get_dismissed_notices();

		# Bail if this notice has been dismissed
		if ( in_array( $notice_id, $dismissed_notices ) ) {
			return;
		}

		$title             = esc_html( $content['title'] ?? '' );
		$body              = esc_html( $content['body'] ?? '' );
		$cta_text          = esc_html( $content['cta'] ?? __( 'Learn more', 'map-location-picker-at-checkout-for-woocommerce' ) );
		$learn_more_link   = esc_attr( $content['link'] ?? '' );
		$learm_more_output = '';

		if ( ! empty( $learn_more_link ) ) {
			$learm_more_output = "<li id='lpac-notice-cta'><a target='_blank' href='$learn_more_link' style='color: #2b4fa3'><span class='dashicons dashicons-share-alt2'></span>$cta_text</a></li>";
		}

		$dismiss_url  = esc_html( $this->create_dismiss_url( $notice_id ) );
		$dismiss_text = esc_html__( 'Dismiss', 'map-location-picker-at-checkout-for-woocommerce' );

		$markup = <<<HTML

			<div class="update-nag lpac-admin-notice">
			<div class="lpac-notice-logo"></div> 
			<p class="lpac-notice-title">$title</p> 
			<p class="lpac-notice-body">$body</p>
			<ul class="lpac-notice-body">
			$learm_more_output
			<li id="lpac-notice-dismiss"><a href="$dismiss_url" style="color: #2b4fa3"> <span class="dashicons dashicons-dismiss"></span> $dismiss_text</a></li>
			</ul>
			</div>

HTML;

		return $markup;

	}

	/**
	 * Get the ID of a notice from the URL.
	 *
	 * @return mixed
	 */
	protected function get_notice_id() {

		$notice_id = $_REQUEST['lpac_notice_id'] ?? '';

		if ( empty( $notice_id ) ) {
			return;
		}

		return $notice_id;
	}

	/**
	 * Dismiss a notice so it doesn't show again.
	 *
	 * @return void
	 */
	public function dismiss_notice() {

		if ( ! wp_verify_nonce( $_REQUEST['lpac_notice_nonce'], 'lpac_notice_nonce_value' ) ) {
			exit( 'Failed to verify nonce. Please try going back and refreshing the page to try again.' );
		}

		$notice_id = $this->get_notice_id();

		if ( ! empty( $notice_id ) ) {

			$dismissed_notices = $this->get_dismissed_notices();

			if ( empty( $dismissed_notices ) ) {
				$dismissed_notices = array();
			}

			# Add our new notice ID to the currently dismissed ones.
			array_push( $dismissed_notices, $notice_id );

			$dismissed_notices = array_unique( $dismissed_notices );

			update_user_meta( $this->get_user_id(), 'lpac_dismissed_notices', $dismissed_notices );

			wp_redirect( $_SERVER['HTTP_REFERER'] );
			exit;

		}

		return;

	}

}
