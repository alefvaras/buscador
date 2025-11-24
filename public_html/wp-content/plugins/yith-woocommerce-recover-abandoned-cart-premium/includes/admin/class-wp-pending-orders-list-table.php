<?php //phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Recover Abandoned Cart
 */


use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abandoned Carts List Table
 *
 * @class   YITH_YWRAC_Pending_Orders_List_Table
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author YITH <plugins@yithemes.com>
 */
class YITH_YWRAC_Pending_Orders_List_Table extends WP_List_Table {
	/**
	 * Post type
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * YITH_YWRAC_Pending_Orders_List_Table constructor.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = array() ) {
		parent::__construct( array() );
		$this->post_type = 'shop_order';
	}

	/**
	 * Get columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'post_title'   => esc_html__( 'Order', 'yith-woocommerce-recover-abandoned-cart' ),
			'purchased'    => esc_html__( 'Purchased', 'yith-woocommerce-recover-abandoned-cart' ),
			'date'         => esc_html__( 'Date', 'yith-woocommerce-recover-abandoned-cart' ),
			'total'        => esc_html__( 'Total', 'yith-woocommerce-recover-abandoned-cart' ),
			'status_email' => esc_html__( 'Last email sent', 'yith-woocommerce-recover-abandoned-cart' ),
			'action'       => esc_html__( 'Action', 'yith-woocommerce-recover-abandoned-cart' ),
		);
		return $columns;
	}

	/**
	 * Prepare items
	 */
	public function prepare_items() {
		global $wpdb, $_wp_column_headers;

		$screen  = get_current_screen();
		$columns = $this->get_columns();

		$hidden                = array();
		$sortable              = array();

		$this->_column_headers             = array( $columns, $hidden, $sortable );
		$_wp_column_headers[ $screen->id ] = $columns;

		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$limit = $this->get_items_per_page( 'edit_' . $this->post_type . '_per_page' );

			$args = array(
				'limit'    => $limit,
				'page'     => $this->get_pagenum(),
				'paginate' => true,
				'status'   => array( 'wc-pending' ),
				'type'     => $this->post_type,
			);
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$customer = (int) wp_unslash( $_REQUEST['_customer_user'] ?? '' );

			if ( $customer > 0 ) {
				$args['customer'] = $customer;
			}

			$orders      = wc_get_orders( $args );
			$this->items = $orders->orders;

			$max_num_pages = $orders->max_num_pages;

			// Check in case the user has attempted to page beyond the available range of orders.
			if ( 0 === $max_num_pages && $args['page'] > 1 ) {
				$count_query_args          = $args;
				$count_query_args['page']  = 1;
				$count_query_args['limit'] = 1;
				$order_count               = wc_get_orders( $count_query_args );
				$max_num_pages             = (int) ceil( $order_count->total / $args['limit'] );
			}

			$this->set_pagination_args(
				array(
					'total_items' => $orders->total ?? 0,
					'per_page'    => $limit,
					'total_pages' => $max_num_pages,
				)
			);
		} else {
			$order_string = 'ORDER BY ywrac_p.post_date DESC ';

			$join  = ' LEFT JOIN ' . $wpdb->prefix . "postmeta as ywrac_pm6 ON ywrac_p.ID =  ywrac_pm6.post_id  AND  ywrac_pm6.meta_key = 'is_a_renew' ";
			$where = '';

			if ( isset( $_REQUEST['_customer_user'] ) && ! empty( $_REQUEST['_customer_user'] ) ) { //phpcs:ignore
				$customers = sanitize_text_field( wp_unslash( $_REQUEST['_customer_user'] ) ); //phpcs:ignore
				$join      .= 'INNER JOIN ' . $wpdb->prefix . 'postmeta as ywrac_pm5 ON ( ywrac_p.ID =  ywrac_pm5.post_id ) ';
				$where     .= " AND ( ywrac_pm5.meta_key = '_customer_user' AND ywrac_pm5.meta_value = '" . $customers . "' )";
			}

			$query = $wpdb->prepare(
				"SELECT ywrac_p.* FROM $wpdb->posts AS ywrac_p  $join
            WHERE 	ywrac_p.post_type 	= '%s' $where 
            AND 	ywrac_p.post_status 	= 'wc-pending' 
            AND ywrac_pm6.meta_key IS NULL or ywrac_pm6.meta_value != 'yes'
             $order_string",
				$this->post_type
			);

			$totalitems = $wpdb->query( $query ); //phpcs:ignore

			$perpage = 10;
			// Which page is this?.
			$paged = ! empty( $_GET['paged'] ) ? sanitize_text_field( wp_unslash( $_GET['paged'] ) ) : ''; //phpcs:ignore
			// Page Number.
			if ( empty( $paged ) || ! is_numeric( $paged ) || $paged <= 0 ) {
				$paged = 1;
			}
			// How many pages do we have in total?.
			$totalpages = ceil( $totalitems / $perpage );
			// adjust the query to take pagination into account.
			if ( ! empty( $paged ) && ! empty( $perpage ) ) {
				$offset = ( $paged - 1 ) * $perpage;
				$query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
			}

			/* -- Register the pagination -- */
			$this->set_pagination_args(
				array(
					'total_items' => $totalitems,
					'total_pages' => $totalpages,
					'per_page'    => $perpage,
				)
			);
			// The pagination links are automatically built according to those parameters.

			$this->items                       = $wpdb->get_results( $query ); //phpcs:ignore
		}
	}

	/**
	 * Column default
	 *
	 * @param array|object $item Items.
	 * @param string       $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$the_order = $item;
		} else {
			$the_order = wc_get_order( $item->ID );
		}
		$billing_phone      = $the_order->get_billing_phone();
		$billing_first_name = $the_order->get_billing_first_name();
		$billing_last_name  = $the_order->get_billing_last_name();
		$billing_email      = $the_order->get_billing_email();
		$user_id            = $the_order->get_customer_id();
		switch ( $column_name ) {
			case 'post_title':
				$customer_tip = array();
				$address      = $the_order->get_formatted_billing_address();
				if ( $address ) {
					$customer_tip[] = esc_html__( 'Billing:', 'woocommerce' ) . ' ' . $address . '<br/><br/>';
				}

				if ( $billing_phone ) {
					$customer_tip[] = esc_html__( 'Tel:', 'woocommerce' ) . ' ' . $billing_phone;
				}

				echo '<div class="tips" data-tip="' . wp_kses_post( wc_sanitize_tooltip( implode( '<br/>', $customer_tip ) ) ) . '">';

				if ( $user_id ) {
					$user_info = get_userdata( $user_id );
				}

				if ( ! empty( $user_info ) ) {

					$username = '<a href="user-edit.php?user_id=' . absint( $user_info->ID ) . '">';

					if ( $user_info->first_name || $user_info->last_name ) {
						$username .= esc_html( ucfirst( $user_info->first_name ) . ' ' . ucfirst( $user_info->last_name ) );
					} else {
						$username .= esc_html( ucfirst( $user_info->display_name ) );
					}

					$username .= '</a>';

				} else {
					if ( $billing_first_name || $billing_last_name ) {
						$username = trim( $billing_first_name . ' ' . $billing_last_name );
					} else {
						$username = __( 'Guest', 'woocommerce' );
					}
				}

				// translators: Order number by X.
				printf( wp_kses_post( _x( '%1$s by %2$s', 'Order number by X', 'woocommerce' ) ), '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $the_order->get_id() ) . '&action=edit' ) ) . '"><strong>#' . esc_attr( $the_order->get_order_number() ) . '</strong></a>', wp_kses_post( $username ) );

				if ( $billing_email ) {
					echo '<small class="meta email"><a href="' . esc_url( 'mailto:' . $billing_email ) . '">' . esc_html( $billing_email ) . '</a></small>';
				}

				echo '</div>';
				break;
			case 'purchased':
				echo wp_kses_post( apply_filters( 'woocommerce_admin_order_item_count', sprintf( _n( '%d item', '%d items', $the_order->get_item_count(), 'woocommerce' ), $the_order->get_item_count() ), $the_order ) );

				break;
			case 'date':
				if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
					$order_timestamp = $item->get_date_created() ? $item->get_date_created()->getTimestamp() : '';

					if ( ! $order_timestamp ) {
						return '&ndash;';
					}

					// Check if the order was created within the last 24 hours, and not in the future.
					if ( $order_timestamp > strtotime( '-1 day', time() ) && $order_timestamp <= time() ) {
						$show_date = sprintf(
						/* translators: %s: human-readable time difference */
							_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
							human_time_diff( $item->get_date_created()->getTimestamp(), time() )
						);
					} else {
						$show_date = $item->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'woocommerce' ) ) ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
					}
					$date = sprintf(
						'<time datetime="%1$s" title="%2$s">%3$s</time>',
						esc_attr( $item->get_date_created()->date( 'c' ) ),
						esc_html( $item->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
						esc_html( $show_date )
					);
				} else {
					if ( '0000-00-00 00:00:00' === $item->post_date ) {
						$t_time = esc_html__( 'Unpublished', 'woocommerce' );
						$h_time = esc_html__( 'Unpublished', 'woocommerce' );
					} else {
						$t_time = get_the_time( __( 'Y/m/d g:i:s A', 'woocommerce' ), $item );
						$h_time = get_the_time( __( 'Y/m/d', 'woocommerce' ), $item );
					}

					$date = '<abbr title="' . esc_attr( $t_time ) . '">' . esc_html( apply_filters( 'post_date_column_time', $h_time, $item ) ) . '</abbr>';
				}

				return $date;
			case 'total':
				$currency = $the_order->get_currency();

				if ( $the_order->get_total_refunded() > 0 ) {
					echo '<del>' . wp_kses_post( wp_strip_all_tags( $the_order->get_formatted_order_total() ) ) . '</del> <ins>' . wp_kses_post( wc_price( $the_order->get_total() - $the_order->get_total_refunded(), array( 'currency' => $currency ) ) ) . '</ins>';
				} else {
					echo esc_html( wp_strip_all_tags( $the_order->get_formatted_order_total() ) );
				}

				$payment_method_title = $the_order->get_payment_method_title();
				if ( $payment_method_title ) {
					echo '<small class="meta">' . esc_html__( 'Via', 'woocommerce' ) . ' ' . esc_html( $payment_method_title ) . '</small>';
				}
				break;
			case 'status_email':
				$emails_sent = $the_order->get_meta( '_emails_sent' );
				if ( empty( $emails_sent ) ) {
					$email_status = __( 'Not sent', 'yith-woocommerce-recover-abandoned-cart' );
				} else {
					$last         = end( $emails_sent );
					$email_name   = esc_html__( 'Email:', 'yith-woocommerce-recover-abandoned-cart' ) . ' "<i>' . $last['email_name'] . '</i>"';
					$email_time   = esc_html__( 'Sent on:', 'yith-woocommerce-recover-abandoned-cart' ) . ' ' . substr( $last['data_sent'], 0, -3 );
					$email_status = $email_name . '<br>' . $email_time;
				}
				return '<span class="email_status" data-id="' . $the_order->get_id() . '">' . $email_status . '</span>';
			default:
				return ''; // Show the whole array for troubleshooting purposes.
		}

	}

	/**
	 * Add the content of the column 'action' in the list table
	 *
	 * @since 1.1.0
	 *
	 * @param StdObject $item Current Item.
	 *
	 * @return string|void
	 */
	public function column_action( $item ) {
		$id      = is_callable( array( $item, 'get_id' ) ) ? $item->get_id() : $item->ID;
		$actions = array();

		if ( isset( $actions['trash'] ) ) {
			unset( $actions['trash'] );
		}

		if ( isset( $actions['edit'] ) ) {
			unset( $actions['edit'] );
		}

		$send_email_menu = array();
		$email_templates = YITH_WC_Recover_Abandoned_Cart_Email()->get_email_templates( 'order', false );

		if ( ! empty( $email_templates ) ) {
			foreach ( $email_templates as $em ) {
				array_push(
					$send_email_menu,
					array(
						'name'       => esc_html__( 'Send now', 'yith-woocommerce-recover-abandoned-cart' ) . ' "' . $em->post_title . '"',
						'url'        => '#',
						'attributes' => array(
							'data-id'      => $em->ID,
							'data-type'    => 'order',
							'data-cart_id' => $id,
						),
					)
				);
			}
		} else {
			$new_post_url = admin_url( 'post-new.php' );
			$args         = array(
				'post_type' => YITH_WC_Recover_Abandoned_Cart_Email()->post_type_name,
			);
			$new_post_url = esc_url( add_query_arg( $args, $new_post_url ) );
			array(
				'name' => esc_html__( 'Add a new email template', 'yith-woocommerce-recover-abandoned-cart' ),
				'url'  => $new_post_url,
			);
		}

		$actions['view'] = array(
			'type'   => 'action-button',
			'action' => 'view',
			'title'  => __( 'View Order', 'yith-woocommerce-recover-abandoned-cart' ),
			'icon'   => 'eye',
			'url'    => get_edit_post_link( $id ),
		);

		$actions['sendmail'] = array(
			'type'   => 'action-button',
			'title'  => _x( 'Send email', 'Post action', 'yith-woocommerce-recover-abandoned-cart' ),
			'action' => 'sendmail',
			'icon'   => 'mail-out',
			'url'    => '#',
			'menu'   => $send_email_menu,
		);

		$actions['delete']                 = array(
			'type'   => 'action-button',
			'title'  => _x( 'Delete permanently', 'Post action', 'yith-woocommerce-recover-abandoned-cart' ),
			'action' => 'delete',
			'icon'   => 'trash',
			'url'    => get_delete_post_link( $id, '', true ),
		);
		$actions['delete']['confirm_data'] = array(
			'title'               => __( 'Confirm delete', 'yith-woocommerce-recover-abandoned-cart' ),
			'message'             => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-woocommerce-recover-abandoned-cart' ), '<strong>' . _draft_or_post_title( $id ) . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-woocommerce-recover-abandoned-cart' ),
			'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
			'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-woocommerce-recover-abandoned-cart' ),
			'confirm-button-type' => 'delete',
		);

		return yith_plugin_fw_get_action_buttons( $actions, true );
	}

	/**
	 * Display the search box.
	 *
	 * @since 1.1.0
	 * @access public
	 *
	 * @param string $text The search button text.
	 * @param string $input_id The search input id.
	 */
	public function search_box( $text, $input_id ) {

		$input_id = $input_id . '-search-input';
		$request = $_REQUEST; //phpcs:ignore
		if ( isset( $request['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $request['orderby'] ) . '" />';
		}
		if ( isset( $request['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $request['order'] ) . '" />';
		}

		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php esc_attr( _admin_search_query() ); ?>" placeholder="<?php esc_html_e( 'Search', 'yith-woocommerce-recover-abandoned-cart' ); ?>"/>
			<?php submit_button( $text, 'button', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination, which
	 * includes our Filters: Customers, Products, Availability Dates
	 *
	 * @see WP_List_Table::extra_tablenav();
	 * @since 1.0
	 * @param string $which the placement, one of 'top' or 'bottom'.
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' === $which ) {
			// Customers, products.

			echo '<div class="alignleft actions">';

			$user_id     = 0;
			$sel         = array();
			if ( isset( $_REQUEST['_customer_user'] ) ) { //phpcs:ignore
				$user_id = absint( sanitize_text_field( wp_unslash( $_REQUEST['_customer_user'] ) ) ); //phpcs:ignore
				$user    = get_user_by( 'id', $user_id );

				$user_string = sprintf(
				/* translators: 1: user display name 2: user ID 3: user email */
					esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
					$user->display_name,
					absint( $user->ID ),
					$user->user_email
				);

				$sel[ $user_id ] = $user_string;
			}

			yit_add_select2_fields(
				array(
					'type'              => 'hidden',
					'class'             => 'wc-customer-search',
					'id'                => 'customer_user',
					'name'              => '_customer_user',
					'data-placeholder'  => __( 'Show All Customers', 'yith-woocommerce-recover-abandoned-cart' ),
					'data-allow_clear'  => true,
					'data-selected'     => $sel,
					'data-multiple'     => false,
					'data-action'       => '',
					'value'             => $user_id,
					'style'             => 'width:200px',
					'custom-attributes' => array(),
				)
			);
			submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
			echo '</div>';
		}
	}
}
