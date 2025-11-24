<?php
/**
 * Class responsible for handling ajax calls on the admin for Pre-Orders
 *
 * WooCommerce Pre-Orders
 *
 * @package   WC_Pre_Orders/Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Pre-Orders Admin class.
 */
class WC_Pre_Orders_Admin_Ajax {

	public function __construct() {
		//Adds validation to make sure only one pre-order product is added to order
		add_action( 'woocommerce_ajax_add_order_item_validation', array( $this, 'can_add_product_to_order' ), 10, 4 );

		//Adds fees to pre-order items when creating order from admin
		add_action( 'woocommerce_ajax_order_items_added', array( $this, 'maybe_add_pre_order_fee_admin' ), 10, 2 );

		//Remove fees from order when removing a pre-order item from admin
		add_action( 'woocommerce_before_delete_order_item', array( $this, 'maybe_remove_pre_order_fee_admin' ), 10, 1 );

		//Remove fees from order when removing a pre-order item from admin
		add_action( 'woocommerce_order_before_calculate_totals', array( $this, 'maybe_adjust_pre_order_fee_admin' ), 10, 2 );

		//Add handlers for products list features
		add_action( 'wp_ajax_wc_pre_orders_toggle_product', array( $this, 'ajax_toggle_pre_order' ) );
		add_action( 'wp_ajax_wc_pre_orders_get_product_settings', array( $this, 'ajax_get_product_settings' ) );
	}

	/**
	 * Adds validation to make sure only one pre-order product is added to an order
	 * @param WP_Error $validation_error
	 * @param WC_Product $product
	 * @param WC_Order $order
	 * @param int $qty
	 *
	 * @return WP_Error
	 */
	public function can_add_product_to_order( $validation_error, $product, $order, $qty ) {
		$items                      = $order->get_items();
		$is_added_product_pre_order = WC_Pre_Orders_Product::product_can_be_pre_ordered( $product );

		foreach ( $items as $item ) {
			if ( ! WC_Pre_Orders_Product::product_can_be_pre_ordered( $item->get_product() ) && ! $is_added_product_pre_order ) {
				continue;
			}

			if ( $item->get_product()->get_id() === $product->get_id() ) {
				$validation_error->add( 'multiple-pre-order-products', __( "You can't add multiple products on a pre-order. Change the quantity of the item instead of adding more items.", 'woocommerce-pre-orders' ) );
				break;
			}

			if ( $item->get_product()->get_id() !== $product->get_id() ) {
				$validation_error->add( 'multiple-pre-order-products', __( "You can't add multiple products on a pre-order", 'woocommerce-pre-orders' ) );
				break;
			}
		}

		return $validation_error;
	}

	/**
	 * Add pre-order fee when a pre-order product is added
	 *
	 * @param WC_Order_Item[] $added_items
	 * @param WC_Order $order
	 *
	 * @since 1.6.0
	 */
	public function maybe_add_pre_order_fee_admin( $added_items, $order ) {
		$wc_pre_order_cart = new WC_Pre_Orders_Cart();

		foreach ( $added_items as $item_id => $item ) {
			$fee = $wc_pre_order_cart->generate_fee( $item->get_product() );

			if ( ! $fee ) {
				continue;
			}

			$item_fee = new WC_Order_Item_Fee();
			$item_fee->set_name( $fee['label'] );
			$item_fee->set_tax_status( $fee['tax_status'] );
			$item_fee->set_total( $fee['amount'] * $item->get_quantity() );
			$item_fee->add_meta_data( 'pre_order_parent_item_id', $item_id, true );
			$item_fee->save();

			$order->add_item( $item_fee );
		}

		$order->save();
	}

	/**
	 * Removes pre-order fees from the order when the pre-order product is removed
	 *
	 * @param int $item_id
	 *
	 * @since 1.6.0
	 */
	public function maybe_remove_pre_order_fee_admin( $item_id ) {

		$item = WC_Order_Factory::get_order_item( absint( $item_id ) );

		if ( ! $item || 'line_item' !== $item->get_type() || ! WC_Pre_Orders_Product::product_can_be_pre_ordered( $item->get_product() ) ) {
			return;
		}

		$order = $item->get_order();
		$fees  = $order->get_fees();

		foreach ( $fees as $fee_id => $fee ) {
			if ( $item_id === (int) $fee->get_meta( 'pre_order_parent_item_id', true ) ) {
				$order->remove_item( $fee_id );
				$order->save();

				return;
			}
		}
	}

	/**
	 * Adjusts pre-order fees when product quantity changes
	 *
	 * @param bool $and_taxes
	 * @param WC_Order $order
	 */
	public function maybe_adjust_pre_order_fee_admin( $and_taxes, $order ) {

		$items = $order->get_items();

		foreach ( $items as $item ) {
			if ( WC_Pre_Orders_Product::product_can_be_pre_ordered( $item->get_product() ) ) {
				foreach ( $order->get_fees() as $item_fee ) {
					if ( $item->get_id() === (int) $item_fee->get_meta( 'pre_order_parent_item_id' ) ) {
						$wc_pre_order_cart = new WC_Pre_Orders_Cart();
						$fee               = $wc_pre_order_cart->generate_fee( $item->get_product() );

						$item_fee->set_total( $fee['amount'] * $item->get_quantity() );
						break;
					}
				}
			}
		}
	}

