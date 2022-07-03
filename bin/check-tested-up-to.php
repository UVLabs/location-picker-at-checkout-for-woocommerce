<?php
/**
* Check tested up to versions against current minor versions of plugins in the wp.org repo.
*
* Author:          Uriahs Victor
* Created on:      08/05/2022 (d/m/y)
*
* @link    https://uriahsvictor.com
* @since   1.0.0
*/
class Check_Tested_Up_To {

	/**
	 * Wp.org API Endpoint.
	 * @var string
	 */
	private $endpoint;

	/**
	 * Wp.org object
	 * @var object
	 */
	private $resource;

	/**
	 * File object
	 * @var mixed
	 */
	private $file_obj;

	/**
	 * Construct class.
	 * @return void
	 */
	public function __construct() {
		global $argv;
		$plugin = $argv[1];
		$field  = $argv[2] ?? '';

		$this->endpoint = "https://api.wordpress.org/plugins/info/1.0/{$plugin}.json";
		$this->resource = $this->setup_resource();
		$this->file_obj = new SplFileObject( './lpac.php' );

		switch ( $field ) {
			case 'version':
				echo $this->get_latest_version();
				break;
			case 'changelog':
				echo $this->get_changelog();
				break;
			case 'check':
			default:
				echo $this->compare_versions();
				break;
		}
	}

	/**
	 * Setup the Wp.org details
	 * @return object
	 */
	private function setup_resource() {
		$details = file_get_contents( $this->endpoint );
		return json_decode( $details );
	}

	/**
	 * Get the last tested up to version in Neve.
	 * @return string
	 */
	private function get_tested_up_to() {
		$file             = $this->file_obj;
		$version_haystack = '';

		while ( ! $file->eof() ) {
			$line = $file->fgets();
			if ( strpos( $line, 'WC tested up to:' ) !== false ) {
				$version_haystack = $line;
				break;
			}
		}

		$parts = explode( ' ', $version_haystack );

		return array_pop( $parts );
	}

	/**
	 * Get the latest version uploaded to wp.org
	 *
	 * Drop the patch version in the version number so that we only test Minor versions (6.4, 6.5 ... ...).
	 *
	 * @return string
	 */
	private function get_latest_version() {
		$latest       = $this->resource->version;
		$parts        = explode( '.', $latest );
		$latest_minor = $parts[0] . '.' . $parts[1];
		return $latest_minor;
	}

	/**
	 * Compate the last tested up to version with the latest version released in the Repo.
	 * @return string|null
	 */
	private function compare_versions() {
		$tested_up_to   = $this->get_tested_up_to();
		$latest_version = $this->get_latest_version();

		$lower = version_compare( $tested_up_to, $latest_version, '<' );

		return var_export( $lower, true );
	}

	/**
	 * Get Changelog
	 * @return string
	 */
	private function get_changelog() {
		return $this->resource->sections->changelog;
	}

}

new Check_Tested_Up_To();
