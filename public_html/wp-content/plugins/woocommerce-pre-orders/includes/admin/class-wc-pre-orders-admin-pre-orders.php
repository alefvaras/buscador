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
	exit;
}

/**
 * Pre-Orders Admin Pre Orders class.
 */
class WC_Pre_Orders_Admin_Pre_Orders {

	/**
	 * The pre-orders list table object.
	 *
	 * @var WC_Pre_Orders_List_Table
	 */
	private $pre_orders_list_table;

	/**
	 * Mensage transient prefix.
	 *
	 * @var string
	 */
	private $message_transient_prefix = '_wc_pre_orders_messages_';

	/**
	 * Initialize the admin settings actions.
	 */
	public function __construct() {
		// Add 'Pre-Orders' link under WooCommerce menu.
		add_action( 'admin_menu', array( $this, 'add_menu_link' ) );

		// Pre-Orders list table settings
		add_action( 'in_admin_header', array( $this, 'load_pre_orders_list_table' ) );
		add_filter( 'set-screen-option', array( $this, 'set_pre_orders_list_option' ), 10, 3 );

		// Remove query string args from URLs in the admin - only on pre-orders pages.
		add_action( 'current_screen', array( $this, 'maybe_add_remove_query_args_filter' ) );

		// Fix duplicate email bug when pre-orders are completed.
		add_action( 'init', array( $this, 'prevent_duplicate_preorder_completion_emails' ) );
	}

	/**
	 * Add the remove_query_args filter only on pre-orders admin pages.
	 *
	 * @since 2.3.0
	 * @param WP_Screen $current_screen Current screen object.
	 */
	public function maybe_add_remove_query_args_filter( $current_screen ) {
		// Only add the filter on pre-orders admin pages.
		if ( isset( $current_screen->id ) && 'woocommerce_page_wc_pre_orders' === $current_screen->id ) {
			add_filter( 'removable_query_args', array( $this, 'remove_query_args' ) );
		}
	}

	/**
	 * Modify query string parameters to be removed on Pre-order admin pages.
	 *
	 * Modifies the query string parameters to be removed via JavaScript when setting the canonical
	 * admin URL for the admin page.
	 *
	 * The success and product ID query string parameters are removed to reduce the chance of
	 * a user bookmarking the page with a success message or product ID in the URL.
	 *
	 * @param string[] $args Query string parameter that can be removed.
	 * @return string[] Modified query string parameters.
	 */
	public function remove_query_args( $args ) {
		global $pagenow;

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( 'admin.php' !== $pagenow || ! isset( $_GET['page'] ) || 'wc_pre_orders' !== $_GET['page'] ) {
			return $args;
		}

		$args = array_merge(
			$args,
			array(
				'cancel_pre_order_nonce',
				'complete_pre_order_nonce',
				'customer_note',
				'customer_message',
				'order_id',
				'success',
			)
		);

		return $args;
	}

	/**
	 * Get pre-orders tabs.
	 *
	 * @return array
	 */
	protected function get_tabs() {
		$pre_orders_count = WC_Pre_Orders_Data_Store::get_pre_orders_count();
		$products_count   = WC_Pre_Orders_Data_Store::get_products_enabled_for_pre_orders_count();

		return array(
			/* translators: %d = number of pre-orders */
			'all-pre-orders'     => sprintf( __( 'Pre-orders (%d)', 'woocommerce-pre-orders' ), $pre_orders_count ),
			/* translators: %d = number of products */
			'pre-order-products' => sprintf( __( 'Products (%d)', 'woocommerce-pre-orders' ), $products_count ),
			'actions'            => __( 'Actions', 'woocommerce-pre-orders' ),
		);
	}

	/**
	 * Add 'Pre-Orders' sub-menu link under 'WooCommerce' top level menu.
	 */
	public function add_menu_link() {
		$count = WC_Pre_Orders_Data_Store::get_pre_orders_count();

		$menu_title = __( 'Pre-orders', 'woocommerce-pre-orders' );
		if ( $count > 0 ) {
			$menu_title .= ' <span class="awaiting-mod update-plugins count-' . esc_attr( $count ) . '"><span class="processing-count">' . esc_html( $count ) . '</span></span>';
		}

		$hook = add_submenu_page(
			'woocommerce',
			__( 'Pre-orders', 'woocommerce-pre-orders' ),
			$menu_title,
			'manage_woocommerce',
			'wc_pre_orders',
			array( $this, 'show_sub_menu_page' )
		);

		// add the Pre-Orders list Screen Options
		add_action( 'load-woocommerce_page_wc_pre_orders', array( $this, 'add_pre_orders_list_options' ) );
		add_action( 'load-' . $hook, array( $this, 'process_actions' ) );
	}

