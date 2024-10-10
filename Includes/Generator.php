<?php

namespace KodeZen\GutenImageParser;

use Exception;

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
		$blocks         = guten_image_parser( $guten_img_code );

		update_option( '_guten_img_prs_success_form', $blocks );
		wp_safe_redirect( admin_url( 'admin.php?page=guten-image-parser' ) );
	}

	/**
	 * @return void
	 */
	public function display_notices(): void {
		$failed = get_option( '_guten_img_prs_failed' );
		if ( $failed ) {
			require_once "views/failed-notice.php";
			delete_option( '_guten_img_prs_failed' );
		}
		$blocks = get_option( '_guten_img_prs_success_form' );
		if ( ! $blocks || ! is_array( $blocks ) ) {
			return;
		}

		require_once "views/success-notice.php";
	}
}