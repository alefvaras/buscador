<?php
/**
 * Multi Vendor Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

/**
 * Multi Vendor Compatibility Class
 *
 * @since   1.1.23
 */
class YITH_WCBEP_Multivendor_Compatibility_Legacy {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCBEP_Multivendor_Compatibility_Legacy
	 */
	protected static $instance;

	/**
	 * Returns single instance of the class
	 *
	 * @return YITH_WCBEP_Multivendor_Compatibility_Legacy
	 */
	public static function get_instance() {
		return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * The plugin page.
	 *
	 * @var string
	 */
	public $plugin_page = 'admin.php?page=yith_wcbep_panel';

	/**
	 * Constructor
	 */
	protected function __construct() {
		add_filter( 'yith_wcbep_product_list_query_args', array( $this, 'add_vendor_tax_in_query_args' ) );
		add_filter( 'yith_wcbep_settings_admin_tabs', array( $this, 'remove_tabs_for_vendors' ), 11 );

		add_action( 'admin_menu', array( $this, 'add_page_to_menu' ), 5 );
		add_action( 'admin_init', array( $this, 'check_vendor_cant_manage_bulk' ), 15 );

		add_filter( 'yith_wpv_vendor_menu_items', array( $this, 'add_menu_page_to_vendors' ) );
	}

	/**
	 * Add menu page for vendors.
	 *
	 * @param array $pages The pages.
	 *
	 * @return array
	 */
	public function add_menu_page_to_vendors( $pages ) {
		$pages[] = $this->plugin_page;

		return $pages;
	}

	/**
	 * Get vendors capabilities
	 *
	 * @return string
	 */
	protected function get_vendors_capabilities() {
		return YITH_Vendors()->admin->get_special_cap();
	}

	/**
	 * Add page to menu
	 */
	public function add_page_to_menu() {
		if ( 'yes' === get_option( 'yith_wpv_vendors_option_bulk_product_editing_options_management', 'no' ) && $this->is_vendor() ) {
			add_menu_page( 'yith_wc_bulk_product_editing', 'Bulk Product Editing', $this->get_vendors_capabilities(), $this->plugin_page, null, 'dashicons-forms' );
		}
	}

	/**
	 * Check if vendor cannot manage bulk.
	 */
	public function check_vendor_cant_manage_bulk() {
		global $pagenow;

		if ( $this->is_vendor() ) {
			$vendor_cant_manage_bulk_product_editing = 'no' === get_option( 'yith_wpv_vendors_option_bulk_product_editing_options_management', 'no' );
			$is_bulk_editing_page                    = 'admin.php' === $pagenow && ! empty( $_GET['page'] ) && 'yith_wcbep_panel' === $_GET['page']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( $vendor_cant_manage_bulk_product_editing && $is_bulk_editing_page ) {
				wp_die( esc_html__( 'Permission denied!', 'yith-woocommerce-bulk-product-editing' ) );
			}
		}
	}

	/**
	 * Return true if the current user is vendor
	 *
	 * @return bool
	 */
	public function is_vendor() {
		$vendor = yith_get_vendor( 'current', 'user' );

		return $vendor->is_valid() && $vendor->has_limited_access();
	}

	/**
	 * Add vendor tax in query args
	 * Vendors can view only their products
	 *
	 * @param array $query_args Query args.
	 *
	 * @return array
	 */
	public function add_vendor_tax_in_query_args( $query_args ) {
		if ( function_exists( 'yith_get_vendor' ) && class_exists( 'YITH_Vendor' ) ) {
			$vendor = yith_get_vendor( 'current', 'user' );
			if ( $vendor->is_valid() && $vendor->has_limited_access() ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => YITH_Vendor::$taxonomy,
					'field'    => 'id',
					'terms'    => $this->get_vendor_id( $vendor ),
				);
			}
		}

		return $query_args;
	}

	/**
	 * Get the vendor ID
	 *
	 * @param YITH_Vendor $vendor The vendor.
	 *
	 * @return int|false
	 */
	protected function get_vendor_id( $vendor ) {
		return method_exists( $vendor, 'get_id' ) ? $vendor->get_id() : ( $vendor->id ?? false );
	}

	/**
	 * Remove tabs for vendors.
	 *
	 * @param array $tabs The tabs.
	 *
	 * @return array
	 */
	public function remove_tabs_for_vendors( $tabs ) {
		if ( $this->is_vendor() ) {
			$tabs = array( 'bulk-edit' => $tabs['bulk-edit'] );
		}

		return $tabs;
	}

	/**
	 * Remove Vendor in custom taxonomies.
	 *
	 * @param array $taxonomies The taxonomies.
	 *
	 * @return array
	 */
	public function remove_vendor_in_custom_taxonomies( $taxonomies ) {
		if ( $this->is_vendor() ) {
			$taxonomies = array_diff( $taxonomies, array( YITH_Vendor::$taxonomy ) );
		}

		return $taxonomies;
	}

}
