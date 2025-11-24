<?php
/**
 * WooCommerce Pre-Orders
 * Products list page customizations.
 *
 * @package   WC_Pre_Orders/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Pre-Orders Admin Products List class.
 *
 * Adds pre-order customizations to the WooCommerce products list.
 *
 * @since 2.3.0
 */
class WC_Pre_Orders_Admin_Product_List_Customizations {

	/**
	 * Initialize the products list customizations actions.
	 *
	 * @since 2.3.0
	 */
	public function __construct() {
		// Add pre-orders column to products list.
		add_filter( 'manage_product_posts_columns', array( $this, 'add_pre_order_column' ), 20 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_pre_order_column' ), 10, 2 );

		// Add pre-orders filter dropdown.
		add_action( 'restrict_manage_posts', array( $this, 'add_pre_order_filter_dropdown' ) );
		add_filter( 'request', array( $this, 'filter_products_by_pre_order_status' ) );

		// Add pre-orders bulk actions.
		add_filter( 'bulk_actions-edit-product', array( $this, 'add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-product', array( $this, 'handle_bulk_actions' ), 10, 3 );

		// Add scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add admin notices.
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

		// Add modal to the footer of the page
		add_action( 'admin_footer', array( $this, 'add_modal_view_to_footer' ) );
	}

	/**
	 * Add modal to the footer of the page
	 *
	 * @since 2.3.0
	 */
	public function add_modal_view_to_footer() {
		include WC_PRE_ORDERS_PLUGIN_PATH . '/includes/admin/views/html-wc-pre-orders-product-list-modal.php';
	}

	/**
	 * Add PO column to product list.
	 *
	 * @since 2.3.0
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function add_pre_order_column( $columns ): array {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;

			// Add pre-order column after price, after this comes taxonomies.
			if ( 'price' === $key ) {
				$new_columns['pre_order'] = __( 'Pre-orders', 'woocommerce-pre-orders' );
			}
		}

		return $new_columns;
	}

	/**
	 * Render PO column content.
	 *
	 * @since 2.3.0
	 * @param string $column Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_pre_order_column( $column, $post_id ) {
		if ( 'pre_order' !== $column ) {
			return;
		}

		$product = wc_get_product( $post_id );
		if ( ! $product ) {
			return;
		}

		// Check if product supports pre-order feature
		if ( ! WC_Pre_Orders_Product::product_supports_pre_order_feature( $product ) ) {
			echo '<span class="na" title="' . esc_attr__( 'Only simple, variable, composite, bundle, booking, mix-and-match, and subscription products (with compatible features) can be pre-ordered.', 'woocommerce-pre-orders' ) . '">N/A</span>';
			return;
		}

		// Toggle switch for enabling/disabling pre-orders.
		if ( WC_Pre_Orders_Product::product_has_active_pre_orders( $product ) ) {
			$disabled_attr = 'disabled';
			$disabled_tip  = __( 'Cannot toggle - product has active pre-orders', 'woocommerce-pre-orders' );
		} else {
			$disabled_attr = '';
			$disabled_tip  = '';
		}

		// If product has pre-orders add a link to pre-orders list page.
		if ( WC_Pre_Orders_Product::product_has_pre_orders( $product ) ) {
			$pre_orders_url_product_filter = add_query_arg(
				array(
					'page'        => 'wc_pre_orders',
					'tab'         => 'all-pre-orders',
					'_product_id' => $post_id,
				),
				admin_url( 'admin.php' )
			);

			$pre_orders_url_product_filter_link = sprintf(
				'<span class="view-orders"><a href="%s" target="_blank">%s</a></span>',
				esc_url( $pre_orders_url_product_filter ),
				esc_html__( 'View pre-orders', 'woocommerce-pre-orders' )
			);
		} else {
			// Show no pre orders without link
			$pre_orders_url_product_filter_link = sprintf( '<span class="view-orders na">%s</span>', esc_html__( 'No pre-orders', 'woocommerce-pre-orders' ) );
		}

		$is_enabled = WC_Pre_Orders_Product::product_can_be_pre_ordered( $product );
		printf(
			'<div class="pre-order-toggle-wrapper" title="%s">
				<label class="switch">
					<input type="checkbox" class="pre-order-toggle" %s %s data-product-id="%d" data-enabled="%s">
					<span class="slider"></span>
				</label>
				%s
			</div>',
			esc_attr( $disabled_tip ),
			checked( $is_enabled, true, false ),
			esc_attr( $disabled_attr ),
			esc_attr( $post_id ),
			esc_attr( $is_enabled ? 'yes' : 'no' ),
			wp_kses_post( $pre_orders_url_product_filter_link )
		);
	}

	/**
	 * Add pre-order filter dropdown.
	 *
	 * @since 2.3.0
	 */
	public function add_pre_order_filter_dropdown() {
		global $typenow;

		if ( 'product' !== $typenow ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected = isset( $_GET['pre_order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pre_order_status'] ) ) : '';
		?>
		<select name="pre_order_status" id="dropdown_pre_order_status">
			<option value=""><?php esc_html_e( 'All pre-order statuses', 'woocommerce-pre-orders' ); ?></option>
			<option value="enabled" <?php selected( $selected, 'enabled' ); ?>><?php esc_html_e( 'Pre-orders enabled', 'woocommerce-pre-orders' ); ?></option>
			<option value="disabled" <?php selected( $selected, 'disabled' ); ?>><?php esc_html_e( 'Pre-orders disabled', 'woocommerce-pre-orders' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Filter products by pre-order status.
	 *
	 * @since 2.3.0
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function filter_products_by_pre_order_status( $vars ): array {
		global $typenow;

		if ( 'product' !== $typenow ) {
			return $vars;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['pre_order_status'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$status = sanitize_text_field( wp_unslash( $_GET['pre_order_status'] ) );

			if ( ! isset( $vars['meta_query'] ) ) {
				$vars['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			if ( 'enabled' === $status ) {
				$vars['meta_query'][] = array(
					'key'   => '_wc_pre_orders_enabled',
					'value' => 'yes',
				);
			} elseif ( 'disabled' === $status ) {
				$vars['meta_query'][] = array(
					'relation' => 'OR',
					array(
						'key'     => '_wc_pre_orders_enabled',
						'value'   => 'yes',
						'compare' => '!=',
					),
					array(
						'key'     => '_wc_pre_orders_enabled',
						'compare' => 'NOT EXISTS',
					),
				);
			}
		}

		return $vars;
	}

	/**
	 * Add bulk actions.
	 *
	 * @since 2.3.0
	 * @param array $actions Existing actions.
	 * @return array Modified actions.
	 */
	public function add_bulk_actions( $actions ): array {
		$actions['enable_pre_orders']  = __( 'Enable pre-orders', 'woocommerce-pre-orders' );
		$actions['disable_pre_orders'] = __( 'Disable pre-orders', 'woocommerce-pre-orders' );

		return $actions;
	}

	/**
	 * Handle bulk actions.
	 *
	 * @since 2.3.0
	 * @param string $redirect_to Redirect URL.
	 * @param string $action Action name.
	 * @param array  $post_ids Post IDs.
	 * @return string Redirect URL.
	 */
	public function handle_bulk_actions( $redirect_to, $action, $post_ids ): string {
		if ( 'enable_pre_orders' !== $action && 'disable_pre_orders' !== $action ) {
			return $redirect_to;
		}

		$changed = 0;
		$enable  = 'enable_pre_orders' === $action;

		foreach ( $post_ids as $post_id ) {
			$product = wc_get_product( $post_id );

			if ( ! $product || ! WC_Pre_Orders_Product::product_supports_pre_order_feature( $product ) ) {
				continue;
			}

			// Skip products with active pre-orders when disabling.
			if ( ! $enable && WC_Pre_Orders_Product::product_has_active_pre_orders( $product ) ) {
				continue;
			}

			update_post_meta( $post_id, '_wc_pre_orders_enabled', $enable ? 'yes' : 'no' );
			++$changed;
		}

		$redirect_to = add_query_arg(
			array(
				'pre_orders_bulk_action' => $enable ? 'enabled' : 'disabled',
				'changed'                => $changed,
			),
			$redirect_to
		);

		return $redirect_to;
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.3.0
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'edit.php' !== $hook || ! isset( $_GET['post_type'] ) || 'product' !== $_GET['post_type'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		global $wc_pre_orders;

		// Enqueue jQuery UI dependencies for datetimepicker.
		wp_enqueue_script( 'jquery-ui-datepicker' );

		// Enqueue timepicker addon.
		wp_enqueue_script(
			'jquery-ui-timepicker-addon',
			WC_PRE_ORDERS_PLUGIN_URL . '/src/jquery-ui-timepicker-addon/jquery-ui-timepicker-addon.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			WC_PRE_ORDERS_VERSION,
			true
		);

		// Enqueue main admin styles.
		wp_enqueue_style( 'wc_pre_orders_admin', $wc_pre_orders->get_plugin_url() . '/build/admin/wc-pre-orders-admin.css', array(), WC_PRE_ORDERS_VERSION );

		// Enqueue product list specific CSS.
		$products_list_css_path    = WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-product-list-css.css';
		$products_list_css_version = file_exists( $products_list_css_path ) ? filemtime( $products_list_css_path ) : WC_PRE_ORDERS_VERSION;
		wp_enqueue_style( 'wc_pre_orders_product_list', WC_PRE_ORDERS_PLUGIN_URL . '/build/admin/wc-pre-orders-product-list-css.css', array(), $products_list_css_version );

		// Enqueue product list script.
		$script_url        = WC_PRE_ORDERS_PLUGIN_URL . '/build/admin/wc-pre-orders-product-list.js';
		$script_asset_path = WC_PRE_ORDERS_PLUGIN_PATH . '/build/admin/wc-pre-orders-product-list.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-timepicker-addon' ),
				'version'      => WC_PRE_ORDERS_VERSION,
			);

		wp_register_script( 'wc_pre_orders_products_list', $script_url, $script_asset['dependencies'], $script_asset['version'], true );

		// Internationalization for JavaScript.
		wp_localize_script(
			'wc_pre_orders_products_list',
			'wcPreOrdersProductsList',
			array(
				'nonce'               => wp_create_nonce( 'wc_pre_orders_products_list' ),
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'currency_symbol'     => get_woocommerce_currency_symbol(),
				'currency_position'   => get_option( 'woocommerce_currency_pos' ),
				'datepicker_timezone' => wc_timezone_string(),
				'i18n'                => array(
					'modal_title_enable'     => __( 'Enable Pre-orders', 'woocommerce-pre-orders' ),
					'modal_title_disable'    => __( 'Disable Pre-orders', 'woocommerce-pre-orders' ),
					'modal_title_bulk'       => __( 'Manage Pre-orders', 'woocommerce-pre-orders' ),
					'confirm_disable'        => __( 'Are you sure you want to disable pre-orders for this product?', 'woocommerce-pre-orders' ),
					'confirm_disable_bulk'   => __( 'Are you sure you want to disable pre-orders for the selected products? Products with active pre-orders will be skipped.', 'woocommerce-pre-orders' ),
					'availability_label'     => __( 'Release date (optional)', 'woocommerce-pre-orders' ),
					'fee_label'              => __( 'Pre-order fee (optional)', 'woocommerce-pre-orders' ),
					'when_to_charge_label'   => __( 'Customers will be charged', 'woocommerce-pre-orders' ),
					'charge_upfront'         => __( 'Upfront (pay now)', 'woocommerce-pre-orders' ),
					'charge_upon_release'    => __( 'Upon release (pay later)', 'woocommerce-pre-orders' ),
					'enable_button'          => __( 'Enable pre-orders', 'woocommerce-pre-orders' ),
					'disable_button'         => __( 'Disable pre-orders', 'woocommerce-pre-orders' ),
					'cancel_button'          => __( 'Cancel', 'woocommerce-pre-orders' ),
					'processing'             => __( 'Processing...', 'woocommerce-pre-orders' ),
					'error_generic'          => __( 'An error occurred. Please try again.', 'woocommerce-pre-orders' ),
					'enabled_text'           => __( 'Enabled pre-orders', 'woocommerce-pre-orders' ),
					'disabled_text'          => __( 'Disabled pre-orders', 'woocommerce-pre-orders' ),
					'active_pre_orders_note' => __( 'Products with active pre-orders cannot be modified and will be skipped.', 'woocommerce-pre-orders' ),
				),
			)
		);

		wp_enqueue_script( 'wc_pre_orders_products_list' );
	}

	/**
	 * Display admin notices.
	 *
	 * @since 2.3.0
	 */
	public function display_admin_notices() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['pre_orders_bulk_action'] ) ) {
			return;
		}

		$action  = sanitize_text_field( wp_unslash( $_GET['pre_orders_bulk_action'] ) );
		$changed = isset( $_GET['changed'] ) ? absint( wp_unslash( $_GET['changed'] ) ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! $changed ) {
			return;
		}

		$message = '';

		if ( 'enabled' === $action ) {
			/* translators: %d: number of products */
			$message  = sprintf( _n( 'Pre-orders enabled for %d product.', 'Pre-orders enabled for %d products.', $changed, 'woocommerce-pre-orders' ), $changed );
			$message .= ' ' . __( 'Default settings applied: no availability date, no fee, and charge upon release. You can edit individual products to make changes.', 'woocommerce-pre-orders' );
		} elseif ( 'disabled' === $action ) {
			/* translators: %d: number of products */
			$message = sprintf( _n( 'Pre-orders disabled for %d product.', 'Pre-orders disabled for %d products.', $changed, 'woocommerce-pre-orders' ), $changed );
		}

		if ( $message ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}
	}
}
