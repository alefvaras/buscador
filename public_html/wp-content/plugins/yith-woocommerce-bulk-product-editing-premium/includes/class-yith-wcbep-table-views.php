<?php
/**
 * Table views manager class
 *
 * @package YITH\BulkProductEditing
 * @author  YITH <plugins@yithemes.com>
 */

if ( ! class_exists( 'YITH_WCBEP_Table_Views' ) ) {
	/**
	 * Table views class
	 */
	class YITH_WCBEP_Table_Views {
		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Table_Views
		 */
		protected static $instance;

		/**
		 * Table views option name
		 *
		 * @var string
		 */
		private $table_views_option = 'yith_wcbep_table_views';

		/**
		 * Custom filters option name
		 *
		 * @var string
		 */
		private $custom_filters_option = 'yith_wcbep_custom_filters';

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Table_Views
		 * @since 1.0.0
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * YITH_WCBEP_Table_Views constructor
		 */
		public function __construct() {
			add_action( 'wp_ajax_yith_wcbep_table_view_action', array( $this, 'handle_ajax_view_action' ) );
		}

		/**
		 * Handle AJAX table view action
		 *
		 * @return void
		 */
		public function handle_ajax_view_action() {
			$response = array(
				'success' => 'failure',
			);
			if ( isset( $_REQUEST['security'], $_REQUEST['view_action'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['security'] ) ), 'yith_wcbep_table_view_action' ) ) {
				$action = sanitize_text_field( wp_unslash( $_REQUEST['view_action'] ) );
				switch ( $action ) {
					case 'create':
						if ( isset( $_REQUEST['view_options']['conditions'], $_REQUEST['view_options']['name'] ) ) {
							$view_name       = sanitize_text_field( wp_unslash( $_REQUEST['view_options']['name'] ) );
							$view_conditions = wp_unslash( $_REQUEST['view_options']['conditions'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							$table_view      = $this->create_table_view( $view_name, $view_conditions );
							if ( $table_view ) {
								$response['success'] = 'success';
								$response['view']    = array(
									'viewKey'         => $table_view['key'],
									'viewName'        => $table_view['name'],
									'viewOptionsJSON' => wp_json_encode( $table_view ),
								);
							}
						}
						break;
					case 'clone':
						if ( isset( $_REQUEST['view_key'] ) ) {
							$view_key      = sanitize_text_field( wp_unslash( $_REQUEST['view_key'] ) );
							$table_views   = $this->get_table_views_option();
							$view_to_clone = $table_views[ $view_key ] ?? false;
							if ( $view_to_clone ) {
								foreach ($view_to_clone['conditions'] as &$condition) {
									if('attribute' === $condition['type']){
										foreach ( $condition['options'] as &$attribute_condition ) {
											if (!empty($attribute_condition['terms'])) {
												$attribute_condition[ 'terms' ] = array_keys( $attribute_condition[ 'terms' ] );
											}
										}
									}
								}
								$table_view = $this->create_table_view( $view_to_clone['name'], $view_to_clone['conditions'], false );

								$view_index  = array_search( $view_key, array_keys( $table_views ), true );
								$view        = array(
									$table_view['key'] => array(
										'name'       => $table_view['name'],
										'conditions' => $table_view['conditions'],
									),
								);
								$table_views = array_merge( array_slice( $table_views, 0, $view_index + 1 ), $view, array_slice( $table_views, $view_index ) );
								$this->update_table_views( $table_views );
								if ( $table_view ) {
									$response['success'] = 'success';
									$response['view']    = array(
										'viewKey'         => $table_view['key'],
										'viewName'        => $table_view['name'],
										'viewOptionsJSON' => wp_json_encode( $table_view ),
									);
								}
							}
						}
						break;
					case 'delete':
						if ( isset( $_REQUEST['view_key'] ) ) {
							$view_key = sanitize_text_field( wp_unslash( $_REQUEST['view_key'] ) );
							if ( $this->delete_table_view( $view_key ) ) {
								$response['success'] = 'success';
							}
						}
						break;
					case 'update':
						if ( isset( $_REQUEST['view_key'], $_REQUEST['view_properties'] ) ) {
							$result = $this->update_table_view( sanitize_text_field( wp_unslash( $_REQUEST['view_key'] ) ), wp_unslash( $_REQUEST['view_properties'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
							if ( $result ) {
								$response['success']        = 'success';
								$response['viewProperties'] = $result;
							}
						}
						break;
				}
			}
			wp_send_json( $response );
			exit();
		}

		/**
		 * Create table view
		 *
		 * @param string $view_name       The view name.
		 * @param array  $view_conditions The view conditions.
		 * @param bool   $update_option   The view conditions.
		 *
		 * @return array
		 */
		public function create_table_view( $view_name, $view_conditions, $update_option = true ) {
			$table_views           = $this->get_table_views_option();
			$index                 = 'table-view-' . time();
			$table_view            = false;
			$view_name             = $this->get_unique_view_name( $view_name );
			$table_views[ $index ] = array(
				'name'       => $view_name,
				'conditions' => $this->build_table_view_condition( $view_conditions ),
			);
			if ( ! $update_option || $this->update_table_views( $table_views ) ) {
				$table_view = array(
					'key'        => $index,
					'name'       => $view_name,
					'conditions' => $table_views[ $index ]['conditions'],
				);
			}

			return $table_view;
		}

		/**
		 * Update table view
		 *
		 * @param string $view_key        The view key.
		 * @param array  $view_properties The view properties.
		 *
		 * @return array|false
		 */
		public function update_table_view( $view_key, $view_properties ) {
			$table_views = $this->get_table_views_option();
			if ( array_key_exists( $view_key, $table_views ) ) {

				if ( ! empty( $view_properties['conditions'] ) ) {
					$view_properties['conditions'] = $this->build_table_view_condition( $view_properties['conditions'] );
				}

				$view_properties['name']  = $this->get_unique_view_name( $view_properties['name'], $view_key );
				$table_views[ $view_key ] = $view_properties;
				if ( ! $this->update_table_views( $table_views ) ) {
					return false;
				}
			}

			return $view_properties;
		}

		/**
		 * Delete table view
		 *
		 * @param string $view_key The table view key.
		 *
		 * @return bool
		 */
		public function delete_table_view( $view_key ) {
			$deleted     = false;
			$table_views = $this->get_table_views_option();
			if ( array_key_exists( $view_key, $table_views ) ) {
				unset( $table_views[ $view_key ] );
				$deleted = $this->update_table_views( $table_views );
			}

			return $deleted;
		}

		/**
		 * Update table views
		 *
		 * @param array $table_views Table views.
		 *
		 * @return bool
		 */
		public function update_table_views( $table_views ): bool {
			$updated = $table_views === $this->get_table_views_option();
			if ( ! $updated && is_array( $table_views ) ) {
				$updated = update_option( $this->table_views_option, $table_views );
			}

			return $updated;
		}

		/**
		 * Get table views
		 *
		 * @return array[]
		 */
		public function get_table_views() {
			$defaults = array(
				'table-view-all' => array( 'name' => __( 'All products', 'yith-woocommerce-bulk-product-editing' ) ),
			);

			return array_merge( $defaults, $this->get_table_views_option() );
		}

		/**
		 * Get table view conditions
		 *
		 * @param string $table_view_key The table view key.
		 *
		 * @return array
		 */
		public function get_table_view_conditions( string $table_view_key ): array {
			$table_view = $this->get_table_view( $table_view_key );

			return $table_view['conditions'] ?? array();
		}

		/**
		 * Get unique name for the table view
		 *
		 * @param string       $name The view name.
		 * @param string|false $key  The view key.
		 *
		 * @return string
		 */
		private function get_unique_view_name( $name, $key = false ) {
			$table_views = $this->get_table_views_option();
			if ( $key && array_key_exists( $key, $table_views ) ) {
				unset( $table_views[ $key ] );
			}

			$views_names = array_column( $table_views, 'name' );
			$suffix      = 0;
			$name        = trim( $name );
			$unique_name = $name;
			while ( in_array( $unique_name, $views_names, true ) ) {
				$suffix++;
				// translators: %1$s is the Cloned View Name; %2$s is the number of the copy.
				$unique_name = sprintf( _x( '%1$s - Copy %2$s', 'Name of a cloned table view', 'yith-woocommerce-bulk-product-editing' ), $name, $suffix );
			}

			return $unique_name;
		}

		/**
		 * Build table view condition
		 *
		 * @param array $conditions The view conditions.
		 *
		 * @return array
		 */
		public function build_table_view_condition( $conditions ) {
			foreach ( $conditions as &$condition ) {
				$condition_type = strpos( $condition['type'] ?? '', 'yith_wcbep_tf_' ) === 0 ? 'custom-taxonomy' : ( $condition['type'] ?? '' );
				switch ( $condition_type ) {
					case 'attribute':
						foreach ( $condition['options'] as &$attribute_condition ) {
							if ( in_array( $attribute_condition['condition'], array( 'has', 'has-not' ), true ) && isset( $attribute_condition['terms'] ) ) {
								$args                         = array(
									'taxonomy'   => $attribute_condition['taxonomy'],
									'hide_empty' => false,
								);
								$terms                        = get_terms( $args );
								$terms                        = array_combine( array_column( $terms, 'term_id' ), array_column( $terms, 'name' ) );
								$attribute_condition['terms'] = array_intersect_key( $terms, array_flip( $attribute_condition['terms'] ) );
							}
						}
						break;
					case 'custom-taxonomy':
					case 'tag':
					case 'category':
					case 'shipping-class':
						$props_to_taxonomies = array(
							'tag'            => 'product_tag',
							'category'       => 'product_cat',
							'shipping-class' => 'product_shipping_class',
						);
						$taxonomy            = 'custom-taxonomy' === $condition_type ? substr( $condition['type'], 14 ) : $props_to_taxonomies[ $condition['type'] ] ?? '';
						if ( $taxonomy && isset( $condition['options']['taxonomies'] ) ) {
							$condition_taxonomies = array();
							foreach ( $condition['options']['taxonomies'] as $taxonomy_id ) {
								$condition_taxonomies[ $taxonomy_id ] = get_term_by( 'id', $taxonomy_id, $taxonomy, ARRAY_A )['name'] ?? '';
							}
							$condition['options']['taxonomies'] = $condition_taxonomies;
						}
						break;
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcbep_table_view_build_condition
			 *
			 * Build table view conditions, used for taxonomies and attributes properties.
			 *
			 * @param array $conditions The table view conditions.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcbep_table_view_build_condition', $conditions );
		}

		/**
		 * Get table view query args
		 *
		 * @param string|array $table_view The table view ID or the view conditions.
		 *
		 * @return array
		 */
		public function get_table_view_query_args( $table_view ): array {
			$view_conditions = is_array( $table_view ) ? $table_view : $this->get_table_view_conditions( $table_view );

			$args = array(
				'post_type'           => 'product',
				'post_status'         => 'any',
				'ignore_sticky_posts' => true,
				'tax_query'           => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					'relation' => 'AND',
				),
				'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'AND',
				),
			);

			$props_to_meta = array(
				'stock-status'   => '_stock_status',
				'backorder'      => '_backorders',
				'regular-price'  => '_regular_price',
				'sale-price'     => '_sale_price',
				'on-sale'        => '_sale_price',
				'stock-quantity' => '_stock',
				'weight'         => '_weight',
				'length'         => '_length',
				'width'          => '_width',
				'height'         => '_height',
				'sku'            => '_sku',
				'virtual'        => '_virtual',
				'downloadable'   => '_downloadable',
				'image'          => '_thumbnail_id',
				'tax-class'      => '_tax_class',
				'tax-status'     => '_tax_status',
			);

			$props_to_tax = array(
				'product-type'       => 'product_type',
				'category'           => 'product_cat',
				'tag'                => 'product_tag',
				'product-visibility' => 'product_visibility',
				'featured'           => 'product_visibility',
				'shipping-class'     => 'product_shipping_class',
			);

			foreach ( $view_conditions as $condition ) {
				$options           = $condition['options'] ?? array();
				$condition_type    = $condition['type'] ?? '';
				$condition_options = $condition['options'] ?? array();
				$custom_key        = false;
				if ( strpos( $condition_type, 'yith_wcbep_cf_' ) === 0 ) {
					$custom_key     = substr( $condition_type, 14 );
					$condition_type = yith_wcbep_custom_fields_manager()->get_column_type( $condition_type, $condition_type );
				} elseif ( strpos( $condition_type, 'yith_wcbep_tf_' ) === 0 ) {
					$custom_key     = substr( $condition_type, 14 );
					$condition_type = 'taxonomy';
				}

				switch ( $condition_type ) {
					case 'category':
					case 'tag':
					case 'taxonomy':
					case 'shipping-class':
						$tax_query = array(
							'taxonomy' => ! ! $custom_key ? $custom_key : $props_to_tax[ $condition_type ],
						);
						if ( ! empty( $condition_options['taxonomies'] ) ) {
							$tax_query['terms']    = array_keys( $condition_options['taxonomies'] ?? array() );
							$tax_query['fields']   = 'term_id';
							$tax_query['operator'] = 'is' === ( $condition_options['condition'] ?? 'is' ) ? 'IN' : 'NOT IN';
						} else {
							$tax_query['operator'] = 'is' === ( $condition_options['condition'] ?? 'is' ) ? 'NOT EXISTS' : 'EXISTS';
						}
						$args['tax_query'][] = $tax_query;
						break;
					case 'attribute':
						foreach ( $condition_options as $attribute ) {
							if ( in_array( $attribute['condition'], array( 'has', 'has-not' ), true ) ) {
								$operator = 'has-not' === $attribute['condition'] ? 'NOT IN' : 'IN';
								if ( ! array_keys( $attribute['terms'] ?? array() ) ) {
									$operator = 'has-not' === $attribute['condition'] ? 'EXISTS' : 'NOT EXISTS';
								}

								if ( $this->is_for_variations( $table_view ) ) {
									$value = array();
									foreach ( $attribute['terms'] as $term ) {
										$attr = get_term_by( 'name', $term, $attribute['taxonomy'], ARRAY_A );
										if ( $attr ) {
											$value[] = $attr['slug'];
										}
									}
									$args['meta_query'][] = array(
										'key'     => 'attribute_' . $attribute['taxonomy'],
										'value'   => $value,
										'compare' => $operator,
									);
								} else {
									$args['tax_query'][] = array(
										'taxonomy' => $attribute['taxonomy'],
										'fields'   => 'term_id',
										'terms'    => array_keys( $attribute['terms'] ?? array() ),
										'operator' => $operator,
									);
								}
							}
						}
						break;
					case 'product-type':
						$product_type = $options['value'] ?? '';
						$tax_query    = false;
						if ( 'variation' !== $product_type ) {
							if ( 'any' !== $product_type ) {
								$tax_query = array(
									'taxonomy' => $props_to_tax[ $condition_type ],
									'field'    => 'slug',
									'terms'    => array( $product_type ),
									'operator' => 'IN',
								);
							} else {
								$tax_query = array(
									'taxonomy' => $props_to_tax[ $condition_type ],
									'field'    => 'slug',
									'operator' => 'EXISTS',
								);
							}
						} else {
							$args['post_type'] = 'product_variation';
						}

						if ( $tax_query ) {
							$args['tax_query'][] = $tax_query;
						}
						break;
					case 'backorder':
					case 'stock-status':
					case 'tax-status':
					case 'tax-class':
						if ( array_key_exists( $condition_type, $props_to_meta ) ) {
							$args['meta_query'][] = array(
								'key'   => $props_to_meta[ $condition_type ],
								'value' => $condition_options['value'] ?? 'no',
							);
						}

						break;
					case 'status':
						$args['post_status'] = sanitize_text_field( wp_unslash( $options['value'] ) );
						break;
					case 'featured':
						$args['tax_query'][] = array(
							'taxonomy' => $props_to_tax[ $condition_type ],
							'field'    => 'slug',
							'terms'    => 'featured',
							'operator' => 'featured' === ( $condition_options['value'] ) ? 'IN' : 'NOT IN',
						);
						break;
					case 'product-visibility':
						$default_args = array(
							'taxonomy' => $props_to_tax[ $condition_type ],
							'field'    => 'slug',
							'operator' => 'IN',
						);

						$query_args = array(
							'catalog' => array(
								array_merge( $default_args, array( 'terms' => 'exclude-from-search' ) ),
								array_merge(
									$default_args,
									array(
										'terms'    => 'exclude-from-catalog',
										'operator' => 'NOT IN',
									)
								),
							),
							'search'  => array(
								array_merge( $default_args, array( 'terms' => 'exclude-from-catalog' ) ),
								array_merge(
									$default_args,
									array(
										'terms'    => 'exclude-from-search',
										'operator' => 'NOT IN',
									)
								),
							),
							'hidden'  => array(
								array_merge( $default_args, array( 'terms' => 'exclude-from-catalog' ) ),
								array_merge( $default_args, array( 'terms' => 'exclude-from-search' ) ),
							),
							'visible' => array(
								'compare' => 'AND',
								array_merge(
									$default_args,
									array(
										'terms'    => 'exclude-from-catalog',
										'operator' => 'NOT IN',
									)
								),
								array_merge(
									$default_args,
									array(
										'terms'    => 'exclude-from-search',
										'operator' => 'NOT IN',
									)
								),
							),
						);

						if ( array_key_exists( $condition_options['value'], $query_args ) ) {
							$args['tax_query'][] = array_merge( array( 'relation' => 'AND' ), $query_args[ $condition_options['value'] ] );
						}
						break;
					case 'price':
					case 'regular-price':
					case 'sale-price':
						$type = 'DECIMAL(10,' . wc_get_price_decimals() . ')';
					// Add decimal checks to the query.
					case 'stock-quantity':
					case 'weight':
					case 'length':
					case 'width':
					case 'height':
						$symbols = array(
							'greater'    => '>',
							'less'       => '<',
							'equal'      => '=',
							'greater-eq' => '>=',
							'less-eq'    => '<=',
						);

						if ( array_key_exists( $condition_type, $props_to_meta ) || $custom_key ) {
							$args['meta_query'][] = array(
								'key'     => ! ! $custom_key ? $custom_key : $props_to_meta[ $condition_type ],
								'value'   => $condition_options['value'] ?? 0,
								'compare' => $symbols[ $condition_options['operator'] ] ?? '=',
								'type'    => isset( $type ) ? $type : 'numeric',
							);
						}
						break;
					case 'sku':
					case 'text':
						$query_args = array(
							'key'     => ! ! $custom_key ? $custom_key : $props_to_meta[ $condition_type ],
							'value'   => $condition_options['value'],
							'compare' => 'REGEXP',
						);
						switch ( $condition_options['compare'] ) {
							case 'not-contain':
								$query_args['compare'] = 'NOT REGEXP';
								break;
							case 'end':
								$query_args['value'] = $query_args['value'] . '$';
								break;
							case 'start':
								$query_args['value'] = '^' . $query_args['value'];
								break;
						}
						$args['meta_query'][] = $query_args;
						break;
					case 'virtual':
					case 'downloadable':
						$args['meta_query'][] = array(
							'key'   => $props_to_meta[ $condition_type ],
							'value' => wc_bool_to_string( $condition_type === $condition_options['value'] ),
						);
						break;
					case 'on-sale':
						$has_condition = 'on-sale' === $condition_options['value'];
					// Adding condition if it's on-sale.
					case 'image':
						$has_condition = $has_condition ?? ( 'with-image' === $condition_options['value'] );
						$query         = array(
							'key'   => $props_to_meta[ $condition_type ],
							'value' => '',
						);
						if ( $has_condition ) {
							$query['compare'] = '!=';
						} else {
							$query = array(
								'relation' => 'OR',
								$query,
								array(
									'key'     => $props_to_meta[ $condition_type ],
									'compare' => 'NOT EXISTS',
								),
							);
						}
						$args['meta_query'][] = $query;
						break;
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcbep_table_view_get_query_args
			 *
			 * Filters table view query args based on the view conditions.
			 *
			 * @param array        $args       The query args.
			 * @param string|array $table_view The table view ID or directly the conditions.
			 *
			 * @return array
			 */
			return apply_filters( 'yith_wcbep_table_view_get_query_args', $args, $table_view );
		}

		/**
		 * Convert custom filters in table view conditions
		 *
		 * @param array $filters The custom filters.
		 *
		 * @return array
		 */
		public function custom_filters_to_view_conditions( $filters ) {
			$conditions = array();

			foreach ( $filters as $property => $filter ) {
				$options = false;
				switch ( $property ) {
					case 'category':
					case 'shipping-class':
					case 'tag':
						$options = $filter;
						if ( ! empty( $filter['taxonomies'] ) ) {
							$options['taxonomies'] = array_keys( $filter['taxonomies'] );
						}
						break;
					case 'product-type':
						if ( 'any' === $filter['value'] && 'no' === $filter['include_variations'] ) {
							$filter = false;
						}
					case 'title':
					case 'desc':
					case 'shortdesc':
					case 'sku':
					case 'stock-status':
					case 'status':
					case 'product-visibility':
					case 'backorder':
						$options = $filter;
						break;
					case 'height':
					case 'weight':
					case 'length':
					case 'width':
					case 'regular-price':
					case 'sale-price':
					case 'stock-quantity':
						$options = array(
							'operator' => $filter['compare'],
							'value'    => $filter['value'],
						);
						break;
				}

				if ( $options ) {
					$conditions[] = array(
						'type'    => $property,
						'options' => $options,
					);
				}
			}

			return $conditions;
		}

		/**
		 * Checks if the variations are included in the table views
		 *
		 * @param string $table_view The table view ID.
		 *
		 * @return bool
		 */
		public function has_variations_included( string $table_view ): bool {
			$view  = $this->get_table_view_conditions( $table_view );
			$index = array_search( 'product-type', array_column( $view, 'type' ), true );

			return false !== $index && isset( $view[ $index ]['options']['value'] ) && in_array( $view[ $index ]['options']['value'], array( 'any', 'variable' ), true ) && isset( $view[ $index ]['options']['include_variations'] ) && 'yes' === $view[ $index ]['options']['include_variations'];
		}

		/**
		 * Checks if the view shows just variations
		 *
		 * @param string $table_view The table view ID.
		 *
		 * @return bool
		 */
		public function is_for_variations( string $table_view ): bool {
			$view  = $this->get_table_view_conditions( $table_view );
			$index = array_search( 'product-type', array_column( $view, 'type' ), true );

			return false !== $index && isset( $view[ $index ]['options']['value'] ) && 'variation' === $view[ $index ]['options']['value'];
		}

		/**
		 * Get table views
		 *
		 * @return array
		 */
		public function get_table_views_list(): array {
			static $views = null;
			if ( is_null( $views ) ) {
				$views = $this->get_table_views();
				$views = array_combine( array_keys( $views ), array_column( $views, 'name' ) );
			}

			return $views;
		}

		/**
		 * Get selected table view option key.
		 *
		 * @return string
		 */
		private function get_table_view_option_key(): string {
			$key      = 'yith_wcbep_selected_table_view';
			$per_user = 'yes' === get_option( 'yith-wcbep-hidden-columns-per-user', 'no' );
			$user_id  = function_exists( 'get_current_user_id' ) ? get_current_user_id() : false;

			if ( $per_user && $user_id ) {
				$key = $key . '-' . $user_id;
			}

			/**
			 * APPLY_FILTERS: yith_wcbep_get_selected_table_view_option_key
			 *
			 * Filters the option key that stores the selected view.
			 *
			 * @param string $option_key The option key.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcbep_get_selected_table_view_option_key', $key, $per_user );
		}

		/**
		 * Get selected view key
		 *
		 * @return string
		 */
		public function get_selected_view_key(): string {
			$view = get_option( $this->get_table_view_option_key(), '' );

			/**
			 * APPLY_FILTERS: yith_wcbep_table_views_get_selected_view
			 *
			 * Filters the selected view ID.
			 *
			 * @param string $view The selected view key.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcbep_table_views_get_selected_view', $view );
		}

		/**
		 * Update selected view option
		 *
		 * @param string $view The view key.
		 *
		 * @return bool
		 */
		public function update_selected_view_option( string $view ): bool {
			return update_option( $this->get_table_view_option_key(), $view );
		}

		/**
		 * Update custom filters option
		 *
		 * @param array $filters The custom filters.
		 *
		 * @return bool
		 */
		public function update_custom_filters( array $filters ): bool {
			return update_option( $this->custom_filters_option, $filters );
		}

		/**
		 * Get table views option
		 *
		 * @return array[]
		 */
		public function get_table_views_option(): array {
			$table_views = get_option( $this->table_views_option, array() );

			return is_array( $table_views ) ? $table_views : array();
		}

		/**
		 * Get the table view
		 *
		 * @param string $view_key The view key.
		 *
		 * @return array|false
		 */
		public function get_table_view( string $view_key = '' ) {
			$view_key = $view_key ? $view_key : $this->get_selected_view_key();
			$view     = false;
			if ( 'custom-filters' === $view_key ) {
				$view = array(
					'name'       => __( 'Custom filters', 'yith-woocommerce-bulk-product-editing' ),
					'conditions' => $this->get_custom_filters_conditions(),
				);
			} else {
				$views = $this->get_table_views();
				$view  = array_key_exists( $view_key, $views ) ? $views[ $view_key ] : false;
			}

			return $view;
		}

		/**
		 * Get custom filters
		 *
		 * @return array|false
		 */
		public function get_custom_filters() {
			return isset( $_REQUEST['ywcbep_args']['filters'] ) ? wp_unslash( $_REQUEST['ywcbep_args']['filters'] ) : get_option( $this->custom_filters_option, false ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		}

		/**
		 * Get custom filters
		 *
		 * @return array
		 */
		public function get_custom_filters_conditions() {
			$filters = $this->get_custom_filters();

			return $filters ? $this->custom_filters_to_view_conditions( $filters ) : array();
		}
	}
}

if ( ! function_exists( 'yith_wcbep_table_views' ) ) {
	/**
	 * Get the class instance
	 *
	 * @return YITH_WCBEP_Table_Views
	 */
	function yith_wcbep_table_views(): YITH_WCBEP_Table_Views {
		return YITH_WCBEP_Table_Views::get_instance();
	}
}
