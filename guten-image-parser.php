<?php
/*
 * Plugin Name:		Gutenberg Image Parser
 * Plugin URI:		https://kodezen.com
 * Description:		Parse image from gutenberg code.
 * Version:			1.0.0
 * Author:			Istiaq Nirab
 * Author URI:		https://nirab.me
 * License:			GPL-3.0+
 * License URI:		http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:		guten_img_prs
 * Domain Path:		/languages
 */

use KodeZen\GutenImageParser\Generator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once "vendor/autoload.php";

final class Guten_Image_Parser {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	private function __construct() {
		$this->define_constants();

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
	}

	/**
	 * Initializes the Sdevs_Wc_Subscription() class
	 *
	 * Checks for an existing Sdevs_Wc_Subscription() instance
	 * and if it doesn't find one, creates it.
	 *
	 * @return Guten_Image_Parser|bool
	 */
	public static function init() {
		static $instance = false;

		if ( !$instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define the constants.
	 *
	 * @return void
	 */
	private function define_constants() {
		define( 'GUTEN_IMG_PRS_VERSION', self::VERSION );
		define( 'GUTEN_IMG_PRS_FILE', __FILE__ );
		define( 'GUTEN_IMG_PRS_PATH', dirname( GUTEN_IMG_PRS_FILE ) );
		define( 'GUTEN_IMG_PRS_INCLUDES', GUTEN_IMG_PRS_PATH . '/includes' );
		define( 'GUTEN_IMG_PRS_URL', plugins_url( '', GUTEN_IMG_PRS_FILE ) );
		define( 'GUTEN_IMG_PRS_ASSETS', GUTEN_IMG_PRS_URL . '/assets' );
	}

	/**
	 * Placeholder execution after plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
	}

	/**
	 * Placeholder execution after plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
	}

	public function init_plugin() {
		new Generator();
	}

}

Guten_Image_Parser::init();