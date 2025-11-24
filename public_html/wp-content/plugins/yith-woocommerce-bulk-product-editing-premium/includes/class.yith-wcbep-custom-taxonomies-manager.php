<?php
/**
 * Custom Taxonomies class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Custom_Taxonomies_Manager' ) ) {
	/**
	 * Custom Taxonomies Manager
	 *
	 * @class   YITH_WCBEP_Custom_Taxonomies_Manager
	 * @since   1.2.1
	 */
	class YITH_WCBEP_Custom_Taxonomies_Manager {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Custom_Taxonomies_Manager
		 */
		protected static $instance;

		/**
		 * Custom taxonomies.
		 *
		 * @var array
		 */
		private static $custom_taxonomies;

		/**
		 * Custom taxonomy terms.
		 *
		 * @var array
		 */
		private $custom_taxonomies_terms = array();
		/**
		 * Custom taxonomy terms.
		 *
		 * @var array
		 */
		private static $taxonomies_option = 'yith_wcbep_custom_taxonomies';

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Custom_Taxonomies_Manager
		 */
		public static function get_instance(): YITH_WCBEP_Custom_Taxonomies_Manager {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {

			// Add custom taxonomies in BULK.
			add_filter( 'yith_wcbep_default_columns', array( $this, 'add_column' ) );
			add_filter( 'yith_wcbep_manage_custom_columns', array( $this, 'manage_column' ), 10, 3 );
			add_filter( 'yith_wcbep_variation_not_editable_and_empty', array( $this, 'edit_not_editable_and_empty_in_variations' ) );

			add_filter( 'yith_wcbep_extra_obj_class_chosen', array( $this, 'add_chosen_in_js' ) );
			add_action( 'yith_wcbep_extra_custom_input', array( $this, 'extra_custom_input' ) );
			add_action( 'yith_wcbep_update_product', array( $this, 'save' ), 10, 4 );

			add_action( 'yith_wcbep_extra_attr_bulk_fields', array( $this, 'add_extra_bulk_fields' ) );
			add_filter( 'yith_wcbep_extra_bulk_columns_chosen', array( $this, 'add_extra_bulk_columns_chosen' ) );

			// Filters.
			add_action( 'yith_wcbep_filters_after_attribute_fields', array( $this, 'add_fields_in_filters' ) );

			add_action( 'wp_ajax_yith_wcbep_add_taxonomy_column', array( $this, 'add_taxonomy_column' ) );
			add_action( 'wp_ajax_yith_wcbep_update_taxonomy_field_column', array( $this, 'update_taxonomy_column' ) );
			add_action( 'wp_ajax_yith_wcbep_delete_taxonomy_field_column', array( $this, 'delete_taxonomy_column' ) );

			add_filter( 'yith_wcbep_list_table_get_column_type', array( $this, 'get_column_type' ), 10, 2 );
			add_filter( 'yith_wcbep_bulk_editing_modal_tabs', array( $this, 'add_custom_taxonomies_tab_in_bulk_editing_modal' ) );
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
			return strpos( $name, 'yith_wcbep_tf_' ) === 0 ? 'taxonomy' : $type;
		}

		/**
		 * AJAX Add taxonomy column
		 *
		 * @return void
		 */
		public function add_taxonomy_column() {
			$response = array( 'success' => 'failure' );
			if ( isset( $_REQUEST['security'], $_REQUEST['taxonomy'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_add_taxonomy_column' ) ) {
				$taxonomy   = sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) );
				$taxonomies = self::get_custom_taxonomies();
				if ( taxonomy_exists( $taxonomy ) && ! in_array( $taxonomy, $taxonomies, true ) ) {
					$response['data']                 = array(
						'fieldLabel'    => $this->get_taxonomy_name( $taxonomy ),
						'fieldTaxonomy' => $taxonomy,
						'fieldKind'     => 'taxonomy',
						'fieldKey'      => 'yith_wcbep_tf_' . $taxonomy,
					);
					$response['data']['fieldInfo']    = wp_json_encode( $response['data'] );
					$response['data']['fieldClasses'] = 'yith-wcbep-enable-fields__taxonomy-field';

					$taxonomies[] = $taxonomy;
					if ( self::save_custom_taxonomies( $taxonomies ) ) {
						$enabled_columns   = YITH_WCBEP_List_Table::get_enabled_columns();
						$enabled_columns[] = 'yith_wcbep_tf_' . $taxonomy;
						YITH_WCBEP_List_Table::update_enabled_columns( $enabled_columns );
						$response['success'] = 'success';
					}
				}
			}
			wp_send_json( $response );
		}

		/**
		 * AJAX Update taxonomy column
		 *
		 * @return void
		 */
		public function update_taxonomy_column() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['taxonomy'], $_REQUEST['field_key'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_update_taxonomy_field_column' ) ) {
				$old_taxonomy_key  = sanitize_text_field( wp_unslash( $_REQUEST['field_key'] ) );
				$taxonomy          = sanitize_text_field( wp_unslash( $_REQUEST['taxonomy'] ) );
				$taxonomies_fields = self::get_custom_taxonomies();
				$index             = array_search( substr( $old_taxonomy_key, 14 ), $taxonomies_fields, true );
				if ( taxonomy_exists( $taxonomy ) && false !== $index ) {
					unset( $taxonomies_fields[ $index ] );
					$new_taxonomy_key                 = 'yith_wcbep_tf_' . $taxonomy;
					$taxonomies_fields[]              = $taxonomy;
					$response['data']                 = array(
						'fieldLabel'    => $this->get_taxonomy_name( $taxonomy ),
						'fieldTaxonomy' => $taxonomy,
						'fieldKind'     => 'taxonomy',
						'fieldKey'      => $new_taxonomy_key,
					);
					$response['data']['fieldInfo']    = wp_json_encode( $response['data'] );
					$response['data']['fieldClasses'] = 'yith-wcbep-enable-fields__taxonomy-field';
					$response['oldKey']               = $old_taxonomy_key;
					$response['newKey']               = $new_taxonomy_key;
					if ( self::save_custom_taxonomies( $taxonomies_fields ) ) {
						$enabled_columns   = YITH_WCBEP_List_Table::get_enabled_columns();
						$enabled_columns[] = $new_taxonomy_key;
						YITH_WCBEP_List_Table::update_enabled_columns( $enabled_columns );
						$response['success'] = 'success';
					}
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * AJAX Delete taxonomy column
		 *
		 * @return void
		 */
		public function delete_taxonomy_column() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['taxonomy_id'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_delete_taxonomy_field_column' ) ) {
				$taxonomies_fields = self::get_custom_taxonomies();
				$index             = array_search( $_REQUEST['taxonomy_id'], $taxonomies_fields, true );
				if ( false !== $index ) {
					unset( $taxonomies_fields[ $index ] );
					if ( self::save_custom_taxonomies( $taxonomies_fields ) ) {
						$response['success'] = 'success';
					}
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * Save custom taxonomies.
		 *
		 * @param array $taxonomies The taxonomies.
		 *
		 * @return bool
		 */
		public static function save_custom_taxonomies( array $taxonomies ): bool {

			if ( ! ! $taxonomies && is_array( $taxonomies ) ) {
				$taxonomies = array_map( 'sanitize_text_field', array_filter( $taxonomies ) );
			} else {
				$taxonomies = array();
			}

			return update_option( self::$taxonomies_option, $taxonomies );
		}

		/**
		 * Retrieve custom taxonomies.
		 *
		 * @return array
		 */
		public static function get_custom_taxonomies() {
			if ( ! isset( self::$custom_taxonomies ) ) {
				/**
				 * APPLY_FILTERS: yith_wcbep_get_custom_taxonomies
				 *
				 * Filters the custom taxonomies.
				 *
				 * @param array $custom_taxonomies The custom taxonomies options.
				 *
				 * @return array
				 */
				self::$custom_taxonomies = apply_filters( 'yith_wcbep_get_custom_taxonomies', get_option( self::$taxonomies_option, array() ) );
			}

			return self::$custom_taxonomies;
		}

		/**
		 * Retrieve all extra taxonomies options.
		 *
		 * @return array
		 */
		public static function get_all_extra_taxonomies_options(): array {
			$taxonomies = array_filter(
				get_object_taxonomies( 'product' ),
				function ( $taxonomy ) {
					$excluded_taxonomy = array( 'product_type', 'product_cat', 'product_tag', 'product_shipping_class', 'product_visibility' );

					return strpos( $taxonomy, 'pa_' ) !== 0 && ! in_array( $taxonomy, $excluded_taxonomy, true );
				}
			);

			return array_merge( array( '' => '' ), array_combine( $taxonomies, array_map( 'current', json_decode( json_encode( array_map( 'get_taxonomy_labels', array_map( 'get_taxonomy', $taxonomies ) ) ), true ) ) ) );
		}

		/**
		 * Retrieve the taxonomy name
		 *
		 * @param string $taxonomy_slug The tax slug.
		 *
		 * @return mixed
		 */
		public function get_taxonomy_name( $taxonomy_slug ) {
			$name = $taxonomy_slug;
			$tax  = get_taxonomy( $taxonomy_slug );
			if ( $tax ) {
				$labels = get_taxonomy_labels( $tax );
				$name   = $labels->name ?? $taxonomy_slug;
			}

			return $name;
		}

		/**
		 * Get the terms of the tax.
		 *
		 * @param string $taxonomy_slug The tax.
		 * @param array  $args          Arguments.
		 *
		 * @return array|WP_Error
		 */
		public function get_tax_array( string $taxonomy_slug, array $args = array() ) {
			if ( ! isset( $this->custom_taxonomies_terms[ $taxonomy_slug ] ) ) {
				$default_args = array(
					'taxonomy'   => $taxonomy_slug,
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				);
				$args         = wp_parse_args( $args, $default_args );

				$this->custom_taxonomies_terms[ $taxonomy_slug ] = yith_wcbep_get_terms( $args );
			}

			return $this->custom_taxonomies_terms[ $taxonomy_slug ];
		}

		/**
		 *  Add Custom Taxonomies in Bulk
		 */
		public function add_fields_in_filters() {
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( ! taxonomy_exists( $taxonomy_slug ) ) {
					continue;
				}

				$terms = $this->get_tax_array( $taxonomy_slug, array( 'hide_empty' => true ) );

				if ( ! empty( $terms ) ) {
					?>
					<tr>
						<td class="yith-wcbep-filter-form-label-col">
							<label><?php echo esc_html( $this->get_taxonomy_name( $taxonomy_slug ) ); ?></label>
						</td>
						<td class="yith-wcbep-filter-form-content-col">
							<select id="yith-wcbep-<?php echo esc_attr( $taxonomy_slug ); ?>-filter" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>"
								class="chosen is_resetable yith-wcbep-custom-taxonomy-filter" multiple xmlns="http://www.w3.org/1999/html">
								<?php
								foreach ( $terms as $term ) {
									?>
									<option value="<?php echo esc_attr( $term->term_id ); ?>"><?php echo esc_html( $term->name ); ?></option>
									<?php
								}
								?>
							</select>
						</td>
					</tr>
					<?php
				}
			}
		}

		/**
		 * Add classes for multi-select in JS.
		 *
		 * @param array $classes The classes.
		 *
		 * @return array
		 */
		public function add_chosen_in_js( array $classes ): array {
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( ! taxonomy_exists( $taxonomy_slug ) ) {
					continue;
				}

				$classes[] = $taxonomy_slug;
			}

			return $classes;
		}

		/**
		 * Custom input.
		 */
		public function extra_custom_input() {
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( ! taxonomy_exists( $taxonomy_slug ) ) {
					continue;
				}

				$terms = $this->get_tax_array( $taxonomy_slug );

				if ( ! empty( $terms ) ) {
					?>
					<div id="yith-wcbep-custom-input-<?php echo esc_attr( $taxonomy_slug ); ?>" class="yith-wcbep-custom-input">
						<select id="yith-wcbep-custom-input-<?php echo esc_attr( $taxonomy_slug ); ?>-select" class="chosen yith-wcbep-chosen" multiple xmlns="http://www.w3.org/1999/html">
							<?php
							foreach ( $terms as $b ) {
								?>
								<option value="<?php echo esc_attr( $b->term_id ); ?>"><?php echo esc_html( $b->name ); ?></option>
								<?php
							}
							?>
						</select>
					</div>
					<?php
				}
			}
		}

		/**
		 * Add column in table.
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function add_column( array $columns ): array {
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( taxonomy_exists( $taxonomy_slug ) ) {
					$columns[ 'yith_wcbep_tf_' . $taxonomy_slug ] = array(
						'kind'     => 'taxonomy',
						'taxonomy' => $taxonomy_slug,
						'label'    => $this->get_taxonomy_name( $taxonomy_slug ),
					);
				}
			}

			return $columns;
		}

		/**
		 * Manage columns
		 *
		 * @param string  $cell        The value to be shown.
		 * @param string  $column_name The column.
		 * @param WP_Post $post        The Post object.
		 *
		 * @return string
		 */
		public function manage_column( string $cell, string $column_name, WP_Post $post ): string {
			$custom_taxonomies = self::get_custom_taxonomies();
			if ( strpos( $column_name, 'yith_wcbep_tf_' ) === 0 ) {
				$product       = wc_get_product( $post );
				$taxonomy_slug = str_replace( 'yith_wcbep_tf_', '', $column_name );
				if ( $product && in_array( $taxonomy_slug, $custom_taxonomies, true ) ) {
					$terms = get_the_terms( $product->get_id(), $taxonomy_slug );
					$terms = $terms ? array_combine( array_column( $terms, 'term_id' ), array_column( $terms, 'name' ) ) : array();
					$cell  = YITH_WCBEP_List_Table::render_column(
						array(
							'value'   => esc_attr( wp_json_encode( count( $terms ) > 0 ? $terms : new stdClass() ) ),
							'display' => implode( ', ', $terms ),
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
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( ! taxonomy_exists( $taxonomy_slug ) ) {
					continue;
				}

				$columns[] = $taxonomy_slug;
			}

			return $columns;
		}

		/**
		 * Add fields to the "extra-bulk-columns" for multi-select fields
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function add_extra_bulk_columns_chosen( array $columns ): array {
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( ! taxonomy_exists( $taxonomy_slug ) ) {
					continue;
				}

				$columns[] = $taxonomy_slug;
			}

			return $columns;
		}

		/**
		 * Save tax in product.
		 *
		 * @param WC_Product $product       The Product.
		 * @param array      $matrix_keys   The matrix.
		 * @param array      $single_modify The single edit row.
		 * @param bool       $is_variation  True if is a variation.
		 */
		public function save( WC_Product $product, array $matrix_keys, array $single_modify, bool $is_variation ) {
			foreach ( self::get_custom_taxonomies() as $taxonomy_slug ) {
				if ( ! taxonomy_exists( $taxonomy_slug ) ) {
					continue;
				}

				$index = array_search( $taxonomy_slug, $matrix_keys, true );
				if ( ! empty( $single_modify[ $index ] ) ) {
					if ( ! $is_variation ) {
						$new_value = $single_modify[ $index ];
						$terms     = json_decode( $new_value );
						wp_set_post_terms( $product->get_id(), $terms, $taxonomy_slug );
					}
				}
			}
		}

		/**
		 * Adding the "Custom taxonomies" tab in bulk editing modal
		 *
		 * @param array $tabs The tabs of bulk editing modal.
		 *
		 * @return array
		 */
		public function add_custom_taxonomies_tab_in_bulk_editing_modal( array $tabs ): array {
			$tabs['custom-taxonomies'] = _x( 'Custom taxonomies', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' );

			return $tabs;
		}

		/*
		|--------------------------------------------------------------------------
		| Deprecated functions
		|--------------------------------------------------------------------------
		*/

		// Deprecated since 2.0 version.

		/**
		 * Render the panel tab for "custom taxonomies"
		 *
		 * @depecated since 2.0 | The custom taxonomies tab has been removed
		 */
		public function render_panel_tab() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::render_panel_tab', '2.0.0' );
		}

		/**
		 * Add the "custom taxonomies" tab to the panel
		 *
		 * @param array $tabs The tabs.
		 *
		 * @return array
		 * @depecated since 2.0 | Custom Taxonomies tab has been removed
		 */
		public function add_panel_tab( array $tabs ): array {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::add_panel_tab', '2.0.0' );

			return $tabs;
		}

		/**
		 * Save custom taxonomies.
		 *
		 * @depecated since 2.0 | Custom Taxonomies tab has been removed
		 */
		public function action_save_custom_taxonomies() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::action_save_custom_taxonomies', '2.0.0' );
		}

		/**
		 * Add fields to Bulk editor.
		 *
		 * @depreacted since 2.0
		 */
		public function add_extra_bulk_fields() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::add_extra_bulk_fields', '2.0.0' );
		}

		// Deprecated since 1.2.36 version.

		/**
		 * Add the "custom taxonomies" tab to the panel
		 *
		 * @param array $tabs The tabs.
		 *
		 * @return array
		 * @deprecated 1.2.36 | use YITH_WCBEP_Custom_Taxonomies_Manager::add_panel_tab instead
		 */
		public function add_custom_field_tab( array $tabs ): array {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::add_custom_field_tab', '1.2.36', 'YITH_WCBEP_Custom_Taxonomies_Manager::add_panel_tab' );

			return $tabs;
		}

		/**
		 * Render the panel tab for "custom taxonomies"
		 *
		 * @deprecated 1.2.36 | use YITH_WCBEP_Custom_Taxonomies_Manager::render_panel_tab instead
		 */
		public function render_custom_fields_tab() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::render_custom_fields_tab', '1.2.36' );
		}

		/**
		 * Save custom taxonomies.
		 *
		 * @deprecated 1.2.36 | use YITH_WCBEP_Custom_Taxonomies_Manager::action_save_custom_taxonomies instead
		 */
		public function action_save_custom_fields() {
			wc_deprecated_function( 'YITH_WCBEP_Custom_Taxonomies_Manager::action_save_custom_fields', '1.2.36', 'YITH_WCBEP_Custom_Taxonomies_Manager::action_save_custom_taxonomies' );
		}
	}
}

/**
 * Unique access to instance of YITH_WCBEP_Badge_Management_Compatibility class
 *
 * @since 1.2.1
 * @return YITH_WCBEP_Custom_Taxonomies_Manager
 */
function yith_wcbep_custom_taxonomies_manager(): YITH_WCBEP_Custom_Taxonomies_Manager {
	return YITH_WCBEP_Custom_Taxonomies_Manager::get_instance();
}
