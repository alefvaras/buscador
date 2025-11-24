<?php
/**
 * Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Compatibility
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Compatibility' ) ) {
	/**
	 * Compatibility Class
	 *
	 * @since   1.1.2
	 */
	class YITH_WCBEP_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Compatibility
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Compatibility
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Badge Management Compatibility
		 *
		 * @var YITH_WCBEP_Badge_Management_Compatibility
		 * @depreacted since 2.0.0
		 */
		public $badge_management;

		/**
		 * Brands Compatibility
		 *
		 * @var YITH_WCBEP_Brands_Add_On_Compatibility
		 * @depreacted since 2.0.0
		 */
		public $brands;

		/**
		 * Deposits Compatibility
		 *
		 * @var YITH_WCBEP_Deposits_Compatibility
		 * @depreacted since 2.0.0
		 */
		public $deposits;

		/**
		 * Multi Vendor Compatibility
		 *
		 * @var YITH_WCBEP_Multivendor_Compatibility
		 * @depreacted since 2.0.0
		 */
		public $multivendor;

		/**
		 * Plugin compatibilities list
		 *
		 * @var string[]
		 */
		private $compatibilities = array(
			'deposits'         => __DIR__ . '/deposits/class.yith-wcbep-deposits-compatibility.php',
			'badge_management' => __DIR__ . '/badge-management/class.yith-wcbep-badge-management-compatibility.php',
			'multi_currency'   => __DIR__ . '/multi-currency/class.yith-wcbep-multi-currency-compatibility.php',
			'multivendor'      => __DIR__ . '/multi-vendor/class.yith-wcbep-multivendor-compatibility.php',
			'wpml'             => __DIR__ . '/wpml/class.yith-wcbep-wpml-compatibility.php',
		);

		/**
		 * Constructor
		 */
		protected function __construct() {
			/**
			 * Load the compatibility class at plugins_loaded -> priority 15
			 * to make sure all yith plugins are already loaded
			 *
			 * @since  1.1.25
			 */
			add_action( 'plugins_loaded', array( $this, 'init' ), 15 );
		}

		/**
		 * Initialization
		 */
		public function init() {
			foreach ( $this->compatibilities as $plugin => $class_path ) {
				if ( self::has_plugin( $plugin ) && file_exists( $class_path ) ) {
					require_once $class_path;
					$instance_getter = 'yith_wcbep_' . $plugin . '_compatibility';
					if ( function_exists( $instance_getter ) ) {
						$instance_getter();
					}
				}
			}
		}

		/**
		 * Include required files.
		 *
		 * @depreacted since 2.0.0 | The file inclusion is now handled by YITH_WCBEP_Compatibility::init method.
		 */
		public function include_files() {
			wc_deprecated_function( 'YITH_WCBEP_Compatibility::include_files', '2.0.0' );
		}

		/**
		 * Check if user has plugin
		 *
		 * @param string $plugin_name The plugin slug.
		 *
		 * @return bool
		 * @since  1.1.2
		 */
		public static function has_plugin( $plugin_name ) {
			$has_plugin = false;

			switch ( $plugin_name ) {
				case 'badge_management':
					$has_plugin = defined( 'YITH_WCBM_PREMIUM' ) && YITH_WCBM_PREMIUM && defined( 'YITH_WCBM_VERSION' ) && version_compare( YITH_WCBM_VERSION, '1.3.14', '>=' );
					break;
				case 'brands_add_on':
					$has_plugin = defined( 'YITH_WCBR_PREMIUM_INIT' ) && YITH_WCBR_PREMIUM_INIT;
					break;
				case 'deposits':
					$has_plugin = defined( 'YITH_WCDP_PREMIUM_INIT' ) && YITH_WCDP_PREMIUM_INIT;
					break;
				case 'multivendor':
					$has_plugin = defined( 'YITH_WPV_PREMIUM' ) && YITH_WPV_PREMIUM;
					break;
				case 'multi_currency':
					$has_plugin = defined( 'YITH_WCMCS_VERSION' ) && version_compare( YITH_WCMCS_VERSION, '1.10.0', '>=' );
					break;
				case 'wpml':
					$has_plugin = defined( 'WCML_VERSION' ) && version_compare( WCML_VERSION, '5.1.2', '>=' );
					break;
			}

			return $has_plugin;
		}
	}

	if ( ! function_exists( 'yith_wcbep_compatibility' ) ) {
		/**
		 * Unique access to instance of YITH_WCBEP_Compatibility class
		 *
		 * @return YITH_WCBEP_Compatibility
		 */
		function yith_wcbep_compatibility() {
			return YITH_WCBEP_Compatibility::get_instance();
		}
	}
}
