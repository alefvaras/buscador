<?php
/**
 * Multi Currency Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Compatibility\MultiCurrency
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( ! class_exists( 'YITH_WCBEP_Multi_Currency_Compatibility' ) ) {
	/**
	 * Multi Currency Compatibility Class
	 *
	 * @since   2.0
	 */
	class YITH_WCBEP_Multi_Currency_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WCBEP_Multi_Currency_Compatibility
		 */
		protected static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WCBEP_Multi_Currency_Compatibility
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		protected function __construct() {
			add_filter( 'yith_wcbep_default_columns', array( $this, 'add_multi_currencies_columns' ) );
			add_filter( 'yith_wcbep_manage_custom_columns', array( $this, 'manage_multi_currency_column' ), 10, 3 );

			add_filter( 'yith_wcbep_custom_prop_save_handler', array( $this, 'save_prices_on_product' ), 10, 4 );
			add_filter( 'yith_wcbep_list_table_get_column_type', array( $this, 'get_column_type' ), 10, 2 );
			add_filter( 'yith_wcbep_floating_editing_fields', array( $this, 'add_multi_currency_price_floating_editing_field' ) );
			add_filter( 'yith_wcbep_render_floating_editing_field', array( $this, 'render_multi_currency_floating_editing_field' ) );
			add_action( 'yith_wcbep_bulk_editing_field_multi_currency_price', array( $this, 'render_multi_currency_bulk_editing_field' ), 10, 3 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Add multi currency prices columns
		 *
		 * @param array $columns The columns.
		 *
		 * @return array
		 */
		public function add_multi_currencies_columns( $columns ) {
			$regular_price_index = array_search( 'regular_price', array_keys( $columns ), true );

			$enable_multi_currency = array(
				'yith_wcmcs_custom_prices' => array(
					'id'    => 'yith_wcmcs_custom_prices',
					'label' => __( 'Multi Currency' ),
					'kind'  => 'simple',
				),
			);

			return array_merge( array_slice( $columns, 0, $regular_price_index ), $enable_multi_currency, array_slice( $columns, $regular_price_index ) );
		}

		/**
		 * Manage multi currency column
		 *
		 * @param string  $cell        The cell content.
		 * @param string  $column_name The column name.
		 * @param WP_Post $post        The post object.
		 *
		 * @return string
		 */
		public function manage_multi_currency_column( string $cell, string $column_name, WP_Post $post ): string {
			switch ( $column_name ) {
				case 'sale_price':
				case 'regular_price':
					$product          = wc_get_product( $post );
					$currencies       = yith_wcmcs_get_currencies();
					$currency_symbols = get_woocommerce_currency_symbols();

					$prices = array();

					foreach ( $currencies as $currency ) {
						$price = '';
						if ( $currency->is_default() ) {
							$price = $product->{"get_$column_name"}( 'edit' );
						} else {
							$price = $product->get_meta( "_ywcmcs_{$column_name}_" . $currency->get_id() );
						}
						$display_price                 = '<span class="yith-wcbep-multi-currency-price-amount">' . ( $price ? number_format( $price, $currency->get_decimals(), $currency->get_decimal_separator(), $currency->get_thousand_separator() ) : '' ) . '</span>';
						$display_currency              = '<span class="yith-wcbep-multi-currency-price-currency-symbol">' . $currency_symbols[ $currency->get_id() ] . '</span>';
						$prices[ $currency->get_id() ] = array(
							'value'   => $price,
							'display' => '<div class="yith-wcbep-multi-currency-price-' . $currency->get_id() . ' ' . ( $price ? '' : 'hidden' ) . '">' . sprintf( $currency->get_price_format(), $display_currency, $display_price ) . '</div>',
						);
					}

					if ( $product ) {
						$value = array_filter( array_combine( array_keys( $prices ), array_column( $prices, 'value' ) ) );
						$cell  = YITH_WCBEP_List_Table::render_column(
							array(
								'value'   => $value ? $value : '{}',
								'display' => implode( '', array_column( $prices, 'display' ) ),
							)
						);
					}
					break;
				case 'yith_wcmcs_custom_prices':
					$product = wc_get_product( $post );
					$enabled = $product->get_meta( '_yith_wcmcs_custom_prices' );
					$cell    = YITH_WCBEP_List_Table::render_column(
						array(
							'value'   => $enabled,
							'display' => yith_plugin_fw_get_field(
								array(
									'type'  => 'onoff',
									'value' => $enabled,
								),
								false,
								false
							),
						)
					);
					break;
			}

			return $cell;
		}

		/**
		 * Get the column type when is a Multi Currency field
		 *
		 * @param string $type The column type.
		 * @param string $name The column name.
		 *
		 * @return string
		 */
		public function get_column_type( string $type, string $name ): string {
			$columns_types = array(
				'yith_wcmcs_custom_prices' => 'onoff',
				'regular_price'            => 'multi_currency_price',
				'sale_price'               => 'multi_currency_price',
			);

			return $columns_types[ $name ] ?? $type;
		}

		/**
		 * Add multi currency price field in floating editing fields list.
		 *
		 * @param array $fields The floating editing fields list.
		 *
		 * @return array
		 */
		public function add_multi_currency_price_floating_editing_field( array $fields ): array {
			$fields[] = 'multi_currency_price';

			return $fields;
		}

		/**
		 * Render multi currency floating editing field.
		 *
		 * @param string $field_id The field ID.
		 *
		 * @return void
		 */
		public function render_multi_currency_floating_editing_field( $field_id ) {
			if ( 'multi_currency_price' === $field_id && file_exists( __DIR__ . '/templates/multi-currency-price-floating-editing-field.php' ) ) {
				$currencies       = yith_wcmcs_get_currencies();
				$currency_symbols = get_woocommerce_currency_symbols();
				include __DIR__ . '/templates/multi-currency-price-floating-editing-field.php';
			}
		}

		/**
		 * Render multi currency price bulk editing field.
		 *
		 * @param string $id    The field ID.
		 * @param string $label The field label.
		 * @param array  $args  The field args.
		 *
		 * @return void
		 */
		public function render_multi_currency_bulk_editing_field( $id, $label, $args ) {
			$type = 'price';
			yith_wcbep_get_view( 'bulk-editing-fields/' . $type . '.php', compact( 'id', 'label', 'type', 'args' ) );
		}

		/**
		 * Save prices on product
		 *
		 * @param false      $return  To avoid the skip of the savings.
		 * @param WC_Product $product The product.
		 * @param string     $prop    The property.
		 * @param string     $value   The JSON array containing the prices in different currencies.
		 *
		 * @return WC_Product
		 */
		public function save_prices_on_product( $return, $product, $prop, $value ) {
			switch ( $prop ) {
				case 'sale_price':
				case 'regular_price':
					$value      = $value ? json_decode( $value, true ) : array();
					$currencies = yith_wcmcs_get_currencies();
					foreach ( $currencies as $currency ) {
						$price = array_key_exists( $currency->get_id(), $value ) ? wc_format_decimal( $value[ $currency->get_id() ] ) : '';
						if ( $currency->is_default() ) {
							$product->{"set_$prop"}( $price );
						} else {
							$product->update_meta_data( "_ywcmcs_{$prop}_{$currency->get_id()}", $price );
						}
					}
					$return = $product;
					break;
				case 'yith_wcmcs_custom_prices':
					$product->update_meta_data( '_yith_wcmcs_custom_prices', wc_bool_to_string( 'yes' === $value ) );
					$return = $product;
					break;
			}

			return $return;
		}

		/**
		 * Enqueue scripts
		 *
		 * @return void
		 */
		public function enqueue_scripts() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_register_script( 'yith_wcbep_multi_currency_integration', plugin_dir_url( __FILE__ ) . 'assets/js/multi-currency-integration' . $suffix . '.js', array( 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );

			if ( yith_wcbep_admin()->is_panel( 'bulk-edit' ) ) {
				wp_enqueue_script( 'yith_wcbep_multi_currency_integration' );
			}

			wp_localize_script(
				'yith_wcbep_multi_currency_integration',
				'yithWcbepMultiCurrency',
				array(
					'defaultCurrency' => yith_wcmcs_get_wc_currency_options( 'currency' ),
					'currencies'      => array_map(
						function ( $currency ) {
							return array(
								'position'          => $currency->get_position(),
								'decimalSeparator'  => $currency->get_decimal_separator(),
								'thousandSeparator' => $currency->get_thousand_separator(),
								'decimals'          => $currency->get_decimals(),
							);
						},
						yith_wcmcs_get_currencies()
					),
				)
			);
		}

	}

	/**
	 * Unique access to instance of YITH_WCBEP_Multi_Currency_Compatibility class
	 *
	 * @return YITH_WCBEP_Multi_Currency_Compatibility
	 * @since 2.0.0
	 */
	function yith_wcbep_multi_currency_compatibility() {
		return YITH_WCBEP_Multi_Currency_Compatibility::get_instance();
	}
}

