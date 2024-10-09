<?php

namespace KodeZen\GutenImageParser;

use Exception;
use IvoPetkov\HTML5DOMDocument;
use WP_Error;

class Generator {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_guten_img_form', array( $this, 'handle_form_submission' ) );
		add_action( 'admin_notices', array( $this, 'display_notices' ) );
	}

	/**
	 * Register menu to display Image parser form.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page( __( 'Gutenberg Image Parser', 'guten_img_prs' ), __( 'GT Image Parser', 'guten_img_prs' ), 'manage_options', 'guten-image-parser', array(
			$this,
			'display_form'
		), 'dashicons-shortcode', 30 );
	}

	/**
	 * Display the form.
	 *
	 * @return void
	 */
	public function display_form() {
		$blocks         = get_option( '_guten_img_prs_success_form' );
		$generated_code = "";
		if ( $blocks && is_array( $blocks ) ) {
			$generated_code = htmlentities( serialize_blocks( $blocks ) );
			delete_option( '_guten_img_prs_success_form' );
		}

		require_once "views/form.php";
	}

	/**
	 * Handle form submission.
	 *
	 * @return void
	 * @throws Exception
	 */
	public function handle_form_submission() {
		if ( ! isset( $_POST['_wpnonce'], $_POST['_wp_http_referer'], $_POST['guten_img_code'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'guten_img_form_nonce' ) ) {
			return;
		}

		$guten_img_code = wp_kses_post( stripslashes( $_POST['guten_img_code'] ) );
		$blocks         = parse_blocks( $guten_img_code );

		foreach ( $blocks as $key => $block ) {
			if ( "core/image" === $block['blockName'] ) {
				$data                           = $this->parse_image_block( $block );
				$blocks[ $key ]['attrs']['id']  = $data['attachment_id'];
				$blocks[ $key ]['innerHTML']    = str_replace( $data['old_url'], $data['new_url'], $blocks[ $key ]['innerHTML'] );
				$blocks[ $key ]['innerContent'] = array( str_replace( $data['old_url'], $data['new_url'], $blocks[ $key ]['innerHTML'] ) );
			}
		}

		update_option( '_guten_img_prs_success_form', $blocks );

		wp_safe_redirect( admin_url( 'admin.php?page=guten-image-parser' ) );
	}

	/**
	 * Parse only the wp-core image block.
	 *
	 * @param array $block Image Block.
	 *
	 * @return array
	 * @throws Exception
	 */
	private function parse_image_block( array $block ): array {
		$html5   = new HTML5DOMDocument();
		$imgHTML = $block['innerHTML'];
		$html5->loadHTML( $imgHTML );
		$imgURL        = $html5->querySelector( 'img' )->getAttribute( 'src' );
		$attachment_id = $this->upload_file( $imgURL );

		if ( $attachment_id instanceof WP_Error ) {
			update_option('_guten_img_prs_failed', $attachment_id->get_error_message());
			wp_safe_redirect( admin_url( "admin.php?page=guten-image-parser" ) );
			exit();
		}

		$attachment_url = wp_get_attachment_url( $attachment_id );

		return array(
			'attachment_id' => $attachment_id,
			'old_url'       => $imgURL,
			'new_url'       => $attachment_url,
		);

	}

	/**
	 * Function handles downloading a remote file and inserting it
	 * into the WP Media Library.
	 *
	 * @param string $url HTTP URL address of a remote file
	 *
	 * @return int|WP_Error The ID of the attachment or a WP_Error on failure
	 * @see https://developer.wordpress.org/reference/functions/media_handle_sideload/
	 *
	 */
	private function upload_file( string $url ) {
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

	/**
	 * @return void
	 */
	public function display_notices(): void {
		$failed = get_option('_guten_img_prs_failed');
		if ($failed) {
			require_once "views/failed-notice.php";
			delete_option('_guten_img_prs_failed');
		}
		$blocks = get_option( '_guten_img_prs_success_form' );
		if ( ! $blocks || ! is_array( $blocks ) ) {
			return;
		}

		require_once "views/success-notice.php";
	}
}