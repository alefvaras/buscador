<?php
/**
 * Brands Add On Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Brands_Add_On_Compatibility' ) ) {
	/**
	 * Brands Add On Compatibility Class
	 *
	 * @since      1.1.3
	 */
	class YITH_WCBEP_Brands_Add_On_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Brands_Add_On_Compatibility
		 */
		protected static $instance;

		/**
		 * The brands
		 *
		 * @var array
		 */
		public $brands_array;

		/**
		 * The brand taxonomy name
		 *
		 * @var string
		 */
		public $taxonomy_name;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Brands_Add_On_Compatibility
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {
		}

		/**
		 * Add brand field in filters
		 *
		 * @depreacted since 2.0
		 */
		public function add_brand_field_in_filters() {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::add_brand_field_in_filters', '2.0.0' );
		}

		/**
		 * Add class for multi-select fields.
		 *
		 * @param array $classes The classes.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_chosen_in_js( $classes ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::add_chosen_in_js', '2.0.0' );

			return $classes;
		}

		/**
		 * Print custom input
		 *
		 * @depreacted since 2.0
		 */
		public function extra_custom_input() {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::extra_custom_input', '2.0.0' );
		}

		/**
		 * Add column
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_column( $columns ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::add_column', '2.0.0' );

			return $columns;
		}

		/**
		 * Manage columns
		 *
		 * @param string  $value       The value.
		 * @param string  $column_name The column name.
		 * @param WP_Post $post        The post object.
		 *
		 * @return string
		 * @depreacted since 2.0
		 */
		public function manage_column( $value, $column_name, $post ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::manage_column', '2.0.0' );

			return $value;
		}

		/**
		 * Edit "non-editable and empty" columns in variations.
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function edit_not_editable_and_empty_in_variations( $columns ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::edit_not_editable_and_empty_in_variations', '2.0.0' );

			return $columns;
		}

		/**
		 * Add class for multi-select fields.
		 *
		 * @param array $classes The classes.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_extra_bulk_columns_chosen( $classes ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::add_extra_bulk_columns_chosen', '2.0.0' );

			return $classes;
		}

		/**
		 * Add class for select fields.
		 *
		 * @param array $classes The classes.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_extra_class_select_in_js( $classes ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::add_extra_class_select_in_js', '2.0.0' );

			return $classes;
		}

		/**
		 * Save
		 *
		 * @param WC_Product $product       The product.
		 * @param array      $matrix_keys   The matrix.
		 * @param array      $single_modify The single modify.
		 * @param bool       $is_variation  Set true if this is a variation.
		 *
		 * @depreacted since 2.0
		 */
		public function save( $product, $matrix_keys, $single_modify, $is_variation ) {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::save', '2.0.0' );

		}

		/**
		 * Get brands.
		 *
		 * @return array
		 */
		public function get_brands_array() {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::get_brands_array', '2.0.0' );

			return array();
		}

		/**
		 * Add fields to Bulk Editor.
		 */
		public function add_extra_bulk_fields() {
			wc_deprecated_function( 'YITH_WCBEP_Brands_Add_On_Compatibility::add_extra_bulk_fields', '2.0.0' );
		}
	}

	if ( ! function_exists( 'yith_wcbep_brands_add_on_compatibility' ) ) {
		/**
		 * Unique access to instance of YITH_WCBEP_Brands_Add_On_Compatibility class
		 *
		 * @return YITH_WCBEP_Brands_Add_On_Compatibility
		 * @since      1.1.3
		 */
		function yith_wcbep_brands_add_on_compatibility() {
			return YITH_WCBEP_Brands_Add_On_Compatibility::get_instance();
		}
	}
}
