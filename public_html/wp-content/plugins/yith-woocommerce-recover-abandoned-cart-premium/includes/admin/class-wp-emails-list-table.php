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
 * Email Template List Table
 *
 * @class   YITH_YWRAC_Emails_List_Table
 * @package YITH WooCommerce Recover Abandoned Cart
 * @since   1.0.0
 * @author YITH <plugins@yithemes.com>
 */
class YITH_YWRAC_Emails_List_Table extends YITH_Post_Type_Admin {

	/**
	 * Post type
	 *
	 * @var string
	 */
	protected $post_type;

	/**
	 * Instance of the class
	 *
	 * @var YITH_YWRAC_Emails_List_Table
	 */
	private static $instance;

	/**
	 * YITH_YWRAC_Emails_List_Table constructor.
	 */
	public function __construct() {
		$this->post_type = YITH_WC_Recover_Abandoned_Cart_Email()->post_type_name;
		parent::__construct();
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
			$columns['title'] = __( 'Name', 'yith-woocommerce-recover-abandoned-cart' );
		}

		$rac_columns = array(
			'type'       => esc_html__( 'Type', 'yith-woocommerce-recover-abandoned-cart' ),
			'send_after' => esc_html__( 'Send after', 'yith-woocommerce-recover-abandoned-cart' ),
			'subject'    => esc_html__( 'Subject', 'yith-woocommerce-recover-abandoned-cart' ),
			'conversion' => esc_html__( 'Conversion Rate', 'yith-woocommerce-recover-abandoned-cart' ),
			'status'     => esc_html__( 'Status', 'yith-woocommerce-recover-abandoned-cart' ),
			'actions'    => '',
		);
		return array_merge( $columns, $rac_columns );
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
			case 'status':
				$status = get_post_meta( $post_id, '_ywrac_email_active', true );
				echo "<div class='yith-plugin-ui'>";
				echo yith_plugin_fw_get_field(
					array(
						'type'  => 'onoff',
						'class' => 'ywrac-toggle-enabled',
						'value' => yith_plugin_fw_is_true( $status ) ? 'yes' : 'no',
						'data'  => array(
							'email-template-id' => $post_id,
							'security'          => wp_create_nonce( 'email-template-status-toggle-enabled' ),
						),
					)
				);
				echo '</div>';
				break;
			case 'type':
				$email_type       = get_post_meta( $post_id, '_ywrac_email_type', true );
				$email_type_label = '';
				if ( $email_type && 'cart' === $email_type ) {
					$email_type_label = esc_html__( 'Abandoned cart', 'yith-woocommerce-recover-abandoned-cart' );
				} elseif ( $email_type && 'order' === $email_type ) {
					$email_type_label = esc_html__( 'Pending order', 'yith-woocommerce-recover-abandoned-cart' );
				}
				echo esc_html( $email_type_label );
				break;
			case 'send_after':
				$email_time = get_post_meta( $post_id, '_ywrac_email_time', true );
				$type_time  = is_array( $email_time ) && ! empty( $email_time['type'] ) ? $email_time['type'] : get_post_meta( $post_id, '_ywrac_type_time', true );
				$time       = is_array( $email_time ) && ! empty( $email_time['time'] ) ? $email_time['time'] : get_post_meta( $post_id, '_ywrac_time', true );
				echo esc_html( $time . ' ' . $type_time );
				break;
			case 'subject':
				$user_email = get_post_meta( $post_id, '_ywrac_email_subject', true );
				echo esc_html( $user_email );
				break;
			case 'conversion':
				/**
				 * APPLY_FILTERS: ywrac_email_template_sent_counter
				 *
				 * Set email sent.
				 *
				 * @param bool $is_sent
				 * @param int $post_id
				 */
				$email_sent = intval( apply_filters( 'ywrac_email_template_sent_counter', get_post_meta( $post_id, '_email_sent_counter', true ), $post_id ) );
				/**
				 * APPLY_FILTERS: ywrac_email_template_cart_recovered
				 *
				 * Set if recovered cart.
				 *
				 * @param int $is_recovered
				 * @param int $post_id
				 */
				$recovered_carts = intval( apply_filters( 'ywrac_email_template_cart_recovered', get_post_meta( $post_id, '_cart_recovered', true ), $post_id ) );
				if ( $email_sent != 0 ) {    //phpcs:ignore
					$conversion = number_format( 100 * $recovered_carts / $email_sent, 2, '.', '' ) . ' %';
				} else {
					$conversion = '0.00 %';
				}
				echo esc_html( $conversion );
				break;
			case 'actions':
				$actions = yith_plugin_fw_get_default_post_actions( $post_id );
				if ( isset( $actions['trash'] ) ) {
					unset( $actions['trash'] );
				}
				$actions['delete']                 = array(
					'type'   => 'action-button',
					'title'  => _x( 'Delete permanently', 'Post action', 'yith-plugin-fw' ),
					'action' => 'delete',
					'icon'   => 'trash',
					'url'    => get_delete_post_link( $post_id, '', true ),
				);
				$actions['delete']['confirm_data'] = array(
					'title'               => __( 'Confirm delete', 'yith-plugin-fw' ),
					/* translators: %s is the post id */
					'message'             => sprintf( __( 'Are you sure you want to delete "%s"?', 'yith-plugin-fw' ), '<strong>' . _draft_or_post_title( $post_id ) . '</strong>' ) . '<br /><br />' . __( 'This action cannot be undone and you will not be able to recover this data.', 'yith-plugin-fw' ),
					'cancel-button'       => __( 'No', 'yith-plugin-fw' ),
					'confirm-button'      => _x( 'Yes, delete', 'Delete confirmation action', 'yith-plugin-fw' ),
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
		$actions['activate']   = __( 'Enable', 'yith-woocommerce-recover-abandoned-cart' );
		$actions['deactivate'] = __( 'Disable', 'yith-woocommerce-recover-abandoned-cart' );
		$actions['delete']     = __( 'Delete', 'yith-woocommerce-recover-abandoned-cart' );

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

		if ( 'activate' !== $do_action && 'deactivate' !== $do_action ) {
			return parent::handle_bulk_actions( $redirect_to, $do_action, $post_ids );
		}

		foreach ( $post_ids as $email_id ) {

			$post_type_object = get_post_type_object( $this->post_type );

			if ( current_user_can( $post_type_object->cap->delete_post, $email_id ) ) {
				switch ( $do_action ) {
					case 'activate':
						YITH_WC_Recover_Abandoned_Cart_Email()->activate( $email_id, true );
						break;
					case 'deactivate':
						YITH_WC_Recover_Abandoned_Cart_Email()->activate( $email_id, false );
						break;
					default:
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
		return __( 'Back to email templates list', 'yith-woocommerce-recover-abandoned-cart' );
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
		$submessage = '<p><small>' . esc_html__( 'Create the first one now', 'yith-woocommerce-recover-abandoned-cart' ) . '</small></p>';

		$new_post_url = admin_url( 'post-new.php' );
		$args         = array(
			'post_type' => $this->post_type,
		);
		$new_post_url = esc_url( add_query_arg( $args, $new_post_url ) );

		return array(
			'icon_url' => esc_url( YITH_YWRAC_ASSETS_URL ) . '/images/email.svg',
			'message'  => __( 'You have no email templates created yet', 'yith-woocommerce-recover-abandoned-cart' ) . $submessage,
			'cta'      => array(
				'title' => __( 'Add email template', 'yith-woocommerce-recover-abandoned-cart' ),
				'class' => 'ywrac_add_new_email_template',
				'icon'  => 'plus',
				'url'   => $new_post_url,
			),
			'class'    => 'yith_rac_div_new_email_template',
		);
	}
}

return YITH_YWRAC_Emails_List_Table::instance();
