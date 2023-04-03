<?php declare(strict_types=1);
/**
* Holds traits for creating upload folders.
*
* Author:          Uriahs Victor
* Created on:      22/12/2021 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.4.0
* @package Lpac
*/

namespace Lpac\Traits;

/**
* Class Upload_Folders.
*
* Creates upload folders.
*/
trait Upload_Folders {

	/**
	 *
	 * Create a folder inside the WP uploads directory.
	 *
	 * @param int $folder_name
	 * @return mixed
	 */
	private function create_upload_folder( string $folder_name ) {

		$upload_dir = wp_upload_dir();
		$folder_dir = '';

		if ( ! empty( $upload_dir['basedir'] ) ) {

			$folder_dir = $upload_dir['basedir'] . "/lpac/$folder_name/";
			$folder_dir = apply_filters( "lpac_{$folder_name}_path", $folder_dir );

			if ( ! file_exists( $folder_dir ) ) {
				$path_created = wp_mkdir_p( $folder_dir );
				// Add index.php to folder to help prevent farming of customer location data.
				if ( $path_created ) {
					$outstream = fopen( $folder_dir . 'index.php', 'w' );
					fwrite( $outstream, '<?php //Silence;' );
					fclose( $outstream );
				}
			}

			return $folder_dir;
		}

		return $folder_dir;
	}

	/**
	 *
	 * Get the resource link to a file.
	 *
	 * @param string $folder_name
	 * @param int    $order_id
	 * @param string $ext
	 * @return string The upload URL
	 */
	public function get_resource_url( string $folder_name, int $order_id, string $ext = '.jpg' ) {

		$upload_url = wp_upload_dir()['baseurl'];

		$upload_url = $upload_url . "/lpac/$folder_name/" . $order_id . $ext;

		return $upload_url;
	}

}
