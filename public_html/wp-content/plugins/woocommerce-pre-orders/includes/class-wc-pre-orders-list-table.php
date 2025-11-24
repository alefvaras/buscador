<?php
/**
 * WooCommerce Pre-Orders
 *
 * @package     WC_Pre_Orders/List-Table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Pre-Orders List Table class
 *
 * Extends WP_List_Table to display pre-orders and related information
 *
 * @since 1.0
 * @extends \WP_List_Table
 */
class WC_Pre_Orders_List_Table extends WP_List_Table {

	private $message_transient_prefix = '_wc_pre_orders_messages_';

	/**
	 * Set of available views can include (All, Active, Completed, Cancelled, Trash)
	 *
	 * @var array
	 */
	private $views;

	/**
	 * Flag if there are filters applied.
	 *
	 * @var bool
	 */
	private $has_filters_applied = false;

	/**
	 * Setup list table
	 *
	 * @see WP_List_Table::__construct()
	 * @since 1.0
	 * @return \WC_Pre_Orders_List_Table
	 */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'Pre-order', 'woocommerce-pre-orders' ),
				'plural'   => __( 'Pre-orders', 'woocommerce-pre-orders' ),
				'ajax'     => false,
			)
		);

		$this->message_transient_prefix = '_wc_pre_orders_messages_';

		add_action( 'admin_footer', array( $this, 'add_modal_html' ) );
	}

	/**
	 * Add modal HTML to footer
	 */
	public function add_modal_html() {
		?>
		<!-- Send Email Modal -->
		<div id="customer-email-modal" class="wc-po-modal">
			<div class="wc-po-modal-content">
				<h2><?php esc_html_e( 'Send customer email', 'woocommerce-pre-orders' ); ?></h2>
				<p id="customer-email-desc"></p>
				<div class="email-note-container">
					<textarea id="email-note" placeholder="<?php esc_attr_e( 'Enter note to include in email', 'woocommerce-pre-orders' ); ?>" required></textarea>
					<p class="validation-message" style="display: none; color: #d63638; margin-top: 5px;"><?php esc_html_e( 'Please enter a message to send to the customer', 'woocommerce-pre-orders' ); ?></p>
				</div>
				<div class="wc-po-modal-buttons">
					<button class="button" id="confirm-email-btn" disabled></button>
					<button class="button button-primary cancel-modal"><?php esc_html_e( 'Cancel', 'woocommerce-pre-orders' ); ?></button>
				</div>
			</div>
		</div>

		<!-- Cancel Pre-order Modal -->
		<div id="cancel-preorder-modal" class="wc-po-modal">
			<div class="wc-po-modal-content">
				<h2><?php esc_html_e( 'Cancel Pre-order', 'woocommerce-pre-orders' ); ?></h2>
				<p id="cancel-preorder-desc"><?php esc_html_e( 'Are you sure you want to cancel this pre-order? This action will notify the customer.', 'woocommerce-pre-orders' ); ?></p>
				<div class="email-note-container">
					<textarea id="cancel-note" placeholder="<?php esc_attr_e( 'Optional note to include in email (leave empty for no note)', 'woocommerce-pre-orders' ); ?>"></textarea>
					<p class="validation-message" style="display: none; color: #d63638; margin-top: 5px;"><?php esc_html_e( 'Please enter a message to send to the customer', 'woocommerce-pre-orders' ); ?></p>
				</div>
				<div class="wc-po-modal-buttons">
					<button class="button" id="confirm-cancel-btn"><?php esc_html_e( 'Confirm cancelling pre-orders', 'woocommerce-pre-orders' ); ?></button>
					<button class="button button-primary cancel-modal"><?php esc_html_e( "Don't cancel", 'woocommerce-pre-orders' ); ?></button>
				</div>
			</div>
		</div>


		<!-- Complete Pre-order Modal -->
		<div id="complete-preorder-modal" class="wc-po-modal">
			<div class="wc-po-modal-content">
				<h2><?php esc_html_e( 'Complete Pre-order', 'woocommerce-pre-orders' ); ?></h2>
				<p id="complete-preorder-desc"><?php esc_html_e( 'Are you sure you want to complete this pre-order? This action will notify the customer.', 'woocommerce-pre-orders' ); ?></p>
				<div class="email-note-container">
					<textarea id="complete-note" placeholder="<?php esc_attr_e( 'Optional note to include in email (leave empty for no note)', 'woocommerce-pre-orders' ); ?>"></textarea>
					<p class="validation-message" style="display: none; color: #d63638; margin-top: 5px;"><?php esc_html_e( 'Please enter a message to send to the customer', 'woocommerce-pre-orders' ); ?></p>
				</div>
				<div class="wc-po-modal-buttons">
					<button class="button" id="confirm-complete-btn"><?php esc_html_e( 'Complete pre-order', 'woocommerce-pre-orders' ); ?></button>
					<button class="button button-primary cancel-modal"><?php esc_html_e( 'Cancel', 'woocommerce-pre-orders' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the bulk actions available for pre-orders: complete, cancel
	 * or message.
	 *
	 * @see WP_List_Table::get_bulk_actions()
	 * @since 1.0
	 * @return array associative array of action_slug => action_title
	 */
	public function get_bulk_actions() {

		$actions = array(
			'message'  => __( 'Email customers', 'woocommerce-pre-orders' ),
			'complete' => __( 'Complete pre-orders', 'woocommerce-pre-orders' ),
			'cancel'   => __( 'Cancel pre-orders', 'woocommerce-pre-orders' ),
		);

		return $actions;
	}

	/**
	 * Get list of views available (one per available pre-order status) plus
	 * default of 'all', with counts for each
	 *
	 * @see WP_List_Table::get_views()
	 * @since 1.0
	 * @return array
	 */
	public function get_views() {
		$this->views = array();

		// Use cached status counts from data store instead of direct DB queries
		$status_counts = WC_Pre_Orders_Data_Store::get_pre_orders_status_counts();

		// Build counts array with 'all' total
		$counts = array(
			'all'       => array_sum( $status_counts ),
			'active'    => $status_counts['active'] ?? 0,
			'completed' => $status_counts['completed'] ?? 0,
			'cancelled' => $status_counts['cancelled'] ?? 0,
		);

		// Note: trash count not implemented in data store yet
		$counts['trash'] = 0;

		$base_url = admin_url( 'admin.php?page=wc_pre_orders' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['s'] ) ) {
			$search_string = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// When an array is provided, sanitize_text_field returns an empty string.
			if ( '' !== $search_string ) {
				$base_url = add_query_arg( 's', rawurlencode( $search_string ), $base_url );
			}
		}

		// build the set of views, if any
		foreach ( $counts as $status => $count ) {
			if ( $count > 0 ) {
				if ( $this->get_current_pre_order_status( $counts ) === $status ) {
					$class = ' class="current"';
				} else {
					$class = '';
				}

				$status_url = add_query_arg( 'pre_order_status', rawurlencode( $status ), $base_url );

				$this->views[ $status ] = sprintf(
					'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
					esc_url( $status_url ),
					$class,
					ucfirst( $status ),
					$count
				);
			}
		}

		return $this->views;
	}

	/**
	 * Gest the currently selected pre-order status (the current view) if any.
	 * Defaults to 'all'.  Status is verified to exist in $available_status if
	 * provided
	 *
	 * @since 1.0
	 * @param array $available_status optional array of status => count used for validation
	 * @return string the current pre-order status
	 */
	public function get_current_pre_order_status( $available_status = null ) {
		// is there a status view selected?
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['pre_order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pre_order_status'] ) ) : 'all';

		// verify the status exists, otherwise default to 'all'
		if ( ! is_null( $available_status ) && ! isset( $available_status[ $status ] ) ) {
			return 'all';
		}

		//  otherwise just return the status
		return $status;
	}

	/**
	 * Returns the column slugs and titles
	 *
	 * @see WP_List_Table::get_columns()
	 * @since 1.0
	 * @return array of column slug => title
	 */
	public function get_columns() {
		$columns = array(
			'cb'                => '<input type="checkbox" />',
			'order'             => __( 'Order', 'woocommerce-pre-orders' ),
			'order_date'        => __( 'Date', 'woocommerce-pre-orders' ),
			'status'            => __( 'Pre-order / Order status', 'woocommerce-pre-orders' ),
			'availability_date' => __( 'Release date', 'woocommerce-pre-orders' ),
			'total'             => __( 'Total', 'woocommerce-pre-orders' ),
			'actions'           => __( 'Pre-order actions', 'woocommerce-pre-orders' ),
		);

		return $columns;
	}

	/**
	 * Returns the sortable columns.  We make order_date and order sortable
	 * because they're available right in the posts table, and they make sense
	 * to order over.
	 *
	 * @see WP_List_Table::get_sortable_columns()
	 * @since 1.0
	 * @return array of sortable column slug => array( 'orderby', boolean )
	 *         where true indicates the initial sort is descending
	 */
	public function get_sortable_columns() {

		return array(
			'order'      => array( 'ID', false ), // false because the initial sort direction is DESC so we want the first column click to sort ASC.
			'order_date' => array( 'date', false ), // false because the initial sort direction is DESC so we want the first column click to sort ASC.
			'total'      => array( 'total', false ),
		);
	}

	/**
	 * Get content for the special checkbox column
	 *
	 * @see WP_List_Table::single_row_columns()
	 * @since 1.0
	 * @param WC_Order $order one row (item) in the table
	 * @return string the checkbox column content
	 */
	public function column_cb( $order ) {
		return '<input type="checkbox" name="order_id[]" value="' . $order->get_id() . '" />';
	}

	/**
	 * Generate column content for status.
	 *
	 * @since 2.3.0
	 * @param WC_Order $order The order object.
	 * @return string The column content.
	 */
	public function generate_column_status( $order ) {
		// Pre-order status
		$status      = WC_Pre_Orders_Order::get_pre_order_status( $order );
		$status_text = WC_Pre_Orders_Order::get_pre_order_status_to_display( $order );

		// Order status
		$order_status       = $order->get_status();
		$order_status_label = wc_get_order_status_name( $order_status );

		// Pre-order tips
		$pre_order_tips = array(
			'active'    => esc_html__( 'Pre-order is active and waiting for product release. You can: wait for automatic release, manually complete the order, change product release date, or cancel. Customer will be notified of any changes.', 'woocommerce-pre-orders' ),
			'completed' => esc_html__( 'Pre-order is completed - product was released or manually completed by admin. Order now follows regular WooCommerce fulfillment flow. Check order status for next steps.', 'woocommerce-pre-orders' ),
			'cancelled' => esc_html__( 'Pre-order was cancelled. Customer has been notified. No further actions available.', 'woocommerce-pre-orders' ),
		);

		$order_tips = array(
			'pre-ordered' => esc_html__( 'Order is pre-ordered and payment secured. For upfront charges: payment completed. For release charges: payment method saved. Wait for product release or take manual action.', 'woocommerce-pre-orders' ),
			'pending'     => esc_html__( 'Payment is pending. For upfront charges: wait for customer payment or send payment reminder. For release charges: this status should be temporary during processing.', 'woocommerce-pre-orders' ),
			'processing'  => esc_html__( 'Order is being fulfilled. Pre-order completed, payment confirmed. If has saved payment method: wait for automatic processing. If no saved method: verify payment and proceed with shipping.', 'woocommerce-pre-orders' ),
			'on-hold'     => esc_html__( 'Order requires manual review. Check payment status, verify customer details, or resolve any payment issues. You can: process manually, request more info, or cancel. Customer will be notified of any changes.', 'woocommerce-pre-orders' ),
			'completed'   => esc_html__( 'Order is completed and fulfilled. Payment confirmed, items shipped/delivered. Customer notified. You can: view order details, process refunds if needed, or mark as archived.', 'woocommerce-pre-orders' ),
			'cancelled'   => esc_html__( 'Order was cancelled. Payment reversed if applicable, stock restored. Customer notified. You can: view cancellation reason or delete order record.', 'woocommerce-pre-orders' ),
			'refunded'    => esc_html__( 'Order was refunded. Payment returned to customer, stock restored if applicable. Customer notified. You can: view refund details or process partial refunds if needed.', 'woocommerce-pre-orders' ),
			'failed'      => esc_html__( 'Payment failed during processing. You can: retry payment with saved method, send customer payment link, update payment method, or cancel order.', 'woocommerce-pre-orders' ),
		);

		if ( empty( $status ) || ! isset( $pre_order_tips[ $status ] ) ) {
			if ( ! empty( $order_status ) && isset( $order_tips[ $order_status ] ) ) {
				return sprintf(
					'<mark class="order-status status-%s tips" data-tip="%s"><span>order</span><span>%s</span></mark>',
					esc_attr( $order_status ),
					esc_attr( $order_tips[ $order_status ] ),
					esc_html( $order_status_label )
				);
			}
			return '';
		}

		// First tag: Pre-order status
		$column_content = sprintf(
			'<mark class="order-status status-%s tips" data-tip="%s"><span>pre-order</span><span>%s</span></mark>',
			esc_attr( $status ),
			esc_attr( $pre_order_tips[ $status ] ),
			esc_html( $status_text )
		);

		// Second tag: Order status
		if ( ! empty( $order_status ) && isset( $order_tips[ $order_status ] ) ) {
			$column_content .= sprintf(
				'&nbsp;&nbsp;<mark class="order-status status-%s tips" data-tip="%s"><span>order</span><span>%s</span></mark>',
				esc_attr( $order_status ),
				esc_attr( $order_tips[ $order_status ] ),
				esc_html( $order_status_label )
			);
		}
		return $column_content;
	}

	/**
	 * Format timestamp into column content with date and relative time.
	 *
	 * @since 2.3.0
	 * @param int|null $timestamp The timestamp to format.
	 * @param bool $mobile_format Whether to use mobile format (stacked layout).
	 * @return string The formatted column content.
	 */
	private function format_date_column( $timestamp, $mobile_format = false ) {
		return self::format_date( $timestamp, $mobile_format );
	}

	/**
	 * Static method to format date with relative time.
	 *
	 * @since 2.3.0
	 * @param int|null $timestamp The timestamp to format.
	 * @param bool $mobile_format Whether to use mobile format (stacked layout).
	 * @return string The formatted column content.
	 */
	public static function format_date( $timestamp, $mobile_format = false ) {
		if ( ! $timestamp ) {
			return '--';
		}

		$formatted_date = date_i18n( wc_date_format(), $timestamp );
		$full_date      = date_i18n( wc_date_format() . ' ' . wc_time_format(), $timestamp );
		$relative_time  = human_time_diff( $timestamp, time() );
		$relative_time  = $timestamp > time()
			// translators: %s = relative time
			? sprintf( esc_html__( 'in %s', 'woocommerce-pre-orders' ), $relative_time )
			// translators: %s = relative time
			: sprintf( esc_html__( '%s ago', 'woocommerce-pre-orders' ), $relative_time );

		if ( $mobile_format ) {
			// Mobile format: date on top, relative time below
			return sprintf(
				'<time datetime="%s" title="%s">%s</time><br><small style="color: #666;">%s</small>',
				esc_attr( gmdate( 'c', $timestamp ) ),
				esc_attr( $full_date ),
				esc_html( $formatted_date ),
				esc_attr( $relative_time )
			);
		}

		// Non-mobile format: inline with parentheses
		return sprintf(
			'<span title="%s">%s</span>&nbsp;<small title="%s">(%s)</small>',
			esc_attr( $full_date ),
			esc_html( $formatted_date ),
			esc_attr( $full_date ),
			esc_attr( $relative_time )
		);
	}

	/**
	 * Generate column content for order date.
	 *
	 * @since 2.3.0
	 * @param WC_Order $order The order object.
	 * @param string $column_name The column name.
	 * @param bool $mobile_format Whether to use mobile format.
	 * @return string The column content.
	 */
	public function generate_column_order_date( $order, $column_name, $mobile_format = false ) {
		$timestamp = null;

		if ( 'order_date' === $column_name ) {
			$date_created = $order->get_date_created();
			if ( $date_created ) {
				$timestamp = $date_created->getOffsetTimestamp();
			}
		} else {
			$product = WC_Pre_Orders_Order::get_pre_order_product( $order );
			if ( $product ) {
				$timestamp = get_post_meta( $product->get_id(), '_wc_pre_orders_availability_datetime', true );
			}
		}

		return $this->format_date_column( $timestamp, $mobile_format );
	}

	/**
	 * Generate column content for availability date.
	 *
	 * @since 2.3.0
	 * @param WC_Order $order The order object.
	 * @param string $column_name The column name.
	 * @return string The column content.
	 */
	public function generate_column_availability_date( $order, $column_name ) {
		$timestamp = null;

		$product = WC_Pre_Orders_Order::get_pre_order_product( $order );
		if ( $product ) {
			$timestamp = get_post_meta( $product->get_id(), '_wc_pre_orders_availability_datetime', true );
		}

		return $this->format_date_column( $timestamp );
	}

	/**
	 * Generate simplified mobile status content for small screens.
	 *
	 * @since 2.3.0
	 * @param WC_Order $order The order object.
	 * @return string The simplified status content.
	 */
	public function generate_mobile_status( $order ) {
		// Pre-order status only
		$pre_order_status      = WC_Pre_Orders_Order::get_pre_order_status( $order );
		$pre_order_status_text = WC_Pre_Orders_Order::get_pre_order_status_to_display( $order );

		// Return only pre-order status without label, inline style
		return sprintf(
			'<mark class="order-status status-%s tips" style="display: inline-block; text-align: center; line-height: 1.2; padding: 4px 8px;"><span>%s</span></mark>',
			esc_attr( $pre_order_status ),
			esc_html( strtoupper( $pre_order_status_text ) )
		);
	}

	/**
	 * Get column content, this is called once per column, per row item ($order)
	 * returns the content to be rendered within that cell.
	 *
	 * @see WP_List_Table::single_row_columns()
	 * @since 1.0
	 * @param WC_Order $order one row (item) in the table
	 * @param string $column_name the column slug
	 * @return string the column content
	 */
	public function column_default( $order, $column_name ) {
		$order_id = $order->get_id();

		switch ( $column_name ) {

			case 'status':
				$column_content = $this->generate_column_status( $order );
				break;

			case 'order':
				$customer_name = WC_Pre_Orders_Order::get_customer_name( $order );
				$order_number  = $order->get_order_number();
				$order_id      = $order->get_id();

				// Get product info
				$items        = $order->get_items();
				$item         = reset( $items );
				$product_name = '';
				$product_link = '';

				if ( $item ) {
					$product_id       = $item['product_id'];
					$product_name     = $item['name'];
					$product_edit_url = get_edit_post_link( $product_id );
					if ( $product_edit_url ) {
						// translators: %s = product name
						$product_link = sprintf( '<a href="%s">%s</a>', esc_url( $product_edit_url ), esc_html( $product_name ) );
					}
				}

				// Main order content wrapped in span
				$main_content = sprintf(
					'<a href="%s" title="%s" class="order-link" style="display: block;"><strong>%s</strong></a>',
					esc_url( $order->get_edit_order_url() ),
					/* translators: %s = order number */
					sprintf( esc_attr__( 'See order #%s', 'woocommerce-pre-orders' ), esc_attr( $order_number ) ),
					/* translators: %1$s = order number, %2$s = customer name */
					sprintf( esc_html__( '#%1$s %2$s', 'woocommerce-pre-orders' ), esc_html( $order_number ), esc_html( $customer_name ) )
				);

				if ( $product_link ) {
					// translators: %s = product name
					$main_content .= '<small class="meta" style="display: block;">' . sprintf(
						'<a href="%s" title="%s">%s</a>',
						esc_url( $product_edit_url ),
						/* translators: %s = product name */
						sprintf( esc_attr__( 'See product %s', 'woocommerce-pre-orders' ), esc_attr( $product_name ) ),
						esc_html( $product_name )
					) . '</small>';
				} elseif ( $product_name ) {
					$main_content .= '<small class="meta" style="display: block;">' . esc_html( $product_name ) . '</small>';
				}

				// Get mobile-only content
				$mobile_date   = $this->generate_column_order_date( $order, 'order_date', true );
				$mobile_status = $this->generate_mobile_status( $order );

				// Wrap everything in a flex container using CSS classes
				$column_content = '<div class="mobile-order-container">';

				// Order number, customer name and product name
				$column_content .= sprintf(
					'<div class="mobile-order-main">%s</div>',
					$main_content
				);

				// Order date
				$column_content .= sprintf(
					'<div class="order_date small-screen-only">%s</div>',
					$mobile_date
				);

				// Order status
				$column_content .= sprintf(
					'<div class="order_status small-screen-only">%s</div>',
					$mobile_status
				);

				$order_link      = $order->get_edit_order_url();
				$column_content .= '<a href="' . esc_url( $order_link ) . '" class="order_preview small-screen-only" data-order-id="' . esc_attr( $order_id ) . '" title="Preview">Preview</a>';

				$column_content .= '</div>';

				break;

			case 'product':
				// Past pre-orders may contain products that are no longer marked as a pre-order product
				// As only one product can exist in a pre-order, pick the first product
				$items = $order->get_items();
				$item  = reset( $items );

				if ( $item ) {
					$product_edit   = get_edit_post_link( $item['product_id'] );
					$column_content = ( $product_edit ) ? sprintf( '<a href="%s">%s</a>', $product_edit, $item['name'] ) : $item['name'];
				} else {
					$column_content = '';
				}
				break;
			case 'order_date':
				$column_content = $this->generate_column_order_date( $order, $column_name );
				break;
			case 'availability_date':
				$column_content = $this->generate_column_availability_date( $order, $column_name );
				break;

			case 'total':
				$payment_method = $order->get_payment_method_title();
				$total_amount   = wc_price( $order->get_total() );
				if ( $payment_method ) {
					$column_content = sprintf(
						// translators: %s = payment method
						'<span title="%s">%s</span>&nbsp;<small>(' . esc_html__( 'via %s', 'woocommerce-pre-orders' ) . ')</small>',
						/* translators: %s = payment method */
						esc_attr( sprintf( esc_html__( 'Payment via %s', 'woocommerce-pre-orders' ), $payment_method ) ),
						$total_amount,
						esc_html( $payment_method )
					);
				} else {
					$column_content = $total_amount;
				}
				break;

			case 'actions':
				// Action buttons as specified in requirements.
				$actions = array();

				// View button with eye icon
				$actions[] = sprintf(
					'<a href="%s" class="button button-small view-order" title="%s"><span class="dashicons dashicons-visibility"></span><span class="button-text">%s</span></a>',
					esc_url( $order->get_edit_order_url() ),
					esc_attr__( 'View order', 'woocommerce-pre-orders' ),
					esc_html__( 'View', 'woocommerce-pre-orders' )
				);

				// Email button - only for active pre-orders
				$pre_order_status = WC_Pre_Orders_Order::get_pre_order_status( $order );
				if ( 'active' === $pre_order_status ) {
					$nonce     = wp_create_nonce( 'bulk-pre-orders' );
					$actions[] = sprintf(
						'<button class="button button-small email-customer" onclick="handleEmailCustomer(%d, \'%s\', event)" title="%s"><span class="dashicons dashicons-email-alt"></span><span class="button-text">%s</span></button>',
						esc_attr( $order_id ),
						esc_attr( $nonce ),
						esc_attr__( 'Email customer', 'woocommerce-pre-orders' ),
						esc_html__( 'Email', 'woocommerce-pre-orders' )
					);
				}

				// Complete button if available
				if ( WC_Pre_Orders_Manager::can_pre_order_be_changed_to( 'completed', $order ) ) {
					$nonce     = wp_create_nonce( 'complete_pre_order' );
					$actions[] = sprintf(
						'<button class="button button-small complete-order" onclick="handleCompletePreOrder(%d, \'%s\', event)" title="%s"><span class="dashicons dashicons-yes"></span><span class="button-text">%s</span></button>',
						esc_attr( $order_id ),
						esc_attr( $nonce ),
						esc_attr__( 'Complete pre-order', 'woocommerce-pre-orders' ),
						esc_html__( 'Complete', 'woocommerce-pre-orders' )
					);
				}

				// Cancel button if available
				if ( WC_Pre_Orders_Manager::can_pre_order_be_changed_to( 'cancelled', $order ) ) {
					$nonce     = wp_create_nonce( 'cancel_pre_order' );
					$actions[] = sprintf(
						'<button class="button button-small cancel-pre-order" onclick="handleCancelPreOrder(%d, \'%s\', event)" title="%s"><span class="dashicons dashicons-no"></span><span class="button-text">%s</span></button>',
						esc_attr( $order_id ),
						esc_attr( $nonce ),
						esc_attr__( 'Cancel this pre-order', 'woocommerce-pre-orders' ),
						esc_html__( 'Cancel', 'woocommerce-pre-orders' )
					);
				}

				$column_content = implode( ' ', $actions );
				break;

			default:
				$column_content = '';
				break;
		}

		return $column_content;
	}

	/**
	 * Output any messages from the bulk action handling
	 *
	 * @since 1.0
	 */
	public function render_messages() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['message'] ) ) {

			$memo = get_transient( $this->message_transient_prefix . wp_kses_post( wp_unslash( $_GET['message'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( ! empty( $memo ) ) {

				delete_transient( $this->message_transient_prefix . wp_kses_post( wp_unslash( $_GET['message'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( ! empty( $memo['messages'] ) ) {
					echo '<div id="moderated" class="updated"><p>' . esc_html( $memo['messages'] ) . '</p></div>';
				}
			}
		}
	}

	/**
	 * Gets the current orderby, defaulting to 'date' if none is selected
	 */
	private function get_current_orderby() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'date';
	}

	/**
	 * Gets the current orderby, defaulting to 'DESC' if none is selected
	 */
	private function get_current_order() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC';
	}

	/**
	 * Prepare the list of pre-order items for display
	 *
	 * @see WP_List_Table::prepare_items()
	 * @since 1.0
	 */
	public function prepare_items() {

		$base_args = WC_Pre_Orders_Data_Store::get_pre_orders_base_args();

		$per_page = $this->get_items_per_page( 'wc_pre_orders_edit_pre_orders_per_page' );
		$page_num = $this->get_pagenum();
		$orderby  = $this->get_current_orderby();
		$order    = $this->get_current_order();
		$offset   = ( $page_num - 1 ) * $per_page;

		// Add pagination args
		$args = array_merge(
			$base_args,
			array(
				'limit'    => $per_page,
				'paginate' => true,
				'offset'   => $offset,
				'orderby'  => $orderby,
				'order'    => $order,
			)
		);

		// Include filter args
		$args = $this->add_filter_args( $args );

		// Include search args
		$args = $this->add_search_args( $args );

		$base_meta_constraints = array(
			array(
				'key'     => '_wc_pre_orders_is_pre_order',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => '_wc_pre_orders_status',
				'value'   => WC_Pre_Orders_Data_Store::VALID_STATUSES,
				'compare' => 'IN',
			),
		);

		if ( empty( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) ) {
			$args['meta_query'] = $base_meta_constraints; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		} else {
			$args['meta_query'] = array_merge( $base_meta_constraints, $args['meta_query'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		if ( ! WC_Pre_Orders::is_hpos_enabled() ) {
			$args['post_type']   = 'shop_order';
			$args['type']        = 'shop_order';
			$args['meta_key']    = '_wc_pre_orders_is_pre_order'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['meta_value']  = '1'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			$args['post_status'] = array_diff( array_keys( wc_get_order_statuses() ), array( 'draft' ) );
		}

		if ( WC_Pre_Orders::is_hpos_enabled() ) {
			unset( $args['offset'] );
			$args['page'] = max( 1, (int) $page_num );
			if ( isset( $args['orderby'] ) ) {
				if ( 'ID' === $args['orderby'] ) {
					$args['orderby'] = 'id';
				} elseif ( 'date' === $args['orderby'] ) {
					$args['orderby'] = 'date_created';
				}
			}
			if ( isset( $args['include'] ) && is_array( $args['include'] ) ) {
				$args['include'] = array_values( array_unique( array_map( 'intval', $args['include'] ) ) );
				$args['id']      = $args['include'];
			}
			unset( $args['post__in'] );
		}

		/**
		 * Filter the arguments used to query pre-orders in the admin list table.
		 *
		 * @since 1.0.0
		 *
		 * @param array $args The query arguments.
		 */
		$args = apply_filters( 'wc_pre_orders_edit_pre_orders_request', $args );

		$is_hpos = WC_Pre_Orders::is_hpos_enabled();

		if (
			( $is_hpos && isset( $args['include'] ) && array( 0 ) === $args['include'] )
			|| ( ! $is_hpos && isset( $args['post__in'] ) && array( 0 ) === $args['post__in'] )
		) {
			/*
			 * No results if include/post__in is explicitly set to array(0).
			 *
			 * This is the result of a product or maturity date filter that return no
			 * matches. Therefore, it's known that there will be no results so the final
			 * database query can be skipped.
			 */
			$this->items = array();
			$this->set_pagination_args(
				array(
					'total_items' => 0,
					'per_page'    => $per_page,
					'total_pages' => 0,
				)
			);
			return;
		}

		if ( $is_hpos ) {
			$orders      = wc_get_orders( $args );
			$this->items = $orders->orders;
			$this->set_pagination_args(
				array(
					'total_items' => $orders->total,
					'per_page'    => $per_page,
					'total_pages' => ceil( $orders->total / $per_page ),
				)
			);
		} else {
			$wp_args = array(
				'post_type'      => 'shop_order',
				'post_status'    => isset( $args['status'] ) ? (array) $args['status'] : ( isset( $args['post_status'] ) ? (array) $args['post_status'] : array_keys( wc_get_order_statuses() ) ),
				'posts_per_page' => (int) $per_page,
				'paged'          => max( 1, (int) $page_num ),
				'orderby'        => $orderby,
				'order'          => $order,
				'no_found_rows'  => false,
				'fields'         => 'ids',
			);

			// Map order by total to numeric meta sorting.
			if ( 'total' === $orderby ) {
				$wp_args['meta_key'] = '_order_total'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$wp_args['orderby']  = 'meta_value_num';
			}

			// Filtering constraints.
			if ( ! empty( $args['meta_query'] ) ) {
				$wp_args['meta_query'] = $args['meta_query']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			}

			// Customer filter: translate billing_email / customer to CPT postmeta query.
			if ( ! empty( $args['billing_email'] ) || ! empty( $args['customer'] ) ) {
				$customer_email = ! empty( $args['billing_email'] ) ? $args['billing_email'] : $args['customer'];
				if ( ! isset( $wp_args['meta_query'] ) || ! is_array( $wp_args['meta_query'] ) ) {
					$wp_args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				}
				$wp_args['meta_query'][] = array(
					'key'     => '_billing_email',
					'value'   => $customer_email,
					'compare' => '=',
				);
			}

			// Apply accumulated IDs for both non-HPOS and HPOS.
			if ( ! empty( $args['post__in'] ) && is_array( $args['post__in'] ) ) {
				$wp_args['post__in'] = $args['post__in'];
			}

			$query       = new WP_Query( $wp_args );
			$order_ids   = $query->posts;
			$this->items = array();
			foreach ( $order_ids as $oid ) {
				$ord = wc_get_order( $oid );
				if ( $ord ) {
					$this->items[] = $ord;
				}
			}

			// Build pagination.
			$total_items = (int) $query->found_posts;
			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
					'total_pages' => (int) ceil( $total_items / max( 1, (int) $per_page ) ),
				)
			);
		}
	}

	/**
	 * Get start and end timestamps for availability date interval.
	 *
	 * @since 2.3.0
	 * @param string $slug The interval slug (e.g., 'today', 'next_7_days', etc.)
	 * @return array|null Array with [start_timestamp, end_timestamp, label] or ['label' => label] for 'all', or null if invalid slug
	 */
	private function get_availability_date_interval( $slug ): ?array {
		$now         = time();
		$today_start = strtotime( 'today', $now );
		$today_end   = strtotime( 'tomorrow', $now ) - 1;

		// Calculate dynamic labels for month/year periods
		$current_year    = gmdate( 'Y', $now );
		$current_month   = gmdate( 'F', $now );
		$next_year       = gmdate( 'Y', strtotime( '+1 year', $now ) );
		$next_month      = gmdate( 'F', strtotime( 'first day of next month', $now ) );
		$next_month_year = gmdate( 'Y', strtotime( 'first day of next month', $now ) );

		switch ( $slug ) {
			case 'all':
				return array(
					'label' => __( 'All availability dates', 'woocommerce-pre-orders' ),
				);

			// Past periods (inclusive of today going backwards)
			case 'last_7_days':
				return array(
					strtotime( '-6 days', $today_start ), // Today - 6 days = 7 days total
					$today_end,
					'label' => __( 'Last 7 days', 'woocommerce-pre-orders' ),
				);

			case 'last_30_days':
				return array(
					strtotime( '-29 days', $today_start ), // Today - 29 days = 30 days total
					$today_end,
					'label' => __( 'Last 30 days', 'woocommerce-pre-orders' ),
				);

			case 'last_90_days':
				return array(
					strtotime( '-89 days', $today_start ), // Today - 89 days = 90 days total
					$today_end,
					'label' => __( 'Last 90 days', 'woocommerce-pre-orders' ),
				);

			// Present
			case 'today':
				return array(
					$today_start,
					$today_end,
					'label' => __( 'Today only', 'woocommerce-pre-orders' ),
				);

			// Future periods (inclusive of today going forwards)
			case 'next_7_days':
				return array(
					$today_start,
					strtotime( '+6 days', $today_end ), // Today + 6 days = 7 days total
					'label' => __( 'Next 7 days', 'woocommerce-pre-orders' ),
				);

			case 'next_14_days':
				return array(
					$today_start,
					strtotime( '+13 days', $today_end ), // Today + 13 days = 14 days total
					'label' => __( 'Next 14 days', 'woocommerce-pre-orders' ),
				);

			case 'next_30_days':
				return array(
					$today_start,
					strtotime( '+29 days', $today_end ), // Today + 29 days = 30 days total
					'label' => __( 'Next 30 days', 'woocommerce-pre-orders' ),
				);

			case 'next_90_days':
				return array(
					$today_start,
					strtotime( '+89 days', $today_end ), // Today + 89 days = 90 days total
					'label' => __( 'Next 90 days', 'woocommerce-pre-orders' ),
				);

			// Calendar periods
			case 'this_month':
				return array(
					strtotime( 'first day of this month', $now ),
					strtotime( 'last day of this month', $now ) + 86399,
					/* translators: %1$s = year, %2$s = month */
					'label' => sprintf( __( 'Within %1$s %2$s', 'woocommerce-pre-orders' ), $current_year, $current_month ),
				);

			case 'next_month':
				return array(
					strtotime( 'first day of next month', $now ),
					strtotime( 'last day of next month', $now ) + 86399,
					/* translators: %1$s = year, %2$s = month */
					'label' => sprintf( __( 'Within %1$s %2$s', 'woocommerce-pre-orders' ), $next_month_year, $next_month ),
				);

			case 'after_next_month':
				return array(
					strtotime( 'first day of next month', $now ),
					strtotime( '+10 years', $now ),
					/* translators: %1$s = year, %2$s = month */
					'label' => sprintf( __( 'After %1$s %2$s', 'woocommerce-pre-orders' ), $next_month_year, $next_month ),
				);

			default:
				return null;
		}
	}

	/**
	 * Retrieve pre-orders availability date filters ordered by temporal proximity.
	 *
	 * @since 2.3.0
	 * @return array<string,string> List of filter slugs => labels.
	 */
	private function get_pre_orders_availability_date_filters(): array {
		// Define the order of intervals (past to future chronologically)
		$interval_slugs = array(
			'all',
			'last_90_days',
			'last_30_days',
			'last_7_days',
			'today',
			'next_7_days',
			'next_14_days',
			'next_30_days',
			'next_90_days',
			'this_month',
			'next_month',
			'after_next_month',
		);

		$filters = array();
		foreach ( $interval_slugs as $slug ) {
			$interval_data = $this->get_availability_date_interval( $slug );
			if ( $interval_data && isset( $interval_data['label'] ) ) {
				$filters[ $slug ] = $interval_data['label'];
			}
		}

		return $filters;
	}

	/**
	 * Display date dropdown for filtering items by availability date
	 *
	 * @since 2.3.0
	 */
	private function render_availability_dates_dropdown() {
		$filters  = $this->get_pre_orders_availability_date_filters();
		$selected = isset( $_GET['availability_date'] ) ? sanitize_text_field( wp_unslash( $_GET['availability_date'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<select id="dropdown_availability_dates" name="availability_date">
			<?php foreach ( $filters as $slug => $label ) : ?>
				<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected, $slug ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Adds in any query arguments based on the current filters
	 *
	 * @since 1.0
	 * @global wpdb $wpdb WordPress database abstraction object
	 *
	 * @param array $initial_args associative array of WP_Query arguments used to query and populate the list table
	 * @return array associative array of WP_Query arguments used to query and populate the list table
	 */
	private function add_filter_args( $initial_args ): array {
		global $wpdb;
		$args                = $initial_args;
		$filters_applied     = false;
		$acc_include_ids     = null;
		$include_product_ids = null;

		// Filter by customer
		if ( isset( $_GET['_customer_user'] ) && ! empty( $_GET['_customer_user'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$customer_billing_email = sanitize_email( wp_unslash( $_GET['_customer_user'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['billing_email']  = $customer_billing_email;
			$filters_applied        = true;
		}

		// Filter by availability date intervals using shared method - only if not 'all'
		$slug = isset( $_GET['availability_date'] ) ? sanitize_text_field( wp_unslash( $_GET['availability_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== trim( $slug ) && 'all' !== $slug ) {
			$interval = $this->get_availability_date_interval( $slug );

			// Only proceed if there is a valid start/end
			if ( isset( $interval[0], $interval[1] ) ) {
				$start_ts            = (int) $interval[0];
				$end_ts              = (int) $interval[1];
				$products            = WC_Pre_Orders_Data_Store::get_pre_order_products();
				$include_product_ids = array();

				foreach ( $products as $product_id => $product_data ) {
					$avail_ts = (int) ( $product_data['availability_datetime'] ?? 0 );

					if ( $avail_ts >= $start_ts && $avail_ts <= $end_ts ) {
						$include_product_ids[] = $product_id;
					}
				}

				$filters_applied = true;
			}
		}

		// Filter by product.
		if ( is_array( $include_product_ids ) || ! empty( $_GET['_product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['_product_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				// If specific products are selected, use those.
				$filter_by_product_ids = array_map( 'absint', (array) wp_unslash( $_GET['_product_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( is_array( $include_product_ids ) ) {
					$filter_by_product_ids = array_intersect( $include_product_ids, $filter_by_product_ids );
				}
			} else {
				// Otherwise use the products that match the availability date filter.
				$filter_by_product_ids = $include_product_ids;
			}

			if ( empty( $filter_by_product_ids ) ) {
				// No products matched the filter, so ensure no orders are returned.
				$acc_include_ids = array( 0 );
			} else {
				$filter_by_product_ids = implode( ',', $filter_by_product_ids );
				$order_ids             = $wpdb->get_col(
					"
					SELECT order_id
					FROM {$wpdb->prefix}woocommerce_order_items as order_items
					JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta
						ON order_itemmeta.order_item_id = order_items.order_item_id
					WHERE meta_key = '_product_id' AND meta_value IN (" . esc_sql( $filter_by_product_ids ) . ")" // phpcs:ignore Squiz.Strings.DoubleQuoteUsage.NotRequired
				);

				if ( $order_ids ) {
					$acc_include_ids = is_array( $acc_include_ids ) ? array_intersect( $acc_include_ids, $order_ids ) : $order_ids;
				} else {
					// No orders match the criteria, so ensure no orders are returned.
					$acc_include_ids = array( 0 );
				}
			}
			$filters_applied = true;
		}

		// Filter by pre-order status
		$pre_order_status = isset( $_GET['pre_order_status'] ) && ! empty( $_GET['pre_order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pre_order_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'all' !== $pre_order_status && '' !== $pre_order_status ) {
			$args['meta_query'][] = array(
				'key'     => '_wc_pre_orders_status',
				'value'   => $pre_order_status,
				'compare' => '=',
			);
			$filters_applied      = true;
		}

		// Filter by order status
		if ( isset( $_GET['_order_status'] ) && ! empty( $_GET['_order_status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$order_status    = sanitize_text_field( wp_unslash( $_GET['_order_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['status']  = 'wc-' === substr( $order_status, 0, 3 ) ? $order_status : 'wc-' . $order_status;
			$filters_applied = true;
		}

		// Apply IDs for non-HPOS and HPOS.
		if ( is_array( $acc_include_ids ) ) {
			$acc_include_ids = array_values( array_unique( array_map( 'intval', $acc_include_ids ) ) );
			if ( empty( $acc_include_ids ) ) {
				$acc_include_ids = array( 0 );
			}
			if ( WC_Pre_Orders::is_hpos_enabled() ) {
				$args['include'] = $acc_include_ids;
				unset( $args['post__in'] );
			} else {
				$args['post__in'] = $acc_include_ids;
				$args['orderby']  = 'post__in';
				unset( $args['include'] );
			}
		}

		if ( $filters_applied ) {
			$this->has_filters_applied = true;
		}

		return $args;
	}

	/**
	 * Adds in any query arguments based on the search term
	 *
	 * Uses wc_get_orders built-in search functionality instead of manual DB queries.
	 * Supports: order numbers, customer emails, billing info, product names, and order keys.
	 *
	 * @since 1.0
	 * @param array $args associative array of wc_get_orders arguments used to query and populate the list table
	 * @return array associative array of wc_get_orders arguments used to query and populate the list table
	 */
	private function add_search_args( $args ) {
		if ( ! isset( $_GET['s'] ) || empty( $_GET['s'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return $args;
		}

		$search_term = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Billing email
		if ( is_email( $search_term ) ) {
			$args['billing_email'] = $search_term;
			$args['customer']      = $search_term;
			return $args;
		}

		// Order ID - direct number and strings like "Order #987"
		$numeric_search_term = ctype_digit( $search_term ) ? (int) $search_term : (int) preg_replace( '/[^0-9]/', '', $search_term );
		if ( ! empty( $numeric_search_term ) ) {
			$order_id_found   = array( $numeric_search_term );
			$args['include']  = $order_id_found;   // legacy posts-table
			$args['post__in'] = $order_id_found;   // HPOS
			$args['orderby']  = 'post__in';
			return $args;
		}

		if ( WC_Pre_Orders::is_hpos_enabled() ) {
			/**
			 * Other text search.
			 *
			 * If didn't match order id and email, try to match against these
			 * keys which are generated to speed up text searches
			 */
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key'     => '_billing_address_index',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
				array(
					'key'     => '_shipping_address_index',
					'value'   => $search_term,
					'compare' => 'LIKE',
				),
			);
		} else {
			// Legacy search - let core do the most basic search
			$args['search']         = $search_term;
			$args['search_columns'] = array(
				'billing_email',
				'billing_first_name',
				'billing_last_name',
				'order_number',
				'order_key',
			);
		}

		return $args;
	}

	/**
	 * The text to display when there are no pre-orders
	 *
	 * @see WP_List_Table::no_items()
	 * @since 1.0
	 */
	public function no_items() {

		if ( $this->has_filters_applied || isset( $_REQUEST['s'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>
			<p><?php esc_html_e( 'No pre-orders found matching your criteria. Please adjust filters and try again.', 'woocommerce-pre-orders' ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Any pre-orders placed by customers will appear here so you can view and manage them.', 'woocommerce-pre-orders' ); ?></p>
			<p>
				<?php
					// translators: %1$s = Opening anchor tag for WooCommerce Pre-Orders documentation on WooCommerce.com, %2$s = closing anchor tag
					printf( esc_html__( '%1$sLearn more about managing pre-orders%2$s', 'woocommerce-pre-orders' ), '<a href="http://docs.woothemes.com/document/pre-orders/#section-6" target="_blank">', ' &raquo;</a>' );
				?>
			</p>
			<p>
				<?php
					// translators: %1$s = Opening anchor tag for adding new product, %2$s = closing anchor tag
					printf( esc_html__( '%1$sSetup a product to allow pre-orders%2$s', 'woocommerce-pre-orders' ), '<a href="' . esc_url( admin_url( 'post-new.php?post_type=product' ) ) . '">', ' &raquo;</a>' );
				?>
			</p>
			<?php
		endif;
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination, which
	 * includes our Filters: Customers, Products, Release Dates
	 *
	 * @see WP_List_Table::extra_tablenav();
	 * @since 1.0
	 * @param string $which the placement, one of 'top' or 'bottom'
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			$dropdown_data            = WC_Pre_Orders_Data_Store::get_admin_dropdowns_data();
			$products                 = $dropdown_data['products'];
			$customers                = $dropdown_data['customers'];
			$selected_product         = isset( $_GET['_product_id'] ) ? absint( $_GET['_product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$selected_customer        = isset( $_GET['_customer_user'] ) ? sanitize_text_field( wp_unslash( $_GET['_customer_user'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$selected_preorder_status = isset( $_GET['pre_order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['pre_order_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$selected_order_status    = isset( $_GET['_order_status'] ) ? sanitize_text_field( wp_unslash( $_GET['_order_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			?>

			<div class="extra-actions">

				<!-- Customer Filter -->
				<select id="dropdown_customers" name="_customer_user" class="wc-enhanced-select" data-placeholder="<?php esc_attr_e( 'All customers', 'woocommerce-pre-orders' ); ?>">
					<option value=""><?php esc_html_e( 'All customers', 'woocommerce-pre-orders' ); ?></option>
										<?php foreach ( $customers as $id => $label ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $selected_customer, $id ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<!-- Product Filter -->
				<select id="dropdown_products" name="_product_id">
					<option value=""><?php esc_html_e( 'All products', 'woocommerce-pre-orders' ); ?></option>
					<?php foreach ( $products as $id => $data ) : ?>
						<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $selected_product, $id ); ?>>
							<?php
								echo esc_html( $data['label'] );
							?>
						</option>
					<?php endforeach; ?>
				</select>

				<!-- Availability Date Filter -->
				<?php $this->render_availability_dates_dropdown(); ?>

				<!-- Pre-order Status Filter -->
				<select id="dropdown_preorder_status" name="pre_order_status">
					<option value=""><?php esc_html_e( 'All pre-order statuses', 'woocommerce-pre-orders' ); ?></option>
					<option value="active" <?php selected( $selected_preorder_status, 'active' ); ?>><?php esc_html_e( 'Active', 'woocommerce-pre-orders' ); ?></option>
					<option value="completed" <?php selected( $selected_preorder_status, 'completed' ); ?>><?php esc_html_e( 'Completed', 'woocommerce-pre-orders' ); ?></option>
					<option value="cancelled" <?php selected( $selected_preorder_status, 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'woocommerce-pre-orders' ); ?></option>
				</select>

				<!-- Order Status Filter -->
				<select id="dropdown_order_status" name="_order_status">
					<option value=""><?php esc_html_e( 'All order statuses', 'woocommerce-pre-orders' ); ?></option>
					<?php
					$order_statuses = wc_get_order_statuses();
					foreach ( $order_statuses as $status => $label ) :
						$status_name = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
						?>
						<option value="<?php echo esc_attr( $status_name ); ?>" <?php selected( $selected_order_status, $status_name ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>

				<!-- Filter Button -->
				<?php submit_button( esc_html__( 'Filter', 'woocommerce-pre-orders' ), 'button', false, false, array( 'id' => 'post-query-submit' ) ); ?>

				<!-- Clear Filters Button -->
				<?php
					echo ' <a href="' . esc_url( admin_url( 'admin.php?page=wc_pre_orders' ) ) . '" class="button">' . esc_html__( 'Clear filters', 'woocommerce-pre-orders' ) . '</a>';
				?>
			</div>
			<div id="bulk-action-fields" style="clear:left;padding-top:10px;display:none;">
				<textarea cols="62" rows="3" name="customer_message" placeholder="<?php esc_attr_e( 'Optional message to include in the email to the customer', 'woocommerce-pre-orders' ); ?>"></textarea>
			</div>
				<?php
				// Initialize enhanced select for customer dropdown
				wc_enqueue_js(
					"
					// Initialize enhanced select for customer dropdown
					$( '#dropdown_customers' ).selectWoo({
						width: '200px',
						allowClear: true,
						minimumInputLength: 0
					});"
				);
				?>
			<?php
		}
	}
}
