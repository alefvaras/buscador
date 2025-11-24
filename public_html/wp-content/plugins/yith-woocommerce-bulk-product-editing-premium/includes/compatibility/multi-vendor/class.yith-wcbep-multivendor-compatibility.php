<?php
/**
 * Multi Vendor Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Compatibility
 */

( defined( 'YITH_WCBEP' ) && defined( 'YITH_WPV_VERSION' ) ) || exit(); // Exit if accessed directly.

require_once __DIR__ . '/class.yith-wcbep-multivendor-compatibility-legacy.php';

if ( ! class_exists( 'YITH_WCBEP_Multivendor_Compatibility' ) ) {
	/**
	 * Multi Vendor Compatibility Class
	 *
	 * @since   2.0
	 */
	class YITH_WCBEP_Multivendor_Compatibility extends YITH_WCBEP_Multivendor_Compatibility_Legacy {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Multivendor_Compatibility
		 */
		protected static $instance;

		/**
		 * The vendor panel page.
		 */
		const PANEL_PAGE = 'yith_wcbep_vendor_panel';

		/**
		 * Returns single instance of the class
		 *
		 * @return self|YITH_WCBEP_Multivendor_Compatibility_Legacy
		 */
		public static function get_instance() {
			if ( version_compare( YITH_WPV_VERSION, '4.0.0', '<' ) ) {
				return YITH_WCBEP_Multivendor_Compatibility_Legacy::get_instance();
			}

			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {
			if ( 'yes' === get_option( 'yith_wpv_vendors_option_bulk_product_editing_options_management', 'no' ) ) {
				add_filter( 'yith_wcbep_product_list_query_args', array( $this, 'add_vendor_tax_in_query_args' ) );
				add_filter( 'yith_wcbep_settings_admin_tabs', array( $this, 'remove_tabs_for_vendors' ), 11 );

				add_action( 'admin_menu', array( $this, 'add_page_to_menu' ), 5 );
				add_action( 'admin_init', array( $this, 'check_vendor_cant_manage_bulk' ), 15 );

				add_filter( 'yith_wcmv_admin_vendor_menu_items', array( $this, 'add_menu_page_to_vendors' ) );
			}
		}

		/**
		 * Get vendors capabilities
		 *
		 * @return string
		 */
		protected function get_vendors_capabilities() {
			return YITH_Vendors_Capabilities::ROLE_ADMIN_CAP;
		}

		/**
		 * Get the vendor ID
		 *
		 * @param YITH_Vendor $vendor The vendor.
		 *
		 * @return int
		 */
		protected function get_vendor_id( $vendor ) {
			return $vendor instanceof YITH_Vendor ? $vendor->get_id() : false;
		}

		/**
		 * Return true if the current user is vendor
		 *
		 * @return bool
		 */
		public function is_vendor() {
			$vendor = $this->get_current_vendor();

			return $vendor && $vendor->is_valid() && $vendor->has_limited_access();
		}

		/**
		 * Retrieve the current Vendor.
		 *
		 * @return YITH_Vendor|false
		 * @since 2.0
		 */
		public function get_current_vendor() {
			if ( function_exists( 'yith_wcmv_get_vendor' ) ) {
				$vendor = yith_wcmv_get_vendor( 'current', 'user' );
				if ( $vendor && $vendor->is_valid() && $vendor->has_limited_access() ) {
					return $vendor;
				}
			}

			return false;
		}
	}

	if ( ! function_exists( 'yith_wcbep_multivendor_compatibility' ) ) {
		/**
		 * Unique access to instance of YITH_WCBEP_Multivendor_Compatibility class
		 *
		 * @return YITH_WCBEP_Multivendor_Compatibility|YITH_WCBEP_Multivendor_Compatibility_Legacy
		 * @since 2.0
		 */
		function yith_wcbep_multivendor_compatibility() {
			return YITH_WCBEP_Multivendor_Compatibility::get_instance();
		}
	}
}