	/**
	 * Show Pre-Orders Manage/Actions page content.
	 */
	public function show_sub_menu_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab = ( empty( $_GET['tab'] ) ) ? 'all-pre-orders' : urldecode( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) );

		// Backward compatibility: redirect old tab names to new ones.
		if ( 'manage' === $current_tab ) {
			$current_tab = 'all-pre-orders';
		}

		echo '<div class="wrap woocommerce pre-orders">';
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">';

		// Display tabs.
		foreach ( $this->get_tabs() as $tab_id => $tab_title ) {

			$class = ( $tab_id === $current_tab ) ? 'nav-tab nav-tab-active' : 'nav-tab';
			$url   = add_query_arg( 'tab', $tab_id, admin_url( 'admin.php?page=wc_pre_orders' ) );

			printf( '<a href="%s" class="%s">%s</a>', esc_url( $url ), esc_attr( $class ), esc_attr( $tab_title ) );
		}

		echo '</h2>';

		// Show any messages.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['success'] ) ) {
			$notice_type     = 'notice-success';
			$extended_notice = array();
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			switch ( $_GET['success'] ) {

				case 'email':
					$message = __( 'Pre-order customers emailed successfully', 'woocommerce-pre-orders' );
					break;

				case 'change-date':
					$message = __( 'Pre-order date changed', 'woocommerce-pre-orders' );
					break;

				case 'complete':
					$message = __( 'Pre-orders completed', 'woocommerce-pre-orders' );
					break;

				case 'cancel':
					$message = __( 'Pre-orders cancelled', 'woocommerce-pre-orders' );
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- not required, does not modify data
					if ( isset( $_GET['product_id'] ) ) {
						// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- not required, does not modify data
						$product_id = (int) $_GET['product_id'];

						if ( WC_Pre_Orders_Product::product_is_charged_upfront( $product_id ) ) {
							$notice_type = 'notice-error';

							$extended_notice[] = sprintf(
								/* translators: 1: The name of the product that was cancelled. */
								__( 'The product %1$s is set to charge upfront. You must manually refund the customer within each order.', 'woocommerce-pre-orders' ),
								wp_strip_all_tags( wc_get_product( $product_id )->get_name() )
							);
						} else {
							$extended_notice[] = sprintf(
								/* translators: 1: The name of the product that was cancelled. */
								__( 'The product %1$s is set to charge upon release. If the product was previously set to charge customers upfront, these orders will need to be manually refunded.', 'woocommerce-pre-orders' ),
								wp_strip_all_tags( wc_get_product( $product_id )->get_name() )
							);
						}

						$extended_notice[] = sprintf(
							/* translators: 1: Opening link tag to manage pre-orders page; 2: closing link tag */
							__( 'You can view a list of %1$scancelled orders for the product%2$s on the manage pre-orders page.', 'woocommerce-pre-orders' ),
							'<a href="' . add_query_arg( '_product', (int) $product_id, admin_url( 'admin.php?page=wc_pre_orders&pre_order_status=cancelled' ) ) . '">',
							'</a>'
						);
					}

					break;

				default:
					$message = '';
					break;
			}

			if ( $message ) {
				echo '<div id="message" class="' . sanitize_html_class( $notice_type ) . ' notice fade">';
				echo '<p><strong>' . wp_kses_post( $message ) . '</strong></p>';

				if ( $extended_notice ) {
					$extended_notice = (array) $extended_notice;
					foreach ( $extended_notice as $paragraph ) {
						echo '<p>' . wp_kses_post( $paragraph ) . '</p>';
					}
				}

				echo '</div>';
			}
		}

		// Display tab content, default to 'Pre-Orders' tab.
		if ( 'pre-order-products' === $current_tab ) {
			$this->show_pre_order_products_tab();
		} elseif ( 'actions' === $current_tab ) {
			$this->show_actions_tab();
		} else {
			$this->show_all_pre_orders_tab();
		}

		echo '</div>';
	}

	/**
	 * Add the Pre-Orders list table Screen Options.
	 */
	public function add_pre_orders_list_options() {
		$args = array(
			'label'   => __( 'Pre-orders', 'woocommerce-pre-orders' ),
			'default' => 20,
			'option'  => 'wc_pre_orders_edit_pre_orders_per_page',
		);

		add_screen_option( 'per_page', $args );
	}

	/**
	 * Processes the cancelling of individual pre-order.
	 *
	 * @since 1.4.6
	 * @version 1.4.7
	 * @return bool
	 */
	public function process_cancel_pre_order_action() {
		if ( empty( $_GET['action'] ) || 'cancel_pre_order' !== $_GET['action'] ) {
			return;
		}

		if (
			empty( $_GET['cancel_pre_order_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['cancel_pre_order_nonce'] ) ), 'cancel_pre_order' )
		) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pre-orders' ) );
		}

		// User check.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have the correct permissions to do this.', 'woocommerce-pre-orders' ) );
		}

		$order_id         = ( ! empty( $_GET['order_id'] ) ) ? absint( $_GET['order_id'] ) : '';
		$customer_message = ( ! empty( $_GET['customer_note'] ) ) ? sanitize_textarea_field( wp_unslash( $_GET['customer_note'] ) ) : '';

		WC_Pre_Orders_Manager::cancel_pre_order( $order_id, $customer_message );

		/* translators: %s = order id */
		$success_message = sprintf( __( 'Pre-order #%s cancelled.', 'woocommerce-pre-orders' ), $order_id );
		if ( ! WC_Pre_Orders_Order::order_will_be_charged_upon_release( $order_id ) ) {
			$success_message .= ' ';
			$success_message .= __( 'The order was paid upfront, you will need to manually process a refund for this order.', 'woocommerce-pre-orders' );
		}

		$this->_redirect_with_notice( $success_message );
	}

	/**
	 * Processes the completion of individual pre-order.
	 *
	 * @since 2.3.0
	 */
	public function process_complete_pre_order_action() {
		if ( empty( $_GET['action'] ) || 'complete_pre_order' !== $_GET['action'] ) {
			return;
		}

		if (
			empty( $_GET['complete_pre_order_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['complete_pre_order_nonce'] ) ), 'complete_pre_order' )
		) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pre-orders' ) );
		}

		// User check.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have the correct permissions to do this.', 'woocommerce-pre-orders' ) );
		}

		$order_id         = ( ! empty( $_GET['order_id'] ) ) ? absint( $_GET['order_id'] ) : '';
		$customer_message = ( ! empty( $_GET['customer_note'] ) ) ? sanitize_textarea_field( wp_unslash( $_GET['customer_note'] ) ) : '';

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			wp_die( esc_html__( 'Invalid order.', 'woocommerce-pre-orders' ) );
		}

		WC_Pre_Orders_Manager::complete_pre_order( $order, $customer_message );

		/* translators: %s = order id */
		$success_message = sprintf( __( 'Pre-order #%s completed.', 'woocommerce-pre-orders' ), $order_id );

		$this->_redirect_with_notice( $success_message );
	}

	/**
	 * Process the actions from the 'Actions' tab.
	 */
	public function process_actions_tab() {
		global $wc_pre_orders;

		if ( empty( $_POST['wc_pre_orders_action'] ) ) {
			return;
		}

		// Security check.
		if (
			! isset( $_POST['_wpnonce'] ) ||
			! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'wc-pre-orders-process-actions' ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		) {
			wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'woocommerce-pre-orders' ) );
		}

		// User check.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have the correct permissions to do this.', 'woocommerce-pre-orders' ) );
		}

		// Get parameters.
		$action                = ( in_array( $_POST['wc_pre_orders_action'], array( 'email', 'change-date', 'complete', 'cancel' ), true ) ) ? wc_clean( wp_unslash( $_POST['wc_pre_orders_action'] ) ) : '';
		$product_id            = ( ! empty( $_POST['wc_pre_orders_action_product'] ) ) ? absint( $_POST['wc_pre_orders_action_product'] ) : '';
		$send_email            = ( isset( $_POST['wc_pre_orders_action_enable_email_notification'] ) && '1' === $_POST['wc_pre_orders_action_enable_email_notification'] ) ? true : false;
		$email_message         = ( ! empty( $_POST['wc_pre_orders_action_email_message'] ) ) ? wp_kses_post( wp_unslash( $_POST['wc_pre_orders_action_email_message'] ) ) : '';
		$new_availability_date = ( ! empty( $_POST['wc_pre_orders_action_new_availability_date'] ) ) ? sanitize_text_field( wp_unslash( $_POST['wc_pre_orders_action_new_availability_date'] ) ) : '';

		if ( ! $action || ! $product_id ) {
			return;
		}

		switch ( $action ) {

			// Email all pre-ordered customers.
			case 'email':
				WC_Pre_Orders_Manager::email_all_pre_order_customers( $product_id, $email_message );

				break;

			// Change the release date for all pre-orders.
			case 'change-date':
				// Remove email notification if disabled.
				if ( ! $send_email ) {
					remove_action( 'wc_pre_orders_pre_order_date_changed', array( $wc_pre_orders, 'send_transactional_email' ), 10 );
				}

				WC_Pre_Orders_Manager::change_release_date_for_all_pre_orders( $product_id, $new_availability_date, $email_message );

				break;

			// Complete all pre-orders.
			case 'complete':
				// Remove email notification if disabled.
				if ( ! $send_email ) {
					remove_action( 'wc_pre_order_status_completed', array( $wc_pre_orders, 'send_transactional_email' ), 10 );
				}

				WC_Pre_Orders_Manager::complete_all_pre_orders( $product_id, $email_message );

				break;

			// Cancel all pre-orders.
			case 'cancel':
				// Remove email notification if disabled.
				if ( ! $send_email ) {
					remove_action( 'wc_pre_order_status_active_to_cancelled', array( $wc_pre_orders, 'send_transactional_email' ), 10 );
				}

				WC_Pre_Orders_Manager::cancel_all_pre_orders( $product_id, $email_message );

				break;

			default:
				break;
		}

		wp_safe_redirect(
			esc_url_raw(
				add_query_arg(
					array(
						'action_default_product' => false, // Remove.
						'success'                => wp_unslash( sanitize_key( $_POST['wc_pre_orders_action'] ) ),
						'product_id'             => $product_id,
					)
				)
			)
		);
		exit;
	}

	/**
	 * Process the actions from the 'Pre-Orders' tab.
	 */
	public function process_all_pre_orders_tab() {
		if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-pre-orders' ) ) {
			return;
		}

		// Get the current action (if any).
		$action = $this->current_action();

		// Cancellation of individual pre-order should be handled by
		// self::process_cancel_pre_order_action.
		if ( 'cancel_pre_order' === $action ) {
			return;
		}

		// Completion of individual pre-order should be handled by
		// self::process_complete_pre_order_action.
		if ( 'complete_pre_order' === $action ) {
			return;
		}

		// Get the set of orders to operate on.
		$order_ids = isset( $_REQUEST['order_id'] ) ? array_map( 'absint', $_REQUEST['order_id'] ) : array();

		$message = $this->get_current_customer_message();

		// No action, or invalid action.
		if ( isset( $_GET['page'] ) && 'wc_pre_orders' === $_GET['page'] ) {

			if ( false === $action || empty( $order_ids ) ) {
				if ( ! empty( $_REQUEST['_wp_http_referer'] ) ) {
					// remove _wp_http_referer/_wp_nonce/action params
					$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					wp_safe_redirect(
						esc_url_raw(
							remove_query_arg(
								array( '_wp_http_referer', '_wpnonce', 'action', 'action2' ),
								$request_uri
							)
						)
					);
					exit;
				}
				return;
			}

			$success_count = 0;
			$error_count   = 0;
			$paid_upfront  = array();

			// Process the orders
			foreach ( $order_ids as $order_id ) {

				$order = new WC_Order( $order_id );

				// Perform the action.
				switch ( $action ) {
					case 'cancel':
						if ( WC_Pre_Orders_Manager::can_pre_order_be_changed_to( 'cancelled', $order ) ) {
							$success_count++;
							if ( ! WC_Pre_Orders_Order::order_will_be_charged_upon_release( $order ) ) {
								$paid_upfront[] = $order->get_id();
							}
							WC_Pre_Orders_Manager::cancel_pre_order( $order, $message );
						} else {
							$error_count++;
						}
						break;

					case 'complete':
						if ( WC_Pre_Orders_Manager::can_pre_order_be_changed_to( 'completed', $order ) ) {
							$success_count++;
							WC_Pre_Orders_Manager::complete_pre_order( $order, $message );
						} else {
							$error_count++;
						}
						break;

					case 'message':
						WC_Pre_Orders_Manager::email_pre_order_customer( $order_id, $message );
						break;
				}
			}

			$messages = array();

			switch ( $action ) {
				case 'cancel':
					if ( $success_count > 0 ) {
						/* translators: %d = success count */
						$messages[] = sprintf( _n( '%d pre-order cancelled.', '%d pre-orders cancelled.', $success_count, 'woocommerce-pre-orders' ), $success_count );
					}
					if ( $error_count > 0 ) {
						/* translators: %d = error count */
						$messages[] = sprintf( _n( '%d pre-order could not be cancelled.', '%d pre-orders could not be cancelled.', $error_count, 'woocommerce-pre-orders' ), $error_count );
					}

					if ( count( $paid_upfront ) > 0 ) {
						$messages[] = sprintf(
							/* translators: $d number of orders paid for upfront */
							_n( '%d order was paid upfront, you will need to manually process a refund for this order.', '%d orders were paid upfront, you will need to manually process refunds for these orders.', count( $paid_upfront ), 'woocommerce-pre-orders' ),
							count( $paid_upfront )
						);
					}

					break;

				case 'complete':
					if ( $success_count > 0 ) {
						/* translators: %d = success count */
						$messages[] = sprintf( _n( '%d pre-order completed.', '%d pre-orders completed.', $success_count, 'woocommerce-pre-orders' ), $success_count );
					}
					if ( $error_count > 0 ) {
						/* translators: %d = error count */
						$messages[] = sprintf( _n( '%d pre-order could not be completed.', '%d pre-orders could not be completed.', $error_count, 'woocommerce-pre-orders' ), $error_count );
					}
					break;

				case 'message':
					/* translators: %d = The count of emails dispatched */
					$messages[] = sprintf( _n( '%d email dispatched.', '%d emails dispatched.', count( $order_ids ), 'woocommerce-pre-orders' ), count( $order_ids ) );
					break;
			}

			$this->_redirect_with_notice( implode( '  ', $messages ) );
		}
	}

	/**
	 * Get the current action selected from the bulk actions dropdown, verifying
	 * that it's a valid action to perform.
	 *
	 * @see WP_List_Table::current_action()
	 *
	 * @return string|bool The action name or False if no action was selected.
	 */
	public function current_action() {
		$current_action = false;

		if ( isset( $_REQUEST['action'] ) && -1 !== sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		if ( isset( $_REQUEST['action2'] ) && -1 !== sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_action = sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$valid_actions   = array_keys( $this->get_bulk_actions() );
		$valid_actions[] = 'cancel_pre_order';
		$valid_actions[] = 'complete_pre_order';

		if ( $current_action && ! in_array( $current_action, $valid_actions ) ) {
			return false;
		}

		return $current_action;
	}

	/**
	 * Dispatch actions from Pre-Orders tab and Actions tab.
	 *
	 * @since 1.0
	 */
	public function process_actions() {
		$this->process_actions_tab();
		$this->process_all_pre_orders_tab();
		$this->process_cancel_pre_order_action();
		$this->process_complete_pre_order_action();
	}

	/**
	 * Gets the bulk actions available for pre-orders: complete, cancel or message.
	 *
	 * @see WP_List_Table::get_bulk_actions()
	 *
	 * @return array associative array of action_slug => action_title.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'cancel'   => __( 'Cancel', 'woocommerce-pre-orders' ),
			'complete' => __( 'Complete', 'woocommerce-pre-orders' ),
			'message'  => __( 'Customer message', 'woocommerce-pre-orders' ),
		);

		return $actions;
	}

	/**
	 * Gets the current customer message which is used for bulk actions.
	 *
	 * @return string the current customer message.
	 */
	public function get_current_customer_message() {
		if ( isset( $_REQUEST['customer_message'] ) && sanitize_text_field( wp_unslash( $_REQUEST['customer_message'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_text_field( wp_unslash( $_REQUEST['customer_message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		return null;
	}

	/**
	 * Loads the pre-orders list table so the columns can be hidden/shown from
	 * the page Screen Options dropdown (this must be done prior to Screen Options
	 * being rendered).
	 */
	public function load_pre_orders_list_table() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['page'] ) && 'wc_pre_orders' === $_GET['page'] ) {
			$this->get_pre_orders_list_table();
		}
	}

	/**
	 * Gets the pre-orders list table object.
	 *
	 * @return WC_Pre_Orders_List_Table the pre-orders list table object
	 */
	private function get_pre_orders_list_table() {
		global $wc_pre_orders;

		if ( ! isset( $this->pre_orders_list_table ) ) {

			$class_name = apply_filters( 'wc_pre_orders_list_table_class_name', 'WC_Pre_Orders_List_Table' );

			require $wc_pre_orders->get_plugin_path() . '/includes/class-wc-pre-orders-list-table.php';
			$this->pre_orders_list_table = new $class_name();
		}

		return $this->pre_orders_list_table;
	}

	/**
	 * Show the Pre-Orders > Pre-Orders tab content.
	 *
	 * @since 2.3.0
	 */
	private function show_all_pre_orders_tab() {
		// Setup 'Pre-Orders' list table and prepare the data.
		$manage_table = $this->get_pre_orders_list_table();
		$manage_table->prepare_items();

		echo '<form method="get" id="mainform" action="" enctype="multipart/form-data">';
		// title/search result string
		echo '<h1>' . esc_html__( 'Pre-orders', 'woocommerce-pre-orders' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['s'] ) && sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) {
			$search_query = sanitize_text_field( wp_unslash( $_GET['s'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			/* translators: %s = The search query */
			echo '<span class="subtitle">' . esc_html__( 'Search results for:', 'woocommerce-pre-orders' ) . ' <strong>' . esc_html( sanitize_text_field( wp_unslash( $_GET['s'] ) ) ) . '</strong></span>'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		echo '</h1>';

		// display any action messages
		$manage_table->render_messages();

		$manage_table->views();
		$manage_table->search_box( __( 'Search pre-orders', 'woocommerce-pre-orders' ), 'pre_order' );

		$pre_order_status = isset( $_REQUEST['pre_order_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['pre_order_status'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! empty( $pre_order_status ) ) {
			echo '<input type="hidden" name="pre_order_status" value="' . esc_attr( $pre_order_status ) . '" />';
		}

		if ( ! empty( $_page ) ) {
			echo '<input type="hidden" name="page" value="' . esc_attr( $_page ) . '" />';
		}

		// display the list table
		$manage_table->display();
		echo '</form>';
	}

	/**
	 * Show the Pre-Orders > Pre-Order Products tab content.
	 *
	 * @since 2.3.0
	 */
	private function show_pre_order_products_tab() {
		$pre_order_products = WC_Pre_Orders_Data_Store::get_products_enabled_for_pre_orders();

		echo '<div class="wrap">';
		echo '<h2>' . esc_html__( 'Pre-Order Products', 'woocommerce-pre-orders' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'View all products with pre-orders enabled. Manage pre-order settings, view pre-order counts, and take actions on active pre-orders.', 'woocommerce-pre-orders' ) . '</p>';

		// Display messages.
		$this->render_tab_messages();

		if ( empty( $pre_order_products ) ) {
			// Display onboarding message when no pre-orders exist.
			echo '<div class="notice notice-info" style="margin-top: 20px;">';
			echo '<h3>' . esc_html__( 'Get started with Pre-Orders', 'woocommerce-pre-orders' ) . '</h3>';
			echo '<p>' . esc_html__( 'No pre-order products found. You can enable pre-orders for products to start taking pre-orders from customers.', 'woocommerce-pre-orders' ) . '</p>';
			echo '<p>';
			/* translators: %1$s = Opening anchor tag for adding new product, %2$s = closing anchor tag */
			printf( esc_html__( '%1$sSetup a product to allow pre-orders%2$s', 'woocommerce-pre-orders' ), '<a href="' . esc_url( admin_url( 'post-new.php?post_type=product' ) ) . '" class="button button-primary">', '</a>' );
			echo ' ';
			/* translators: %1$s = Opening anchor tag for WooCommerce Pre-Orders documentation, %2$s = closing anchor tag */
			printf( esc_html__( '%1$sLearn more about managing pre-orders%2$s', 'woocommerce-pre-orders' ), '<a href="http://docs.woothemes.com/document/pre-orders/" target="_blank" class="button">', '</a>' );
			echo '</p>';
			echo '</div>';
		} else {
			// Display the products table.
			$this->render_pre_order_products_table( $pre_order_products );
		}

		echo '</div>';
	}

	/**
	 * Render the pre-order products table.
	 *
	 * @since 2.3.0
	 * @param array $products Array of WC_Product objects.
	 */
	private function render_pre_order_products_table( $products ) {
		echo '<table class="wp-list-table widefat fixed striped products-table">';
		echo '<thead>';
		echo '<tr>';
		echo '<th scope="col" class="manage-column column-product">' . esc_html__( 'Product', 'woocommerce-pre-orders' ) . '</th>';
		echo '<th scope="col" class="manage-column column-pre-orders">' . esc_html__( 'Pre-Orders', 'woocommerce-pre-orders' ) . '</th>';
		echo '<th scope="col" class="manage-column column-availability">' . esc_html__( 'Available Date', 'woocommerce-pre-orders' ) . '</th>';
		echo '<th scope="col" class="manage-column column-status">' . esc_html__( 'Pre-order status', 'woocommerce-pre-orders' ) . '</th>';
		echo '<th scope="col" class="manage-column column-actions">' . esc_html__( 'Available actions', 'woocommerce-pre-orders' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		foreach ( $products as $product ) {
			$this->render_pre_order_product_row( $product );
		}

		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Render a single pre-order product row.
	 *
	 * @since 2.3.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param array $product The product data.
	 */
	private function render_pre_order_product_row( $product ) {
		global $wpdb;

		$product_id       = $product['id'];
		$pre_orders_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(order_items.order_item_id) AS count
				FROM {$wpdb->prefix}woocommerce_order_items as order_items
				JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_itemmeta
					ON order_itemmeta.order_item_id = order_items.order_item_id
				WHERE meta_key = '_product_id' AND meta_value = %d",
				$product_id
			)
		);

		$availability_timestamp = (int) $product['availability_datetime']; // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		$product_edit_url       = get_edit_post_link( $product_id );
		$all_orders_url         = add_query_arg( '_product_id', $product_id, admin_url( 'admin.php?page=wc_pre_orders&tab=all-pre-orders' ) );
		$status_counts          = WC_Pre_Orders_Data_Store::get_pre_order_product_status_counts( $product_id );

		echo '<tr>';

		// Product column.
		echo '<td class="column-product">';
		if ( $product_edit_url ) {
			echo '<a href="' . esc_url( $product_edit_url ) . '">' . esc_html( $product['label'] ) . '</a>';
		} else {
			echo esc_html( $product['label'] );
		}
		echo '</td>';

		// Pre-Orders count column.
		echo '<td class="column-pre-orders">';
		if ( $pre_orders_count > 0 ) {
			echo '<a href="' . esc_url( $all_orders_url ) . '">' . esc_html( $pre_orders_count ) . '</a>';
		} else {
			echo '0';
		}
		echo '</td>';

		// Availability date column.
		echo '<td class="column-availability">' . wp_kses_post( WC_Pre_Orders_List_Table::format_date( $availability_timestamp ) ) . '</td>';

		// Status column.
		echo '<td class="column-status">';
		if ( $pre_orders_count > 0 ) {
			echo esc_html( $status_counts['active'] ) . ' ' . esc_html__( 'active', 'woocommerce-pre-orders' ) . ' | ';
			echo esc_html( $status_counts['completed'] ) . ' ' . esc_html__( 'completed', 'woocommerce-pre-orders' ) . ' | ';
			echo esc_html( $status_counts['cancelled'] ) . ' ' . esc_html__( 'cancelled', 'woocommerce-pre-orders' );
		} else {
			echo '---';
		}
		echo '</td>';

		// Actions column.
		echo '<td class="column-actions">';

		// Change release date action - available for all products with pre-orders enabled
		$change_date_url = add_query_arg(
			array(
				'tab'                    => 'actions',
				'section'                => 'change-date',
				'action_default_product' => $product_id,
			),
			admin_url( 'admin.php?page=wc_pre_orders' )
		);
		echo '<a href="' . esc_url( $change_date_url ) . '" class="button button-small change-date"><span class="dashicons dashicons-calendar-alt"></span><span class="button-text">' . esc_html__( 'Change Date', 'woocommerce-pre-orders' ) . '</span></a> ';

		// Only show other action buttons if there are active pre-orders
		if ( isset( $status_counts['active'] ) && $status_counts['active'] > 0 ) {
			// Email customers action.
			$email_url = add_query_arg(
				array(
					'tab'                    => 'actions',
					'section'                => 'email',
					'action_default_product' => $product_id,
				),
				admin_url( 'admin.php?page=wc_pre_orders' )
			);
			echo '<a href="' . esc_url( $email_url ) . '" class="button button-small email-customers"><span class="dashicons dashicons-email-alt"></span><span class="button-text">' . esc_html__( 'Email Customers', 'woocommerce-pre-orders' ) . '</span></a> ';

			// Complete pre-orders action.
			$complete_url = add_query_arg(
				array(
					'tab'                    => 'actions',
					'section'                => 'complete',
					'action_default_product' => $product_id,
				),
				admin_url( 'admin.php?page=wc_pre_orders' )
			);
			echo '<a href="' . esc_url( $complete_url ) . '" class="button button-small complete-pre-orders"><span class="dashicons dashicons-yes"></span><span class="button-text">' . esc_html__( 'Complete Pre-Orders', 'woocommerce-pre-orders' ) . '</span></a> ';

			// Cancel pre-orders action.
			$cancel_url = add_query_arg(
				array(
					'tab'                    => 'actions',
					'section'                => 'cancel',
					'action_default_product' => $product_id,
				),
				admin_url( 'admin.php?page=wc_pre_orders' )
			);
			echo '<a href="' . esc_url( $cancel_url ) . '" class="button button-small button-link-delete"><span class="dashicons dashicons-no"></span><span class="button-text">' . esc_html__( 'Cancel Pre-Orders', 'woocommerce-pre-orders' ) . '</span></a>';
		} else {
			echo '<span class="na">' . esc_html__( 'No other actions available as this product has no active pre-orders.', 'woocommerce-pre-orders' ) . '</span>';
		}

		echo '</td>';
		echo '</tr>';
	}

	/**
	 * Get the fields to display for the selected action, in the format required by woocommerce_admin_fields().
	 *
	 * @param  string $section The current section to get fields for.
	 *
	 * @return array
	 */
	private function get_action_fields( $section ) {

		$products = array( '' => __( 'Select a product', 'woocommerce-pre-orders' ) );

		// Getting only ['label'] from WC_Pre_Orders_Data_Store::get_pre_order_products()
		$products = array();
		// For change-date section, include all products with pre-orders enabled (including zero pre-orders)
		if ( 'change-date' === $section ) {
			foreach ( WC_Pre_Orders_Data_Store::get_products_enabled_for_pre_orders() as $product ) {
				$products[ $product['id'] ] = $product['label'];
			}
		} else {
			// For other sections, only include products with active pre-orders
			foreach ( WC_Pre_Orders_Data_Store::get_pre_order_products() as $product ) {
				$products[ $product['id'] ] = $product['label'];
			}
		}

		$fields = array(

			'email'       => array(

				array(
					'name' => __( 'Email pre-order customers', 'woocommerce-pre-orders' ),
					'desc' => sprintf(
						/* translators: %1$s: opening <a> tag linking to the customer note email settings; %2$s: closing </a> tag */
						__( 'Email all customers who have pre-ordered this product using the default %1$sCustomer Note%2$s email template. <strong>Note that only customers with active pre-orders will be emailed.</strong>', 'woocommerce-pre-orders' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_email_customer_note' ) ) . '">',
						'</a>'
					),
					'type' => 'title',
				),

				array(
					'id'                => 'wc_pre_orders_action_product',
					'name'              => __( 'Product', 'woocommerce-pre-orders' ),
					'desc_tip'          => __( 'Select the product whose pre-order customers will be emailed.', 'woocommerce-pre-orders' ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'default'           => ( ! empty( $_GET['action_default_product'] ) ) ? absint( $_GET['action_default_product'] ) : '',
					'options'           => $products,
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'custom_attributes' => array(
						'required' => 'required',
					),
				),

				array(
					'id'                => 'wc_pre_orders_action_email_message',
					'name'              => __( 'Message', 'woocommerce-pre-orders' ),
					'desc_tip'          => __( 'Enter a message to include in the customer notification email. No HTML allowed.', 'woocommerce-pre-orders' ),
					'css'               => 'min-width: 300px;',
					'default'           => '',
					'type'              => 'textarea',
					'custom_attributes' => array(
						'required' => 'required',
					),
				),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Email customers', 'woocommerce-pre-orders' ),
					'type' => 'submit_button',
				),
			),

			'change-date' => array(

				array(
					'name' => __( 'Change release date', 'woocommerce-pre-orders' ),
					'desc' => __( 'Change the release date for all <strong>active</strong> pre-orders of this product. Customers will be notified by email with the new date.', 'woocommerce-pre-orders' ),
					'type' => 'title',
				),

				array(
					'id'                => 'wc_pre_orders_action_product',
					'name'              => __( 'Product', 'woocommerce-pre-orders' ),
					'desc_tip'          => __( 'Select the product to change the release date for.', 'woocommerce-pre-orders' ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'default'           => ( ! empty( $_GET['action_default_product'] ) ) ? absint( $_GET['action_default_product'] ) : '',
					'options'           => $products,
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'custom_attributes' => array(
						'required' => 'required',
					),
				),

				array(
					'id'                => 'wc_pre_orders_action_new_availability_date',
					'name'              => __( 'New availability date', 'woocommerce-pre-orders' ),
					'desc_tip'          => __( 'Enter the new availability date. Must be later than the current availability date.', 'woocommerce-pre-orders' ),
					'default'           => '',
					'type'              => 'text',
					'custom_attributes' => array(
						'required' => 'required',
					),
				),

				array(
					'id'      => 'wc_pre_orders_action_enable_email_notification',
					'name'    => __( 'Send email notification', 'woocommerce-pre-orders' ),
					'desc'    => __( 'Uncheck to prevent email notifications from being sent to customers.', 'woocommerce-pre-orders' ),
					'default' => 'yes',
					'type'    => 'checkbox',
				),

				array(
					'id'       => 'wc_pre_orders_action_email_message',
					'name'     => __( 'Message', 'woocommerce-pre-orders' ),
					'desc_tip' => __( 'Enter a message to include in the email notification. No HTML allowed.', 'woocommerce-pre-orders' ),
					'default'  => '',
					'css'      => 'min-width: 300px;',
					'type'     => 'textarea',
				),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Change release date', 'woocommerce-pre-orders' ),
					'type' => 'submit_button',
				),
			),

			'complete'    => array(

				array(
					'name' => __( 'Complete pre-orders', 'woocommerce-pre-orders' ),
					'desc' => __( 'Complete all pre-orders for this product. Charges the customer\'s card, marks orders as completed, and sends an email notification.', 'woocommerce-pre-orders' ),
					'type' => 'title',
				),

				array(
					'id'                => 'wc_pre_orders_action_product',
					'name'              => __( 'Product', 'woocommerce-pre-orders' ),
					'desc_tip'          => __( 'Select the product to complete all pre-orders for.', 'woocommerce-pre-orders' ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'default'           => ( ! empty( $_GET['action_default_product'] ) ) ? absint( $_GET['action_default_product'] ) : '',
					'options'           => $products,
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'custom_attributes' => array(
						'required' => 'required',
					),
				),

				array(
					'id'      => 'wc_pre_orders_action_enable_email_notification',
					'name'    => __( 'Send email notification', 'woocommerce-pre-orders' ),
					'desc'    => __( 'Uncheck to prevent email notifications from being sent to customers.', 'woocommerce-pre-orders' ),
					'default' => 'yes',
					'type'    => 'checkbox',
				),

				array(
					'id'       => 'wc_pre_orders_action_email_message',
					'name'     => __( 'Message', 'woocommerce-pre-orders' ),
					'desc_tip' => __( 'Enter a message to include in the email notification. No HTML allowed.', 'woocommerce-pre-orders' ),
					'default'  => '',
					'css'      => 'min-width: 300px;',
					'type'     => 'textarea',
				),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Complete pre-orders', 'woocommerce-pre-orders' ),
					'type' => 'submit_button',
				),
			),

			'cancel'      => array(

				array(
					'name' => __( 'Cancel pre-orders', 'woocommerce-pre-orders' ),
					'desc' => __( 'Cancel all pre-orders for this product. Marks orders as cancelled and sends an email notification. If pre-orders were charged upfront, process refunds manually.', 'woocommerce-pre-orders' ),
					'type' => 'title',
				),

				array(
					'id'                => 'wc_pre_orders_action_product',
					'name'              => __( 'Product', 'woocommerce-pre-orders' ),
					'desc_tip'          => __( 'Select the product to cancel all pre-orders for.', 'woocommerce-pre-orders' ),
					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					'default'           => ( ! empty( $_GET['action_default_product'] ) ) ? absint( $_GET['action_default_product'] ) : '',
					'options'           => $products,
					'type'              => 'select',
					'class'             => 'wc-enhanced-select',
					'custom_attributes' => array(
						'required' => 'required',
					),
				),

				array(
					'id'      => 'wc_pre_orders_action_enable_email_notification',
					'name'    => __( 'Send email notification', 'woocommerce-pre-orders' ),
					'desc'    => __( 'Uncheck to prevent email notifications from being sent to customers.', 'woocommerce-pre-orders' ),
					'default' => 'yes',
					'type'    => 'checkbox',
				),

				array(
					'id'       => 'wc_pre_orders_action_email_message',
					'name'     => __( 'Message', 'woocommerce-pre-orders' ),
					'desc_tip' => __( 'Enter a message to include in the email notification. No HTML allowed.', 'woocommerce-pre-orders' ),
					'default'  => '',
					'css'      => 'min-width: 300px;',
					'type'     => 'textarea',
				),

				array( 'type' => 'sectionend' ),

				array(
					'name' => __( 'Cancel pre-orders', 'woocommerce-pre-orders' ),
					'type' => 'submit_button',
				),
			),

		);

		return ( isset( $fields[ $section ] ) ) ? $fields[ $section ] : array();
	}

	/**
	 * Generate a submit button, called via a do_action() inside woocommerce_admin_fields() for non-default field types.
	 *
	 * @param array $field The field info.
	 */
	public function generate_submit_button( $field ) {
		submit_button( $field['name'] );
	}

	/**
	 * Save our list option.
	 *
	 * @param  string $status unknown.
	 * @param  string $option the option name.
	 * @param  string $value the option value.
	 *
	 * @return string
	 */
	public function set_pre_orders_list_option( $status, $option, $value ) {
		if ( 'wc_pre_orders_edit_pre_orders_per_page' === $option ) {
			return $value;
		}

		return $status;
	}

	/**
	 * Redirect with message notice.
	 *
	 * @since 1.4.7
	 *
	 * @param string $message Message to display
	 */
	protected function _redirect_with_notice( $message ) {
		$message_nonce = wp_create_nonce( __FILE__ );

		set_transient( $this->message_transient_prefix . $message_nonce, array( 'messages' => $message ), 60 * 60 );

		// Get our next destination, stripping out all actions and other unneeded parameters.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['_wp_http_referer'] ) ) {
			$redirect_url = sanitize_text_field( wp_unslash( $_REQUEST['_wp_http_referer'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$redirect_url = remove_query_arg(
				array(
					'_wp_http_referer',
					'_wpnonce',
					'action',
					'action2',
					'order_id',
					'customer_message',
					'product_id',
					'complete_pre_order_nonce',
					'cancel_pre_order_nonce',
					'customer_note',
					'message',
				),
				sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) )
			);
		}

		wp_safe_redirect( esc_url_raw( add_query_arg( 'message', $message_nonce, $redirect_url ) ) );
		exit;
	}

	/**
	 * Render tab messages.
	 *
	 * @since 2.3.0
	 */
	private function render_tab_messages() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['message'] ) ) {
			$message_nonce = sanitize_text_field( wp_unslash( $_GET['message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$messages      = get_transient( $this->message_transient_prefix . $message_nonce );

			if ( $messages && is_array( $messages ) && isset( $messages['messages'] ) ) {
				echo '<div id="message" class="notice notice-success fade">';
				echo '<p><strong>' . wp_kses_post( $messages['messages'] ) . '</strong></p>';
				echo '</div>';

				// Clear the transient after displaying the message
				delete_transient( $this->message_transient_prefix . $message_nonce );
			}
		}
	}

	/**
	 * Show the Pre-Orders > Actions tab content.
	 */
	private function show_actions_tab() {
		global $woocommerce;

		// Load file for woocommerce_admin_fields() usage.
		if ( file_exists( $woocommerce->plugin_path() . '/includes/admin/wc-admin-functions.php' ) ) {
			require_once $woocommerce->plugin_path() . '/includes/admin/wc-admin-functions.php';
		} else {
			require_once $woocommerce->plugin_path() . '/admin/woocommerce-admin-settings.php';
		}

		$pre_order_products = WC_Pre_Orders_Data_Store::get_pre_order_products();
		if ( empty( $pre_order_products ) ) {
			?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( 'No pre-order products found. Pre-order products will appear in the Product dropdown below.', 'woocommerce-pre-orders' ); ?></p>
			</div>
			<?php
		}

		// Add 'submit_button' woocommerce_admin_fields() field type.
		add_action(
			'woocommerce_admin_field_submit_button',
			array( $this, 'generate_submit_button' )
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_section = ( empty( $_REQUEST['section'] ) ) ? 'email' : urldecode( sanitize_text_field( wp_unslash( $_REQUEST['section'] ) ) );

		$actions = array(
			'email'       => __( 'Email customers', 'woocommerce-pre-orders' ),
			'change-date' => __( 'Change release date', 'woocommerce-pre-orders' ),
			'complete'    => __( 'Complete pre-orders', 'woocommerce-pre-orders' ),
			'cancel'      => __( 'Cancel pre-orders', 'woocommerce-pre-orders' ),
		);

		// Preserve action_default_product parameter if it exists
		$query_args = array( 'section' => $current_section );
		if ( ! empty( $_REQUEST['action_default_product'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$query_args['action_default_product'] = sanitize_text_field( wp_unslash( $_REQUEST['action_default_product'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		foreach ( $actions as $action_id => $action_title ) {
			$current = ( $action_id === $current_section ) ? ' class="current"' : '';

			$query_args['section'] = $action_id;

			$links[] = sprintf(
				'<a href="%s"%s>%s</a>',
				add_query_arg(
					$query_args,
					admin_url( 'admin.php?page=wc_pre_orders&tab=actions' )
				),
				$current,
				$action_title
			);
		}

		echo '<ul class="subsubsub"><li>' . wp_kses_post( implode( ' | </li><li>', $links ) ) . '</li></ul><br class="clear" />';
		echo '<form method="post" id="mainform" action="" enctype="multipart/form-data">';
		woocommerce_admin_fields( $this->get_action_fields( $current_section ) );
		wp_nonce_field( 'wc-pre-orders-process-actions' );
		echo '<input type="hidden" name="wc_pre_orders_action" value="' . esc_attr( $current_section ) . '" /></form>';
	}

	/**
	 * Prevents duplicate emails when pre-orders are completed.
	 *
	 * Bug fix: When a pre-order is completed (either when product is released or admin
	 * completes it manually), customers were receiving duplicate emails:
	 * 1) Standard WooCommerce "Your order was received" email
	 * 2) Pre-order specific "Your pre-order is available" email
	 *
	 * Also, merchants were receiving unnecessary "New order" emails for pre-orders
	 * that were already created previously.
	 *
	 * This method disables the standard WooCommerce emails for pre-orders to ensure
	 * customers only receive the appropriate pre-order specific notifications.
	 *
	 * @since 2.3.0
	 */
	public function prevent_duplicate_preorder_completion_emails() {
		// Prevent duplicate "New order" email to admin when pre-order is completed.
		add_filter( 'woocommerce_email_enabled_new_order', array( $this, 'should_disable_email_for_preorder' ), 10, 2 );

		// Prevent duplicate "Processing order" email to customer.
		add_filter( 'woocommerce_email_enabled_customer_processing_order', array( $this, 'should_disable_email_for_preorder' ), 10, 2 );

		// Prevent duplicate "Order complete" email to customer.
		add_filter( 'woocommerce_email_enabled_customer_completed_order', array( $this, 'should_disable_email_for_preorder' ), 10, 2 );
	}

	/**
	 * Determine if WooCommerce email should be disabled for pre-orders.
	 *
	 * This prevents customers from receiving confusing duplicate emails when
	 * their pre-order is completed. They should only receive the pre-order
	 * specific "Your pre-order is available" email, not the standard WooCommerce
	 * order emails.
	 *
	 * @since 2.3.0
	 * @param bool     $enabled Whether the email is enabled.
	 * @param WC_Order $order   The order object.
	 * @return bool Whether the email should be sent (false for pre-orders).
	 */
	public function should_disable_email_for_preorder( $enabled, $order ): bool {
		// Bail if email already disabled or order is not valid.
		if ( ! $enabled || ! is_object( $order ) ) {
			return $enabled;
		}

		// Check if this is a pre-order.
		$is_preorder = WC_Pre_Orders::is_hpos_enabled()
			? $order->get_meta( '_wc_pre_orders_is_pre_order', true )
			: get_post_meta( $order->get_id(), '_wc_pre_orders_is_pre_order', true );

		// Disable standard emails for pre-orders to prevent duplicates.
		$result = $is_preorder ? false : $enabled;
		return $result;
	}
}

new WC_Pre_Orders_Admin_Pre_Orders();
