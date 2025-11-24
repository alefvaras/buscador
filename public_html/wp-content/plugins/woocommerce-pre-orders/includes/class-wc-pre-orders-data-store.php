<?php
/**
 * WooCommerce Pre-Orders
 *
 * @package   WC_Pre_Orders/Data_Store
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pre-Orders Data Store class.
 *
 * Handle data retrieve for pre-orders and related entities.
 *
 * @since 2.3.0
 */
class WC_Pre_Orders_Data_Store {

	/**
	 * Valid pre-order status.
	 *
	 * @var array
	 */
	const VALID_STATUSES = array( 'active', 'completed', 'cancelled' );

	/**
	 * Cache expiration time
	 *
	 * This can be done as the cache is cleared when an order is created, updated, or deleted.
	 *
	 * @var int
	 */
	const CACHE_EXPIRATION = HOUR_IN_SECONDS;

	/**
	 * All pre-orders cache.
	 *
	 * @var array|null
	 */
	private static $all_pre_orders = null;

	/**
	 * Pre-orders status counts cache.
	 *
	 * @var array|null
	 */
	private static $status_counts = null;

	/**
	 * Pre-order products cache.
	 *
	 * @var array|null
	 */
	private static $pre_order_products = null;

	/**
	 * Pre-order customers cache.
	 *
	 * @var array|null
	 */
	private static $pre_order_customers = null;

	/**
	 * Admin dropdown data cache.
	 *
	 * @var array|null
	 */
	private static $admin_dropdown_data = null;

	/**
	 * Get base query arguments for pre-orders.
	 *
	 * @since 2.3.0
	 * @return array Query arguments.
	 */
	public static function get_pre_orders_base_args(): array {
		$base         = array();
		$base['type'] = 'shop_order';
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- exists hits an index.
		if ( WC_Pre_Orders::is_hpos_enabled() ) {
			$base['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_wc_pre_orders_is_pre_order',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_wc_pre_orders_status',
					'value'   => self::VALID_STATUSES,
					'compare' => 'IN',
				),
			);

			return $base;
		}

		// non-HPOS: avoid meta_query, use meta_key / value instead.
		$base['post_type'] = 'shop_order';
		$base['meta_key']  = '_wc_pre_orders_is_pre_order'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		$base['status']    = array_keys( wc_get_order_statuses() );

