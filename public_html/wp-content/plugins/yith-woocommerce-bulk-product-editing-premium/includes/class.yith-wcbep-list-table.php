<?php
/**
 * Product List Table
 *
 * @package YITH\BulkProductEditing
 * @author  YITH <plugins@yithemes.com>
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( ! class_exists( 'YITH_WCBEP_List_Table' ) ) {
	/**
	 * List table class
	 *
	 * @since    1.0.0
	 */
	class YITH_WCBEP_List_Table extends WP_List_Table {
		/**
		 * Used to show/hide variations in table
		 *
		 * @since 1.1.4
		 * @var bool
		 */
		public $show_variations = false;

		/**
		 * The columns type.
		 *
		 * @since 2.0
		 * @var string[]
		 */
		protected static $column_types = array(
			'title'              => 'text',
			'slug'               => 'text',
			'description'        => 'text-editor',
			'shortdesc'          => 'text-editor',
			'regular_price'      => 'price',
			'sale_price'         => 'price',
			'categories'         => 'category',
			'tags'               => 'tag',
			'prod_type'          => 'product-type',
			'date'               => 'date',
			'image'              => 'image',
			'image_gallery'      => 'image-gallery',
			'purchase_note'      => 'text',
			'sku'                => 'text',
			'weight'             => 'number',
			'height'             => 'number',
			'width'              => 'number',
			'length'             => 'number',
			'stock_quantity'     => 'number',
			'download_limit'     => 'number',
			'download_expiry'    => 'number',
			'downloadable_files' => 'downloadable-files',
			'menu_order'         => 'number',
			'stock_status'       => 'stock-status',
			'low_stock_amount'   => 'number',
			'manage_stock'       => 'onoff',
			'sold_individually'  => 'onoff',
			'featured'           => 'onoff',
			'virtual'            => 'onoff',
			'downloadable'       => 'onoff',
			'enable_reviews'     => 'onoff',
			'tax_status'         => 'tax-status',
			'tax_class'          => 'tax-class',
			'allow_backorders'   => 'allow-backorders',
			'shipping_class'     => 'shipping-class',
			'status'             => 'status',
			'visibility'         => 'visibility',
			'sale_price_from'    => 'date',
			'sale_price_to'      => 'date',
			'button_text'        => 'text',
			'product_url'        => 'text',
			'up_sells'           => 'products',
			'cross_sells'        => 'products',
		);

		/**
		 * Add classes to HTML table element
		 *
		 * @return string[]
		 */
		protected function get_table_classes() {
			$classes = array( 'yith-plugin-fw__classic-table' );

			return $classes + parent::get_table_classes();
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			parent::__construct(
				array(
					'singular' => 'yith_wcbep_product',
					'plural'   => 'yith_wcbep_products',
					'ajax'     => true,
					'screen'   => 'yith-wcbep-product-list',
				)
			);
		}

		/**
		 * Get column types
		 *
		 * @return string[]
		 * @since 2.0
		 */
		public static function get_columns_type(): array {
			/**
			 * APPLY_FILTERS: yith_wcbep_list_table_get_columns_type
			 *
			 * Filters the columns type list.
			 *
			 * @param string[] $columns_types The columns type.
			 *
			 * @return string[]
			 */
			return apply_filters( 'yith_wcbep_list_table_get_columns_type', self::$column_types );
		}

		/**
		 * Get column type
		 *
		 * @param string $column_name The column name.
		 *
		 * @return string
		 * @since 2.0
		 */
		public static function get_column_type( $column_name ) {
			static $column_types = null;
			if ( is_null( $column_types ) ) {
				$column_types = self::get_columns_type();
			}

			/**
			 * APPLY_FILTERS: yith_wcbep_list_table_get_column_type
			 *
			 * Filters the column type.
			 *
			 * @param string $column_name The column name.
			 *
			 * @return string
			 */
			return apply_filters( 'yith_wcbep_list_table_get_column_type', strpos( $column_name, 'attr_pa' ) !== 0 ? $column_types[ $column_name ] ?? $column_name : 'attribute', $column_name, $column_types );
		}

		/**
		 * Get base columns
		 *
		 * @return array
		 */
		public static function get_base_columns(): array {
			static $columns = array();
			if ( ! $columns ) {
				$column_keys = array(
					'cb',
					'title',
					'id',
					'slug',
					'image',
					'image_gallery',
					'description',
					'shortdesc',
					'regular_price',
					'sale_price',
					'purchase_note',
					'categories',
					'tags',
					'sku',
					'weight',
					'height',
					'width',
					'length',
					'stock_quantity',
					'download_limit',
					'download_expiry',
					'downloadable_files',
					'menu_order',
					'stock_status',
					'low_stock_amount',
					'manage_stock',
					'sold_individually',
					'featured',
					'virtual',
					'downloadable',
					'enable_reviews',
					'tax_status',
					'tax_class',
					'allow_backorders',
					'shipping_class',
					'status',
					'visibility',
					'prod_type',
					'date',
					'sale_price_from',
					'sale_price_to',
					'button_text',
					'product_url',
					'up_sells',
					'cross_sells',
				);
				foreach ( $column_keys as $column_key ) {
					switch ( $column_key ) {
						case 'cb':
							$label = '<input type="checkbox">';
							break;
						default:
							$label = yith_wcbep_get_label( $column_key );
					}
					if ( $label ) {
						$columns[ $column_key ] = array(
							'label' => $label,
							'kind'  => 'simple',
						);
					}
				}
			}

			return $columns;
		}

		/**
		 * Get the default columns.
		 *
		 * @param array $args Arguments.
		 *
		 * @return array
		 */
		public static function get_default_columns( array $args = array() ): array {
			$default_args     = array(
				'limit_attributes' => -1,
			);
			$args             = wp_parse_args( $args, $default_args );
			$limit_attributes = $args['limit_attributes'];

			$columns = self::get_base_columns();

			$attribute_taxonomies = wc_get_attribute_taxonomies();
			if ( $attribute_taxonomies && is_array( $attribute_taxonomies ) ) {
				if ( $limit_attributes > -1 ) {
					$attribute_taxonomies = array_slice( $attribute_taxonomies, 0, absint( $limit_attributes ) );
				}

				foreach ( $attribute_taxonomies as $tax ) {
					$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
					$label                   = $tax->attribute_label ?? $tax->attribute_name;

					$columns[ 'attr_' . $attribute_taxonomy_name ] = array(
						'label' => $label,
						'kind'  => 'simple',
					);
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcbep_default_columns
			 *
			 * Filters the default shown column.
			 *
			 * @param array $columns The default columns.
			 *
			 * @return array
			 */
			return (array) apply_filters( 'yith_wcbep_default_columns', $columns );
		}

		/**
		 * Get column info
		 *
		 * @return array
		 */
		public function get_column_info() {
			$column_info    = parent::get_column_info();
			$column_info[0] = array_combine( array_keys( $column_info[0] ), array_column( $column_info[0], 'label' ) );

			return $column_info;
		}

		/**
		 * Get enabled default columns.
		 *
		 * @return array
		 */
		public static function get_enabled_default_columns() {
			$default_columns = self::get_default_columns();
			$enabled_columns = self::get_enabled_columns();

			$ever_enabled    = array( 'cb' );
			$enabled_columns = array_unique( array_merge( $ever_enabled, $enabled_columns ) );

			$disabled_columns = array_diff( array_keys( $default_columns ), $enabled_columns );

			foreach ( $disabled_columns as $disabled_column ) {
				if ( isset( $default_columns[ $disabled_column ] ) ) {
					unset( $default_columns[ $disabled_column ] );
				}
			}

			return $default_columns;
		}

		/**
		 * Retrieve the columns
		 *
		 * @return array|
		 */
		public function get_columns(): array {
			return self::get_enabled_default_columns();
		}

		/**
		 * Get sortable columns
		 *
		 * @return array
		 */
		public function get_sortable(): array {
			return array(
				'title'          => array( 'title', false ),
				'id'             => array( 'id', false ),
				'regular_price'  => array( 'regular_price', false ),
				'sale_price'     => array( 'sale_price', false ),
				'date'           => array( 'date', false ),
				'weight'         => array( 'weight', false ),
				'height'         => array( 'height', false ),
				'width'          => array( 'width', false ),
				'length'         => array( 'length', false ),
				'stock_quantity' => array( 'stock_quantity', false ),
				'sku'            => array( 'sku', false ),
				'menu_order'     => array( 'menu_order', false ),
			);
		}

		/**
		 * Get enabled columns.
		 *
		 * @return string[]
		 */
		public static function get_enabled_columns(): array {
			static $enabled_columns = null;
			if ( is_null( $enabled_columns ) ) {
				$enabled_columns = get_option( self::get_enabled_columns_option_key(), false );
				if ( false === $enabled_columns ) {
					$enabled_columns = array_keys( self::get_default_columns( array( 'limit_attributes' => 3 ) ) );
				}
				foreach ( $enabled_columns as &$enabled_column ) {
					if ( taxonomy_exists( $enabled_column ) ) {
						$enabled_column = 'yith_wcbep_tf_' . $enabled_column;
					}
				}
				$enabled_columns = array_merge(
					$enabled_columns,
					yith_wcbep_get_always_visible_columns()
				);
			}

			return $enabled_columns;
		}

		/**
		 * Get Enabled Columns option key
		 *
		 * @return string
		 */
		public static function get_enabled_columns_option_key(): string {
			$option_name = 'yith_wcbep_enabled_columns';
			$per_user    = 'yes' === get_option( 'yith-wcbep-hidden-columns-per-user', 'no' );
			$user_id     = function_exists( 'get_current_user_id' ) ? get_current_user_id() : false;

			if ( $per_user && $user_id ) {
				$option_name = $option_name . '-' . $user_id;
			}

			return $option_name;
		}

		/**
		 * Get enabled columns.
		 *
		 * @return string[]
		 */
		public static function get_enabled_columns_option(): array {
			$enabled_columns = get_option( self::get_enabled_columns_option_key(), array() );

			return is_array( $enabled_columns ) ? $enabled_columns : array();
		}

		/**
		 * Update enabled columns
		 *
		 * @param string[] $columns The enabled columns.
		 *
		 * @return bool
		 */
		public static function update_enabled_columns( $columns ): bool {
			return is_array( $columns ) && update_option( self::get_enabled_columns_option_key(), $columns );
		}

		/**
		 * Is column Enabled?
		 *
		 * @param string $column_name The column name.
		 *
		 * @return bool
		 */
		public static function is_column_enabled( $column_name ): bool {
			$enabled_columns = self::get_enabled_columns();

			return in_array( $column_name, $enabled_columns, true );
		}

		/**
		 * Get default hidden columns
		 *
		 * @return array
		 */
		public static function get_default_hidden(): array {
			$hidden_columns = yith_wcbep_get_hidden_columns();
			if ( ! is_array( $hidden_columns ) ) {
				// Set Defaults for first time!
				$hidden_columns = array(
					'slug',
					'image_gallery',
					'shortdesc',
					'purchase_note',
					'sku',
					'id',
					'weight',
					'height',
					'width',
					'length',
					'stock_quantity',
					'download_limit',
					'download_expiry',
					'downloadable_files',
					'menu_order',
					'stock_status',
					'low_stock_amount',
					'manage_stock',
					'sold_individually',
					'featured',
					'virtual',
					'downloadable',
					'enable_reviews',
					'tax_status',
					'tax_class',
					'allow_backorders',
					'shipping_class',
					'status',
					'visibility',
					'download_type',
					'sale_price_from',
					'sale_price_to',
					'button_text',
					'product_url',
					'up_sells',
					'cross_sells',
				);

				$attribute_taxonomies = wc_get_attribute_taxonomies();
				if ( $attribute_taxonomies ) {
					foreach ( $attribute_taxonomies as $tax ) {
						$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
						$hidden_columns[]        = 'attr_' . $attribute_taxonomy_name;
					}
				}
			}

			return array_diff( $hidden_columns, yith_wcbep_get_always_visible_columns() );
		}

		/**
		 * Get hidden columns
		 *
		 * @return array
		 */
		public function get_hidden(): array {
			return self::get_default_hidden();
		}

		/**
		 * Generates content for a single row of the table
		 *
		 * @param WP_Post $item The current item.
		 *
		 * @since  3.1.0
		 * @access public
		 */
		public function single_row( $item ) {
			if ( apply_filters( 'yith_wcbep_show_main_product', true ) ) {
				echo '<tr class="yith-wcbep-product-' . absint( $item->ID ) . '" data-product-id="' . absint( $item->ID ) . '">';
				$this->single_row_columns( $item );
				echo '</tr>';
			}
		}

		/**
		 * Prepare items.
		 */
		public function prepare_items() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended

			$current_page = isset( $_REQUEST['ywcbep_args']['paged'] ) ? max( 1, intval( $_REQUEST['ywcbep_args']['paged'] ) ) : $this->get_pagenum();
			$per_page     = max( get_option( 'yith_wcbep_products_per_page', 10 ), 1 );
			$columns      = $this->get_columns();
			$hidden       = $this->get_hidden();
			$sortable     = $this->get_sortable();
			$table_view   = isset( $_REQUEST['ywcbep_args']['table_view'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ywcbep_args']['table_view'] ) ) : yith_wcbep_table_views()->get_selected_view_key();

			if ( $table_view ) {
				if ( isset( $_REQUEST['ywcbep_args']['update_table_view'] ) && 'yes' === $_REQUEST['ywcbep_args']['update_table_view'] ) {
					yith_wcbep_table_views()->update_selected_view_option( $table_view );
				}
				$this->show_variations = yith_wcbep_table_views()->has_variations_included( $table_view );
			}

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$order_by = sanitize_text_field( wp_unslash( $_REQUEST['ywcbep_args']['order_by'] ?? 'ID' ) );
			$order    = sanitize_text_field( wp_unslash( $_REQUEST['ywcbep_args']['order'] ?? 'DESC' ) );

			$order_options = array(
				'regular_price'  => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_regular_price',
				),
				'sale_price'     => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_sale_price',
				),
				'weight'         => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_weight',
				),
				'height'         => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_height',
				),
				'width'          => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_width',
				),
				'length'         => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_length',
				),
				'stock_quantity' => array(
					'order_by' => 'meta_value_num',
					'meta'     => '_stock',
				),
				'sku'            => array(
					'order_by' => 'meta_value',
					'meta'     => '_sku',
				),
				'date'           => array(
					'order_by' => 'publish_date',
				),
			);

			$query_args = array(
				'post_type'           => 'product',
				'post_status'         => 'any',
				'posts_per_page'      => $per_page,
				'ignore_sticky_posts' => true,
				'paged'               => $current_page,
				'orderby'             => $order_by,
				'order'               => $order,
			);

			if ( array_key_exists( $order_by, $order_options ) && isset( $order_options[ $order_by ]['order_by'] ) ) {
				$query_args['orderby'] = $order_options[ $order_by ]['order_by'];
				if ( isset( $order_options[ $order_by ]['meta'] ) ) {
					$query_args['meta_key'] = $order_options[ $order_by ]['meta']; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				}
			}

			$table_views_handler = yith_wcbep_table_views();

			$view_args  = $table_views_handler->get_table_view_query_args( 'custom-filters' === $table_view ? $table_views_handler->build_table_view_condition( $table_views_handler->custom_filters_to_view_conditions( $table_views_handler->get_custom_filters() ) ) : $table_view );
			$query_args = wp_parse_args( $view_args, $query_args );

			/**
			 * APPLY_FILTERS: yith_wcbep_product_list_query_args
			 *
			 * Filters the query arguments that retrieves the product list shown on the plugin table.
			 *
			 * @param array $query_args The query args.
			 *
			 * @return array
			 */
			$query_args = apply_filters( 'yith_wcbep_product_list_query_args', $query_args );

			add_filter( 'posts_where', 'yith_wcbep_posts_filter_where' );
			add_filter( 'posts_join', 'yith_wcbep_posts_filter_join' );

			$products_query   = new WP_Query( $query_args );
			$products         = $products_query->posts;
			$found_variations = 0;
			if ( ! yith_wcbep_is_light_query_mode_enabled() && $table_views_handler->has_variations_included( $table_view ) ) {
				$products = array();
				foreach ( $products_query->posts as $product_id ) {
					$products[] = $product_id;
					$product    = wc_get_product( $product_id );
					if ( $product->is_type( 'variable' ) ) {
						/**
						 * The variable product
						 *
						 * @var $product WC_Product_Variable
						 */
						$children = $product->get_children();
						$products = array_merge( $products, array_map( 'get_post', $children ) );

						$found_variations += count( $children );
					}
				}
			}

			remove_filter( 'posts_where', 'yith_wcbep_posts_filter_where' );
			remove_filter( 'posts_join', 'yith_wcbep_posts_filter_join' );

			/**
			 * APPLY_FILTERS: yith_wcbep_items
			 *
			 * Filters the products table items.
			 *
			 * @param array $products The products list.
			 *
			 * @return array
			 */
			$my_items        = apply_filters( 'yith_wcbep_items', $products );
			$this->items     = $my_items;
			$pagination_args = array(
				'orderby'      => $order_by,
				'order'        => $order,
				'total_items'  => $products_query->found_posts,
				'per_page'     => $per_page,
				'total_pages'  => ceil( $products_query->found_posts / $per_page ),
				'variations'   => $found_variations,
				'current_page' => $current_page,
			);
			$this->set_pagination_args( $pagination_args );
			// phpcs:enable
		}

		/**
		 * Render column
		 *
		 * @param WP_Post $item        The Item.
		 * @param string  $column_name The column name.
		 *
		 * @return mixed|string|void
		 */
		public function column_default( $item, $column_name ) {
			$container_classes = array_unique(
				array_merge(
					array( 'yith-wcbep-column-container' ),
					/**
					 * APPLY_FILTERS: yith_wcbep_column_container_classes
					 *
					 * Filters the column container classes.
					 *
					 * @param string[] $classes     The classes.
					 * @param WP_Post  $item        The displaying item.
					 * @param string   $column_name The column name.
					 *
					 * @return string[]
					 */
					apply_filters( 'yith_wcbep_column_container_classes', array(), $item, $column_name )
				)
			);

			/**
			 * APPLY_FILTERS: yith_wcbep_column_container_data
			 *
			 * Filters the column container data html attributes.
			 *
			 * @param array   $container_data The container data.
			 * @param WP_Post $item           The displaying item.
			 * @param string  $column_name    The column name.
			 *
			 * @return array
			 */
			$container_data = apply_filters( 'yith_wcbep_column_container_data', array(), $item, $column_name );

			$display    = '';
			$value      = false;
			$product_id = $item->ID;
			$product    = wc_get_product( $product_id );

			/**
			 * APPLY_FILTERS: yith_wcbep_manage_custom_columns
			 *
			 * Manage custom column displaying.
			 *
			 * @param string  $display     The html code to display.
			 * @param string  $column_name The column name.
			 * @param WP_Post $item        The displaying item.
			 *
			 * @return string
			 */
			$display = apply_filters( 'yith_wcbep_manage_custom_columns', $display, $column_name, $item );
			if ( ! $product || $display ) {
				return $display;
			}

			$is_variation = $product->is_type( 'variation' );

			switch ( $column_name ) {
				case 'id':
					$display = $product_id;
					break;

				case 'sku':
					$display = $product->get_sku( 'edit' );
					break;

				case 'title':
					$display     = $is_variation ? $product->get_formatted_name() : $product->get_name( 'edit' );
					$row_actions = '<div class="yith-wcbep-product-actions"><a href="' . esc_url( $product->get_permalink() ) . '" target="_blank">' . __( 'View', 'yith-woocommerce-bulk-product-editing' ) . '</a><a href="' . esc_url( get_edit_post_link( $is_variation ? $product->get_parent_id() : $product->get_id() ) ) . '" target="_blank">' . __( 'Edit', 'yith-woocommerce-bulk-product-editing' ) . '</a></div>';
					break;

				case 'slug':
					$display = $product->get_slug( 'edit' );
					break;

				case 'image':
					$thumb_id = $product->get_image_id( 'edit' );
					$image    = $thumb_id ? wp_get_attachment_image_src( $thumb_id, 'full' ) : false;

					$display = $thumb_id ? '<img src="' . esc_url( $image[0] ?? '' ) . '" />' : '';
					$value   = $thumb_id ? wp_json_encode(
						array(
							'image_id'  => intval( $thumb_id ),
							'image_url' => $image[0] ?? '',
						),
						JSON_UNESCAPED_SLASHES
					) : false;
					break;

				case 'image_gallery':
					$image_gallery = $product->get_gallery_image_ids();
					$display       = '';
					$value         = array();
					foreach ( $image_gallery as $img_id ) {
						$image = wp_get_attachment_image_src( $img_id );
						if ( $image ) {
							$value[] = array(
								'image_id'  => $img_id,
								'image_url' => $image[0],
							);

							$display .= '<img data-image-id="' . esc_attr( $img_id ) . '" src="' . esc_url( $image[0] ) . '" />';
						}
					}
					$display = $display ? '<div class="yith-wcbep-table-image-gallery">' . $display . '</div>' : '';
					break;

				case 'downloadable_files':
					$downloadable_files = $product->get_downloads();
					if ( is_array( $downloadable_files ) && ! empty( $downloadable_files ) ) {
						$downloadable_files_count = count( $downloadable_files );
						// translators: %s is the number of files.
						$display = sprintf( _n( '%s file', '%s files', $downloadable_files_count, 'yith-woocommerce-bulk-product-editing' ), $downloadable_files_count );
						$value   = array();
						foreach ( $downloadable_files as $downloadable_file ) {
							$value[] = array(
								'name' => $downloadable_file['name'],
								'file' => $downloadable_file['file'],
							);
						}
					}
					break;

				case 'description':
					$display = $product->get_description( 'edit' );
					break;

				case 'shortdesc':
					$display = $product->get_short_description( 'edit' );
					break;

				case 'regular_price':
				case 'sale_price':
					$to_return = array();
					if ( $product->is_type( 'variable' ) && is_a( $product, 'WC_Product_Variable' ) ) {
						$prices = $product->get_variation_prices();

						$regular_price_min = current( $prices['regular_price'] );
						$regular_price_max = end( $prices['regular_price'] );
						$sale_price_min    = current( $prices['sale_price'] );
						$sale_price_max    = end( $prices['sale_price'] );

						$regular_price = '';
						if ( $regular_price_min !== $regular_price_max ) {
							$regular_price = wc_price( $regular_price_min ) . ' - ' . wc_price( $regular_price_max );
						} elseif ( $regular_price_min > 0 ) {
							$regular_price = wc_price( $regular_price_min );
						}

						$sale_price = '';
						if ( $sale_price_min !== $sale_price_max ) {
							$sale_price = wc_price( $sale_price_min ) . ' - ' . wc_price( $sale_price_max );
						} elseif ( $sale_price_min > 0 ) {
							$sale_price = wc_price( $sale_price_min );
						}

						$to_return['regular_price'] = $regular_price;
						$to_return['sale_price']    = $sale_price;
					} else {
						$getter                    = 'get_' . $column_name;
						$to_return[ $column_name ] = is_callable( array( $product, $getter ) ) ? $product->$getter( 'edit' ) : '';
					}

					$value   = $to_return[ $column_name ];
					$display = $product->is_type( 'variable' ) ? $value : self::format_price_column_content( $value );
					break;

				case 'stock_quantity':
					$display = $product->get_stock_quantity( 'edit' );
					break;

				case 'low_stock_amount':
					$display = $product->get_low_stock_amount( 'edit' );
					break;

				case 'weight':
				case 'height':
				case 'width':
				case 'length':
					$unit = get_option( 'weight' === $column_name ? 'woocommerce_weight_unit' : 'woocommerce_dimension_unit' );
					if ( $unit ) {
						$container_data['unit-of-measure'] = $unit;
					}
				// Adding the unit.
				case 'download_expiry':
				case 'purchase_note':
				case 'download_limit':
					$getter  = 'get_' . $column_name;
					$display = is_callable( array( $product, $getter ) ) ? $product->$getter( 'edit' ) : '';
					if ( 'purchase_note' === $column_name ) {
						$display = htmlspecialchars( $display );
					}
					break;

				case 'menu_order':
					$display = $product->get_menu_order( 'edit' );
					break;

				case 'cross_sells':
					$value   = $product->get_cross_sell_ids( 'edit' );
					$value   = array_combine( $value, array_map( 'get_the_title', $value ) );
					$display = '<div class="yith-wcbep-select-values">' . esc_html( implode( ', ', $value ) ) . '</div>';
					$value   = wp_json_encode( (object) $value );
					break;

				case 'up_sells':
					$value   = $product->get_upsell_ids( 'edit' );
					$value   = array_combine( $value, array_map( 'get_the_title', $value ) );
					$display = '<div class="yith-wcbep-select-values">' . esc_html( implode( ', ', $value ) ) . '</div>';
					$value   = wp_json_encode( (object) $value );
					break;

				case 'stock_status':
					$value   = $product->get_stock_status( 'edit' );
					$options = wc_get_product_stock_status_options();
					$display = $options[ $value ] ?? current( $options );
					break;

				case 'manage_stock':
				case 'sold_individually':
				case 'featured':
				case 'virtual':
				case 'downloadable':
					$getter  = 'get_' . $column_name;
					$value   = wc_bool_to_string( is_callable( array( $product, $getter ) ) ? $product->$getter( 'edit' ) : false );
					$display = yith_plugin_fw_get_field(
						array(
							'type'  => 'onoff',
							'value' => $value,
						),
						false,
						false
					);
					break;

				case 'enable_reviews':
					$value   = wc_bool_to_string( ! ! $product->get_reviews_allowed( 'edit' ) );
					$display = yith_plugin_fw_get_field(
						array(
							'type'  => 'onoff',
							'value' => $value,
						),
						false,
						false
					);
					break;

				case 'tax_status':
					$value   = $product->get_tax_status( 'edit' );
					$options = array(
						'taxable'  => __( 'Taxable', 'yith-woocommerce-bulk-product-editing' ),
						'shipping' => __( 'Shipping only', 'yith-woocommerce-bulk-product-editing' ),
						'none'     => _x( 'None', 'Tax status', 'yith-woocommerce-bulk-product-editing' ),
					);
					$display = $options[ $value ] ?? current( $options );

					break;

				case 'tax_class':
					$value = $product->get_tax_class( 'edit' );

					$tax_classes         = WC_Tax::get_tax_classes();
					$classes_options     = array();
					$classes_options[''] = __( 'Standard', 'yith-woocommerce-bulk-product-editing' );
					if ( $tax_classes ) {
						foreach ( $tax_classes as $class ) {
							$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
						}
					}
					$display = $classes_options[ $value ] ?? current( $classes_options );
					break;

				case 'allow_backorders':
					$value   = $product->get_backorders( 'edit' );
					$options = wc_get_product_backorder_options();

					$display = $options[ $value ] ?? current( $options );
					break;

				case 'shipping_class':
					$current_shipping_class = $product->get_shipping_class_id();
					$shipping_class_term    = get_term( $current_shipping_class, 'product_shipping_class' );
					$value                  = $current_shipping_class;
					$display                = $shipping_class_term->name ?? __( 'No shipping class', 'yith-woocommerce-bulk-product-editing' );
					break;

				case 'status':
					$value   = $product->get_status( 'edit' );
					$options = ! $is_variation ? yith_wcbep_get_product_statuses() : array(
						'private' => __( 'Private' ),
						'publish' => __( 'Published' ),
					);
					$display = $options[ $value ] ?? current( $options );
					break;

				case 'visibility':
					$value   = $product->get_catalog_visibility( 'edit' );
					$options = wc_get_product_visibility_options();
					$display = $options[ $value ] ?? current( $options );
					break;

				case 'download_type':
					$download_types = array(
						''            => __( 'Standard product', 'yith-woocommerce-bulk-product-editing' ),
						'application' => __( 'Application/Software', 'yith-woocommerce-bulk-product-editing' ),
						'music'       => __( 'Music', 'yith-woocommerce-bulk-product-editing' ),
					);
					$display        = '<select class="yith-wcbep-editable-select">';
					$value          = $product->get_meta( '_download_type', true, 'edit' );

					foreach ( $download_types as $key => $label ) {
						$display .= '<option value="' . $key . '" ' . selected( $value, $key, false ) . '>' . esc_html( $label ) . '</option>';
					}
					$display .= '</select>';
					break;

				case 'prod_type':
					if ( $is_variation ) {
						$product_type = 'variation';
						$display      = __( 'Variation', 'yith-woocommerce-bulk-product-editing' );
					} else {
						$product_type = $product->get_type();
						$options      = wc_get_product_types();
						$display      = $options[ $product_type ] ?? current( $options );
					}
					$value = $product_type;
					break;

				case 'sale_price_from':
					$value   = $product->get_date_on_sale_from( 'edit' );
					$value   = $value ? yit_datetime_to_timestamp( $value ) + wc_timezone_offset() : '';
					$display = $value ? date_i18n( 'Y-m-d', $value ) : '';
					break;

				case 'sale_price_to':
					$value   = $product->get_date_on_sale_to( 'edit' );
					$value   = $value ? yit_datetime_to_timestamp( $value ) + wc_timezone_offset() : '';
					$display = $value ? date_i18n( 'Y-m-d', $value ) : '';
					break;

				case 'button_text':
				case 'product_url':
					$getter  = 'get_' . $column_name;
					$display = is_callable( array( $product, $getter ) ) ? $product->$getter( 'edit' ) : '';
					break;

				case 'categories':
					$cats    = get_the_terms( $product_id, 'product_cat' );
					$cats    = ! empty( $cats ) ? $cats : array();
					$cat_ids = array_column( $cats, 'term_id' );
					$cats    = array_combine( $cat_ids, array_map( 'yith_wcbep_get_category_name', $cats ) );

					$display = '<div class="yith-wcbep-select-values">' . esc_html( implode( ', ', array_values( $cats ) ) ) . '</div>';
					$value   = wp_json_encode( $cats );
					break;

				case 'tags':
					$tags    = get_the_terms( $product_id, 'product_tag' );
					$tags    = ! empty( $tags ) ? $tags : array();
					$value   = array_combine( array_column( $tags, 'term_id' ), array_column( $tags, 'name' ) );
					$display = '<div class="yith-wcbep-select-values">' . esc_html( implode( ', ', $value ) ) . '</div>';
					break;

				case 'date':
					$value = $product->get_date_created();
					if ( $value ) {
						$value   = strtotime( $value );
						$display = date_i18n( 'Y-m-d', $value );
					}
					break;

				default:
					if ( 'attr_pa_' === substr( $column_name, 0, 8 ) ) {
						$container_data['taxonomy'] = substr( $column_name, 5 );
						$attributes                 = $product->get_attributes();
						$default_attributes         = $product->get_default_attributes();

						if ( ! empty( $attributes ) ) {
							$attr_key  = substr( $column_name, 5 );
							$attribute = $attributes[ $attr_key ] ?? false;
							$value     = array(
								'terms'        => new stdClass(),
								'is_visible'   => ! $is_variation ? $attribute['is_visible'] ?? 0 : false,
								'is_variation' => ! $is_variation ? $attribute['is_variation'] ?? 0 : false,
								'default'      => $default_attributes[ $attr_key ] ?? '',
							);
							if ( $attribute ) {
								if ( $is_variation ) {
									$variation_data = $product->get_variation_attributes();
									if ( isset( $variation_data[ 'attribute_' . $attr_key ] ) ) {
										$variation_attrs = explode( ', ', $variation_data[ 'attribute_' . $attr_key ] );
										if ( count( $variation_attrs ) > 0 ) {
											foreach ( $variation_attrs as &$variation_attr ) {
												$variation_attr = get_term_by( 'slug', $variation_attr, $attr_key );
											}
											$variation_attrs = array_filter( $variation_attrs );
											$value           = array_combine( array_column( $variation_attrs, 'term_id' ), array_column( $variation_attrs, 'name' ) );
											$display         = implode( ', ', $value );
										}
									}
								} else {
									$display         = '<div class="yith-wcbep-select-values">' . esc_html( $product->get_attribute( $attr_key ) ) . '</div>';
									$terms           = array_map( 'intval', wc_get_product_terms( $product_id, $attribute['name'], array( 'fields' => 'ids' ) ) );
									$value ['terms'] = array_combine( $terms, array_column( array_map( 'get_term', $terms ), 'name' ) );
								}
							}
						}
					} else {
						$display = $column_name;
					}
					break;
			}

			if ( false === $value ) {
				$value = $display;
			}

			$display = $display ?? '';

			if ( ! empty( $container_data['unit-of-measure'] ) && '' !== $display ) {
				$display .= ' ' . htmlspecialchars( esc_html( $container_data['unit-of-measure'] ) );
			}

			$args = array(
				'column_name' => $column_name,
				'data'        => $container_data,
				'value'       => $value,
				'display'     => $display,
				'classes'     => $container_classes,
			);

			if ( ! empty( $row_actions ) ) {
				$args['row_actions'] = $row_actions;
			}

			return self::render_column( $args );
		}

		/**
		 * Column render
		 *
		 * @param array $args The column args.
		 * @param bool  $echo If you want to print it.
		 *
		 * @return string
		 */
		public static function render_column( $args, $echo = false ) {
			$defaults        = array(
				'column_name' => '',
				'data'        => array(),
				'classes'     => array(),
				'value'       => '',
				'display'     => '',
			);
			$args            = wp_parse_args( $args, $defaults );
			$args['classes'] = array_merge( $args['classes'], array( 'yith-wcbep-column-container' ) );

			$args['value']                 = is_array( $args['value'] ) ? wp_json_encode( $args['value'] ) : $args['value'];
			$args['data']['initial-value'] = esc_attr( $args['value'] );

			$data = '';
			foreach ( $args['data'] as $data_key => $data_value ) {
				$data_value = is_array( $data_value ) ? esc_attr( wp_json_encode( $data_value ) ) : $data_value;

				$data .= 'data-' . $data_key . '="' . $data_value . '" ';
			}

			if ( 'text-editor' === self::get_column_type( $args['column_name'] ) ) {
				$args['display'] = '<div class="yith-wcbep-text-editor-content">' . $args['display'] . '</div>';
			}

			if ( is_array( $args['display'] ) ) {
				$args['display'] = implode( ', ', self::sanitize_array_values( $args['display'] ) );
			}

			if ( is_object( $args['display'] ) ) {
				$args['display'] = '';
			}

			$cell = '<div class="' . esc_attr( implode( ' ', $args['classes'] ) ) . '" ' . $data . '>' . $args['display'] . '</div>';

			$cell .= '<input class="yith-wcbep-column-value" type="hidden" value="' . esc_attr( $args['value'] ) . '"/>';

			if ( ! empty( $args['row_actions'] ) ) {
				$cell .= $args['row_actions'];
			}

			if ( $echo ) {
				echo $cell; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return $cell;
		}

		/**
		 * Make all the values inside the array printable
		 *
		 * @param array $arr    The array to sanitize.
		 * @param bool  $filter True if the array should be without empty values, false otherwise.
		 * @return array
		 */
		public static function sanitize_array_values( $arr, $filter = true ) {
			$sanitized = array();
			if ( is_array( $arr ) ) {
				foreach ( $arr as $key => $value ) {
					if ( is_string( $value ) || is_numeric( $value ) ) {
						$sanitized[ $key ] = $value;
					} elseif ( is_bool( $value ) ) {
						$sanitized[ $key ] = wc_bool_to_string( $value );
					} elseif ( is_object( $value ) ) {
						$sanitized[ $key ] = '';
					} elseif ( is_array( $value ) ) {
						$sanitized[ $key ] = self::sanitize_array_values( $value );
					}
				}
			}

			return $filter ? array_filter( $sanitized ) : $sanitized;
		}

		/**
		 * Format the price column content
		 *
		 * @param string|float $price The price.
		 *
		 * @return string
		 */
		public static function format_price_column_content( $price ) {
			$currency_pos    = get_option( 'woocommerce_currency_pos', 'left' );
			$currency_symbol = get_woocommerce_currency_symbol();

			$price_formats = array(
				'left'        => '%1$s%2$s',
				'right'       => '%2$s%1$s',
				'left_space'  => '%1$s&nbsp%2$s',
				'right_space' => '%2$s&nbsp%1$s',
			);

			return sprintf( $price_formats[ $currency_pos ], '<div class="yith-wcbep-price-currency" ' . ( '' !== $price ? '' : 'style="display:none;"' ) . '>' . $currency_symbol . '</div>', '<div class="yith-wcbep-price-amount">' . wc_format_localized_price( $price ) . '</div>' );
		}

		/**
		 * Generates the columns for a single row of the table.
		 *
		 * @param object|array $item The current item.
		 */
		protected function single_row_columns( $item ) {
			list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();
			foreach ( $columns as $column_name => $column_display_name ) {
				$column_type = self::get_column_type( $column_name );
				$classes     = array(
					$column_name,
					"column-$column_name",
					"yith-wcbep-column-$column_type-type",
				);

				if ( $primary === $column_name ) {
					$classes[] = 'has-row-actions column-primary';
				}

				if ( in_array( $column_name, $hidden, true ) ) {
					$classes[] = ' hidden';
				}

				$product = wc_get_product( $item );
				if ( $product ) {
					$editable = $this->is_column_editable( $product, $column_name, $column_display_name );
					if ( true !== $editable && $editable ) {
						$classes[]                           = 'yith-wcbep-column-not-editable';
						$column_data['not-editable-message'] = $editable;
					}
				}

				$column_data['colname']     = wp_strip_all_tags( $column_display_name );
				$column_data['col-options'] = wp_json_encode(
					array(
						'col_name' => $column_name,
						'type'     => $column_type,
					)
				);

				$data = '';

				foreach ( $column_data as $data_name => $data_value ) {
					$data .= sprintf( 'data-%s="%s" ', $data_name, esc_attr( $data_value ) );
				}

				$attributes = "class='" . implode( ' ', $classes ) . "' $data";
// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				if ( 'cb' === $column_name ) {
					echo '<th scope="row" class="check-column">';
					echo $this->column_cb( $item );
					echo '</th>';
				} elseif ( method_exists( $this, '_column_' . $column_name ) ) {
					echo call_user_func( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array( $this, '_column_' . $column_name ),
						$item,
						$classes,
						$data,
						$primary
					);
				} elseif ( method_exists( $this, 'column_' . $column_name ) ) {
					echo "<td $attributes>";
					echo call_user_func( array( $this, 'column_' . $column_name ), $item );
					echo $this->handle_row_actions( $item, $column_name, $primary );
					echo '</td>';
				} else {
					echo "<td $attributes>";
					echo $this->column_default( $item, $column_name );
					echo $this->handle_row_actions( $item, $column_name, $primary );
					echo '</td>';
				}
				// phpcs:enable
			}
		}

		/**
		 * Checkbox column render
		 *
		 * @param WP_Post $item The item.
		 *
		 * @return string
		 */
		public function column_cb( $item ) {
			return sprintf( '<input type="checkbox" value="%s" />', $item->ID );
		}

		/**
		 * Print the column headers
		 *
		 * @param bool $with_id True to include the id.
		 */
		public function print_column_headers( $with_id = true ) {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended
			list( $columns, $hidden, $sortable ) = $this->get_column_info();

			$current_url = set_url_scheme( admin_url() . '?page=yith_wcbep_panel' );
			$current_url = remove_query_arg( 'paged', $current_url );

			$current_orderby = isset( $_REQUEST['ywcbep_args']['order_by'] ) ? wc_clean( wp_unslash( $_REQUEST['ywcbep_args']['order_by'] ) ) : 'ID';
			$current_order   = isset( $_REQUEST['ywcbep_args']['order'] ) && 'desc' === wc_clean( wp_unslash( $_REQUEST['ywcbep_args']['order'] ) ) ? 'desc' : 'asc';

			if ( ! empty( $columns['cb'] ) ) {
				static $cb_counter = 1;

				$columns['cb'] = array( 'label' => '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select all', 'yith-woocommerce-bulk-product-editing' ) . '</label><input id="cb-select-all-' . $cb_counter . '" type="checkbox" />' );
				$cb_counter++;
			}
			foreach ( $columns as $column_key => $column_display_name ) {
				$column_display_name = is_array( $column_display_name ) ? current( $column_display_name ) : $column_display_name;
				$class               = array( 'manage-column', "column-$column_key" );

				$style = '';
				if ( in_array( $column_key, $hidden, true ) ) {
					$style = 'display:none;';
				}

				$style = ' style="' . $style . '"';

				$class[] = 'yith-wcbep-column-' . self::get_column_type( $column_key ) . '-type';

				if ( 'cb' === $column_key ) {
					$class[] = 'check-column';
				} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ), true ) ) {
					$class[] = 'num';
				}

				if ( isset( $sortable[ $column_key ] ) ) {
					list( $orderby, $desc_first ) = $sortable[ $column_key ];

					if ( $current_orderby === $orderby ) {
						$order   = 'asc' === $current_order ? 'desc' : 'asc';
						$class[] = 'sorted';
						$class[] = $current_order;
					} else {
						$order   = $desc_first ? 'desc' : 'asc';
						$class[] = 'sortable';
						$class[] = $desc_first ? 'asc' : 'desc';
					}

					$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
				}

				$id = $with_id ? 'id="' . esc_attr( $column_key ) . '"' : '';

				if ( ! empty( $class ) ) {
					$class = "class='" . esc_attr( join( ' ', $class ) ) . "'";
				}

				echo "<th scope='col' $id $class $style>$column_display_name</th>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			// phpcs:enable
		}

		/**
		 * Display
		 */
		public function display() {

			wp_nonce_field( 'ajax-yith-wcbep-list-nonce', '_ajax_yith_wcbep_list_nonce' );

			$this->display_empty_state();
			parent::display();
		}

		/**
		 * Display Empty state
		 *
		 * @return void
		 */
		public function display_empty_state() {
			$empty_state_args = array(
				'id'               => 'yith-wcbep-empty-state-due-filters',
				'icon'             => 'empty-table',
				'message'          => __( 'No products found matching your table view conditions. Try to apply different filters.', 'yith-woocommerce-bulk-product-editing' ),
				'cta'              => __( 'Manage views', 'yith-woocommerce-bulk-product-editing' ),
				'cta_button_class' => 'yith-wcbep-empty-state-manage-views',
			);
			yith_wcbep_get_view( 'products-table-empty-state.php', $empty_state_args );
			$empty_state_args = array(
				'id'      => 'yith-wcbep-empty-state-zero-products',
				'icon'    => 'empty-table',
				'message' => __( 'No products found in your store.', 'yith-woocommerce-bulk-product-editing' ),
			);
			if ( current_user_can( 'edit_products' ) ) {
				$empty_state_args['cta']              = __( 'Create product', 'yith-woocommerce-bulk-product-editing' );
				$empty_state_args['cta_button_class'] = 'yith-wcbep-empty-state-add-product';
			}
			yith_wcbep_get_view( 'products-table-empty-state.php', $empty_state_args );

			$empty_state_args = array(
				'id'               => 'yith-wcbep-empty-state-due-disabled-fields',
				'icon'             => 'empty-table',
				'message'          => __( 'Oops! It seems that there are no fields enabled for this table.<br>Try to enable some of them!', 'yith-woocommerce-bulk-product-editing' ),
				'cta'              => __( 'Enable fields', 'yith-woocommerce-bulk-product-editing' ),
				'cta_button_class' => 'yith-wcbep-empty-state-enable-fields',
			);
			yith_wcbep_get_view( 'products-table-empty-state.php', $empty_state_args );
		}

		/**
		 * Display table nav
		 *
		 * @param string $context The context.
		 *
		 * @return void
		 */
		public function display_tablenav( $context ) {
			if ( 'top' === $context ) {
				echo '<div class="yith-wcbep-products-table-container">';
				echo '<span class="yith-wcbep-products-table-progress-bar"></span>';
			} elseif ( 'bottom' === $context ) {
				echo '</div>';
				parent::display_tablenav( $context );
			} else {
				parent::display_tablenav( $context );
			}
		}

		/**
		 * Displays the pagination.
		 *
		 * @param string $which Which pagination it is.
		 *
		 * @since 2.0
		 */
		protected function pagination( $which ) {
			ob_start();
			parent::pagination( $which );
			$pagination = ob_get_clean();
			$variations = $this->get_pagination_arg( 'variations' );
			if ( 'bottom' === $which && $variations ) {
				$total_items = sprintf(
				/* translators: %s: Number of items. */
					_n( '%s item', '%s items', $this->get_pagination_arg( 'total_items' ) ),
					number_format_i18n( $this->get_pagination_arg( 'total_items' ) )
				);

				// translators: %s is the number of found variations.
				$pagination = str_replace( $total_items, $total_items . ' (' . sprintf( _n( '%s variation found', '%s variations found', $variations, 'yith-woocommerce-bulk-product-editing' ), number_format_i18n( $variations ) ) . ')', $pagination );
			}

			echo $pagination;
		}

		/**
		 * Get Ajax Response
		 *
		 * @return array
		 */
		public function get_ajax_response() {

			$this->prepare_items();

			ob_start();
			$this->display_rows_or_placeholder();
			$rows = ob_get_clean();

			ob_start();
			$this->print_column_headers();
			$headers = ob_get_clean();

			ob_start();
			$this->pagination( 'top' );
			$pagination_top = ob_get_clean();

			ob_start();
			$this->pagination( 'bottom' );
			$pagination_bottom = ob_get_clean();

			$paged = $this->get_pagination_arg( 'current_page' );

			$response = array(
				'rows'           => $rows,
				'pagination'     => array(
					'top'    => $pagination_top,
					'bottom' => $pagination_bottom,
				),
				//'variations'      => $variations,
				// translators: %s is the number of found variations.
				//'variations_i18n' => $variations ? _n( '%s variation found', '%s variations found', $variations, 'yith-woocommerce-bulk-product-editing' ) : '',
				'column_headers' => $headers,
				'paged'          => $paged,
			);

			if ( isset( $this->_pagination_args['total_items'] ) ) {
				// translators: %s is the number of items.
				$response['total_items_i18n'] = sprintf( _n( '%s item', '%s items', $this->_pagination_args['total_items'] ), number_format_i18n( $this->_pagination_args['total_items'] ) );
			}

			if ( isset( $this->_pagination_args['variations'] ) ) {
				// translators: %s is the number of items.
				$response['variations_i18n'] = sprintf( _n( '%s variation shown', '%s variations shown', $this->_pagination_args['variations'] ), number_format_i18n( $this->_pagination_args['variations'] ) );
			}

			$args = array(
				'limit'  => 1,
				'return' => 'ids',
			);

			$response['container_classes'] = ! $this->items ? 'yith-wcbep-empty-state-due-filters' : '';
			$response['container_classes'] = $response['container_classes'] && ! wc_get_products( $args ) ? 'yith-wcbep-empty-state-zero-products' : $response['container_classes'];
			if ( isset( $this->_pagination_args['total_pages'] ) ) {
				$response['total_pages']      = $this->_pagination_args['total_pages'];
				$response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
			}

			return $response;
		}

		/**
		 * Ajax Response
		 *
		 * @param array $extra_data The extra data to send in the AJAX response.
		 */
		public function ajax_response( $extra_data = array() ) {
			check_ajax_referer( 'yith_wcbep_load_products_table', 'security' );
			$ajax_response = $this->get_ajax_response();

			wp_send_json( is_array( $extra_data ) ? array_merge( $extra_data, $ajax_response ) : $ajax_response );
		}

		/**
		 * Check if the column is editable
		 *
		 * @param WC_Product $product             The product.
		 * @param string     $column_name         The column name.
		 * @param string     $column_display_name The column display name.
		 *
		 * @return true|string
		 */
		private function is_column_editable( $product, $column_name, $column_display_name ) {
			$editable = true;

			if ( 'id' === $column_name ) {
				$editable = __( 'The product ID is not editable', 'yith-woocommerce-bulk-product-editing' );
			} elseif ( $product->is_type( 'variable' ) && in_array( $column_name, array( 'regular_price', 'sale_price' ), true ) ) {
				$editable = __( 'The price of variable products depends on their variations, so you cannot directly edit this price, you have to edit the price of the variations instead.', 'yith-woocommerce-bulk-product-editing' );
			} elseif ( $product->is_type( 'variation' ) ) {
				/**
				 * APPLY_FILTERS: yith_wcbep_variation_not_editable
				 *
				 * Filters the columns list that are not editable in the variation products.
				 *
				 * @param string[] $variation_not_editable The columns list.
				 *
				 * @return string[]
				 */
				$variation_not_editable = apply_filters(
					'yith_wcbep_variation_not_editable',
					array(
						'title',
						'slug',
						'status',
					)
				);

				/**
				 * APPLY_FILTERS: yith_wcbep_variation_not_editable_and_empty
				 *
				 * Filters the columns list that are not editable and empty in the variation products.
				 *
				 * @param string[] $variation_not_editable The columns list.
				 *
				 * @return string[]
				 */
				$variation_not_editable_and_empty = apply_filters(
					'yith_wcbep_variation_not_editable_and_empty',
					array(
						'shortdesc',
						'purchase_note',
						'menu_order',
						'up_sells',
						'cross_sells',
						'sold_individually',
						'enable_reviews',
						'visibility',
						'button_text',
						'product_url',
						'categories',
						'tags',
						'date',
						'featured',
						'tax_status',
						'download_type',
						'image_gallery',
					)
				);

				if ( strpos( $column_name, 'attr_pa_' ) === 0 ) {
					$editable = __( 'The attributes of a product variation are not editable', 'yith-woocommerce-bulk-product-editing' );
				} elseif ( in_array( $column_name, array_merge( $variation_not_editable, $variation_not_editable_and_empty ), true ) ) {
					// translators: %s is the column name.
					$editable = sprintf( __( 'The %s column cannot be edited', 'yith-woocommerce-bulk-product-editing' ), $column_display_name );
				}
			}

			/**
			 * APPLY_FILTERS: yith_wcbep_custom_field_type
			 *
			 * Checks if the column is editable.
			 *
			 * @param bool       $editable    The custom field type.
			 * @param WC_Product $product     The product.
			 * @param string     $column_name The column name.
			 *
			 * @return bool
			 */
			return apply_filters( 'yith_wcbep_is_column_editable', $editable, $product, $column_name );
		}
	}
}
