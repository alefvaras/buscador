<?php
/**
 * Bulk Actions class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Admin' ) ) {
	/**
	 * The class manage all the Bulk Actions.
	 *
	 * @since    2.0
	 */
	class YITH_WCBEP_Bulk_Actions {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Bulk_Actions
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Bulk_Actions
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {
			add_action( 'wp_ajax_yith_wcbep_bulk_actions', array( $this, 'ajax_bulk_actions_handler' ) );
		}

		/**
		 * AJAX bulk actions handler
		 *
		 * @return void
		 */
		public function ajax_bulk_actions_handler() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['product_ids'], $_REQUEST['bulk_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_bulk_actions' ) ) {
				$product_ids = array_map( 'absint', $_REQUEST['product_ids'] );
				$action      = sanitize_text_field( wp_unslash( $_REQUEST['bulk_action'] ) );
				$handler     = "bulk_action_$action";
				if ( method_exists( $this, $handler ) ) {
					$this->$handler( $product_ids );
				}
				$response['success'] = 'success';
			}

			wp_send_json( $response );
			exit();
		}

		/**
		 * Bulk action to trash products
		 *
		 * @param int[] $product_ids The product IDs.
		 *
		 * @return void
		 */
		public function bulk_action_trash( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				wp_trash_post( $product_id );
			}
		}

		/**
		 * Bulk action to delete permanently products
		 *
		 * @param int[] $product_ids The product IDs.
		 *
		 * @return void
		 */
		public function bulk_action_delete( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				wp_delete_post( $product_id );
			}
		}

		/**
		 * Bulk action to duplicate products
		 *
		 * @param int[] $product_ids The product IDs.
		 *
		 * @return void
		 */
		public function bulk_action_duplicate( $product_ids ) {
			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$duplicate = clone $product;
					$duplicate->set_id( 0 );
					$duplicate->save();
				}
			}
		}
	}
}

/**
 * Unique access to instance of YITH_WCBEP_Bulk_Actions class
 *
 * @return YITH_WCBEP_Bulk_Actions
 * @since 2.0
 */
function yith_wcbep_bulk_actions() {
	return YITH_WCBEP_Bulk_Actions::get_instance();
}
