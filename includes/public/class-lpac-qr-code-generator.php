<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://uriahsvictor.com
 * @since      1.1.0
 *
 * @package    Lpac
 * @subpackage Lpac/public
 */
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Label\Font\NotoSans;

class Lpac_Qr_Code_Generator {

	/**
	 * Creates a QR code
	 *
	 * @param int $options The options for the barcode such as its link and colors.
	 * @param int $order_id The current order id.
	 *
	 * @since    1.1.0
	 */
	public static function lpac_generate_qr_code( $options, $order_id ) {

		$path = self::lpac_qr_codes_directory();

		if ( empty( $path ) ) {
			error_log( 'Location Picker At Checkout for WooCommerce: QR Code directory path returned empty. See Lpac_Qr_Code_Generator::lpac_generate_qr_code()' );
			return;
		}

		$path_with_filename = $path . $order_id . '.jpg';

		$qr_code_data           = $options['qr_code_data'];
		$qr_code_foreground_rgb = explode( ',', $options['qr_code_foreground_rgb'] );
		$qr_code_background_rgb = explode( ',', $options['qr_code_background_rgb'] );

		// It might be possible to pass RGBA as well...check class to be sure
		// https://stackoverflow.com/a/40764343/4484799
		$fr = $qr_code_foreground_rgb[0];
		$fg = $qr_code_foreground_rgb[1];
		$fb = $qr_code_foreground_rgb[2];

		$br = $qr_code_background_rgb[0];
		$bg = $qr_code_background_rgb[1];
		$bb = $qr_code_background_rgb[2];

		$writer = new PngWriter();

		$qr_code_label = __( 'Delivery Location', 'lpac' );
		$qr_code_label = apply_filters( 'lpac_map_location_link_button_text', $qr_code_label );

		/*
		* Create QR Code
		*/
		$qrCode = QrCode::create( $qr_code_data )
			->setEncoding( new Encoding( 'UTF-8' ) )
			->setErrorCorrectionLevel( new ErrorCorrectionLevelLow() )
			->setSize( 200 )
			->setMargin( 10 )
			->setRoundBlockSizeMode( new RoundBlockSizeModeMargin() )
			->setForegroundColor( new Color( $fr, $fg, $fb ) )
			->setBackgroundColor( new Color( $br, $bg, $bb ) );

		$label = Label::create( $qr_code_label )
		->setFont( new NotoSans( 18 ) );

		$result = $writer->write( $qrCode, null, $label );

		$image = $result->saveToFile( $path_with_filename );

		// Doesn't work in email clients like Gmail
		// $image_base64 = $result->getDataUri();

		// return $image_base64;

	}

	/**
	 * Creates a QR code
	 *
	 * @since    1.1.0
	 */
	public static function lpac_qr_codes_directory() {

		$upload_dir   = wp_upload_dir();
		$qr_codes_dir = '';

		if ( ! empty( $upload_dir['basedir'] ) ) {

			$qr_codes_dir = $upload_dir['basedir'] . '/' . 'lpac-qr-codes' . '/' . date( 'Y' ) . '/' . date( 'm' ) . '/' . date( 'd' ) . '/';

			if ( ! file_exists( $qr_codes_dir ) ) {
				wp_mkdir_p( $qr_codes_dir );
			}

			return apply_filters( 'lpac_qrcodes_path', $qr_codes_dir );
		}

		return $qr_codes_dir;

	}

}
