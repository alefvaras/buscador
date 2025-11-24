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


if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abandoned Carts List Table
 *
 * @class   YITH_YWRAC_Carts_List_Table
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author YITH <plugins@yithemes.com>
 */
class YITH_YWRAC_Carts_List_Table extends YITH_Post_Type_Admin {

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Instance of the class
	 *
	 * @var YITH_YWRAC_Carts_List_Table
	 */
	private static $instance;

	/**
	 * YITH_YWRAC_Carts_List_Table constructor.
	 */
	public function __construct() {
		$this->post_type = YITH_WC_Recover_Abandoned_Cart()->post_type_name;
		parent::__construct();

		add_filter( 'pre_get_posts', array( $this, 'list_only_abandoned' ) );
	}
	/**
	 * Only show abandoned carts.
	 *
	 * @param WP_Query $query WordPress query.
	 *
	 * @return array
	 */
	public function list_only_abandoned( $query ) {
		global $pagenow;

		if ( $query->is_admin && 'edit.php' === $pagenow && ( isset( $query->query_vars['post_type'] ) && 'ywrac_cart' === $query->query_vars['post_type'] ) ) {
			$meta_query = array(
				array(
					'key'     => '_cart_status',
					'value'   => 'abandoned',
					'compare' => '=',
				),
			);
			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	/**
	 * Get columns
	 *
	 * @param array $columns columns.
	 * @return array
	 */
	public function define_columns( $columns ) {
		if ( isset( $columns['date'] ) ) {
			unset( $columns['date'] );
		}

		if ( isset( $columns['title'] ) ) {
			$columns['title'] = __( 'User', 'yith-woocommerce-recover-abandoned-cart' );
		}

		$rac_columns = array(
			'email'        => __( 'Email', 'yith-woocommerce-recover-abandoned-cart' ),
			'phone'        => __( 'Phone', 'yith-woocommerce-recover-abandoned-cart' ),
			'subtotal'     => __( 'Subtotal', 'yith-woocommerce-recover-abandoned-cart' ),
			'status_email' => __( 'Last email sent', 'yith-woocommerce-recover-abandoned-cart' ),
			'last_update'  => __( 'Last update', 'yith-woocommerce-recover-abandoned-cart' ),
			'action'       => __( 'Action', 'yith-woocommerce-recover-abandoned-cart' ),
		);
		return array_merge( $columns, $rac_columns );
	}

	/**
	 * Return the sortable columns
	 *
	 * @param array $columns The sortable columns.
	 *
	 * @return array
	 * @since  1.3.0
	 */
	public function define_sortable_columns( $columns ) {
		$columns = array(
			'title'       => array( 'title', true ),
			'last_update' => array( 'last_update', true ),
			'email'       => array( 'email', true ),
			'subtotal'    => array( 'subtotal', true ),
		);

		return $columns;
	}

	/**
	 * Use the post id
	 *
	 * @return bool
	 * @since 2.0.0
	 * @author YITH
	 */
	public function use_object() {
		return false;
	}

	/**
	 * Render Columns
	 *
	 * @param string $column column id.
	 * @param int    $post_id post id.
	 * @return mixed|string|void
	 */
	public function render_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'email':
				echo esc_html( get_post_meta( $post_id, '_user_email', true ) );
				break;
			case 'phone':
				echo esc_html( get_post_meta( $post_id, '_user_phone', true ) );
				break;
			case 'status_email':
				$emails_sent = get_post_meta( $post_id, '_emails_sent', true );
				if ( empty( $emails_sent ) ) {
					$email_status = __( 'Not sent', 'yith-woocommerce-recover-abandoned-cart' );
				} else {
					$last         = end( $emails_sent );
					$email_name   = esc_html__( 'Email:', 'yith-woocommerce-recover-abandoned-cart' ) . ' "<i>' . $last['email_name'] . '</i>"';
					$email_time   = esc_html__( 'Sent on:', 'yith-woocommerce-recover-abandoned-cart' ) . ' ' . substr( $last['data_sent'], 0, -3 );
					$email_status = $email_name . '<br>' . $email_time;
				}
				echo '<span class="email_status" data-id="' . esc_html( $post_id ) . '">' . wp_kses_post( $email_status ) . '</span>';
				break;
			case 'subtotal':
				$currency = get_post_meta( $post_id, '_user_currency', true );
				$subtotal = get_post_meta( $post_id, '_cart_subtotal', true );
				if ( class_exists( 'WOOCS' ) ) {
					global $WOOCS; //phpcs:ignore
					$WOOCS->current_currency = $currency; //phpcs:ignore
				}

				$cart_subtotal = wc_price( $subtotal, array( 'currency' => $currency ) );
				echo wp_kses_post( $cart_subtotal );
				break;
			case 'last_update':
				$item        = get_post( $post_id );
				$last_update = $item->post_date;
				echo esc_html( substr( $last_update, 0, -3 ) );
				break;
			case 'action':
				$actions = yith_plugin_fw_get_default_post_actions( $post_id, array() );

				if ( isset( $actions['trash'] ) ) {
					unset( $actions['trash'] );
				}

				if ( isset( $actions['edit'] ) ) {
					unset( $actions['edit'] );
				}

				$send_email_menu = array();
				$email_templates = YITH_WC_Recover_Abandoned_Cart_Email()->get_email_templates( 'cart', false );

				if ( ! empty( $email_templates ) ) {
					foreach ( $email_templates as $em ) {
						array_push(
							$send_email_menu,
							array(
								'name'       => esc_html__( 'Send now', 'yith-woocommerce-recover-abandoned-cart' ) . ' "' . $em->post_title . '"',
								'url'        => '#',
								'attributes' => array(
									'data-id'      => $em->ID,
									'data-type'    => 'cart',
									'data-cart_id' => $post_id,
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
					'title'  => __( 'View Cart', 'yith-woocommerce-recover-abandoned-cart' ),
					'icon'   => 'eye',
					'url'    => get_edit_post_link( $post_id ),
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
					'url'    => get_delete_post_link( $post_id, '', true ),
				);
				$actions['delete']['confirm_data'] = array(
					'title'               => __( 'Confirm delete', 'yith-woocommerce-recover-abandoned-cart' ),
					'message'             => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-woocommerce-recover-abandoned-cart' ), '<strong>' . _draft_or_post_title( $post_id ) . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-woocommerce-recover-abandoned-cart' ),
					'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
					'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-woocommerce-recover-abandoned-cart' ),
					'confirm-button-type' => 'delete',
				);

				yith_plugin_fw_get_action_buttons( $actions, true );

				break;
			default:
				return ''; // Show the whole array for troubleshooting purposes.
		}
	}

	/**
	 * Define bulk actions.
	 *
	 * @param array $actions Existing actions.
	 *
	 * @return array
	 */
	public function define_bulk_actions( $actions ) {
		unset( $actions['trash'] );
		unset( $actions['edit'] );
		$actions['delete'] = __( 'Delete', 'yith-woocommerce-recover-abandoned-cart' );
		return $actions;
	}
	/**
	 * Handle the custom bulk action.
	 *
	 * @param string $redirect_to Redirect URL.
	 * @param string $do_action   Selected bulk action.
	 * @param array  $post_ids    Post ids.
	 *
	 * @return string
	 */
	public function handle_bulk_actions( $redirect_to, $do_action, $post_ids ) {

		if ( 'delete' === $do_action ) {
			foreach ( $post_ids as $cart_id ) {

				$post_type_object = get_post_type_object( $this->post_type );

				if ( current_user_can( $post_type_object->cap->delete_post, $cart_id ) ) {
					ywrac_log( 'Deleting cart from bulk actions: ' . $cart_id, 'info' );
					wp_delete_post( $cart_id );
				}
			}
		}

		return $redirect_to;
	}
	/**
	 * Get_back_to_wp_list_text
	 *
	 * @return string
	 */
	public function get_back_to_wp_list_text() {
		return __( 'Back to Abandoned Cart List', 'yith-woocommerce-recover-abandoned-cart' );
	}
	/**
	 * Render blank state.
	 *
	 * @author YITH
	 * @since 2.0.0
	 */
	protected function render_blank_state() {
		parent::render_blank_state();
		echo '<style>.page-title-action{display: none!important;}</style>';
	}

	/**
	 * Retrieve an array of parameters for blank state.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	protected function get_blank_state_params() {
		$submessage = '<p><small>' . esc_html__( "But don't worry, soon something cool will appear here.", 'yith-woocommerce-recover-abandoned-cart' ) . '</small></p>';
		return array(
			'icon_url' => esc_url( YITH_YWRAC_ASSETS_URL ) . '/images/abandoned-cart.svg',
			'message'  => __( 'You have no abandoned carts yet.', 'yith-woocommerce-recover-abandoned-cart' ) . $submessage,
			'class'    => '',
		);
	}
}

return YITH_YWRAC_Carts_List_Table::instance();
