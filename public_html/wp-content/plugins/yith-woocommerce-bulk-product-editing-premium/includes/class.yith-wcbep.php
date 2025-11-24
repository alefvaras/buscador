<?php
/**
 * Main class
 *
 * @package YITH\BulkProductEditing
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP' ) ) {
	/**
	 * YITH WooCommerce Bulk Edit Products
	 *
	 * @since 1.0.0
	 */
	class YITH_WCBEP {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_WCBEP
		 */
		protected static $instance;

		/**
		 * Plugin version
		 *
		 * @since 1.0.0
		 * @var string
		 */
		public $version = YITH_WCBEP_VERSION;

		/**
		 * Compatibility.
		 *
		 * @var YITH_WCBEP_Compatibility
		 */
		public $compatibility;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP
		 * @since 1.0.0
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {

			if ( is_admin() ) {
				yith_wcbep_admin();
				yith_wcbep_install_class();

				$this->compatibility = yith_wcbep_compatibility();
			}

			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

			add_action( 'before_woocommerce_init', array( $this, 'declare_wc_features_support' ) );
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since 1.2.25
		 */
		public function register_plugin_for_activation() {
			if ( function_exists( 'YIT_Plugin_Licence' ) ) {
				YIT_Plugin_Licence()->register( YITH_WCBEP_INIT, YITH_WCBEP_SECRET_KEY, YITH_WCBEP_SLUG );
			}
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since 1.2.25
		 */
		public function register_plugin_for_updates() {
			if ( function_exists( 'YIT_Upgrade' ) ) {
				YIT_Upgrade()->register( YITH_WCBEP_SLUG, YITH_WCBEP_INIT );
			}
		}

		/**
		 * Declare support for WooCommerce features.
		 *
		 * @since 2.9.0
		 */
		public function declare_wc_features_support() {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', YITH_WCBEP_INIT );
			}
		}
	}
}

if ( ! function_exists( 'yith_wcbep' ) ) {
	/**
	 * Unique access to instance of YITH_WCBEP class
	 *
	 * @return YITH_WCBEP
	 * @since 1.0.0
	 */
	function yith_wcbep() {
		return YITH_WCBEP::get_instance();
	}
}
