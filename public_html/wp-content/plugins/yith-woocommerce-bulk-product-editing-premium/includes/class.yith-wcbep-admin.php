<?php
/**
 * Admin class
 *
 * @package YITH\BulkProductEditing
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Admin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since    1.0.0
	 */
	class YITH_WCBEP_Admin {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 * @var YITH_WCBEP_Admin
		 */
		protected static $instance;

		/**
		 * Panel Object
		 *
		 * @var YIT_Plugin_Panel_WooCommerce $panel
		 */
		protected $panel;

		/**
		 * Panel page
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wcbep_panel';

		/**
		 * Docs url
		 *
		 * @since 2.0
		 * @var string Official documentation url
		 */
		public $doc_url = 'https://docs.yithemes.com/yith-woocommerce-bulk-product-editing/';

		/**
		 * Help center url
		 *
		 * @since 2.0
		 * @var string Official documentation url
		 */
		public $hc_url = 'https://support.yithemes.com/hc/en-us/categories/360003475698-YITH-WOOCOMMERCE-BULK-PRODUCT-EDITING';

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Admin
		 * @since 1.0.0
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Table views class instance
		 *
		 * @var YITH_WCBEP_Table_Views
		 */
		public $table_views_instance;

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  1.0.0
		 */
		protected function __construct() {
			$this->table_views_instance = yith_wcbep_table_views();
			yith_wcbep_custom_fields_manager();
			yith_wcbep_custom_taxonomies_manager();
			yith_wcbep_bulk_actions();

			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );

			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCBEP_DIR . '/' . basename( YITH_WCBEP_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 3 );

			add_action( 'yith_wcbep_bulk_edit_main_tab', array( $this, 'main_tab' ) );

			add_action( 'yith_wcbep_print_product_image_field', array( $this, 'print_product_image_field' ) );

			add_action( 'admin_init', array( $this, 'redirect_to_bulk_edit_page' ) );

			add_action( 'wp_ajax_yith_wcbep_load_products_table', array( $this, 'ajax_fetch_table_callback' ) );
			add_action( 'wp_ajax_yith_wcbep_save_columns_visibility', array( $this, 'save_default_hidden_cols' ) );
			add_action( 'wp_ajax_yith_wcbep_save_enabled_fields', array( $this, 'save_enabled_columns' ) );

			add_filter( 'woocommerce_product_export_product_query_args', array( $this, 'filter_product_ids_to_export' ) );

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
			add_action( 'wp_ajax_yith_wcbep_create_product', array( $this, 'ajax_create_product' ) );
			add_action( 'wp_ajax_yith_wcbep_save_products_changes', array( $this, 'ajax_save_products_changes' ) );
			add_action( 'wp_ajax_yith_wcbep_update_columns_width', array( $this, 'ajax_update_columns_width' ) );
			add_action( 'wp_ajax_yith_wcbep_search_categories', array( $this, 'ajax_search_categories' ) );

			add_action( 'yith_plugin_fw_get_field_text_after', array( $this, 'print_schedule_on_sale_price_field' ) );
		}

		/**
		 * AJAX search categories
		 *
		 * @return void
		 */
		public function ajax_search_categories() {
			$categories = array();
			if ( isset( $_REQUEST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_search_categories' ) ) {
				$name_type  = sanitize_text_field( wp_unslash( $_REQUEST['name_type'] ?? get_option( 'yith-wcbep-name-in-categories-dropdown', 'name' ) ) );
				$term       = sanitize_text_field( wp_unslash( $_REQUEST['term'] ?? '' ) );
				$args       = array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'name__like' => $term,
					'fields'     => 'id=>name',
				);
				$categories = get_terms( $args );
				foreach ( $categories as $cat_id => &$cat_name ) {
					$cat = get_term( $cat_id, 'product_cat' );
					if ( in_array( $name_type, array( 'parent-and-name-with-slug', 'name-with-slug' ), true ) ) {
						$cat_name .= ' (' . $cat->slug . ')';
					}
					if ( in_array( $name_type, array( 'parent-and-name', 'parent-and-name-with-slug' ), true ) ) {
						while ( $cat->parent ) {
							$cat      = get_term( $cat->parent, 'product_cat' );
							$cat_name = $cat->name . ' > ' . $cat_name;
						}
					};
				}
			}
			wp_send_json( $categories );
			exit();
		}

		/**
		 * AJAX create product
		 *
		 * @return void
		 */
		public function ajax_create_product() {
			$response = array(
				'success' => 'failure',
			);

			if ( isset( $_REQUEST['security'], $_REQUEST['product_options'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_create_product' ) ) {
				$new_product = new WC_Product();
				foreach ( wp_unslash( $_REQUEST['product_options'] ) as $prop => $value ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					if ( 'image_url' === $prop ) {
						$value = attachment_url_to_postid( $value );
						$prop  = 'image_id';
					}
					$setter = "set_$prop";
					if ( method_exists( $new_product, $setter ) ) {
						$new_product->{$setter}( $value );
					}
				}
				$product_id = $new_product->save();
				if ( $product_id ) {
					$table                 = new YITH_WCBEP_List_Table();
					$response['success']   = 'success';
					$response['productID'] = $product_id;
					$response['tableData'] = $table->get_ajax_response();
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * AJAX save products changes
		 *
		 * @return void
		 * @since 2.0
		 */
		public function ajax_save_products_changes() {
			global $pagenow;
			$response = array(
				'success' => 'failure',
			);

			if ( isset( $_REQUEST['security'], $_REQUEST['products_changes'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_save_products_changes' ) ) {
				$products_changes = wp_unslash( $_REQUEST['products_changes'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				foreach ( $products_changes as $product_id => $changes ) {
					$product = wc_get_product( $product_id );

					if ( $product ) {

						do_action( 'yith_wcbep_before_save_product_changes', $product );

						foreach ( $changes as $prop => $value ) {
							do_action( 'yith_wcbep_save_products_changes_' . $prop . '_prop' );

							/**
							 * APPLY_FILTERS: yith_wcbep_custom_prop_save_handler
							 *
							 * Checks if is a custom saving.
							 *
							 * @param bool       $is_custom The flag.
							 * @param WC_Product $product   The product.
							 * @param string     $prop      The property key.
							 * @param mixed      $value     The property value.
							 *
							 * @return bool
							 */
							$custom_saving = apply_filters( 'yith_wcbep_custom_prop_save_handler', false, $product, $prop, $value );
							if ( $custom_saving instanceof WC_Product && $custom_saving->get_id() === $product->get_id() ) {
								$product = $custom_saving;
							} else {
								if ( strpos( $prop, 'yith_wcbep_tf_' ) === 0 ) {
									// Taxonomy Fields editing.
									$value = json_decode( $value, true );
									wp_set_post_terms( $product->get_id(), array_keys( $value ), substr( $prop, 14 ) );

								} elseif ( strpos( $prop, 'yith_wcbep_cf_' ) === 0 ) {
									// Custom Fields editing.
									$custom_fields = yith_wcbep_custom_fields_manager()->get_custom_fields_info();
									$custom_field  = $custom_fields[ $prop ] ?? false;
									$meta_key      = $custom_field ? $custom_field['id'] : substr( $prop, 14 );
									if ( apply_filters( 'yith_wcbep_serialize_custom_fields_when_saving', true, $prop, $custom_fields ) && yith_wcbep_is_json( $value ) ) {
										$value = json_decode( $value, true );
									}

									if ( 0 === strpos( $meta_key, '_' ) ) {
										update_post_meta( $product->get_id(), $meta_key, $value );
									} else {
										$product->update_meta_data( $meta_key, $value );
									}
								} elseif ( strpos( $prop, 'attr_pa' ) === 0 ) {
									// Attribute Fields editing.
									$attribute  = substr( $prop, 5 );
									$value      = $value ? json_decode( $value, true ) : array();
									$attributes = $product->get_attributes();
									if ( isset( $attributes[ $attribute ] ) ) {
										$attr = $attributes[ $attribute ];
										unset( $attributes[ $attribute ] );
										$product->set_attributes( $attributes );
									} else {
										$attr = new WC_Product_Attribute();

										$attr->set_id( wc_attribute_taxonomy_id_by_name( $attribute ) );
										$attr->set_name( $attribute );
									}

									$attr->set_options( array_filter( array_map( 'intval', array_keys( $value['terms'] ?? array() ) ) ) );
									$attr->set_variation( $value['is_variation'] );
									$attr->set_visible( $value['is_visible'] );
									$attributes[ $attribute ] = $attr;
									$product->set_attributes( $attributes );

									if ( isset( $value['default'] ) ) {
										$defaults   = $product->get_default_attributes( 'edit' );
										$terms      = get_terms( $attribute );
										$terms      = array_combine( array_column( $terms, 'term_id' ), array_column( $terms, 'slug' ) );
										$default_id = is_array( $value['default'] ) ? current( array_keys( $value['default'] ) ) : '';
										$term_slug  = $terms[ $default_id ] ?? false;
										if ( $term_slug && ( $defaults[ $attribute ] ?? '' ) !== $term_slug ) {
											$defaults[ $attribute ] = $term_slug;
											$product->set_default_attributes( $defaults );
										}
									}
								} else {
									$props_to_methods = array(
										'title'            => 'name',
										'shortdesc'        => 'short_description',
										'sale_price_from'  => 'date_on_sale_from',
										'sale_price_to'    => 'date_on_sale_to',
										'categories'       => 'category_ids',
										'tags'             => 'tag_ids',
										'enable_reviews'   => 'reviews_allowed',
										'allow_backorders' => 'backorders',
										'shipping_class'   => 'shipping_class_id',
										'visibility'       => 'catalog_visibility',
										'up_sells'         => 'upsell_ids',
										'cross_sells'      => 'cross_sell_ids',
									);

									switch ( $prop ) {
										case 'button_text':
										case 'product_url':
											break;
										case 'prod_type':
											$product->save();
											$new_type_class_name = WC_Product_Factory::get_product_classname( $product->get_id(), $value );
											$product             = new $new_type_class_name( $product->get_id() );
											break;
										case 'date':
											$value = $value - wc_timezone_offset();
											$product->set_date_created( $value );
											break;
										case 'up_sells':
										case 'cross_sells':
										case 'tags':
										case 'categories':
											$value  = $value ? array_keys( json_decode( $value, true ) ) : array();
											$setter = 'set_' . ( $props_to_methods[ $prop ] ?? '' );
											if ( method_exists( $product, $setter ) ) {
												$product->{$setter}( array_filter( array_map( 'intval', $value ) ) );
											}
											break;
										case 'image':
											$image_options = $value ? json_decode( $value, true ) : array();
											$product->set_image_id( $image_options['image_id'] ?? false );
											break;
										case 'image_gallery':
											$gallery_options = $value ? json_decode( $value, true ) : array();
											$product->set_gallery_image_ids( array_filter( array_map( 'intval', array_column( $gallery_options, 'image_id' ) ) ) );
											break;
										case 'downloadable_files':
											$value = $value ? json_decode( $value, true ) : array();
											$product->set_downloads( $value );
											break;
										case 'sale_price_from':
											$value = $value ? $value - wc_timezone_offset() : null;
											$product->set_date_on_sale_from( $value );
											break;
										case 'sale_price_to':
											$value = $value ? $value - wc_timezone_offset() : null;
											$product->set_date_on_sale_to( $value );
											break;
										default:
											$setter = 'set_' . ( array_key_exists( $prop, $props_to_methods ) ? $props_to_methods[ $prop ] : $prop );
											if ( method_exists( $product, $setter ) ) {
												$product->$setter( $value );
											} else {
												/**
												 * APPLY_FILTERS: yith_wcbep_save_product_prop_changes
												 *
												 * Used to update the product when trying to update an option in which the setter do not exist.
												 *
												 * @param WC_Product $product The product.
												 * @param string     $prop    The property key.
												 * @param mixed      $value   The property value.
												 *
												 * @return WC_Product
												 */
												$product = apply_filters( 'yith_wcbep_save_product_prop_changes', $product, $prop, $value );
											}
											break;
									}
								}
							}
						}

						if ( ! $product->is_type( 'variation' ) && $product instanceof WC_Product_External ) {
							if ( isset( $changes['button_text'] ) ) {
								$product->set_button_text( $changes['button_text'] );
							}
							if ( isset( $changes['product_url'] ) ) {
								$product->set_product_url( $changes['product_url'] );
							}
						}

						wc_delete_product_transients( $product_id );

						$old_pagenow = $pagenow;
						$pagenow     = 'post.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$post        = get_post( yit_get_base_product_id( $product ) );

						$product->save();

						do_action( 'save_post', $product_id, $post, true );

						if ( $product_id !== $post->ID ) {
							do_action( 'save_post', $post->ID, $post, true );
						}

						$pagenow             = $old_pagenow; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
						$response['success'] = 'success';

						do_action( 'yith_wcbep_after_save_product_changes', $product );
					}
				}
			}

			wp_send_json( $response );
			exit();
		}

		/**
		 * AJAX update columns width
		 *
		 * @return void
		 */
		public function ajax_update_columns_width() {
			$response = array( 'success' => 'failure' );
			if ( isset( $_POST['columns_width'], $_POST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'yith_wcbep_update_columns_width' ) ) {
				$response['success'] = 'success';
				$response['updated'] = wc_bool_to_string( $this->update_columns_width( array_combine( array_map( 'sanitize_text_field', wp_unslash( array_column( $_POST['columns_width'], 'id' ) ) ), array_map( 'absint', array_column( $_POST['columns_width'], 'width' ) ) ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * Get columns' width
		 *
		 * @return array
		 */
		private function get_columns_width(): array {
			$value = get_option( 'yith_wcbep_products_table_columns_width', array() );

			return is_array( $value ) ? $value : array();
		}

		/**
		 * Update columns width
		 *
		 * @param array $columns_width The columns' width to update.
		 *
		 * @return bool
		 */
		private function update_columns_width( $columns_width ): bool {
			$old_columns_width = $this->get_columns_width();
			$columns_width     = array_merge( $old_columns_width, $columns_width );

			return update_option( 'yith_wcbep_products_table_columns_width', $columns_width );
		}

		/**
		 * Print schedule on sale price field
		 *
		 * @param array $field The field options.
		 *
		 * @return void
		 */
		public function print_schedule_on_sale_price_field( $field ) {
			if ( 'yith-wcbep-add-product-sale_price-field' === ( $field['id'] ?? '' ) ) {
				echo '<span class="yith-wcbep-add-product__sale-price-schedule">' . esc_html__( 'Schedule', 'yith-woocommerce-bulk-product-editing' ) . '</span>';
			}
		}

		/**
		 * Print product image field
		 *
		 * @return void
		 */
		public function print_product_image_field() {
			yith_wcbep_get_view( 'modals/add-product/product-image-field.php' );
		}

		/**
		 * Get table [AJAX]
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function ajax_fetch_table_callback() {
			// phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting

			if ( isset( $_REQUEST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_load_products_table' ) ) {
				$extra_data = array();
				if ( isset( $_REQUEST['ywcbep_args']['update_products_per_page'], $_REQUEST['ywcbep_args']['posts_per_page'] ) ) {
					update_option( 'yith_wcbep_products_per_page', absint( $_REQUEST['ywcbep_args']['posts_per_page'] ) );
				}
				if ( isset( $_REQUEST['ywcbep_args']['filters'] ) ) {
					if ( isset( $_REQUEST['ywcbep_args']['createNewView'], $_REQUEST['ywcbep_args']['viewName'] ) && 'yes' === sanitize_text_field( wp_unslash( $_REQUEST['ywcbep_args']['createNewView'] ) ) ) {
						$new_table_view                        = yith_wcbep_table_views()->create_table_view( sanitize_text_field( wp_unslash( $_REQUEST['ywcbep_args']['viewName'] ) ), yith_wcbep_table_views()->custom_filters_to_view_conditions( wp_unslash( $_REQUEST['ywcbep_args']['filters'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$extra_data['newTableView']            = array(
							'viewKey'         => $new_table_view['key'],
							'viewName'        => $new_table_view['name'],
							'viewOptionsJSON' => wp_json_encode( $new_table_view ),
						);
						$_REQUEST['ywcbep_args']['table_view'] = $new_table_view['key'];
					}
					yith_wcbep_table_views()->update_custom_filters( wp_unslash( $_REQUEST['ywcbep_args']['filters'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				}

				// Disable display_errors during this ajax requests to prevent malformed JSON.
				$current_error_reporting = error_reporting();
				error_reporting( 0 );

				$table = new YITH_WCBEP_List_Table();
				$table->ajax_response( $extra_data );

				error_reporting( $current_error_reporting );
			}

			// phpcs:enable
		}

		/**
		 * Get main-tab template
		 */
		public function main_tab() {
			$products_per_page   = get_option( 'yith_wcbep_products_per_page', 10 );
			$table_views         = $this->table_views_instance->get_table_views_list();
			$selected_table_view = $this->table_views_instance->get_selected_view_key();
			$bulk_actions        = array(
				'edit'      => __( 'Edit', 'yith-woocommerce-bulk-product-editing' ),
				'duplicate' => __( 'Duplicate', 'yith-woocommerce-bulk-product-editing' ),
				'trash'     => __( 'Move to trash', 'yith-woocommerce-bulk-product-editing' ),
				'delete'    => __( 'Delete permanently', 'yith-woocommerce-bulk-product-editing' ),
			);
			if ( current_user_can( 'edit_products' ) && current_user_can( 'export' ) ) {
				$bulk_actions['export'] = __( 'Export', 'yith-woocommerce-bulk-product-editing' );
			}

			if ( 'custom-filters' === $selected_table_view ) {
				$table_views = array_merge(
					array(
						'custom-filters' => __( 'Custom filters', 'yith-woocommerce-bulk-product-editing' ),
					),
					$table_views
				);
			}

			yith_wcbep_get_view( 'products-table-tab.php', compact( 'table_views', 'bulk_actions', 'selected_table_view', 'products_per_page' ) );
		}

		/**
		 * Get table [AJAX]
		 */
		public function get_table_ajax() {
			$table = new YITH_WCBEP_List_Table();
			$table->prepare_items();
			$table->display();
			die();
		}

		/**
		 * Action Links
		 * add the action links to plugin admin page
		 *
		 * @param array $links The action links.
		 *
		 * @return   array
		 * @use      plugin_action_links_{$plugin_file_name}
		 * @since    1.0
		 */
		public function action_links( $links ) {
			return yith_add_action_links( $links, $this->panel_page, defined( 'YITH_WCBEP_PREMIUM' ), YITH_WCBEP_SLUG );
		}

		/**
		 * Adds row meta.
		 *
		 * @param array    $row_meta_args Row meta arguments.
		 * @param string[] $plugin_meta   An array of the plugin's metadata,
		 *                                including the version, author,
		 *                                author URI, and plugin URI.
		 * @param string   $plugin_file   Path to the plugin file relative to the plugins directory.
		 *
		 * @return array
		 */
		public function plugin_row_meta( $row_meta_args, $plugin_meta, $plugin_file ) {
			$init = defined( 'YITH_WCBEP_FREE_INIT' ) ? YITH_WCBEP_FREE_INIT : YITH_WCBEP_INIT;

			if ( $init === $plugin_file ) {
				$row_meta_args['slug']       = YITH_WCBEP_SLUG;
				$row_meta_args['is_premium'] = defined( 'YITH_WCBEP_PREMIUM' );
			}

			return $row_meta_args;
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @use      /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {
			if ( ! empty( $this->panel ) ) {
				return;
			}

			$admin_tabs = $this->get_admin_tabs();

			$args = apply_filters(
				'yith_wcbep_plugin_panel_args',
				array(
					'ui_version'       => 2,
					'create_menu_page' => true,
					'parent_slug'      => '',
					'plugin_slug'      => YITH_WCBEP_SLUG,
					'plugin_version'   => YITH_WCBEP_VERSION,
					'class'            => yith_set_wrapper_class(),
					'page_title'       => 'YITH WooCommerce Bulk Product Editing',
					'menu_title'       => 'Bulk Product Editing',
					'capability'       => 'edit_products',
					'parent'           => '',
					'parent_page'      => 'yith_plugin_panel',
					'page'             => $this->panel_page,
					'admin-tabs'       => $admin_tabs,
					'options-path'     => YITH_WCBEP_DIR . '/plugin-options',
					'is_premium'       => true,
					'is_extended'      => false,
					'welcome_modals'   => $this->get_welcome_modals(),
					'help_tab'         => array(
						'hc_url'     => $this->hc_url,
						'main_video' => array(
							'desc' => _x( 'Check this video to learn how to <b>bulk edit your product info in no time:</b>', 'Help tab - Video title', 'yith-woocommerce-bulk-product-editing' ),
							'url'  => array(
								'en' => 'https://www.youtube.com/embed/G79dSFqABPI',
								'it' => 'https://www.youtube.com/embed/dUwnDfZk_Es',
								'es' => 'https://www.youtube.com/embed/rj-Gm__WbaQ',
							),
						),
						'doc_url'    => $this->doc_url,
					),
					'your_store_tools' => $this->get_your_store_tools_tab_args(),
				)
			);

			/* === Fixed: not updated theme  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once 'plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );

			add_action( 'woocommerce_admin_field_yith_wcbep_upload', array( $this->panel, 'yit_upload' ), 10, 1 );
		}

		/**
		 * Get admin Tabs
		 *
		 * @return array
		 */
		protected function get_admin_tabs() {
			/**
			 * APPLY_FILTERS: yith_wcbep_settings_admin_tabs
			 *
			 * Filter the admin panel tabs.
			 *
			 * @param array $tabs The tabs.
			 *
			 * @return array
			 */
			return apply_filters(
				'yith_wcbep_settings_admin_tabs',
				array(
					'bulk-edit' => array(
						'title'       => __( 'Products table', 'yith-woocommerce-bulk-product-editing' ),
						'description' => _x( 'Customize the products table and use bulk editing to save time and effort.', '[ADMIN] Tab description', 'yith-woocommerce-bulk-product-editing' ),
						'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"> <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M13.125 12h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125M20.625 12c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5M12 14.625v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 14.625c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 1.5v-1.5m0 0c0-.621.504-1.125 1.125-1.125m0 0h7.5" /> </svg>',
					),
					'settings'  => array(
						'title'       => __( 'Settings', 'yith-woocommerce-bulk-product-editing' ),
						'description' => _x( "Configure the plugin's general settings.", '[ADMIN] Tab description', 'yith-woocommerce-bulk-product-editing' ),
						'icon'        => 'settings',
					),
				)
			);
		}

		/**
		 * Get welcome modals options
		 *
		 * @return array[][]
		 */
		protected function get_welcome_modals() {
			return array(
				'show_in' => 'panel',
				'modals'  => array(
					'welcome' => array(
						'type'     => 'welcome',
						'show'     => ! isset( $_GET['yith-wcbep-no-welcome-modal'] ) && false === get_option( 'yith_wcbep_enabled_columns', false ) && 'welcome' === get_option( 'yith-wcbep-welcome-modal', 'welcome' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'on_close' => function () {
							update_option( 'yith-wcbep-welcome-modal', 'no' );
						},
						'items'    => array(
							'documentation'       => array( 'url' => $this->doc_url ),
							'start_from_step_one' => array(
								'title'       => __( 'Are you ready? <mark>Start with step 1!</mark>', 'yith-woocommerce-bulk-product-editing' ),
								'description' => __( 'Open the step 1 modal window and choose which fields to enable for the bulk editing window', 'yith-woocommerce-bulk-product-editing' ),
								'url'         => add_query_arg(
									array(
										'page'                        => $this->panel_page,
										'yith-wcbep-no-welcome-modal' => '',
									),
									admin_url( 'admin.php' )
								),
							),
						),
					),
					'update'  => array(
						'type'     => 'update',
						'show'     => ! isset( $_GET['yith-wcbep-no-welcome-modal'] ) && false !== get_option( 'yith_wcbep_enabled_columns', false ) && 'update' === get_option( 'yith-wcbep-welcome-modal', 'update' ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						'on_close' => function () {
							update_option( 'yith-wcbep-welcome-modal', 'no' );
						},
						'items'    => array(
							'improved_uxui'  => array(
								'title'       => __( 'Improved UX/UI', 'yith-woocommerce-bulk-product-editing' ),
								'description' => __( 'With the new design, configuring our plugin is easier than ever', 'yith-woocommerce-bulk-product-editing' ),
							),
							'table_views'    => array(
								'title'       => __( 'Create and save unlimited table views with filters', 'yith-woocommerce-bulk-product-editing' ),
								'description' => __( 'Create as many table views as needed to choose which products to show in your bulk editing table', 'yith-woocommerce-bulk-product-editing' ),
							),
							'create_product' => array(
								'title'       => __( 'An easy way to create new products', 'yith-woocommerce-bulk-product-editing' ),
								'description' => __( 'Create your products in a fast and simple process, directly from the integrated modal window', 'yith-woocommerce-bulk-product-editing' ),
							),
						),
						'since'    => '2.0',
					),
				),
			);
		}

		/**
		 * Get "Your Store Tools" tab arguments
		 *
		 * @return array[][]
		 */
		protected function get_your_store_tools_tab_args() {
			return array(
				'items' => array(
					'wishlist'               => array(
						'name'           => 'YITH WooCommerce Wishlist',
						'icon_url'       => YITH_WCBEP_ASSETS_URL . '/images/plugins/wishlist.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-wishlist/',
						'description'    => _x(
							'Allow your customers to create lists of products they want and share them with family and friends.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Wishlist',
							'yith-woocommerce-badges-management'
						),
						'is_active'      => defined( 'YITH_WCWL_PREMIUM' ),
						'is_recommended' => true,
					),
					'gift-cards'             => array(
						'name'           => 'YITH WooCommerce Gift Cards',
						'icon_url'       => YITH_WCBEP_ASSETS_URL . '/images/plugins/gift-cards.svg',
						'url'            => '//yithemes.com/themes/plugins/yith-woocommerce-gift-cards/',
						'description'    => _x(
							'Sell gift cards in your shop to increase your earnings and attract new customers.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Gift Cards',
							'yith-woocommerce-badges-management'
						),
						'is_active'      => defined( 'YITH_YWGC_PREMIUM' ),
						'is_recommended' => true,
					),
					'request-a-quote'        => array(
						'name'        => 'YITH WooCommerce Request a Quote',
						'icon_url'    => YITH_WCBEP_ASSETS_URL . '/images/plugins/request-a-quote.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-request-a-quote/',
						'description' => _x(
							'Hide prices and/or the "Add to cart" button and let your customers request a custom quote for every product.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Request a Quote',
							'yith-woocommerce-badges-management'
						),
						'is_active'   => defined( 'YITH_YWRAQ_PREMIUM' ),
					),
					'ajax-product-filter'    => array(
						'name'        => 'YITH WooCommerce Ajax Product Filter',
						'icon_url'    => YITH_WCBEP_ASSETS_URL . '/images/plugins/ajax-product-filter.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-ajax-product-filter/',
						'description' => _x(
							'Help your customers to easily find the products they are looking for and improve the user experience of your shop.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Ajax Product Filter',
							'yith-woocommerce-badges-management'
						),
						'is_active'   => defined( 'YITH_WCAN_PREMIUM' ),
					),
					'product-addons'         => array(
						'name'        => 'YITH WooCommerce Product Add-Ons & Extra Options',
						'icon_url'    => YITH_WCBEP_ASSETS_URL . '/images/plugins/product-add-ons.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-product-add-ons/',
						'description' => _x(
							'Add paid or free advanced options to your product pages using fields like radio buttons, checkboxes, drop-downs, custom text inputs, and more.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Product Add-Ons',
							'yith-woocommerce-badges-management'
						),
						'is_active'   => defined( 'YITH_WAPO_PREMIUM' ),
					),
					'dynamic-pricing'        => array(
						'name'        => 'YITH WooCommerce Dynamic Pricing and Discounts',
						'icon_url'    => YITH_WCBEP_ASSETS_URL . '/images/plugins/dynamic-pricing-and-discounts.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-dynamic-pricing-and-discounts/',
						'description' => _x(
							'Increase conversions through dynamic discounts and price rules, and build powerful and targeted offers.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Dynamic Pricing and Discounts',
							'yith-woocommerce-badges-management'
						),
						'is_active'   => defined( 'YITH_YWDPD_PREMIUM' ),
					),
					'customize-my-account'   => array(
						'name'        => 'YITH WooCommerce Customize My Account Page',
						'icon_url'    => YITH_WCBEP_ASSETS_URL . '/images/plugins/customize-myaccount-page.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-customize-my-account-page/',
						'description' => _x(
							'Customize the My Account page of your customers by creating custom sections with promotions and ad-hoc content based on your needs.',
							'[YOUR STORE TOOLS TAB] Description for plugin YITH WooCommerce Customize My Account',
							'yith-woocommerce-badges-management'
						),
						'is_active'   => defined( 'YITH_WCMAP_PREMIUM' ),
					),
					'recover-abandoned-cart' => array(
						'name'        => 'YITH WooCommerce Recover Abandoned Cart',
						'icon_url'    => YITH_WCBEP_ASSETS_URL . '/images/plugins/recover-abandoned-cart.svg',
						'url'         => '//yithemes.com/themes/plugins/yith-woocommerce-recover-abandoned-cart/',
						'description' => _x(
							'Contact users who have added products to the cart without completing the order and try to recover lost sales.',
							'[YOUR STORE TOOLS TAB] Description for plugin Recover Abandoned Cart',
							'yith-woocommerce-badges-management'
						),
						'is_active'   => defined( 'YITH_YWRAC_PREMIUM' ),
					),
				),
			);
		}

		/**
		 * Redirect to bulk edit page
		 */
		public function redirect_to_bulk_edit_page() {
			global $pagenow;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'edit.php' === $pagenow && isset( $_GET['post_type'], $_GET['page'] ) && 'product' === $_GET['post_type'] && 'yith-wcbep-bulk-product-editing' === $_GET['page'] ) {
				wp_safe_redirect( admin_url( 'admin.php?page=yith_wcbep_panel' ) );
				exit;
			}
		}

		/**
		 *
		 * Filter product ids to export.
		 *
		 * @param array $query_args The query args.
		 *
		 * @return array
		 */
		public function filter_product_ids_to_export( $query_args ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_REQUEST['form'] ) ) {
				$form_request = esc_url_raw( wp_unslash( $_REQUEST['form'] ) );
				$form_request = str_replace( array( 'http://', 'https://' ), '', $form_request );
				parse_str( $form_request, $form ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( ! empty( $form['yith-wcbep-selected-products'] ) ) {
					$ids                   = json_decode( $form['yith-wcbep-selected-products'] );
					$query_args['include'] = $ids;
				}
			}

			// phpcs:enable
			return $query_args;
		}

		/**
		 * Save default hidden cols in table
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function save_default_hidden_cols() {
			$response = array( 'success' => 'failure' );

			if ( isset( $_POST['security'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'yith-wcbep-save-hidden-columns' ) && ( current_user_can( 'manage_options' ) || 'yes' === get_option( 'yith-wcbep-hidden-columns-per-user', 'no' ) ) ) {
				$hidden_columns = isset( $_POST['hidden_cols'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['hidden_cols'] ) ) : array();
				yith_wcbep_set_hidden_columns( $hidden_columns );
				$response['success'] = 'success';
			}

			wp_send_json( $response );
			exit();
		}

		/**
		 * Save enabled columns in table
		 *
		 * @access public
		 * @since  1.1.8
		 */
		public function save_enabled_columns() {
			$response = array( 'success' => 'failure' );
			if ( isset( $_POST['security'], $_POST['enabled_field_ids'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'yith_wcbep_save_enabled_fields' ) ) {
				$enabled_columns       = is_array( $_POST['enabled_field_ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['enabled_field_ids'] ) ) : array();
				$old_enabled_columns   = YITH_WCBEP_List_Table::get_enabled_columns_option();
				$enabled_columns_count = count( $enabled_columns );
				if ( ( count( array_intersect( $old_enabled_columns, $enabled_columns ) ) === $enabled_columns_count && count( $old_enabled_columns ) === $enabled_columns_count ) || YITH_WCBEP_List_Table::update_enabled_columns( $enabled_columns ) ) {
					$response['success'] = 'success';
				}
			}

			wp_send_json( $response );
		}

		/**
		 * Return the modal args to use it in JS
		 *
		 * @return array
		 */
		private function get_enable_fields_modal(): array {
			$column_list = array_diff_key(
				YITH_WCBEP_List_Table::get_default_columns(),
				array_flip( yith_wcbep_get_always_visible_columns() )
			);

			$enabled_columns = YITH_WCBEP_List_Table::get_enabled_columns();
			$custom_fields   = YITH_WCBEP_Custom_Fields_Manager::get_custom_fields_option();
			$custom_fields   = array_combine( array_map( array( yith_wcbep_custom_fields_manager(), 'create_field_name' ), array_column( $custom_fields, 'id' ) ), $custom_fields );

			$ever_enabled = yith_wcbep_get_always_visible_columns();

			$column_list = array_diff_key( $column_list, array_flip( $ever_enabled ) );

			$taxonomies = YITH_WCBEP_Custom_Taxonomies_Manager::get_all_extra_taxonomies_options();

			$columns = array(
				'simple'       => array(),
				'custom_field' => array(),
				'taxonomy'     => array(),
			);

			yith_wcbep_sort_alphabetical_columns( $column_list );
			foreach ( $column_list as $key => $column ) {
				$columns[ $column['kind'] ][ $key ] = $column;
			}

			return array(
				'title'                      => esc_html__( 'Enable fields', 'yith-woocommerce-bulk-product-editing' ),
				'content'                    => yith_wcbep_get_view_html( 'modals/enable-fields/content.php', compact( 'columns', 'column_list', 'enabled_columns', 'custom_fields', 'taxonomies' ) ),
				'footer'                     => yith_wcbep_get_view_html( 'modals/enable-fields/footer.php' ),
				'closeWhenClickingOnOverlay' => true,
				'allowClosingWithEsc'        => false,
				'width'                      => 1100,
				'classes'                    => array(
					'wrap' => 'yith-wcbep-enable-fields-modal-wrapper',
				),
			);
		}

		/**
		 * Return the modal args to use it in JS
		 *
		 * @return array
		 */
		private function get_filter_products_modal(): array {

			return array(
				'title'                      => esc_html__( 'Filter products', 'yith-woocommerce-bulk-product-editing' ),
				'content'                    => yith_wcbep_get_view_html( 'modals/filter-products/content.php' ),
				'footer'                     => yith_wcbep_get_view_html( 'modals/filter-products/footer.php' ),
				'closeWhenClickingOnOverlay' => true,
				'allowClosingWithEsc'        => false,
				'width'                      => 800,
				'classes'                    => array(
					'wrap' => 'yith-wcbep-filter-products-modal-wrapper',
				),
			);
		}

		/**
		 * Return the modal args to use it in JS
		 *
		 * @return array
		 */
		private function get_table_views_modal(): array {
			$table_views = $this->table_views_instance->get_table_views();

			return array(
				'title'                      => esc_html__( 'Table views', 'yith-woocommerce-bulk-product-editing' ),
				'content'                    => yith_wcbep_get_view_html( 'modals/table-views/content.php', compact( 'table_views' ) ),
				'footer'                     => yith_wcbep_get_view_html( 'modals/table-views/footer.php' ),
				'closeWhenClickingOnOverlay' => true,
				'allowClosingWithEsc'        => false,
				'width'                      => 800,
				'classes'                    => array(
					'wrap' => 'yith-wcbep-table-views-modal-wrapper',
				),
			);
		}

		/**
		 * Get column list
		 *
		 * @return array
		 */
		private function get_column_list() {
			$columns = YITH_WCBEP_List_Table::get_default_columns();
			if ( isset( $columns['cb'] ) ) {
				unset( $columns['cb'] );
			}
			$columns          = array_combine( array_keys( $columns ), array_column( $columns, 'label' ) );
			$taxonomy_columns = YITH_WCBEP_Custom_Taxonomies_Manager::get_custom_taxonomies();
			$columns          = array_merge(
				$columns,
				array_combine( $taxonomy_columns, array_map( array( YITH_WCBEP_Custom_Taxonomies_Manager::get_instance(), 'get_taxonomy_name' ), $taxonomy_columns ) )
			);

			return $columns;
		}

		/**
		 * Return the Column Visibility modal args to use it in JS
		 *
		 * @return array
		 */
		private function get_columns_visibility_modal(): array {
			return array(
				'title'                      => esc_html__( 'Show/Hide columns', 'yith-woocommerce-bulk-product-editing' ),
				'content'                    => yith_wcbep_get_view_html( 'modals/columns-visibility/content.php' ),
				'footer'                     => yith_wcbep_get_view_html( 'modals/columns-visibility/footer.php' ),
				'closeWhenClickingOnOverlay' => true,
				'allowClosingWithEsc'        => true,
				'width'                      => 800,
			);
		}

		/**
		 * Return the Add Product modal args to use it in JS
		 *
		 * @return array
		 */
		private function get_add_product_modal() {
			return array(
				'title'                      => esc_html__( 'Add new product', 'yith-woocommerce-bulk-product-editing' ),
				'successMessage'             => yith_wcbep_get_view_html( 'modals/add-product/success-message.php' ),
				'content'                    => yith_wcbep_get_view_html( 'modals/add-product/content.php' ),
				'footer'                     => yith_wcbep_get_view_html( 'modals/add-product/footer.php' ),
				'closeWhenClickingOnOverlay' => true,
				'allowClosingWithEsc'        => false,
				'width'                      => 800,
			);
		}

		/**
		 * Return the Bulk Editing modal args to use it in JS
		 *
		 * @return array
		 */
		private function get_bulk_editing_modal() {
			/**
			 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_tabs
			 *
			 * Filters the Bulk Editing modal tabs.
			 *
			 * @param array $tabs The modal tabs.
			 *
			 * @return array
			 */
			$tabs = apply_filters(
				'yith_wcbep_bulk_editing_modal_tabs',
				array(
					'general'    => _x( 'General', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
					'images'     => _x( 'Images', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
					'taxonomies' => _x( 'Categories, tag & attributes', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
					'prices'     => _x( 'Prices', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
					'shipping'   => _x( 'Shipping', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
					'stock'      => _x( 'Stock', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
					'advanced'   => _x( 'Advanced', 'Bulk editing modal - tab name', 'yith-woocommerce-bulk-product-editing' ),
				)
			);

			return array(
				'title'                      => esc_html__( 'Bulk product editing', 'yith-woocommerce-bulk-product-editing' ),
				'content'                    => yith_wcbep_get_view_html( 'modals/bulk-editing/content.php', compact( 'tabs' ) ),
				'footer'                     => yith_wcbep_get_view_html( 'modals/bulk-editing/footer.php' ),
				'closeWhenClickingOnOverlay' => true,
				'allowClosingWithEsc'        => false,
				'width'                      => 1100,
			);
		}

		/**
		 * Enqueue Scripts
		 */
		public function admin_enqueue_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_register_script( 'yith-wcbep-utils-js', YITH_WCBEP_ASSETS_URL . 'js/utils' . $suffix . '.js', array( 'jquery' ), YITH_WCBEP_VERSION, true );
			wp_register_style( 'yith-wcbep-admin-styles', YITH_WCBEP_ASSETS_URL . 'css/admin.css', array( 'yith-plugin-fw-fields', 'wp-color-picker' ), YITH_WCBEP_VERSION );

			wp_register_script( 'yith_wcbep_main_tab_js', YITH_WCBEP_ASSETS_URL . 'js/main_tab' . $suffix . '.js', array( 'jquery', 'jquery-tiptip', 'jquery-ui-resizable', 'jquery-blockui', 'jquery-ui-draggable', 'wp-util', 'wp-hooks', 'yith-wcbep-utils-js' ), YITH_WCBEP_VERSION, true );

			wp_register_script( 'yith_wcbep_filter_products', YITH_WCBEP_ASSETS_URL . 'js/filter-products' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'wp-util', 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );
			wp_register_script( 'yith_wcbep_add_product', YITH_WCBEP_ASSETS_URL . 'js/add-product' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'wp-util', 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );
			wp_register_script( 'yith_wcbep_table_views', YITH_WCBEP_ASSETS_URL . 'js/table-views' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );
			wp_register_script( 'yith_wcbep_enable_fields', YITH_WCBEP_ASSETS_URL . 'js/enable-fields' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );
			wp_register_script( 'yith_wcbep_columns_visibility', YITH_WCBEP_ASSETS_URL . 'js/columns-visibility' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );

			if ( $this->is_panel( 'bulk-edit' ) ) {
				wp_enqueue_editor();
				wp_enqueue_style( 'yith-wcbep-utils-js' );
				wp_enqueue_style( 'yith-wcbep-admin-styles' );
				wp_enqueue_script( 'yith_wcbep_main_tab_js' );

				wp_enqueue_script( 'yith_wcbep_filter_products' );
				wp_enqueue_script( 'yith_wcbep_table_views' );
				wp_enqueue_script( 'yith_wcbep_enable_fields' );
				wp_enqueue_script( 'yith_wcbep_columns_visibility' );
				wp_enqueue_script( 'yith_wcbep_add_product' );
				wp_enqueue_script( 'wc-product-export' );

				$css           = '';
				$columns_width = $this->get_columns_width();
				foreach ( $this->get_column_list() as $column_id => $width ) {
					if ( 'cb' !== $column_id ) {
						$width = $columns_width[ $column_id ] ?? 150;

						$css .= "
							table.yith_wcbep_products th#{$column_id}{
								min-width: var(--yith-wcbep-column-{$column_id}-width, {$width}px);
								width: var(--yith-wcbep-column-{$column_id}-width, {$width}px);
							}
						";
					}
				}

				wp_add_inline_style( 'yith-wcbep-admin-styles', $css );

				$data_to_localize = array(
					array(
						'handle'      => 'yith_wcbep_main_tab_js',
						'object_name' => 'yithWcbep',
						'data'        => array(
							'customFilters'            => yith_wcbep_table_views()->get_custom_filters(),
							/**
							 * APPLY_FILTERS: yith_wcbep_products_to_save_per_request
							 *
							 * Filters the quantity of products to save per AJAX request.
							 *
							 * @param int $products_per_request The quantity of product per request.
							 *
							 * @return int
							 */
							'productsToSavePerRequest' => absint( apply_filters( 'yith_wcbep_products_to_save_per_request', 10 ) ),
							'useRegularExpressions'    => get_option( 'yith-wcbep-use-regex-on-search', 'no' ),
							'roundPrices'              => get_option( 'yith-wcbep-round-prices', 'no' ),
							'wcDecimals'               => wc_get_price_decimals(),
							'wcDecimalSeparator'       => wc_get_price_decimal_separator(),
							'wcThousandSeparator'      => wc_get_price_decimal_separator(),
							'enabledColumns'           => YITH_WCBEP_List_Table::get_enabled_columns(),
							'alwaysVisibleColumns'     => yith_wcbep_get_always_visible_columns(),
							'hiddenColumns'            => yith_wcbep_get_hidden_columns(),
							'columnList'               => $this->get_column_list(),
							'customFields'             => YITH_WCBEP_Custom_Fields_Manager::get_custom_fields_option(),
							'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
							'i18n'                     => array(
								'downloadableFiles' => array(
									// translators: %s is the number of files.
									'singular' => _n( '%s file', '%s files', 1, 'yith-woocommerce-bulk-product-editing' ),
									// translators: %s is the number of files.
									'plural'   => _n( '%s file', '%s files', 2, 'yith-woocommerce-bulk-product-editing' ),
								),
								'stock-status'      => wc_get_product_stock_status_options(),
								'tax-status'        => yith_wcbep_get_wc_tax_status_options(),
								'tax-class'         => yith_wcbep_get_wc_tax_class_options(),
								'shipping-class'    => yith_wcbep_get_wc_shipping_class_options(),
								'status'            => yith_wcbep_get_product_statuses(),
								'visibility'        => wc_get_product_visibility_options(),
								'product-type'      => wc_get_product_types(),
								'allow-backorders'  => wc_get_product_backorder_options(),
								'customFilters'     => __( 'Custom filters', 'yith-woocommerce-bulk-product-editing' ),
							),
							'security'                 => array(
								'addCustomFieldColumn'    => wp_create_nonce( 'yith_wcbep_add_custom_field_column' ),
								'addTaxonomyColumn'       => wp_create_nonce( 'yith_wcbep_add_taxonomy_column' ),
								'updateCustomFieldColumn' => wp_create_nonce( 'yith_wcbep_update_custom_field_column' ),
								'deleteCustomFieldColumn' => wp_create_nonce( 'yith_wcbep_delete_custom_field_column' ),
								'saveEnabledFields'       => wp_create_nonce( 'yith_wcbep_save_enabled_fields' ),
								'saveColumnsVisibility'   => wp_create_nonce( 'yith-wcbep-save-hidden-columns' ),
								'tableViewAction'         => wp_create_nonce( 'yith_wcbep_table_view_action' ),
								'loadProductsTable'       => wp_create_nonce( 'yith_wcbep_load_products_table' ),
								'createProduct'           => wp_create_nonce( 'yith_wcbep_create_product' ),
								'bulkActions'             => wp_create_nonce( 'yith_wcbep_bulk_actions' ),
								'saveProductsChanges'     => wp_create_nonce( 'yith_wcbep_save_products_changes' ),
								'updateColumnsWidth'      => wp_create_nonce( 'yith_wcbep_update_columns_width' ),

								'updateTaxonomyFieldColumn' => wp_create_nonce( 'yith_wcbep_update_taxonomy_field_column' ),
								'deleteTaxonomyFieldColumn' => wp_create_nonce( 'yith_wcbep_delete_taxonomy_field_column' ),
							),
							'actions'                  => array(
								'addCustomFieldColumn'    => 'yith_wcbep_add_custom_field_column',
								'addTaxonomyColumn'       => 'yith_wcbep_add_taxonomy_column',
								'updateCustomFieldColumn' => 'yith_wcbep_update_custom_field_column',
								'deleteCustomFieldColumn' => 'yith_wcbep_delete_custom_field_column',
								'saveEnabledFields'       => 'yith_wcbep_save_enabled_fields',
								'saveColumnsVisibility'   => 'yith_wcbep_save_columns_visibility',
								'tableViewAction'         => 'yith_wcbep_table_view_action',
								'loadProductsTable'       => 'yith_wcbep_load_products_table',
								'createProduct'           => 'yith_wcbep_create_product',
								'bulkActions'             => 'yith_wcbep_bulk_actions',
								'saveProductsChanges'     => 'yith_wcbep_save_products_changes',
								'updateColumnsWidth'      => 'yith_wcbep_update_columns_width',

								'updateTaxonomyFieldColumn' => 'yith_wcbep_update_taxonomy_field_column',
								'deleteTaxonomyFieldColumn' => 'yith_wcbep_delete_taxonomy_field_column',
							),
							'modals'                   => array(
								'enableFields'           => $this->get_enable_fields_modal(),
								'filterProducts'         => $this->get_filter_products_modal(),
								'columnsVisibility'      => $this->get_columns_visibility_modal(),
								'tableViews'             => $this->get_table_views_modal(),
								'addProduct'             => $this->get_add_product_modal(),
								'bulkEditing'            => $this->get_bulk_editing_modal(),
								'confirmProductDeletion' => array(
									'title'   => __( 'Confirm delete', 'yith-woocommerce-bulk-product-editing' ),
									'message' => __( 'Are you sure you want to delete the selected products?<br><br>This action cannot be undone and you will not be able to recover this data.', 'yith-woocommerce-bulk-product-editing' ),
								),
							),
						),
					),
				);

				foreach ( $data_to_localize as $data ) {
					if ( isset( $data['handle'], $data['object_name'], $data['data'] ) ) {
						wp_localize_script( $data['handle'], $data['object_name'], $data['data'] );
					}
				}
			}
		}

		/**
		 * Check if is a panel page
		 *
		 * @param false|string $tab The panel tab.
		 *
		 * @return bool
		 */
		public function is_panel( $tab = false ) {
			$screen   = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
			$is_panel = $screen && strpos( $screen->id, '_page_yith_wcbep_panel' ) !== false;
			if ( $is_panel && false !== $tab ) {
				$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$admin_tabs  = array_keys( $this->get_admin_tabs() );
				$is_panel    = $current_tab === $tab || ( false === $current_tab && current( $admin_tabs ) === $tab ) || ( '' === $tab && ( current( $admin_tabs ) === $current_tab || ! in_array( $current_tab, $admin_tabs, true ) ) );
			}

			return $is_panel;
		}

		/**
		 * Returns url to panel page
		 *
		 * @param string $tab    Tab of the panel to link.
		 * @param array  $params Params to add to url.
		 *
		 * @return string Formatted panel url.
		 */
		public function get_panel_url( $tab = '', $params = array() ) {
			$url = add_query_arg( 'page', $this->panel_page, admin_url( 'admin.php' ) );

			if ( ! empty( $tab ) ) {
				$url = add_query_arg( 'tab', $tab, $url );
			}

			if ( ! empty( $params ) ) {
				$url = add_query_arg( $params, $url );
			}

			return $url;
		}

		/*
		|--------------------------------------------------------------------------
		| Deprecated functions
		|--------------------------------------------------------------------------
		*/

		/**
		 * Bulk Edit Products [AJAX]
		 *
		 * @access     public
		 * @since      1.0.0
		 * @depreacted since 2.0 | Watch YITH_WCBEP_Admin::ajax_save_products_changes instead
		 */
		public function bulk_edit_products() {
			wc_deprecated_function( 'YITH_WCBEP_Admin::bulk_edit_products', '2.0.0' );
		}

		/**
		 * Delete products [AJAX]
		 *
		 * @access     public
		 * @since      1.0.0
		 *
		 * @depreacted since 2.0
		 */
		public function delete_products() {
			wc_deprecated_function( 'YITH_WCBEP_Admin::delete_products', '2.0.0' );
		}

		/**
		 * Render Enabled Columns tab.
		 *
		 * @depecated since 2.0 | Enabled Columns tab has been removed
		 */
		public function render_enabled_columns_tab() {
			wc_deprecated_function( 'YITH_WCBEP_Admin::render_enabled_columns_tab', '2.0.0' );

		}
	}
}

if ( ! function_exists( 'yith_wcbep_admin' ) ) {
	/**
	 * Unique access to instance of YITH_WCBEP_Admin class
	 *
	 * @return YITH_WCBEP_Admin
	 * @since 1.0.0
	 */
	function yith_wcbep_admin() {
		return YITH_WCBEP_Admin::get_instance();
	}
}
