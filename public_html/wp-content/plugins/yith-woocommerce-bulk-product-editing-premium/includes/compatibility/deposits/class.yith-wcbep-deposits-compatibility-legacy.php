<?php
/**
 * Deposits Compatibility Legacy Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Compatibility
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

/**
 * Deposits Compatibility Class
 *
 * @since   1.1.2
 */
class YITH_WCBEP_Deposits_Compatibility_Legacy {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCBEP_Deposits_Compatibility_Legacy
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
		'yith_wcdp_enable_deposit'        => '_enable_deposit',
		'yith_wcdp_force_deposit'         => '_force_deposit',
		'yith_wcdp_deposit_default'       => '_deposit_default',
		'yith_wcdp_create_balance_orders' => '_create_balance_orders',
	);

	/**
	 * Returns single instance of the class
	 *
	 * @return YITH_WCBEP_Deposits_Compatibility_Legacy
	 */
	public static function get_instance() {
		return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		// Columns add and render.
		add_filter( 'yith_wcbep_default_columns', array( $this, 'add_deposit_columns' ) );
		add_filter( 'yith_wcbep_manage_custom_columns', array( $this, 'manage_deposit_column' ), 10, 3 );

		// Floating editing fields.
		add_filter( 'yith_wcbep_floating_editing_fields', array( $this, 'add_deposit_floating_editing_fields' ) );
		add_filter( 'yith_wcbep_render_floating_editing_field', array( $this, 'render_deposit_floating_editing_fields' ) );

		add_filter( 'yith_wcbep_list_table_get_column_type', array( $this, 'get_column_type' ), 10, 2 );
		add_filter( 'yith_wcbep_save_product_prop_changes', array( $this, 'save_deposits_meta_on_product' ), 10, 3 );

		// Bulk editing fields.
		add_filter( 'yith_wcbep_bulk_editing_modal_general_tab_fields', array( $this, 'add_deposit_fields_in_bulk_editing_modal' ), 10 );
		add_action( 'yith_wcbep_bulk_editing_field_enable-deposit', array( $this, 'render_enable_deposit_bulk_editing_field' ) );
		add_action( 'yith_wcbep_bulk_editing_field_force-deposit', array( $this, 'render_enable_deposit_bulk_editing_field' ) );
		add_action( 'yith_wcbep_bulk_editing_field_deposit-default', array( $this, 'render_enable_deposit_bulk_editing_field' ) );
		add_action( 'yith_wcbep_bulk_editing_field_create-balance-orders', array( $this, 'render_enable_deposit_bulk_editing_field' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
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
			'yith_wcdp_enable_deposit'        => array(
				'id'    => 'yith_wcdp_enable_deposit',
				'label' => __( 'Enable deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_force_deposit'         => array(
				'id'    => 'yith_wcdp_force_deposit',
				'label' => __( 'Force deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_deposit_default'       => array(
				'id'    => 'yith_wcdp_deposit_default',
				'label' => __( 'Deposit checked', 'yith-woocommerce-deposits-and-down-payments' ),
				'kind'  => 'simple',
			),
			'yith_wcdp_create_balance_orders' => array(
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
		switch ( $column_name ) {
			case 'yith_wcdp_enable_deposit':
			case 'yith_wcdp_force_deposit':
			case 'yith_wcdp_deposit_default':
			case 'yith_wcdp_create_balance_orders':
				$values = $this->get_field_options( $this->get_column_type( '', $column_name ) );

				$product = wc_get_product( $post );
				if ( $product && array_key_exists( $column_name, $this->fields_id_to_meta ) ) {
					$value = $product->get_meta( $this->fields_id_to_meta[ $column_name ] );
					$cell  = YITH_WCBEP_List_Table::render_column(
						array(
							'value'   => wp_json_encode( array( $value => $values[ $value ] ?? '' ) ),
							'display' => $values[ $value ] ?? '',
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
			'yith_wcdp_enable_deposit'        => 'enable-deposit',
			'yith_wcdp_force_deposit'         => 'force-deposit',
			'yith_wcdp_deposit_default'       => 'deposit-default',
			'yith_wcdp_create_balance_orders' => 'create-balance-orders',

		);

		return $deposits_columns_types[ $name ] ?? $type;
	}

	/**
	 * Gte field options
	 *
	 * @param string $field_type The field type.
	 *
	 * @return string[]
	 */
	public function get_field_options( $field_type ) {
		$options = array();
		switch ( $field_type ) {
			case 'create-balance-orders':
				$options = array(
					'default' => __( 'Default', 'yith-woocommerce-deposits-and-down-payments' ),
					'yes'     => __( 'Let users pay the balance online (pending payment)', 'yith-woocommerce-deposits-and-down-payments' ),
					'no'      => __( 'Customers will pay the balance through other means (on hold)', 'yith-woocommerce-deposits-and-down-payments' ),
				);
				break;
			case 'force-deposit':
				$options = array(
					'default' => __( 'Default', 'yith-woocommerce-deposits-and-down-payments' ),
					'yes'     => __( 'Force deposit', 'yith-woocommerce-deposits-and-down-payments' ),
					'no'      => __( 'Allow deposit', 'yith-woocommerce-deposits-and-down-payments' ),
				);
				break;
			case 'deposit-default':
			case 'enable-deposit':
				$options = array(
					'default' => __( 'Default', 'yith-woocommerce-deposits-and-down-payments' ),
					'yes'     => __( 'Yes', 'yith-woocommerce-deposits-and-down-payments' ),
					'no'      => __( 'No', 'yith-woocommerce-deposits-and-down-payments' ),
				);
				break;
		}

		return $options;
	}

	/**
	 * Add deposit field in floating editing fields list.
	 *
	 * @param array $fields The floating editing fields list.
	 *
	 * @return array
	 */
	public function add_deposit_floating_editing_fields( array $fields ): array {

		return array_merge( $fields, $this->floating_fields );
	}

	/**
	 * Render deposit floating editing field.
	 *
	 * @param string $id The floating field ID.
	 *
	 * @return void
	 */
	public function render_deposit_floating_editing_fields( $id ) {
		if ( in_array( $id, $this->floating_fields, true ) && file_exists( __DIR__ . '/templates/deposit-floating-editing-field.php' ) ) {
			include __DIR__ . '/templates/deposit-floating-editing-field.php';
		}
	}

	/**
	 * Save deposits meta on product
	 *
	 * @param WC_Product $product The product.
	 * @param string     $prop    The property.
	 * @param string     $value   The prop value.
	 *
	 * @return WC_Product
	 */
	public function save_deposits_meta_on_product( $product, $prop, $value ) {
		if ( array_key_exists( $prop, $this->fields_id_to_meta ) ) {
			switch ( $prop ) {
				case 'yith_wcdp_enable_deposit':
				case 'yith_wcdp_deposit_default':
				case 'yith_wcdp_force_deposit':
				case 'yith_wcdp_create_balance_orders':
					$allowed_values = array( 'default', 'yes', 'no' );
					$value          = in_array( $value, $allowed_values, true ) ? $value : json_decode( $value, true );
					if ( is_array( $value ) && count( $value ) ) {
						$value = current( array_keys( $value ) );
					}
					// Manipulating values before the update.
				default:
					$product->update_meta_data( $this->fields_id_to_meta[ $prop ], $value );
			}
		}

		return $product;
	}

	/**
	 * Add deposit fields in bulk editing modal
	 *
	 * @param string[] $fields The fields list.
	 *
	 * @return string[]
	 */
	public function add_deposit_fields_in_bulk_editing_modal( $fields ) {
		$badge_fields = array(
			'yith_wcdp_enable_deposit'        => __( 'Enable deposit', 'yith-woocommerce-deposits-and-down-payments' ),
			'yith_wcdp_force_deposit'         => __( 'Force deposit', 'yith-woocommerce-deposits-and-down-payments' ),
			'yith_wcdp_deposit_default'       => __( 'Deposit checked', 'yith-woocommerce-deposits-and-down-payments' ),
			'yith_wcdp_create_balance_orders' => __( 'Create balance orders', 'yith-woocommerce-deposits-and-down-payments' ),
		);

		return array_merge( $fields, $badge_fields );
	}

	/**
	 * Render deposit bulk editing field.
	 *
	 * @param string $id The field ID (used in the template).
	 *
	 * @return void
	 */
	public function render_enable_deposit_bulk_editing_field( $id ) {
		$deposit_fields_ids = array(
			'yith_wcdp_enable_deposit',
			'yith_wcdp_force_deposit',
			'yith_wcdp_deposit_default',
			'yith_wcdp_create_balance_orders',
		);

		if ( in_array( $id, $deposit_fields_ids, true ) && file_exists( __DIR__ . '/templates/deposit-bulk-editing-field.php' ) ) {
			include __DIR__ . '/templates/deposit-bulk-editing-field.php';
		}
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'yith_wcbep_deposits_integration', plugin_dir_url( __FILE__ ) . 'assets/js/deposits-integration' . $suffix . '.js', array( 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );

		if ( yith_wcbep_admin()->is_panel( 'bulk-edit' ) ) {
			wp_enqueue_script( 'yith_wcbep_deposits_integration' );
		}
	}

	/**
	 * Get fields.
	 *
	 * @return array
	 * @depreacted since 2.0
	 */
	public function get_fields() {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::get_fields', '2.0.0' );

		return array();
	}

	/**
	 * Add column
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 *
	 * @depreacted since 2.0
	 */
	public function add_columns( $columns ) {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::add_columns', '2.0.0' );

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
	 *
	 * @depreacted since 2.0
	 */
	public function manage_columns( $value, $column_name, $post ) {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::manage_columns', '2.0.0' );

		return '';
	}

	/**
	 * Edit "non-editable and empty" columns in variations.
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 * TODO: Check if it's to deprecate
	 */
	public function edit_not_editable_and_empty_in_variations( $columns ) {
		$columns = array_merge( $columns, array_keys( $this->fields ) );

		return $columns;
	}

	/**
	 * Add class for select fields.
	 *
	 * @param array $classes The classes.
	 *
	 * @return array
	 *
	 * @depreacted since 2.0
	 */
	public function add_extra_bulk_columns_select( $classes ) {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::add_extra_bulk_columns_select', '2.0.0' );

		return $classes;
	}

	/**
	 * Add class for select fields.
	 *
	 * @param array $classes The classes.
	 *
	 * @return array
	 *
	 * @depreacted since 2.0
	 */
	public function add_extra_class_select_in_js( $classes ) {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::add_extra_class_select_in_js', '2.0.0' );

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
	public function save_meta( $product, $matrix_keys, $single_modify, $is_variation ) {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::save_meta', '2.0.0' );

	}

	/**
	 * Add fields to Bulk Editor.
	 *
	 * @depreacted since 2.0
	 */
	public function add_extra_bulk_fields() {
		wc_deprecated_function( 'YITH_WCBEP_Deposits_Compatibility::add_extra_bulk_fields', '2.0.0' );

	}
}
