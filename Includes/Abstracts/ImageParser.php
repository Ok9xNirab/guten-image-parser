<?php

namespace KodeZen\GutenImageParser\Abstracts;

use WP_Error;

abstract class ImageParser {

	protected array $block;

	public function __construct( array $block ) {
		$this->block = $block;
	}

	abstract protected function parse(): array;

	public function doParse(): array {
		return $this->parse();
	}

	/**
	 * Function handles downloading a remote file and inserting it
	 * into the WP Media Library.
	 *
	 * @param string $url HTTP URL address of a remote file
	 *
	 * @return int|WP_Error The ID of the attachment or a WP_Error on failure
	 * @see https://developer.wordpress.org/reference/functions/media_handle_sideload/
	 */
	protected function upload_file( string $url ) {
		// URL Validation
		if ( ! wp_http_validate_url( $url ) ) {
			return new WP_Error( 'invalid_url', 'File URL is invalid', array( 'status' => 400 ) );
		}

		// Gives us access to the download_url() and media_handle_sideload() functions.
		if ( ! function_exists( 'download_url' ) || ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		// Download file to temp dir.
		$temp_file = download_url( $url );

		// if the file was not able to be downloaded
		if ( is_wp_error( $temp_file ) ) {
			return $temp_file;
		}

		// An array similar to that of a PHP `$_FILES` POST array
		$file_url_path = parse_url( $url, PHP_URL_PATH );
		$file_info     = wp_check_filetype( $file_url_path );
		$file          = array(
			'tmp_name' => $temp_file,
			'type'     => $file_info['type'],
			'name'     => basename( $file_url_path ),
			'size'     => filesize( $temp_file ),
		);

		// Move the temporary file into the uploads directory.
		$attachment_id = media_handle_sideload( $file );

		@unlink( $temp_file );

		return $attachment_id;
	}

	protected function check_external_url( string $url ): bool {
		$link_url = parse_url( $url );
		$home_url = parse_url( home_url() );

		return ( ! empty( $link_url['host'] ) && ( $link_url['host'] !== $home_url['host'] ) );
	}
}
