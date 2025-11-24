<?php
/**
 * Deposits Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Compatibility
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

if ( file_exists( __DIR__ . '/class.yith-wcbep-deposits-compatibility-legacy.php' ) ) {
	include_once __DIR__ . '/class.yith-wcbep-deposits-compatibility-legacy.php';
}

/**
 * Deposits Compatibility Class
 *
 * @since   1.1.2
 */
class YITH_WCBEP_Deposits_Compatibility extends YITH_WCBEP_Deposits_Compatibility_Legacy {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCBEP_Deposits_Compatibility
	 */
	protected static $instance;

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $floating_fields = array(
		'enable-deposit',
		'force-deposit',
		'deposit-default',
		'create-balance-orders',
	);

	/**
	 * Fields ID to meta
	 *
	 * @var array
	 */
	protected $fields_id_to_meta = array(
		'yith_wcdp_enable_deposit'           => '_enable_deposit',
		'yith_wcdp_force_deposit'            => '_force_deposit',
		'yith_wcdp_deposit_default'          => '_deposit_default',
		'yith_wcdp_create_balance_orders'    => '_create_balance_orders',
		'yith_wcdp_custom_notice'            => '_product_note',
		'yith_wcdp_override_deposit_options' => '_override_deposit_options',
		'yith_wcdp_show_custom_notice'       => '_show_product_notes',
		'yith_wcdp_override_balance_options' => '_override_balance_options',
	);

	/**
	 * Returns single instance of the class
	 *
	 * @return self|YITH_WCBEP_Deposits_Compatibility_Legacy
	 */
	public static function get_instance() {
		if ( class_exists( 'YITH_WCDP' ) ) {
			$version = defined( 'YITH_WCDP::VERSION' ) ? YITH_WCDP::VERSION : ( defined( 'YITH_WCDP::YITH_WCDP_VERSION' ) ? YITH_WCDP::YITH_WCDP_VERSION : false );

			if ( $version && version_compare( $version, '2.0.0', '<' ) ) {
				return YITH_WCBEP_Deposits_Compatibility_Legacy::get_instance();
			}
		}

		return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Add deposit columns
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 */
	public function add_deposit_columns( $columns ) {
		$deposit_columns = array(
			'yith_wcdp_override_deposit_options' => array(
				'id'    => 'yith_wcdp_override_balance_options',
				'label' => __( 'Override deposit options', 'yith-woocommerce-bulk-product-editing' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_enable_deposit'           => array(
				'id'    => 'yith_wcdp_enable_deposit',
				'label' => __( 'Enable deposit', 'yith-woocommerce-bulk-product-editing' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_force_deposit'            => array(
				'id'    => 'yith_wcdp_force_deposit',
				'label' => __( 'Force deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_deposit_default'          => array(
				'id'    => 'yith_wcdp_deposit_default',
				'label' => __( 'Deposit by default', 'yith-woocommerce-bulk-product-editing' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_show_custom_notice'       => array(
				'id'    => 'yith_wcdp_show_custom_notice',
				'label' => __( 'Show custom notice', 'yith-woocommerce-bulk-product-editing' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_custom_notice'            => array(
				'id'    => 'yith_wcdp_custom_notice',
				'label' => __( 'Custom notice', 'yith-woocommerce-bulk-product-editing' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_override_balance_options' => array(
				'id'    => 'yith_wcdp_override_balance_options',
				'label' => __( 'Override balance options', 'yith-woocommerce-bulk-product-editing' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_create_balance_orders'    => array(
				'id'    => 'yith_wcdp_create_balance_orders',
				'label' => __( 'Create balance orders', 'yith-woocommerce-deposits-and-down-payments' ),
				'kind'  => 'simple',
			),
		);

		return array_merge( $columns, $deposit_columns );
	}


	/**
	 * Manage badge column
	 *
	 * @param string  $cell        The cell content.
	 * @param string  $column_name The column name.
	 * @param WP_Post $post        The post object.
	 *
	 * @return string
	 */
	public function manage_deposit_column( string $cell, string $column_name, WP_Post $post ): string {
		$cell = parent::manage_deposit_column( $cell, $column_name, $post );
		switch ( $column_name ) {
			case 'yith_wcdp_custom_notice':
				$product = wc_get_product( $post );
				if ( $product ) {
					$value = $product->get_meta( $this->fields_id_to_meta[ $column_name ] );
					$cell  = YITH_WCBEP_List_Table::render_column(
						array(
							'value'   => $value,
							'display' => '' !== $value ? '<div class="yith-wcbep-text-editor-content">' . $value . '</div>' : '',
						)
					);
				}
				break;
			case 'yith_wcdp_override_deposit_options':
			case 'yith_wcdp_show_custom_notice':
			case 'yith_wcdp_override_balance_options':
				$product = wc_get_product( $post );
				if ( $product ) {
					$value = $product->get_meta( $this->fields_id_to_meta[ $column_name ] );
					$value = wc_bool_to_string( 'yes' === $value );
					$cell  = YITH_WCBEP_List_Table::render_column(
						array(
							'value'   => $value,
							'display' => yith_plugin_fw_get_field(
								array(
									'type'  => 'onoff',
									'value' => $value,
								),
								false,
								false
							),
						)
					);
				}
				break;
		}

		return $cell;
	}

	/**
	 * Get the column type when is a Deposit Field
	 *
	 * @param string $type The column type.
	 * @param string $name The column name.
	 *
	 * @return string
	 */
	public function get_column_type( string $type, string $name ): string {
		$deposits_columns_types = array(
			'yith_wcdp_override_deposit_options' => 'onoff',
			'yith_wcdp_enable_deposit'           => 'enable-deposit',
			'yith_wcdp_force_deposit'            => 'force-deposit',
			'yith_wcdp_show_custom_notice'       => 'onoff',
			'yith_wcdp_custom_notice'            => 'text-editor',
			'yith_wcdp_override_balance_options' => 'onoff',
			'yith_wcdp_create_balance_orders'    => 'create-balance-orders',

		);

		return $deposits_columns_types[ $name ] ?? $type;
	}
}

if ( ! function_exists( 'yith_wcbep_deposits_compatibility' ) ) {
	/**
	 * Unique access to instance of YITH_WCBEP_Deposits_Compatibility class
	 *
	 * @return YITH_WCBEP_Deposits_Compatibility|YITH_WCBEP_Deposits_Compatibility_Legacy
	 * @since 1.0.11
	 */
	function yith_wcbep_deposits_compatibility() {
		return YITH_WCBEP_Deposits_Compatibility::get_instance();
	}
}