		return $base;
	}

	/**
	 * Yielding all pre-orders one by one.
	 *
	 * Keep only IDs in the memory - each order object is hydratated lazily
	 * and released at next loop.
	 *
	 * @since 2.3.0
	 * @return \Generator<WC_Order>
	 */
	public static function get_pre_orders(): \Generator {
		// Cache only the ids – a few MB even on huge stores.
		static $ids = null;

		if ( null === $ids ) {
			$args_for_query = self::get_pre_orders_base_args() + array(
				'return' => 'ids',
			);

			if ( ! WC_Pre_Orders::is_hpos_enabled() ) {
				unset( $args_for_query['meta_query'] );
				$args_for_query['type']      = 'shop_order';
				$args_for_query['post_type'] = 'shop_order';
				$args_for_query['meta_key']  = '_wc_pre_orders_is_pre_order'; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$args_for_query['status']    = array_keys( wc_get_order_statuses() );
			}

			$ids = wc_get_orders( $args_for_query );
			if ( ! is_array( $ids ) ) {
				$ids = array();
			}

			if ( ! WC_Pre_Orders::is_hpos_enabled() && ! empty( $ids ) ) {
				$filtered_ids = array();
				foreach ( $ids as $oid ) {
					$status_meta = get_post_meta( $oid, '_wc_pre_orders_status', true );
					if ( in_array( $status_meta, self::VALID_STATUSES, true ) ) {
						$filtered_ids[] = $oid;
					}
				}
				$ids = $filtered_ids;
			}
		}

		foreach ( $ids as $order_id ) {
			yield wc_get_order( $order_id ); // lazy hydration
		}
	}

	/**
	 * Get pre-orders count.
	 *
	 * @since 2.3.0
	 * @return int Number of pre-orders.
	 */
	public static function get_pre_orders_count(): int {
		return array_sum( self::get_pre_orders_status_counts() );
	}

	/**
	 * Get pre-orders status counts
	 *
	 * @since 2.3.0
	 * @return array Status counts array.
	 */
	public static function get_pre_orders_status_counts(): array {
		$engine       = WC_Pre_Orders::is_hpos_enabled() ? 'hpos' : 'legacy';
		$cached_entry = get_transient( 'wc_pre_orders_status_counts' );
		if ( false !== $cached_entry && is_array( $cached_entry ) ) {
			if ( isset( $cached_entry['engine'], $cached_entry['value'] ) && is_array( $cached_entry['value'] ) && $cached_entry['engine'] === $engine ) {
				return $cached_entry['value'];
			}
		}

		$counts = self::calculate_status_counts();
		set_transient(
			'wc_pre_orders_status_counts',
			array(
				'engine' => $engine,
				'value'  => $counts,
			),
			self::CACHE_EXPIRATION
		);
		return $counts;
	}

	/**
	 * Calculate status counts from pre-orders.
	 *
	 * @since 2.3.0
	 * @return array Status counts.
	 */
	private static function calculate_status_counts(): array {
		$counts = array();

		foreach ( self::VALID_STATUSES as $status ) {
			$status_count = wc_get_orders(
				array(
					'return'     => 'ids',
					'limit'      => 1,
					'paginate'   => true,
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- unavoidable due to how status is stored.
					'meta_key'   => '_wc_pre_orders_status',
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- unavoidable due to how status is stored.
					'meta_value' => $status,
				)
			);

			$counts[ $status ] = $status_count->total;
		}

		return $counts;
	}

	/**
	 * Get pre-order status from order.
	 *
	 * @since 2.3.0
	 * @param WC_Order $order Order object.
	 * @return string|null Status or null if invalid.
	 */
	private static function get_order_pre_order_status( $order ): ?string {
		if ( ! $order instanceof WC_Order ) {
			return null;
		}

		$status = $order->get_meta( '_wc_pre_orders_status' );
		return in_array( $status, self::VALID_STATUSES, true ) ? $status : null;
	}

	/**
	 * Get products with pre-orders and their data.
	 *
	 * @since 2.3.0
	 * @return array Products data indexed by product ID.
	 */
	public static function get_pre_order_products(): array {
		$cached_products = get_transient( 'wc_pre_orders_products' );
		if ( false !== $cached_products && is_array( $cached_products ) ) {
			self::$pre_order_products = $cached_products;
			return $cached_products;
		}

		$product_ids = get_posts(
			array(
				'fields'      => 'ids',
				'nopaging'    => true,
				'post_status' => 'publish',
				'post_type'   => 'product',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- required due to how enabled status is stored.
				'meta_query'  => array(
					'relation' => 'AND',
					array(
						'key'   => '_wc_pre_orders_enabled',
						'value' => 'yes',
					),
				),
			)
		);

		$products = array();

		foreach ( $product_ids as $product_id ) {
			if ( ! WC_Pre_Orders_Product::product_has_active_pre_orders( $product_id ) ) {
				continue;
			}

			$products[ $product_id ] = array(
				'id'                    => $product_id,
				'label'                 => get_the_title( $product_id ),
				'availability_datetime' => (int) get_post_meta( $product_id, '_wc_pre_orders_availability_datetime', true ),
				'pre_order_status'      => array_fill_keys( self::VALID_STATUSES, 0 ),
			);
		}

		// Sort alphabetically
		uasort(
			$products,
			function ( $a, $b ) {
				return strcmp( $a['label'], $b['label'] );
			}
		);

		self::$pre_order_products = $products;
		set_transient( 'wc_pre_orders_products', $products, self::CACHE_EXPIRATION );
		return $products;
	}

	/**
	 * Get single product data.
	 *
	 * @since 2.3.0
	 * @return array Products data indexed by product ID.
	 */
	public static function get_pre_order_product( $product_id ): array {
		if ( null !== self::$pre_order_products ) {
			return self::$pre_order_products[ $product_id ];
		}

		$products = self::get_pre_order_products();
		return $products[ $product_id ];
	}

	/**
	 * Get pre-order product status counts for specific product.
	 *
	 * @since 2.3.0
	 * @param int $product_id Product ID.
	 * @return array Status counts for the product.
	 */
	public static function get_pre_order_product_status_counts( $product_id ): array {
		$products = self::get_pre_order_products();
		return $products[ $product_id ]['pre_order_status'] ?? array();
	}

	/**
	 * Get pre-order products count.
	 *
	 * @since 2.3.0
	 * @return int Number of products with pre-orders.
	 */
	public static function get_pre_order_products_count(): int {
		$cached_count = get_transient( 'wc_pre_orders_products_count' );
		if ( false !== $cached_count ) {
			return (int) $cached_count;
		}

		// Calculate count and cache it
		$count = count( self::get_pre_order_products() );
		set_transient( 'wc_pre_orders_products_count', $count, self::CACHE_EXPIRATION );

		return $count;
	}

	/**
	 * Get count of all products with pre-orders enabled, including those with zero pre-orders.
	 *
	 * @since 2.3.0
	 * @return int Number of products with pre-orders enabled.
	 */
	public static function get_products_enabled_for_pre_orders_count(): int {
		$cached_count = get_transient( 'wc_pre_orders_products_enabled_count' );
		if ( false !== $cached_count ) {
			return (int) $cached_count;
		}

		// Calculate count and cache it
		$products = self::get_products_enabled_for_pre_orders();
		$count    = count( $products );
		set_transient( 'wc_pre_orders_products_enabled_count', $count, self::CACHE_EXPIRATION );

		return $count;
	}

	/**
	 * Get all products with pre orders enabled.
	 *
	 * @since 2.3.0
	 * @return array Products data indexed by product ID.
	 */
	public static function get_products_enabled_for_pre_orders(): array {
		$products_with_orders = self::get_pre_order_products();
		$all_products         = array();

		// Get all products that have pre-orders enabled
		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_wc_pre_orders_enabled',
					'value'   => 'yes',
					'compare' => '=',
				),
			),
		);

		$query = new WP_Query( $args );

		foreach ( $query->posts as $post ) {
			$product_id = $post->ID;
			$product    = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			// If product already exist in products_with_orders, reuse data
			if ( isset( $products_with_orders[ $product_id ] ) ) {
				$all_products[ $product_id ] = $products_with_orders[ $product_id ];
			} else {
				// Create entry for product with zero pre-orders
				$all_products[ $product_id ] = array(
					'id'                    => $product_id,
					'label'                 => $product->get_name(),
					'availability_datetime' => (int) get_post_meta( $product_id, '_wc_pre_orders_availability_datetime', true ),
					'pre_orders_count'      => 0,
					'pre_order_status'      => array_fill_keys( self::VALID_STATUSES, 0 ),
					'orders'                => array(
						'ids' => array(),
					),
				);
			}
		}

		// Sort alphabetically
		uasort(
			$all_products,
			function ( $a, $b ) {
				return strcmp( $a['label'], $b['label'] );
			}
		);

		return $all_products;
	}

	/**
	 * Get customers with pre-orders, sorted alphabetically.
	 *
	 * @since 2.3.0
	 * @return array Customer emails indexed by email with names as values.
	 */
	public static function get_pre_order_customers(): array {
		if ( null !== self::$pre_order_customers ) {
			return self::$pre_order_customers;
		}

		$cached_customers = get_transient( 'wc_pre_orders_customers' );
		if ( false !== $cached_customers && is_array( $cached_customers ) ) {
			self::$pre_order_customers = $cached_customers;
			return $cached_customers;
		}

		self::$pre_order_customers = self::build_customers_data();
		set_transient( 'wc_pre_orders_customers', self::$pre_order_customers, self::CACHE_EXPIRATION );

		return self::$pre_order_customers;
	}

	/**
	 * Build customers data from orders.
	 *
	 * @since 2.3.0
	 * @return array Customers data.
	 */
	private static function build_customers_data(): array {
		try {
			$customers = array();
			foreach ( self::get_pre_orders() as $order ) {
				if ( ! $order instanceof WC_Order ) {
					continue;
				}

				$billing_email = $order->get_billing_email();
				$customer_name = WC_Pre_Orders_Order::get_customer_name( $order );
				if ( ! empty( $customer_name ) && ! isset( $customers[ $billing_email ] ) ) {
					$customers[ $billing_email ] = $customer_name;
				}
			}

			asort( $customers, SORT_NATURAL | SORT_FLAG_CASE );
			return $customers;

		} catch ( Exception $e ) {
			return array();
		}
	}

	/**
	 * Clear all pre-orders data store static properties.
	 *
	 * @since 2.3.0
	 */
	public static function clear_caches() {
		// Static
		self::$all_pre_orders      = null;
		self::$status_counts       = null;
		self::$pre_order_products  = null;
		self::$pre_order_customers = null;
		self::$admin_dropdown_data = null;

		// Transients
		delete_transient( 'wc_pre_orders_count' );
		delete_transient( 'wc_pre_orders_products_count' );
		delete_transient( 'wc_pre_orders_products' );
		delete_transient( 'wc_pre_orders_status_counts' );
		delete_transient( 'wc_pre_orders_customers' );
		delete_transient( 'wc_pre_orders_products_enabled_count' );
	}

	/**
	 * Get dropdown data for admin filters.
	 *
	 * @since 2.3.0
	 * @return array Array with 'products' and 'customers' keys.
	 */
	public static function get_admin_dropdowns_data(): array {
		if ( null !== self::$admin_dropdown_data ) {
			return self::$admin_dropdown_data;
		}

		self::$admin_dropdown_data = array(
			'products'  => self::get_pre_order_products(),
			'customers' => self::get_pre_order_customers(),
		);

		return self::$admin_dropdown_data;
	}
}
