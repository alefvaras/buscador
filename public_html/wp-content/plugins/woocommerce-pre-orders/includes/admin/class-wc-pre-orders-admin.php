<?php
/**
 * WooCommerce Pre-Orders
 *
 * @package   WC_Pre_Orders/Admin
 * @author    WooThemes
 * @copyright Copyright (c) 2015, WooThemes
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Pre-Orders Admin class.
 */
class WC_Pre_Orders_Admin {

	/**
	 * Setup admin class.
	 */
	public function __construct() {
		// Maybe register taxonomies and add admin options
		add_action( 'admin_init', array( $this, 'maybe_install' ), 6 );

		// Load necessary admin styles / scripts (after giving woocommerce a chance to register their scripts so we can make use of them).
		add_filter( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ), 15 );

		// Admin classes.
		$this->includes();
	}

	/**
	 * Includes.
	 */
	protected function includes() {
		require_once 'class-wc-pre-orders-admin-pre-orders.php';
		require_once 'class-wc-pre-orders-admin-orders.php';
		require_once 'class-wc-pre-orders-admin-products.php';
		require_once 'class-wc-pre-orders-admin-settings.php';
	}

	/**
	 * Set installed option and default settings / terms.
	 */
	public function maybe_install() {
		global $woocommerce;

		$installed_version = get_option( 'wc_pre_orders_version' );

		// Install.
		if ( ! $installed_version ) {

			$admin_settings = new WC_Pre_Orders_Admin_Settings();

			// Install default settings.
			foreach ( $admin_settings->get_settings() as $setting ) {

				if ( isset( $setting['default'] ) ) {
					update_option( $setting['id'], $setting['default'] );
				}
			}
		}

		// Upgrade - installed version lower than plugin version?
		if ( -1 === version_compare( $installed_version, WC_PRE_ORDERS_VERSION ) ) {

			// New version number.
			update_option( 'wc_pre_orders_version', WC_PRE_ORDERS_VERSION );
		}
	}

	/**
	 * Add Pre-orders screen to woocommerce_screen_ids.
	 *
	 * @param  array $ids
	 *
	 * @return array
	 */
	public function screen_ids( $ids ) {
		$ids[] = 'woocommerce_page_wc_pre_orders';

		return $ids;
	}

	/**
	 * Load admin styles & scripts only on needed pages.
	 *
	 * @param string $hook_suffix the menu/page identifier
	 */
	public function load_styles_scripts( $hook_suffix ) {
		global $wc_pre_orders, $wp_scripts;

		// Only load on settings / order / product pages.
		if ( 'woocommerce_page_wc-orders' === $hook_suffix || 'woocommerce_page_wc_pre_orders' === $hook_suffix || 'edit.php' === $hook_suffix || 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) {
			$admin_css_path    = $wc_pre_orders->get_plugin_path() . '/build/admin/wc-pre-orders-admin.css';
			$admin_css_version = file_exists( $admin_css_path ) ? filemtime( $admin_css_path ) : WC_PRE_ORDERS_VERSION;
			wp_enqueue_style( 'wc_pre_orders_admin', $wc_pre_orders->get_plugin_url() . '/build/admin/wc-pre-orders-admin.css', array(), $admin_css_version );

			$script_url        = WC_PRE_ORDERS_PLUGIN_URL . '/build/admin/wc-pre-orders-admin.js';
			$script_asset_path = WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-admin.asset.php';
			$script_asset      = file_exists( $script_asset_path )
				? require $script_asset_path
				: array(
					'dependencies' => array(),
					'version'      => file_exists( WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-admin.js' ) ? filemtime( WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-admin.js' ) : WC_PRE_ORDERS_VERSION,
				);

			$script_data = array(
				'datepickerTimezone'                       => (int) ( (float) get_option( 'gmt_offset' ) * 60 ), // WP stores timezone as hours, the timepicker uses minutes.
				'is_subscriptions_supported'               => WC_Pre_Orders_Compat_Subscriptions::is_subscriptions_supported(),
				'is_subscriptions_synchronization_supported' => WC_Pre_Orders_Compat_Subscriptions::is_subscriptions_feature_supported( 'synchronized-subscriptions' ),
				'is_subscriptions_trial_periods_supported' => WC_Pre_Orders_Compat_Subscriptions::is_subscriptions_feature_supported( 'trial-periods' ),
			);

			/**
			 * Filters the data passed to the WC Pre-Orders admin script.
			 *
			 * @since 2.2.0
			 *
			 * @param array  $script_data  The data passed to the WC Pre-Orders admin script.
			 * @param string $hook_suffix  The current admin page hook suffix.
			 */
			$script_data = apply_filters( 'wc_pre_orders_admin_script_data', $script_data, $hook_suffix );

			wp_register_script( 'wc_pre_orders_admin', $script_url, $script_asset['dependencies'], $script_asset['version'], true );
			wp_add_inline_script(
				'wc_pre_orders_admin',
				'WC_PRE_ORDERS_ADMIN = ' . wp_json_encode( $script_data ) . ';',
				'before'
			);
			wp_enqueue_script( 'wc_pre_orders_admin' );

			// Only enqueue product pre-orders tab script on product edit pages
			if ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix ) {
				$product_tab_script_url        = WC_PRE_ORDERS_PLUGIN_URL . '/build/admin/wc-pre-orders-product-tab.js';
				$product_tab_script_asset_path = WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-product-tab.asset.php';

				$product_tab_script_asset = array(
					'dependencies' => array(),
					'version'      => WC_PRE_ORDERS_VERSION,
				);

				if ( file_exists( $product_tab_script_asset_path ) ) {
					$asset_data = include $product_tab_script_asset_path;
					if ( is_array( $asset_data ) ) {
						$product_tab_script_asset = $asset_data;
					}
				}

				wp_enqueue_script(
					'wc_pre_orders_product_tab',
					$product_tab_script_url,
					$product_tab_script_asset['dependencies'],
					$product_tab_script_asset['version'],
					true
				);

				// Localize script with payment timing messages
				wp_localize_script(
					'wc_pre_orders_product_tab',
					'wcPreOrdersMessages',
					$this->get_payment_timing_messages()
				);
			}

			// Only load list script on the pre-orders list page
			if ( 'woocommerce_page_wc_pre_orders' === $hook_suffix ) {
				// Enqueue pre-orders list specific CSS
				$list_css_path    = WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-list-css.css';
				$list_css_version = file_exists( $list_css_path ) ? filemtime( $list_css_path ) : WC_PRE_ORDERS_VERSION;
				wp_enqueue_style( 'wc_pre_orders_list', WC_PRE_ORDERS_PLUGIN_URL . '/build/admin/wc-pre-orders-list-css.css', array(), $list_css_version );

				$script_url_list        = WC_PRE_ORDERS_PLUGIN_URL . '/build/admin/wc-pre-orders-list.js';
				$script_asset_path_list = WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-list.asset.php';

				// Check if asset file exists and is readable
				if ( file_exists( $script_asset_path_list ) && is_readable( $script_asset_path_list ) ) {
					$script_asset_list = require $script_asset_path_list;
				} else {
					// Fallback to default asset configuration
					$script_asset_list = array(
						'dependencies' => array(),
						'version'      => file_exists( WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-list.js' ) ? filemtime( WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-list.js' ) : WC_PRE_ORDERS_VERSION,
					);
				}

				// Register and enqueue the list script
				wp_register_script( 'wc_pre_orders_list', $script_url_list, $script_asset_list['dependencies'], $script_asset_list['version'], true );

				// Localize the script
				$list_script_data = array(
					'modal_email_desc_single'    => __( 'Send an email to the customer', 'woocommerce-pre-orders' ),
					/* translators: %d: number of customers */
					'modal_email_desc_bulk'      => __( 'Send an email to %d customers', 'woocommerce-pre-orders' ),
					'modal_email_btn_single'     => __( 'Send email', 'woocommerce-pre-orders' ),
					/* translators: %d: number of customers */
					'modal_email_btn_bulk'       => __( 'Send email to %d customers', 'woocommerce-pre-orders' ),
					'modal_email_note_bulk'      => __( 'Note that only active pre-orders customers will receive the email.', 'woocommerce-pre-orders' ),
					'modal_complete_desc_single' => __( 'Are you sure you want to complete this pre-order? This action will notify the customer.', 'woocommerce-pre-orders' ),
					/* translators: %d: number of pre-orders */
					'modal_complete_desc_bulk'   => __( 'Are you sure you want to complete %d pre-orders? This action will notify the customer.', 'woocommerce-pre-orders' ),
					'modal_complete_btn_single'  => __( 'Complete pre-order', 'woocommerce-pre-orders' ),
					/* translators: %d: number of pre-orders */
					'modal_complete_btn_bulk'    => __( 'Complete %d pre-orders', 'woocommerce-pre-orders' ),
					'modal_cancel_desc_single'   => __( 'Are you sure you want to cancel this pre-order? This action will notify the customer.', 'woocommerce-pre-orders' ),
					/* translators: %d: number of pre-orders */
					'modal_cancel_desc_bulk'     => __( 'Are you sure you want to cancel %d pre-orders? This action will notify the customer.', 'woocommerce-pre-orders' ),
					'modal_cancel_btn_single'    => __( 'Cancel pre-order', 'woocommerce-pre-orders' ),
					/* translators: %d: number of pre-orders */
					'modal_cancel_btn_bulk'      => __( 'Cancel %d pre-orders', 'woocommerce-pre-orders' ),
					'modal_select_at_least_one'  => __( 'Please select at least one pre-order to apply bulk actions.', 'woocommerce-pre-orders' ),
				);

				wp_localize_script( 'wc_pre_orders_list', 'wcPreOrdersListVars', $list_script_data );
				wp_enqueue_script( 'wc_pre_orders_list' );
			}

			// Load jQuery UI Date/TimePicker on new/edit product page and pre-orders > actions page
			if ( 'post.php' === $hook_suffix || 'post-new.php' === $hook_suffix || 'woocommerce_page_wc_pre_orders' === $hook_suffix || 'edit.php' === $hook_suffix && isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] || 'woocommerce_page_wc-pre-orders-products' === $hook_suffix ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Get loaded jQuery version
				$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.8.2';

				// Load jQuery UI CSS while respecting loaded jQuery version
				wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' ); // phpcs:ignore PluginCheck.CodeAnalysis.EnqueuedResourceOffloading.OffloadedContent, WordPress.WP.EnqueuedResourceParameters.MissingVersion

				// Load TimePicker add-on which extends jQuery DatePicker
				wp_enqueue_script( 'jquery_ui_timepicker', $wc_pre_orders->get_plugin_url() . '/build/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker' ), '1.2', true );
			}
		}
	}

	/**
	 * Get payment timing messages for JavaScript.
	 *
	 * @return array
	 */
	public function get_payment_timing_messages() {
		return array(
			'upfront'     => array(
				'title'   => esc_html__( 'Upfront (pay now):', 'woocommerce-pre-orders' ),
				'message' => esc_html__( 'Customers will be charged at the time of checkout.', 'woocommerce-pre-orders' ),
			),
			'uponRelease' => array(
				'title'   => esc_html__( 'Upon release (pay later):', 'woocommerce-pre-orders' ),
				'message' => esc_html__( 'Customers will be charged when the product becomes available.', 'woocommerce-pre-orders' ),
			),
		);
	}
}

new WC_Pre_Orders_Admin();