	/**
	 * AJAX handler for getting product pre-order settings.
	 *
	 * @since 2.3.0
	 */
	public function ajax_get_product_settings() {
		check_ajax_referer( 'wc_pre_orders_products_list', 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'woocommerce-pre-orders' ) ), 403 );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$product    = wc_get_product( $product_id );

		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'woocommerce-pre-orders' ) ), 400 );
		}

		$availability_timestamp = get_post_meta( $product_id, '_wc_pre_orders_availability_datetime', true );
		$availability_date      = '';

		if ( $availability_timestamp ) {
			$availability_date = gmdate( 'Y-m-d\TH:i', $availability_timestamp );
		}

		wp_send_json_success(
			array(
				'enabled'        => WC_Pre_Orders_Product::product_can_be_pre_ordered( $product ) ? 'yes' : 'no',
				'availability'   => $availability_date,
				'fee'            => get_post_meta( $product_id, '_wc_pre_orders_fee', true ),
				'when_to_charge' => get_post_meta( $product_id, '_wc_pre_orders_when_to_charge', true ),
			)
		);
	}

	/**
	 * AJAX handler for toggling pre-order status.
	 *
	 * @since 2.3.0
	 */
	public function ajax_toggle_pre_order() {
		check_ajax_referer( 'wc_pre_orders_products_list', 'security' );

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'woocommerce-pre-orders' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$product_id     = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$enable         = isset( $_POST['enable'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['enable'] ) );
		$availability   = isset( $_POST['availability_date'] ) ? sanitize_text_field( wp_unslash( $_POST['availability_date'] ) ) : '';
		$fee            = isset( $_POST['fee'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['fee'] ) ) ) : '';
		$when_to_charge = isset( $_POST['when_to_charge'] ) ? sanitize_text_field( wp_unslash( $_POST['when_to_charge'] ) ) : 'upfront';

		$product = wc_get_product( $product_id );

		if ( ! $product || ! WC_Pre_Orders_Product::product_supports_pre_order_feature( $product ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid product.', 'woocommerce-pre-orders' ) ) );
		}

		// Check for active pre-orders when disabling.
		if ( ! $enable && WC_Pre_Orders_Product::product_has_active_pre_orders( $product ) ) {
			wp_send_json_error( array( 'message' => __( 'Cannot disable pre-orders for products with active pre-orders.', 'woocommerce-pre-orders' ) ) );
		}

		// Get current settings before update
		$current_enabled        = get_post_meta( $product_id, '_wc_pre_orders_enabled', true );
		$current_availability   = get_post_meta( $product_id, '_wc_pre_orders_availability_datetime', true );
		$current_fee            = get_post_meta( $product_id, '_wc_pre_orders_fee', true );
		$current_when_to_charge = get_post_meta( $product_id, '_wc_pre_orders_when_to_charge', true );

		// Update pre-order settings
		update_post_meta( $product_id, '_wc_pre_orders_enabled', $enable ? 'yes' : 'no' );

		if ( $enable ) {
			if ( $availability ) {
				if ( ! class_exists( 'WC_Pre_Orders_Admin_Products' ) ) {
					include_once __DIR__ . '/class-wc-pre-orders-admin-products.php';
				}
				WC_Pre_Orders_Admin_Products::save_availability_date_time( $product_id, $availability );
			}

			update_post_meta( $product_id, '_wc_pre_orders_fee', $fee );
			update_post_meta( $product_id, '_wc_pre_orders_when_to_charge', $when_to_charge );
		}

		// Check if settings actually change
		$settings_changed = false;

		if ( ( $enable ? 'yes' : 'no' ) !== $current_enabled ) {
			$settings_changed = true;
		} elseif ( $enable ) {
			if ( $availability && strtotime( $availability ) !== $current_availability ) {
				$settings_changed = true;
			} elseif ( $current_fee !== $fee ) {
				$settings_changed = true;
			} elseif ( $current_when_to_charge !== $when_to_charge ) {
				$settings_changed = true;
			}
		}

		// Clear caches when pre-order settings change
		if ( $settings_changed ) {
			if ( class_exists( 'WC_Pre_Orders_Data_Store' ) && method_exists( 'WC_Pre_Orders_Data_Store', 'clear_caches' ) ) {
				WC_Pre_Orders_Data_Store::clear_caches();
			}
		}

		wp_send_json_success(
			array(
				'message' => $settings_changed
					? ( $enable ? __( 'Pre-orders enabled.', 'woocommerce-pre-orders' ) : __( 'Pre-orders disabled.', 'woocommerce-pre-orders' ) )
					: __( 'No changes were made.', 'woocommerce-pre-orders' ),
			)
		);
	}
}


new WC_Pre_Orders_Admin_Ajax();
