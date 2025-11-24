<?php
/**
 * Plugin Name: WC Chilean RUT Validator
 * Description: Simple Chilean RUT validation for WooCommerce
 * Version: 2.1.0
 * Text Domain: wc-chilean-rut
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * License: GPL v3 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_CHILEAN_RUT_VERSION', '2.1.0' );
define( 'WC_CHILEAN_RUT_PLUGIN_FILE', __FILE__ );
define( 'WC_CHILEAN_RUT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WC_CHILEAN_RUT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class WC_Chilean_RUT_Validator {

	private static $instance = null;
	public $validator;
	public $checkout_field;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'init' ), 20 );
	}

	public function init() {
		// Verificar que WooCommerce esté activo
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		load_plugin_textdomain( 'wc-chilean-rut', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		require_once WC_CHILEAN_RUT_PLUGIN_DIR . 'includes/class-rut-validator.php';
		require_once WC_CHILEAN_RUT_PLUGIN_DIR . 'includes/class-checkout-field.php';
		require_once WC_CHILEAN_RUT_PLUGIN_DIR . 'includes/class-ajax-handler.php';
		require_once WC_CHILEAN_RUT_PLUGIN_DIR . 'includes/class-admin-settings.php';

		$this->validator      = new WC_Chilean_RUT_Validator_Core();
		$this->checkout_field = new WC_Chilean_RUT_Checkout_Field( $this->validator );
		new WC_Chilean_RUT_Ajax_Handler( $this->validator );
		new WC_Chilean_RUT_Admin_Settings();
	}
}

function wc_chilean_rut_validator() {
	return WC_Chilean_RUT_Validator::instance();
}

// Inicializar
wc_chilean_rut_validator();
