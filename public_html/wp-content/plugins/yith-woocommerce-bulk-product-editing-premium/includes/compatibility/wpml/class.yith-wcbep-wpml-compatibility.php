<?php
/**
 * WordPress Multi-lingual Compatibility Class
 *
 * @package YITH\BulkProductEditing\Compatibility\WPML
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

/**
 * WordPress Multi-lingual Compatibility Class
 *
 * @since  2.5.0
 */
class YITH_WCBEP_WPML_Compatibility {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCBEP_WPML_Compatibility
	 */
	protected static $instance;

	/**
	 * Array of badges.
	 *
	 * @var array
	 */
	public $badge_array;

	/**
	 * Returns single instance of the class
	 *
	 * @return YITH_WCBEP_WPML_Compatibility
	 */
	public static function get_instance() {
		return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		add_filter( 'yith_wcbep_product_list_query_args', array( $this, 'set_language_before_products_list_query' ) );
		add_filter( 'yith_wcbep_product_list_query_args', array( $this, 'set_language_before_products_list_query' ) );
		add_filter( 'save_post', array( $this, 'maybe_clear_wpml_cache' ) );
	}

	/**
	 * Check if is needed to delete WPML cache before Sync products translations.
	 *
	 * @param int $post_id Post ID.
	 */
	public function maybe_clear_wpml_cache( $post_id ) {
		if ( isset( $_REQUEST['security'], $_REQUEST['products_changes'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_save_products_changes' ) ) {
			$product = wc_get_product( $post_id );
			if ( $product && $product->is_type( 'variation' ) ) {
				$args = array(
					'element_id'   => $product->get_id(),
					'element_type' => 'post_product',
				);
				$lang = apply_filters( 'wpml_element_language_code', null, $args );
				if ( is_string( $lang ) ) {
					wp_cache_delete( $lang . $product->get_id(), 'wpml-all-meta-product-variation' );
				}
			}
		}
	}

	/**
	 * Set the correct language before the products query is ran
	 *
	 * @param array $q the query args.
	 *
	 * @return array
	 */
	public function set_language_before_products_list_query( $q ) {
		$lang = ! empty( $_POST['lang'] ) ? sanitize_text_field( wp_unslash( $_POST['lang'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( $lang ) {
			do_action( 'wpml_switch_language', $lang );
		}

		return $q;
	}

	/**
	 * Check if the column is editable
	 *
	 * @param false|string $editable    False or the "not editable" message to display.
	 * @param WC_Product   $product     The product.
	 * @param string       $column_name The column name.
	 *
	 * @return false|string
	 */
	public function is_column_editable( $editable, $product ) {
		$lang_details = apply_filters( 'wpml_post_language_details', null, $product->get_id() );

		if ( $lang_details['language_code'] !== $this->get_default_language_code() ) {
			$editable = __( 'This field can’t be edited because WPML will copy its value from the original language.', 'yith-woocommerce-bulk-product-editing' );
		}

		return $editable;
	}

	/**
	 * Get the site default language code
	 *
	 * @return null|string
	 */
	private function get_default_language_code() {
		static $language_code = null;
		if ( is_null( $language_code ) ) {
			$language_code = apply_filters( 'wpml_default_language', null );
		}

		return $language_code;
	}

}

/**
 * Unique access to instance of YITH_WCBEP_WPML_Compatibility class
 *
 * @return YITH_WCBEP_WPML_Compatibility
 * @since 2.5.0
 */
function yith_wcbep_wpml_compatibility() {
	return YITH_WCBEP_WPML_Compatibility::get_instance();
}
