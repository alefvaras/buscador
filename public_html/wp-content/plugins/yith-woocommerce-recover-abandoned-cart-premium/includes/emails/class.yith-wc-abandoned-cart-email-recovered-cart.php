<?php
/**
 * Implements features of YITH WooCommerce Recover Abandoned Cart
 *
 * @class   YITH_YWRAC_Send_Email_Recovered_Cart
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Recover Abandoned Cart
 */

if ( ! defined( 'ABSPATH' ) || ! defined( 'YITH_YWRAC_VERSION' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'YITH_YWRAC_Send_Email_Recovered_Cart' ) ) {

	/**
	 * YITH_YWRAC_Send_Email_Recovered_Cart
	 *
	 * @since 1.0.0
	 */
	class YITH_YWRAC_Send_Email_Recovered_Cart extends WC_Email {
		/**
		 * Reply to content.
		 *
		 * @var string
		 */
		public $reply_to;
		
		/**
		 * Constructor method, used to return object of the class to WC
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->id          = 'ywrac_email_recovered_cart';
			$this->title       = __( 'Recovered Abandoned Cart Administrator', 'yith-woocommerce-recover-abandoned-cart' );
			$this->description = __( 'This is the email sent to the administrator when an order is placed from a recover cart email', 'yith-woocommerce-recover-abandoned-cart' );

			$this->heading  = get_option( 'ywrac_admin_sender_name' );
			$this->subject  = get_option( 'ywrac_admin_email_subject' );
			$this->reply_to = '';

			$this->template_html = 'email/email-recover-cart.php';

			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'ywrac_admin_email_recipient' );
			}

			// Triggers for this email.
			add_action( 'send_recovered_cart_mail_notification', array( $this, 'trigger' ), 15 );

			// Call parent constructor.
			parent::__construct();
		}

		/**
		 * Method triggered to send email
		 *
		 * @param int $args .
		 *
		 * @return void
		 * @since  1.0
		 */
		public function trigger( $args ) {
			$this->order = wc_get_order( $args['order_id'] );
			$return      = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content_html(), $this->get_headers(), $this->get_attachments() );
		}

		/**
		 * Get_headers function.
		 *
		 * @access public
		 * @return string
		 */
		public function get_headers() {

			$header = 'Content-Type: ' . $this->get_content_type() . "\r\n";

			if ( '' !== $this->reply_to ) {
				$header .= 'Reply-to: ' . $this->reply_to . "\r\n";
			}

			return apply_filters( 'woocommerce_email_headers', $header, $this->id, $this->object, $this );
		}

		/**
		 * Get HTML content for the mail
		 *
		 * @return string HTML content of the mail
		 * @since  1.0
		 */
		public function get_content_html() {
			ob_start();
			wc_get_template(
				$this->template_html,
				array(
					'order'         => $this->order,
					'email_heading' => $this->get_heading(),
					'sent_to_admin' => true,
					'plain_text'    => false,
					'email'         => $this,
				)
			);

			return ob_get_clean();
		}


	}
}


// returns instance of the mail on file include.
return new YITH_YWRAC_Send_Email_Recovered_Cart();
