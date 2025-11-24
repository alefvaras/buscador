<?php
/**
 * Custom Fields class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Custom_Fields_Manager' ) ) {
	/**
	 * Custom Fields Manager
	 *
	 * @class   YITH_WCBEP_Custom_Fields_Manager
	 * @since   1.1.2
	 */
	class YITH_WCBEP_Custom_Fields_Manager {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Custom_Fields_Manager
		 */
		protected static $instance;

		/**
		 * Prefix for custom fields
		 *
		 * @var string
		 */
		public $prefix = 'yith_wcbep_cf_';

		/**
		 * Array of custom fields.
		 *
		 * @var array
		 */
		public $custom_fields = array();

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Custom_Fields_Manager
		 */
		public static function get_instance(): YITH_WCBEP_Custom_Fields_Manager {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {
			$this->custom_fields = self::get_custom_fields();

			add_filter( 'yith_wcbep_default_columns', array( $this, 'add_columns' ) );
			add_filter( 'yith_wcbep_manage_custom_columns', array( $this, 'manage_columns' ), 10, 3 );
			add_filter( 'yith_wcbep_variation_not_editable_and_empty', array( $this, 'edit_not_editable_and_empty_in_variations' ) );

			// NEW Hooks.
			add_action( 'wp_ajax_yith_wcbep_add_custom_field_column', array( $this, 'add_custom_field_column' ) );
			add_action( 'wp_ajax_yith_wcbep_delete_custom_field_column', array( $this, 'delete_custom_field_column' ) );
			add_action( 'wp_ajax_yith_wcbep_update_custom_field_column', array( $this, 'update_custom_field_column' ) );

			add_filter( 'yith_wcbep_list_table_get_column_type', array( $this, 'get_column_type' ), 10, 2 );
			add_filter( 'yith_wcbep_bulk_editing_modal_tabs', array( $this, 'add_custom_fields_tab_in_bulk_editing_modal' ) );
		}

		/**
		 * Adding the "Custom fields" tab in bulk editing modal
		 *
		 * @param array $tabs The tabs of bulk editing modal.
		 *
		 * @return array
		 */
		public function add_custom_fields_tab_in_bulk_editing_modal( array $tabs ): array {
			$tabs['custom-fields'] = _x( 'Custom fields', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' );

			return $tabs;
		}

		/**
		 * Get the column type when is a Custom Field
		 *
		 * @param string $type The column type.
		 * @param string $name The column name.
		 *
		 * @return string
		 */
		public function get_column_type( string $type, string $name ): string {
			if ( strpos( $name, 'yith_wcbep_cf_' ) === 0 ) {
				$custom_fields = $this->get_custom_fields_info();
				$field         = $custom_fields[ $name ] ?? false;
				if ( $field ) {
					$type = $field['type'] ?? 'text';
				}
			}

			return $type;
		}

		/**
		 * Retrieve the field name with the prefix.
		 *
		 * @param string $name The name of the field.
		 *
		 * @return string
		 */
		public function create_field_name( string $name ): string {
			return $this->prefix . str_replace( '-', '_', sanitize_title( $name ) );
		}

		/**
		 * Add columns to the default ones.
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function add_columns( array $columns ): array {
			$fields = $this->get_custom_fields_info();
			foreach ( $fields as $field ) {
				$field_name = $field['name'];

				$columns[ $field_name ]         = array_diff_key( $field, array( 'name' => '' ) );
				$columns[ $field_name ]['kind'] = 'custom_field';
			}

			return $columns;
		}

		/**
		 * Manage columns in table.
		 *
		 * @param string|null $cell        The value to print.
		 * @param string      $column_name The column name.
		 * @param WP_Post     $post        The Post object.
		 *
		 * @return string|null
		 */
		public function manage_columns( string $cell, string $column_name, WP_Post $post ): string {
			if ( strpos( $column_name, 'yith_wcbep_cf' ) === 0 ) {
				$fields    = $this->get_custom_fields_info();
				$product   = wc_get_product( $post );
				$field_ids = array_map( 'sanitize_title', array_column( $fields, 'id' ) );
				foreach ( $field_ids as &$field_id ) {
					$field_id = str_replace( '-', '_', $field_id );
				}
				if ( $product && in_array( str_replace( $this->prefix, '', $column_name ), $field_ids, true ) ) {
					$column_type = YITH_WCBEP_List_Table::get_column_type( $column_name );
					$meta_key    = array_key_exists( $column_name, $fields ) ? $fields[ $column_name ]['id'] : substr( $column_name, 14 );
					$getter      = "get_{$meta_key}";
					if ( method_exists( $product, $getter ) && apply_filters( 'yith_wcbep_use_getter_for_columns', true ) ) {
						$value = $product->$getter();
					} else {
						$value = 0 === strpos( $meta_key, '_' ) ? get_post_meta( $product->get_id(), $meta_key, true ) : $product->get_meta( $meta_key );
					}
					$cell = YITH_WCBEP_List_Table::render_column(
						array(
							'value'   => $value,
							'display' => 'price' === $column_type ? YITH_WCBEP_List_Table::format_price_column_content( $value ) : $value,
						)
					);
				}
			}

			return $cell;
		}

		/**
		 * Edit "non-editable and empty" columns in variations.
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function edit_not_editable_and_empty_in_variations( array $columns ): array {
			if ( ! $this->allow_editing_in_variations() ) {
				foreach ( $this->custom_fields as $field ) {
					$field_name = $this->create_field_name( $field );
					$columns[]  = $field_name;
				}
			}

			return $columns;
		}

		/**
		 * Add fields to the "extra-bulk-columns" for text fields
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_extra_bulk_columns_text( array $columns ): array {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Fields_Manager::add_extra_bulk_columns_text', '2.0.0' );

			return $columns;
		}

		/**
		 * Add fields to the "extra-bulk-columns" for numeric fields
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_extra_bulk_columns_number( array $columns ): array {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Fields_Manager::add_extra_bulk_columns_number', '2.0.0' );

			return $columns;
		}

		/**
		 * Add extra classes for "text" fields.
		 *
		 * @param array $classes The classes.
		 *
		 * @return array
		 * @depreacted since 2.0
		 */
		public function add_extra_class_text_in_js( array $classes ): array {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Fields_Manager::add_extra_class_text_in_js', '2.0.0' );

			return $classes;
		}

		/**
		 * Save meta.
		 *
		 * @depreacted since 2.0
		 */
		public function save_meta() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Fields_Manager::save_meta', '2.0.0' );
		}

		/**
		 * AJAX Add custom field
		 */
		public function add_custom_field_column() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['kind'], $_REQUEST['field_id'] ) && 'custom-field' === $_REQUEST['kind'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_add_custom_field_column' ) ) {
				$custom_fields        = self::get_custom_fields_option();
				$new_custom_field     = array(
					'id'    => sanitize_text_field( wp_unslash( $_REQUEST['field_id'] ) ),
					'type'  => sanitize_text_field( wp_unslash( $_REQUEST['type'] ?? 'text' ) ),
					'label' => sanitize_text_field( wp_unslash( ( $_REQUEST['label'] ?? '' ) ? $_REQUEST['label'] : $_REQUEST['field_id'] ) ),
				);
				$new_custom_field_key = $this->create_field_name( $new_custom_field['id'] );
				if ( ! array_key_exists( $new_custom_field_key, $custom_fields ) ) {
					$custom_fields[ $new_custom_field_key ] = $new_custom_field;
					if ( update_option( 'yith_wcbep_custom_fields', $custom_fields ) ) {
						$response['data']                 = array(
							'fieldKey'   => $new_custom_field_key,
							'fieldId'    => $new_custom_field['id'],
							'fieldLabel' => $new_custom_field['label'],
							'fieldType'  => $new_custom_field['type'],
							'fieldKind'  => 'custom_field',
						);
						$response['data']['fieldInfo']    = wp_json_encode( $response['data'] );
						$response['data']['fieldClasses'] = 'yith-wcbep-enable-fields__custom-field';
						$enabled_columns                  = YITH_WCBEP_List_Table::get_enabled_columns();
						$enabled_columns[]                = $new_custom_field_key;
						YITH_WCBEP_List_Table::update_enabled_columns( $enabled_columns );
						$response['success'] = 'success';
					}
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * AJAX Delete custom field
		 */
		public function delete_custom_field_column() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['field_id'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_delete_custom_field_column' ) ) {
				$custom_fields      = self::get_custom_fields_option();
				$cf_id_to_index     = array_combine( array_column( $custom_fields, 'id' ), array_keys( $custom_fields ) );
				$field_id_to_delete = sanitize_text_field( wp_unslash( $_REQUEST['field_id'] ) );
				if ( array_key_exists( $field_id_to_delete, $cf_id_to_index ) ) {
					unset( $custom_fields[ $cf_id_to_index[ $field_id_to_delete ] ] );
					if ( update_option( 'yith_wcbep_custom_fields', $custom_fields ) ) {
						$response['success'] = 'success';
					}
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * AJAX Update custom field
		 */
		public function update_custom_field_column() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['field_id'], $_REQUEST['changes'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_update_custom_field_column' ) ) {
				$custom_fields      = self::get_custom_fields_option();
				$cf_id_to_index     = array_combine( array_column( $custom_fields, 'id' ), array_keys( $custom_fields ) );
				$field_id_to_update = sanitize_text_field( wp_unslash( $_REQUEST['field_id'] ) );
				$changes            = array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['changes'] ) );
				if ( array_key_exists( $field_id_to_update, $cf_id_to_index ) && ( empty( $changes['id'] ) || ! array_key_exists( $changes['id'], $cf_id_to_index ) ) ) {
					$key = $cf_id_to_index[ $field_id_to_update ];
					foreach ( $changes as $prop => $value ) {
						$custom_fields[ $key ][ $prop ] = $value;
					}
					if ( ! empty( $changes['id'] ) ) {
						$keys = array_keys( $custom_fields );

						$new_key                                   = self::create_field_name( $changes['id'] );
						$keys[ array_search( $key, $keys, true ) ] = $new_key;

						$enabled_columns = YITH_WCBEP_List_Table::get_enabled_columns();
						if ( in_array( $key, $enabled_columns, true ) ) {
							unset( $enabled_columns[ array_search( $key, $enabled_columns, true ) ] );
							$enabled_columns[] = $new_key;
							YITH_WCBEP_List_Table::update_enabled_columns( $enabled_columns );
						}

						$key           = $new_key;
						$custom_fields = array_combine( $keys, $custom_fields );
					}
					if ( update_option( 'yith_wcbep_custom_fields', $custom_fields ) ) {
						$response['success'] = 'success';
						$response['data']    = array(
							'fieldKey'   => $key,
							'fieldId'    => $custom_fields[ $key ]['id'],
							'fieldLabel' => $custom_fields[ $key ]['label'],
							'fieldType'  => $custom_fields[ $key ]['type'],
							'fieldKind'  => 'custom_field',
						);
					}
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * Add fields to Bulk editor.
		 *
		 * @depreacted since 2.0
		 */
		public function add_extra_bulk_fields() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Fields_Manager::add_extra_bulk_fields', '2.0.0' );
		}

		/**
		 * Get custom field option
		 * If it has the old structure the option will be updated
		 *
		 * @return array
		 */
		public static function get_custom_fields_option(): array {
			$custom_fields = get_option( 'yith_wcbep_custom_fields', array() );
			if ( $custom_fields && ! is_array( current( $custom_fields ) ) ) {
				foreach ( $custom_fields as &$custom_field ) {
					$custom_field = array(
						'id'    => $custom_field,
						/**
						 * APPLY_FILTERS: yith_wcbep_custom_field_type
						 *
						 * Filters the custom field type.
						 *
						 * @param string $type         The custom field type.
						 * @param string $custom_field The custom field ID.
						 *
						 * @return string
						 */
						'type'  => apply_filters( 'yith_wcbep_custom_field_type', 'text', $custom_field ),
						/**
						 * APPLY_FILTERS: yith_wcbep_custom_field_label
						 *
						 * Filters the custom field label.
						 *
						 * @param string $label        The custom field label.
						 * @param string $custom_field The custom field ID.
						 *
						 * @return string
						 */
						'label' => apply_filters( 'yith_wcbep_custom_field_label', $custom_field, $custom_field ),
					);
				}
				update_option( 'yith_wcbep_custom_fields', $custom_fields );
			}

			return $custom_fields;
		}

		/**
		 * Retrieve the custom fields
		 *
		 * @return array
		 */
		public static function get_custom_fields(): array {
			$custom_fields = self::get_custom_fields_option();

			return array_column( $custom_fields, 'id' );
		}

		/**
		 * Save custom fields.
		 *
		 * @param array $fields The fields.
		 *
		 * @return bool
		 */
		public static function save_custom_fields( array $fields ): bool {

			if ( ! ! $fields ) {
				$fields = array_map( 'sanitize_text_field', array_filter( $fields ) );
			} else {
				$fields = array();
			}

			return update_option( 'yith_wcbep_custom_fields', $fields );
		}

		/**
		 * Retrieve Custom Fields info
		 *
		 * @return array
		 */
		public function get_custom_fields_info(): array {
			static $custom_fields = null;
			if ( is_null( $custom_fields ) ) {
				$custom_fields = self::get_custom_fields_option();

				foreach ( $custom_fields as &$custom_field ) {
					$custom_field['name'] = $this->create_field_name( $custom_field['id'] );
				}
			}

			return $custom_fields;
		}

		/**
		 * Allow editing custom fields in variations
		 *
		 * @return bool
		 * @since 1.2.31
		 */
		public function allow_editing_in_variations(): bool {
			return ! ! apply_filters( 'yith_wcbep_allow_editing_custom_fields_in_variations', false );
		}
	}
}

if ( ! function_exists( 'yith_wcbep_custom_fields_manager' ) ) {
	/**
	 * Unique access to instance of YITH_WCBEP_Badge_Management_Compatibility class
	 *
	 * @return YITH_WCBEP_Custom_Fields_Manager
	 * @since 1.0.11
	 */
	function yith_wcbep_custom_fields_manager(): YITH_WCBEP_Custom_Fields_Manager {
		return YITH_WCBEP_Custom_Fields_Manager::get_instance();
	}
}
