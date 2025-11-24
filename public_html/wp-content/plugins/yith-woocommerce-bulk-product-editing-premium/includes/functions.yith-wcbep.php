<?php
/**
 * Functions
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! function_exists( 'yith_wcbep_posts_filter_where' ) ) {
	/**
	 * Filter 'where' in post query to filter by title and description.
	 *
	 * @param string $query_where The 'where' in the query.
	 *
	 * @return string
	 */
	function yith_wcbep_posts_filter_where( string $query_where = '' ): string {
		$view = yith_wcbep_table_views()->get_table_view();

		if ( ! empty( $view['conditions'] ) ) {
			foreach ( $view['conditions'] as $condition ) {
				$props_to_columns = array(
					'title' => 'post_title',
					'desc'  => 'post_content',
					'shortdesc'  => 'post_excerpt',
				);
				if ( in_array( $condition['type'], array_keys( $props_to_columns ), true ) ) {
					$where = array(
						'column'   => $props_to_columns[ $condition['type'] ],
						'operator' => 'LIKE',
						'value'    => addslashes( sanitize_text_field( $condition['options']['value'] ) ),
					);
					switch ( $condition['options']['compare'] ) {
						case 'not-contain':
							$where['operator'] = 'NOT LIKE';
						// Put where operator.
						case 'contain':
							$where['value'] = '%' . $where['value'] . '%';
							break;
						case 'start':
							$where['value'] .= '%';
							break;
						case 'end':
							$where['value'] = '%' . $where['value'];
							break;
						case 'regex':
							$where['operator'] = 'REGEXP';
							break;
					}
					$query_where .= ' AND ' . $where['column'] . ' ' . $where['operator'] . ' \'' . $where['value'] . '\'';
				}
			}
		}

		if ( ! empty( $_POST['ywcbep_args']['product_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$product_name = sanitize_text_field( wp_unslash( $_POST['ywcbep_args']['product_name'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

			$query_where .= ' AND (' . sprintf( 'post_title LIKE "%s"', '%' . $product_name . '%' ) . ' OR ( meta_key = "_sku" AND ' . sprintf( 'meta_value LIKE "%s"', '%' . $product_name . '%' ) . ' ))';
		}

		return $query_where;
	}
}

if ( ! function_exists( 'yith_wcbep_posts_filter_join' ) ) {
	/**
	 * Filter 'join' in post query to join the query with the post meta table if is needed.
	 *
	 * @param string $join The join conditions in the query.
	 *
	 * @return string
	 */
	function yith_wcbep_posts_filter_join( $join ) {
		global $wpdb;
		if ( ! empty( $_POST['ywcbep_args']['product_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$join .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id";
		}

		return $join;
	}
}

if ( ! function_exists( 'yith_wcbep_get_terms' ) ) {
	/**
	 * Get Terms.
	 *
	 * @param array $args Arguments.
	 *
	 * @return int[]|string|string[]|WP_Error|WP_Term[]
	 */
	function yith_wcbep_get_terms( $args = array() ) {
		global $wp_version;

		if ( version_compare( '4.5.0', $wp_version, '>=' ) ) {
			return get_terms( $args );
		} else {
			$taxonomy = $args['taxonomy'] ?? '';
			if ( isset( $args['taxonomy'] ) ) {
				unset( $args['taxonomy'] );
			}

			return get_terms( $taxonomy, $args );
		}
	}
}

if ( ! function_exists( 'yith_wcbep_get_wc_product_types' ) ) {
	/**
	 * Retrieve WooCommerce Product Types.
	 *
	 * @return array
	 */
	function yith_wcbep_get_wc_product_types() {
		$terms         = yith_wcbep_get_terms( array( 'taxonomy' => 'product_type' ) );
		$wc_types      = wc_get_product_types();
		$product_types = array(
			'any' => 'All product types',
		);
		foreach ( $terms as $term ) {
			$name                   = sanitize_title( $term->name );
			$product_types[ $name ] = $wc_types[ $name ] ?? ucfirst( $term->name );
			if ( 'variable' === $name ) {
				$product_types['variation'] = __( 'Variation', 'yith-woocommerce-bulk-product-editing' );
			}
		}

		return $product_types;
	}
}
if ( ! function_exists( 'yith_wcbep_get_labels' ) ) {
	/**
	 * Return labels.
	 *
	 * @return array
	 */
	function yith_wcbep_get_labels() {
		/**
		 * APPLY_FILTERS: yith_wcbep_labels
		 *
		 * Filters the columns label.
		 *
		 * @param string[] $labels The columns label.
		 *
		 * @return string[]
		 */
		return (array) apply_filters(
			'yith_wcbep_labels',
			array(
				'title'              => __( 'Product name', 'yith-woocommerce-bulk-product-editing' ),
				'id'                 => __( 'ID', 'yith-woocommerce-bulk-product-editing' ),
				'slug'               => __( 'Slug', 'yith-woocommerce-bulk-product-editing' ),
				'image'              => __( 'Image', 'yith-woocommerce-bulk-product-editing' ),
				'image_gallery'      => __( 'Product gallery', 'yith-woocommerce-bulk-product-editing' ),
				'description'        => __( 'Description', 'yith-woocommerce-bulk-product-editing' ),
				'shortdesc'          => __( 'Short description', 'yith-woocommerce-bulk-product-editing' ),
				'regular_price'      => __( 'Regular price', 'yith-woocommerce-bulk-product-editing' ),
				'sale_price'         => __( 'Sale price', 'yith-woocommerce-bulk-product-editing' ),
				'purchase_note'      => __( 'Purchase note', 'yith-woocommerce-bulk-product-editing' ),
				'categories'         => __( 'Categories', 'yith-woocommerce-bulk-product-editing' ),
				'tags'               => __( 'Tags', 'yith-woocommerce-bulk-product-editing' ),
				'sku'                => __( 'SKU', 'yith-woocommerce-bulk-product-editing' ),
				'weight'             => __( 'Weight', 'yith-woocommerce-bulk-product-editing' ),
				'height'             => __( 'Height', 'yith-woocommerce-bulk-product-editing' ),
				'width'              => __( 'Width', 'yith-woocommerce-bulk-product-editing' ),
				'length'             => __( 'Length', 'yith-woocommerce-bulk-product-editing' ),
				'stock_quantity'     => __( 'Stock quantity', 'yith-woocommerce-bulk-product-editing' ),
				'download_limit'     => __( 'Download limit', 'yith-woocommerce-bulk-product-editing' ),
				'download_expiry'    => __( 'Download expiry', 'yith-woocommerce-bulk-product-editing' ),
				'downloadable_files' => __( 'Downloadable files', 'yith-woocommerce-bulk-product-editing' ),
				'menu_order'         => __( 'Menu order', 'yith-woocommerce-bulk-product-editing' ),
				'stock_status'       => __( 'Stock status', 'yith-woocommerce-bulk-product-editing' ),
				'low_stock_amount'   => __( 'Low stock threshold', 'yith-woocommerce-bulk-product-editing' ),
				'stock_qty'          => __( 'Stock quantity', 'yith-woocommerce-bulk-product-editing' ),
				'manage_stock'       => __( 'Manage stock', 'yith-woocommerce-bulk-product-editing' ),
				'sold_individually'  => __( 'Sold individually', 'yith-woocommerce-bulk-product-editing' ),
				'featured'           => __( 'Featured', 'yith-woocommerce-bulk-product-editing' ),
				'virtual'            => __( 'Virtual', 'yith-woocommerce-bulk-product-editing' ),
				'downloadable'       => __( 'Downloadable', 'yith-woocommerce-bulk-product-editing' ),
				'enable_reviews'     => __( 'Enable reviews', 'yith-woocommerce-bulk-product-editing' ),
				'tax_status'         => __( 'Tax status', 'yith-woocommerce-bulk-product-editing' ),
				'tax_class'          => __( 'Tax class', 'yith-woocommerce-bulk-product-editing' ),
				'allow_backorders'   => __( 'Allow backorders?', 'yith-woocommerce-bulk-product-editing' ),
				'shipping_class'     => __( 'Shipping class', 'yith-woocommerce-bulk-product-editing' ),
				'status'             => __( 'Status', 'yith-woocommerce-bulk-product-editing' ),
				'visibility'         => __( 'Catalog visibility', 'yith-woocommerce-bulk-product-editing' ),
				'download_type'      => __( 'Download type', 'yith-woocommerce-bulk-product-editing' ),
				'prod_type'          => __( 'Product type', 'yith-woocommerce-bulk-product-editing' ),
				'date'               => __( 'Date', 'yith-woocommerce-bulk-product-editing' ),
				'sale_price_from'    => __( 'Sale price from', 'yith-woocommerce-bulk-product-editing' ),
				'sale_price_to'      => __( 'Sale price to', 'yith-woocommerce-bulk-product-editing' ),
				'button_text'        => __( 'Button text', 'yith-woocommerce-bulk-product-editing' ),
				'product_url'        => __( 'Product URL', 'yith-woocommerce-bulk-product-editing' ),
				'up_sells'           => __( 'Upsells', 'yith-woocommerce-bulk-product-editing' ),
				'cross_sells'        => __( 'Cross-sells', 'yith-woocommerce-bulk-product-editing' ),
			)
		);
	}
}

if ( ! function_exists( 'yith_wcbep_get_label' ) ) {
	/**
	 * Retrieve a label by key.
	 *
	 * @param string $key The key.
	 *
	 * @return string
	 */
	function yith_wcbep_get_label( string $key ): string {
		$labels = yith_wcbep_get_labels();
		$label  = $labels[ $key ] ?? '';

		/**
		 * APPLY_FILTERS: yith_wcbep_get_label
		 *
		 * Filters the column label.
		 *
		 * @param string $label The column label.
		 * @param string $key   The column key.
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wcbep_get_label', $label, $key );
	}
}

if ( ! function_exists( 'yith_wcbep_get_enabled_columns' ) ) {
	/**
	 * Retrieve the enabled columns .
	 *
	 * @return array|null
	 */
	function yith_wcbep_get_enabled_columns(): array {
		return YITH_WCBEP_List_Table::get_enabled_columns();
	}
}

if ( ! function_exists( 'yith_wcbep_is_column_enabled' ) ) {
	/**
	 * Return true if the column is enabled.
	 *
	 * @param string $column The column.
	 *
	 * @return bool
	 */
	function yith_wcbep_is_column_enabled( string $column ): bool {
		$enabled_columns = yith_wcbep_get_enabled_columns();

		return in_array( $column, $enabled_columns, true );
	}
}

if ( ! function_exists( 'yith_wcbep_get_hidden_columns_option' ) ) {
	/**
	 * Retrieve the "hidden columns" option key.
	 *
	 * @return string
	 */
	function yith_wcbep_get_hidden_columns_option(): string {
		$per_user = 'yes' === get_option( 'yith-wcbep-hidden-columns-per-user', 'no' );
		$user_id  = get_current_user_id();
		$option   = 'yith_wcbep_default_hidden_cols';

		if ( $per_user && $user_id ) {
			$option .= '-' . $user_id;
		}

		return $option;
	}
}

if ( ! function_exists( 'yith_wcbep_get_hidden_columns' ) ) {
	/**
	 * Retrieve the hidden columns.
	 *
	 * @param mixed $default The default value if they are not set.
	 *
	 * @return array
	 */
	function yith_wcbep_get_hidden_columns( $default = array() ): array {
		$option         = yith_wcbep_get_hidden_columns_option();
		$hidden_columns = get_option( $option, $default );

		return is_array( $hidden_columns ) ? array_diff( $hidden_columns, yith_wcbep_get_always_visible_columns() ) : array();
	}
}

if ( ! function_exists( 'yith_wcbep_get_always_visible_columns' ) ) {
	/**
	 * Retrieve the columns that can't be hidden.
	 *
	 * @return array
	 */
	function yith_wcbep_get_always_visible_columns(): array {
		$always_visible = array( 'cb', 'title' );

		/**
		 * APPLY_FILTERS: yith_wcbep_always_visible_columns
		 *
		 * Filters the always visible columns list.
		 *
		 * @param string[] $always_visible The always visible columns.
		 *
		 * @return string[]
		 */
		return apply_filters( 'yith_wcbep_always_visible_columns', $always_visible );
	}
}

if ( ! function_exists( 'yith_wcbep_set_hidden_columns' ) ) {
	/**
	 * Save hidden columns.
	 *
	 * @param array $value The value to set.
	 *
	 * @return bool
	 */
	function yith_wcbep_set_hidden_columns( array $value = array() ): bool {
		$option = yith_wcbep_get_hidden_columns_option();

		return update_option( $option, (array) $value );
	}
}

if ( ! function_exists( 'yith_wcbep_get_category_name' ) ) {
	/**
	 * Retrieve the category name from term
	 *
	 * @param WP_Term $category The category.
	 *
	 * @return string
	 * @since 1.2.28
	 */
	function yith_wcbep_get_category_name( WP_Term $category ): string {
		$category_name = $category->name;
		$name_type     = get_option( 'yith-wcbep-name-in-categories-dropdown', 'name' );

		if ( in_array( $name_type, array( 'parent-and-name', 'parent-and-name-with-slug' ), true ) ) {
			$parent = ! ! $category->parent ? get_term( $category->parent, $category->taxonomy ) : false;
			while ( $parent ) {
				$category_name = $parent->name . ' > ' . $category_name;

				$parent = ! ! $parent->parent ? get_term( $parent->parent, $parent->taxonomy ) : false;
			}
		}

		if ( in_array( $name_type, array( 'name-with-slug', 'parent-and-name-with-slug' ), true ) ) {
			$category_name = sprintf( '%s (%s)', $category_name, $category->slug );
		}

		if ( has_filter( 'yith_wcbep_get_slug_info' ) ) {
			/**
			 * Hook for adding something after the category name.
			 *
			 * @deprecated 1.2.28 | use 'yith_wcbep_category_name' instead
			 */
			$slug_info     = apply_filters( 'yith_wcbep_get_slug_info', '', $category );
			$category_name = implode( ' ', array_filter( array( $category_name, $slug_info ) ) );
		}

		/**
		 * APPLY_FILTERS: yith_wcbep_category_name
		 *
		 * Filters the displayed category name.
		 *
		 * @param string  $category_name The category name.
		 * @param WP_Term $category      The category.
		 *
		 * @return string
		 */
		return apply_filters( 'yith_wcbep_category_name', $category_name, $category );
	}
}

if ( ! function_exists( 'yith_wcbep_get_category_names_list' ) ) {
	/**
	 * Retrieve the list of product category names.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 * @since 1.2.37
	 */
	function yith_wcbep_get_category_names_list( $args = array() ) {
		static $categories_cache = array();

		$defaults  = array(
			'hide_empty' => false,
		);
		$args      = wp_parse_args( $args, $defaults );
		$cache_key = md5( wp_json_encode( $args ) );

		if ( empty( $categories_cache[ $cache_key ] ) ) {
			$name_type = get_option( 'yith-wcbep-name-in-categories-dropdown', 'name' );

			$args['taxonomy'] = 'product_cat';
			$args['orderby']  = 'name';
			$args['order']    = 'ASC';

			$terms = yith_wcbep_get_terms( $args );

			if ( is_wp_error( $terms ) ) {
				return array();
			}

			$categories = array();
			foreach ( $terms as $term ) {
				$categories[ $term->term_id ] = array(
					'name'         => $term->name,
					'display_name' => yith_wcbep_get_category_name( $term ),
				);
			}

			if ( 'name' !== $name_type ) {
				uasort(
					$categories,
					function ( $a, $b ) {
						return $a['display_name'] <=> $b['display_name'];
					}
				);
			}

			$categories_cache[ $cache_key ] = $categories;
		}

		return $categories_cache[ $cache_key ];
	}
}

if ( ! function_exists( 'yith_wcbep_get_view' ) ) {
	/**
	 * Print a view
	 *
	 * @param string $view The view.
	 * @param array  $args Arguments.
	 *
	 * @since 1.2.36
	 */
	function yith_wcbep_get_view( $view, $args = array() ) {
		$_view_path = trailingslashit( YITH_WCBEP_VIEWS_PATH ) . $view;
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		if ( file_exists( $_view_path ) ) {
			include $_view_path;
		}
	}
}

if ( ! function_exists( 'yith_wcbep_get_icon' ) ) {
	/**
	 * Print a view
	 *
	 * @param string $icon The icon.
	 * @param bool   $echo Print the Icon.
	 */
	function yith_wcbep_get_icon( $icon, $echo = false ) {
		$icon_path = trailingslashit( YITH_WCBEP_ASSETS_PATH ) . 'icons/' . $icon . '.svg';
		ob_start();
		if ( file_exists( $icon_path ) ) {
			include $icon_path;
		}
		$icon = ob_get_clean();
		if ( $echo ) {
			echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $icon;
	}
}

if ( ! function_exists( 'yith_wcbep_get_view_html' ) ) {
	/**
	 * Get a view HTML
	 *
	 * @param string $view The view.
	 * @param array  $args Arguments.
	 *
	 * @return string
	 * @since 2.0.0
	 */
	function yith_wcbep_get_view_html( $view, $args = array() ): string {
		ob_start();
		yith_wcbep_get_view( $view, $args );

		return ob_get_clean();
	}
}

if ( ! function_exists( 'yith_wcbep_sort_alphabetical_columns' ) ) {
	/**
	 * Sort columns alphabetically according to their labels
	 *
	 * @param array $columns The columns.
	 *
	 * @return void
	 */
	function yith_wcbep_sort_alphabetical_columns( &$columns ) {
		uasort(
			$columns,
			function ( $column_1, $column_2 ) {
				return intval( $column_1['label'] > $column_2['label'] );
			}
		);
	}
}

if ( ! function_exists( 'yith_wcbep_get_table_views_condition_types' ) ) {
	/**
	 * Get table views condition types
	 *
	 * @return array
	 */
	function yith_wcbep_get_table_views_condition_types(): array {
		$condition_types = array(
			'title'              => __( 'Product name', 'yith-woocommerce-bulk-product-editing' ),
			'desc'               => __( 'Description', 'yith-woocommerce-bulk-product-editing' ),
			'sku'                => __( 'SKU', 'yith-woocommerce-bulk-product-editing' ),
			'regular-price'      => __( 'Regular price', 'yith-woocommerce-bulk-product-editing' ),
			'sale-price'         => __( 'Sale price', 'yith-woocommerce-bulk-product-editing' ),
			'stock-quantity'     => __( 'Stock quantity', 'yith-woocommerce-bulk-product-editing' ),
			'weight'             => __( 'Weight', 'yith-woocommerce-bulk-product-editing' ),
			'height'             => __( 'Height', 'yith-woocommerce-bulk-product-editing' ),
			'width'              => __( 'Width', 'yith-woocommerce-bulk-product-editing' ),
			'length'             => __( 'Length', 'yith-woocommerce-bulk-product-editing' ),
			'stock-status'       => __( 'Stock status', 'yith-woocommerce-bulk-product-editing' ),
			'status'             => __( 'Status', 'yith-woocommerce-bulk-product-editing' ),
			'product-visibility' => __( 'Catalog visibility', 'yith-woocommerce-bulk-product-editing' ),
			'backorder'          => __( 'Backorders', 'yith-woocommerce-bulk-product-editing' ),
			'shipping-class'     => __( 'Shipping class', 'yith-woocommerce-bulk-product-editing' ),
			'product-type'       => __( 'Product type', 'yith-woocommerce-bulk-product-editing' ),
			'on-sale'            => __( 'On sale', 'yith-woocommerce-bulk-product-editing' ),
			'image'              => __( 'Image', 'yith-woocommerce-bulk-product-editing' ),
			'featured'           => __( 'Featured', 'yith-woocommerce-bulk-product-editing' ),
			'virtual'            => __( 'Virtual', 'yith-woocommerce-bulk-product-editing' ),
			'downloadable'       => __( 'Downloadable', 'yith-woocommerce-bulk-product-editing' ),
			'tax-status'         => __( 'Tax status', 'yith-woocommerce-bulk-product-editing' ),
			'tax-class'          => __( 'Tax class', 'yith-woocommerce-bulk-product-editing' ),
			'shortdesc'          => __( 'Short Description', 'yith-woocommerce-bulk-product-editing' ),
		);

		$filters_to_show = array_intersect_key(
			array(
				'category'  => __( 'Categories', 'yith-woocommerce-bulk-product-editing' ),
				'tag'       => __( 'Tags', 'yith-woocommerce-bulk-product-editing' ),
				'attribute' => __( 'Attributes', 'yith-woocommerce-bulk-product-editing' ),
			),
			/**
			 * APPLY_FILTERS: yith_wcbep_filters_to_show
			 *
			 * Filters the filters to show/hide.
			 *
			 * @param string[] $filters The filters.
			 *
			 * @return string[]
			 */
			array_flip( apply_filters( 'yith_wcbep_filters_to_show', array( 'category', 'tag', 'attribute' ) ) )
		);

		$condition_types = array_merge( $condition_types, $filters_to_show );

		/**
		 * APPLY_FILTERS: yith_wcbep_table_views_condition_types
		 *
		 * Filters the conditions type.
		 *
		 * @param array $filtered_condition_types The conditions type.
		 *
		 * @return array
		 */
		$filtered_condition_types = apply_filters( 'yith_wcbep_table_views_condition_types', $condition_types );

		return is_array( $filtered_condition_types ) ? $filtered_condition_types : $condition_types;
	}
}

if ( ! function_exists( 'yith_wcbep_get_wc_tax_status_options' ) ) {
	/**
	 * Get WC Tax status Options
	 *
	 * @return array
	 */
	function yith_wcbep_get_wc_tax_status_options(): array {
		/**
		 * APPLY_FILTERS: yith_wcbep_wc_tax_status_options
		 *
		 * Allow to filter the list of tax status options used in Bulk Editing.
		 *
		 * @param string[] $tax_status_options The tax status options.
		 *
		 * @return string[]
		 */
		return apply_filters(
			'yith_wcbep_wc_tax_status_options',
			array(
				'taxable'  => __( 'Taxable', 'woocommerce' ),
				'shipping' => __( 'Shipping only', 'woocommerce' ),
				'none'     => _x( 'None', 'Tax status', 'woocommerce' ),
			)
		);
	}
}

if ( ! function_exists( 'yith_wcbep_get_wc_tax_class_options' ) ) {
	/**
	 * Get WC Tax status Options
	 *
	 * @return array
	 */
	function yith_wcbep_get_wc_tax_class_options(): array {
		$options = array(
			'standard' => __( 'Standard', 'woocommerce' ),
		);

		$tax_classes = WC_Tax::get_tax_classes();

		if ( ! empty( $tax_classes ) ) {
			foreach ( $tax_classes as $class ) {
				$options[ sanitize_title( $class ) ] = esc_html( $class );
			}
		}

		/**
		 * APPLY_FILTERS: yith_wcbep_wc_tax_class_options
		 *
		 * Allow to filter the list of tax class options used in Bulk Editing.
		 *
		 * @param string[] $tax_class_options The tax class options.
		 *
		 * @return string[]
		 */
		return apply_filters( 'yith_wcbep_wc_tax_class_options', $options );
	}
}

if ( ! function_exists( 'yith_wcbep_get_wc_shipping_class_options' ) ) {
	/**
	 * Get WC Shipping status Options
	 *
	 * @return array
	 */
	function yith_wcbep_get_wc_shipping_class_options(): array {
		$options = array(
			'-1' => __( 'No shipping class', 'woocommerce' ),
		);

		$shipping_classes = get_terms(
			array(
				'taxonomy'   => 'product_shipping_class',
				'hide_empty' => false,
			)
		);

		$options = array_replace( $options, array_combine( array_column( $shipping_classes, 'term_id' ), array_column( $shipping_classes, 'name' ) ) );

		/**
		 * APPLY_FILTERS: yith_wcbep_wc_shipping_class_options
		 *
		 * Allow to filter the list of shipping class options used in Bulk Editing.
		 *
		 * @param string[] $options the shipping class options.
		 *
		 * @return string[]
		 */
		return apply_filters( 'yith_wcbep_wc_shipping_class_options', $options );
	}
}

if ( ! function_exists( 'yith_wcbep_get_custom_fields_types_options' ) ) {
	/**
	 * Get the custom fields types options
	 *
	 * @return array
	 */
	function yith_wcbep_get_custom_fields_types_options() {
		$types = array(
			'text'  => _x( 'Text', '[Enable fields popup] Add custom fields - field option', 'yith-woocommerce-bulk-product-editing' ),
			'price' => _x( 'Price', '[Enable fields popup] Add custom fields - field option', 'yith-woocommerce-bulk-product-editing' ),
		);

		/**
		 * APPLY_FILTERS: yith_wcbep_custom_fields_types_options
		 *
		 * Allow to filter the list of Custom Fields types options.
		 *
		 * @param string[] $types The fields type options.
		 *
		 * @return string[]
		 */
		return apply_filters( 'yith_wcbep_custom_fields_types_options', $types );
	}
}

if ( ! function_exists( 'yith_wcbep_get_product_statuses' ) ) {
	/**
	 * Get the product post statuses
	 *
	 * @return array
	 */
	function yith_wcbep_get_product_statuses() {
		$statuses = array_merge(
			get_post_statuses(),
			array( 'future' => __( 'Scheduled', 'yith-woocommerce-bulk-product-editing' ) )
		);

		/**
		 * APPLY_FILTERS: yith_wcbep_product_statuses
		 *
		 * Allow to filter the product statuses used in bulk editing.
		 *
		 * @param string[] $statuses The product statuses options.
		 *
		 * @return string[]
		 */
		return apply_filters( 'yith_wcbep_product_statuses', $statuses );
	}
}

if ( ! function_exists( 'yith_wcbep_get_enable_fields_add_custom_column_fields' ) ) {
	/**
	 * Get add custom fields to use in enable fields modal
	 *
	 * @return array[]
	 */
	function yith_wcbep_get_enable_fields_add_custom_column_fields(): array {
		return array(
			'kind'               => array(
				'title'     => _x( 'Add', '[Enable fields popup] Add custom fields - field label', 'yith-woocommerce-bulk-product-editing' ),
				'type'      => 'select',
				'class'     => 'wc-enhanced-select',
				'row_class' => 'yith-wcbep-add-custom-fields__kind-select-row',
				'id'        => 'yith-wcbep-custom-column-kind',
				'options'   => array(
					'custom-field' => _x( 'Custom field', '[Enable fields popup] Add custom fields - field option', 'yith-woocommerce-bulk-product-editing' ),
					'taxonomy'     => _x( 'Taxonomy', '[Enable fields popup] Add custom fields - field option', 'yith-woocommerce-bulk-product-editing' ),
				),
			),
			'custom-field-id'    => array(
				'title'             => _x( 'Custom field', '[Enable fields popup] Add custom fields type label', 'yith-woocommerce-bulk-product-editing' ),
				'id'                => 'yith-wcbep-custom-field-id',
				'row_class'         => 'yith-wcbep-add-custom-fields__show-if-custom-field',
				'type'              => 'text',
				'custom_attributes' => array(
					'required' => true,
				),
			),
			'custom-field-type'  => array(
				'title'     => _x( 'Type', '[Enable fields popup] Add custom fields - field label', 'yith-woocommerce-bulk-product-editing' ),
				'type'      => 'select',
				'class'     => 'wc-enhanced-select',
				'row_class' => 'yith-wcbep-add-custom-fields__show-if-custom-field',
				'id'        => 'yith-wcbep-custom-field-type',
				'options'   => yith_wcbep_get_custom_fields_types_options(),
			),
			'custom-field-label' => array(
				'title'     => _x( 'Label', '[Enable fields popup] Add custom fields type label', 'yith-woocommerce-bulk-product-editing' ),
				'id'        => 'yith-wcbep-custom-field-label',
				'row_class' => 'yith-wcbep-add-custom-fields__show-if-custom-field',
				'type'      => 'text',
			),
			'taxonomy'           => array(
				'title'     => _x( 'Taxonomy', '[Enable fields popup] Add custom fields type label', 'yith-woocommerce-bulk-product-editing' ),
				'id'        => 'yith-wcbep-taxonomy-field',
				'row_class' => 'yith-wcbep-add-custom-fields__show-if-taxonomy',
				'class'     => 'wc-enhanced-select',
				'type'      => 'select',
				'options'   => YITH_WCBEP_Custom_Taxonomies_Manager::get_all_extra_taxonomies_options(),
				'data'      => array(
					'placeholder' => __( 'Search for taxonomies...', 'yith-woocommerce-bulk-product-editing' ),
				),
			),
		);
	}
}

if ( ! function_exists( 'yith_wcbep_is_light_query_mode_enabled' ) ) {
	/**
	 * Check if light query mode is enabled
	 *
	 * @return bool
	 */
	function yith_wcbep_is_light_query_mode_enabled() {
		return wc_string_to_bool( get_option( 'yith-wcbep-use-light-query', 'no' ) );
	}
}

if ( ! function_exists( 'yith_wcbep_is_json' ) ) {
	/**
	 * Check if the given string is a JSON object
	 *
	 * @param string $string String to test.
	 *
	 * @return bool
	 */
	function yith_wcbep_is_json( $string ) {
		json_decode( $string );

		return json_last_error() === JSON_ERROR_NONE;
	}
}
