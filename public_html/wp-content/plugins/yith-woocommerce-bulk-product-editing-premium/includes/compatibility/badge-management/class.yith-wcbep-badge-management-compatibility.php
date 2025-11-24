<?php
/**
 * Badge Management Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Compatibility\BadgeManagement
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

/**
 * Badge Management Compatibility Class
 *
 * @since   1.1.2
 */
class YITH_WCBEP_Badge_Management_Compatibility {

	/**
	 * Single instance of the class
	 *
	 * @var YITH_WCBEP_Badge_Management_Compatibility
	 */
	protected static $instance;

	/**
	 * Array of badges.
	 *
	 * @var array
	 */
	public $badge_array;

	/**
	 * Returns single instance of the class
	 *
	 * @return YITH_WCBEP_Badge_Management_Compatibility
	 */
	public static function get_instance() {
		return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
	}

	/**
	 * Constructor
	 */
	protected function __construct() {
		// Columns add and render.
		add_filter( 'yith_wcbep_default_columns', array( $this, 'add_badge_columns' ) );
		add_filter( 'yith_wcbep_manage_custom_columns', array( $this, 'manage_badge_column' ), 10, 3 );

		// Floating editing fields.
		add_filter( 'yith_wcbep_floating_editing_fields', array( $this, 'add_badge_floating_editing_field' ) );
		add_filter( 'yith_wcbep_render_floating_editing_field', array( $this, 'render_badge_floating_editing_field' ) );

		add_filter( 'yith_wcbep_list_table_get_column_type', array( $this, 'get_column_type' ), 10, 2 );
		add_filter( 'yith_wcbep_save_product_prop_changes', array( $this, 'save_badge_meta_on_product' ), 10, 3 );

		// Bulk editing fields.
		add_filter( 'yith_wcbep_bulk_editing_modal_general_tab_fields', array( $this, 'add_badge_fields_in_bulk_editing_modal' ), 10 );
		add_action( 'yith_wcbep_bulk_editing_field_badge', array( $this, 'render_badge_bulk_editing_field' ) );

		// Table Views Conditions.
		add_filter( 'yith_wcbep_table_views_condition_types', array( $this, 'add_badge_table_views_condition' ) );
		add_filter( 'yith_wcbep_table_views_conditions_templates', array( $this, 'add_badge_table_views_condition_template' ) );
		add_filter( 'yith_wcbep_table_view_get_query_args', array( $this, 'table_view_get_query_args' ), 10, 2 );
		add_action( 'yith_wcbep_table_views_conditions_template', array( $this, 'render_badge_table_views_condition' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Save badge meta on product
	 *
	 * @param WC_Product $product The product.
	 * @param string     $prop    The property.
	 * @param string     $value   The prop value.
	 *
	 * @return WC_Product
	 */
	public function save_badge_meta_on_product( $product, $prop, $value ) {
		switch ( $prop ) {
			case 'yith_wcbm_badge':
				if ( $product->is_type( 'variation' ) ) {
					$product->update_meta_data( 'yith_wcbm_badge_options_badges', array_keys( json_decode( $value, true ) ) );
				} else {
					$product->update_meta_data( '_yith_wcbm_badge_ids', array_keys( json_decode( $value, true ) ) );
				}
				break;
			case 'yith_wcbm_badge_schedule':
				if ( $product->is_type( 'variation' ) ) {
					$product->update_meta_data( 'yith_wcbm_badge_options_schedule', intval( wc_string_to_bool( $value ) ) );
				} else {
					$product->update_meta_data( '_yith_wcbm_badge_schedule', intval( wc_string_to_bool( $value ) ) );
				}
				break;
			case 'yith_wcbm_badge_schedule_from':
			case 'yith_wcbm_badge_schedule_to':
				if ( $product->is_type( 'variation' ) ) {
					$product->update_meta_data( 'yith_wcbm_badge_schedule_from' === $prop ? 'yith_wcbm_badge_options_schedule_from' : 'yith_wcbm_badge_options_schedule_to', $value ? date_i18n( 'Y-m-d', $value ) : '' );
				} else {
					$product->update_meta_data( 'yith_wcbm_badge_schedule_from' === $prop ? '_yith_wcbm_badge_from_date' : '_yith_wcbm_badge_to_date', $value ? date_i18n( 'Y-m-d', $value ) : '' );
				}
				break;
		}

		return $product;
	}

	/**
	 * Add badge columns
	 *
	 * @param array $columns The columns.
	 *
	 * @return array
	 */
	public function add_badge_columns( $columns ) {
		$badge_columns = array(
			'yith_wcbm_badge'               => array(
				'id'    => 'yith_wcbm_badge',
				'label' => __( 'Badge', 'yith-woocommerce-badge-management' ),
				'kind'  => 'simple',
			),
			'yith_wcbm_badge_schedule'      => array(
				'id'    => 'yith_wcbm_badge_schedule',
				'label' => __( 'Badge scheduling', 'yith-woocommerce-badge-management' ),
				'kind'  => 'simple',
			),
			'yith_wcbm_badge_schedule_from' => array(
				'id'    => 'yith_wcbm_badge_schedule_from',
				'label' => __( 'Schedule badge from', 'yith-woocommerce-badge-management' ),
				'kind'  => 'simple',
			),
			'yith_wcbm_badge_schedule_to'   => array(
				'id'    => 'yith_wcbm_badge_schedule_from',
				'label' => __( 'Schedule badge to', 'yith-woocommerce-badge-management' ),
				'kind'  => 'simple',
			),
		);

		return array_merge( $columns, $badge_columns );
	}

	/**
	 * Add badge fields in bulk editing modal
	 *
	 * @param string[] $fields The fields list.
	 *
	 * @return string[]
	 */
	public function add_badge_fields_in_bulk_editing_modal( $fields ) {
		$badge_fields = array(
			'yith_wcbm_badge'               => __( 'Badges', 'yith-woocommerce-bulk-product-editing' ),
			'yith_wcbm_badge_schedule'      => __( 'Badges scheduling', 'yith-woocommerce-bulk-product-editing' ),
			'yith_wcbm_badge_schedule_from' => __( 'Schedule badges start date', 'yith-woocommerce-bulk-product-editing' ),
			'yith_wcbm_badge_schedule_to'   => __( 'Schedule badges end date', 'yith-woocommerce-bulk-product-editing' ),
		);

		return array_merge( $fields, $badge_fields );
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
	public function manage_badge_column( string $cell, string $column_name, WP_Post $post ): string {
		switch ( $column_name ) {
			case 'yith_wcbm_badge':
				$badge_info = yith_wcbm_get_product_badge_info( $post->ID );
				$badge_ids  = array_filter( array_map( 'absint', $badge_info['badge_ids'] ) );
				$badges     = array_combine( $badge_ids, array_map( 'get_the_title', $badge_ids ) );

				$cell = YITH_WCBEP_List_Table::render_column(
					array(
						'value'   => esc_attr( wp_json_encode( count( $badges ) > 0 ? $badges : new stdClass() ) ),
						'display' => implode( ', ', $badges ),
					)
				);
				break;
			case 'yith_wcbm_badge_schedule':
				$badge_info = yith_wcbm_get_product_badge_info( $post->ID );
				$scheduled  = $badge_info['scheduled'] ?? 'no';
				$cell       = YITH_WCBEP_List_Table::render_column(
					array(
						'value'   => $scheduled,
						'display' => yith_plugin_fw_get_field(
							array(
								'type'  => 'onoff',
								'value' => $scheduled,
							),
							false,
							false
						),
					)
				);
				break;
			case 'yith_wcbm_badge_schedule_from':
			case 'yith_wcbm_badge_schedule_to':
				$badge_info = yith_wcbm_get_product_badge_info( $post->ID );
				$date       = $badge_info[ 'yith_wcbm_badge_schedule_from' === $column_name ? 'start_date' : 'end_date' ] ?? '';
				$cell       = YITH_WCBEP_List_Table::render_column(
					array(
						'value'   => esc_attr( $date ? strtotime( $date ) : '' ),
						'display' => $date,
					)
				);
				break;
		}

		return $cell;
	}

	/**
	 * Get the column type when is a Badge Field
	 *
	 * @param string $type The column type.
	 * @param string $name The column name.
	 *
	 * @return string
	 */
	public function get_column_type( string $type, string $name ): string {
		$badge_columns_types = array(
			'yith_wcbm_badge'               => 'badge',
			'yith_wcbm_badge_schedule'      => 'onoff',
			'yith_wcbm_badge_schedule_from' => 'date',
			'yith_wcbm_badge_schedule_to'   => 'date',

		);

		return $badge_columns_types[ $name ] ?? $type;
	}

	/**
	 * Add badge field in floating editing fields list.
	 *
	 * @param array $fields The floating editing fields list.
	 *
	 * @return array
	 */
	public function add_badge_floating_editing_field( array $fields ): array {
		$fields[] = 'badge';

		return $fields;
	}

	/**
	 * Render badge floating editing field.
	 *
	 * @param string $id The floating field ID.
	 *
	 * @return void
	 */
	public function render_badge_floating_editing_field( $id ) {
		if ( 'badge' === $id && file_exists( __DIR__ . '/templates/badge-floating-editing-field.php' ) ) {
			include __DIR__ . '/templates/badge-floating-editing-field.php';
		}
	}

	/**
	 * Render badge bulk editing field.
	 *
	 * @param string $id The field ID (used in the template).
	 *
	 * @return void
	 */
	public function render_badge_bulk_editing_field( $id ) {
		if ( file_exists( __DIR__ . '/templates/badge-bulk-editing-field.php' ) ) {
			include __DIR__ . '/templates/badge-bulk-editing-field.php';
		}
	}

	/**
	 * Add badge table views conditions
	 *
	 * @param array $conditions The table views conditions.
	 *
	 * @return array
	 */
	public function add_badge_table_views_condition( $conditions ) {
		$conditions['badge'] = __( 'Badges', 'yith-woocommerce-bulk-product-editing' );

		return $conditions;
	}

	/**
	 * Add badge table views conditions templates
	 *
	 * @param array $templates The table views conditions templates list.
	 *
	 * @return array
	 */
	public function add_badge_table_views_condition_template( $templates ) {
		$templates[] = 'badge';

		return $templates;
	}

	/**
	 * Render badge table views conditions
	 *
	 * @param array $condition_template The table views conditions.
	 *
	 * @return void
	 */
	public function render_badge_table_views_condition( $condition_template ) {
		if ( 'badge' === $condition_template && file_exists( __DIR__ . '/templates/badge-table-view-condition.php' ) ) {
			include __DIR__ . '/templates/badge-table-view-condition.php';
		}
	}

	/**
	 * Add badge args to table view query ones.
	 *
	 * @param array        $args       The table view query args.
	 * @param string|array $table_view The table view ID.
	 *
	 * @return array
	 */
	public function table_view_get_query_args( $args, $table_view ) {
		$view_conditions = is_string( $table_view ) ? yith_wcbep_table_views()->get_table_view_conditions( $table_view ) : $table_view;
		$for_variations  = is_string( $table_view ) ? yith_wcbep_table_views()->is_for_variations( $table_view ) : false;
		foreach ( $view_conditions as $condition ) {
			$condition_type = $condition['type'] ?? '';
			switch ( $condition_type ) {
				case 'badge':
					$meta_query           = array(
						'relation' => 'has' === $condition['options']['condition'] ? 'AND' : 'OR',
						array(
							'key'     => $for_variations ? 'yith_wcbm_badge_options_badges' : '_yith_wcbm_badge_ids',
							'compare' => 'has' === $condition['options']['condition'] ? 'EXISTS' : 'NOT EXISTS',
						),
						array(
							'key'     => $for_variations ? 'yith_wcbm_badge_options_badges' : '_yith_wcbm_badge_ids',
							'value'   => array( 'a:0:{}', '' ),
							'compare' => 'has' === $condition['options']['condition'] ? 'NOT IN' : 'IN',
						),
					);
					$args['meta_query'][] = $meta_query;
					break;
			}
		}

		return $args;
	}

	/**
	 * Enqueue scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script( 'yith_wcbep_badge_management_integration', plugin_dir_url( __FILE__ ) . 'assets/js/badge-management-integration' . $suffix . '.js', array( 'yith_wcbep_main_tab_js' ), YITH_WCBEP_VERSION, true );

		if ( yith_wcbep_admin()->is_panel( 'bulk-edit' ) ) {
			wp_enqueue_script( 'yith_wcbep_badge_management_integration' );
		}
	}

	/**
	 * Add badge Class.
	 *
	 * @param array $classes The classes.
	 *
	 * @return array
	 * @depreacted since 2.0
	 */
	public function add_badge_class( $classes ) {
		wc_deprecated_function( 'YITH_WCBEP_Badge_Management_Compatibility::add_badge_class', '2.0.0' );

		return $classes;
	}

	/**
	 * Add extra input
	 *
	 * @depreacted since 2.0
	 */
	public function extra_custom_input() {
		wc_deprecated_function( 'YITH_WCBEP_Badge_Management_Compatibility::extra_custom_input', '2.0.0' );
	}

	/**
	 * Add extra bulk field.
	 *
	 * @depreacted since 2.0
	 */
	public function add_extra_bulk_field() {
		wc_deprecated_function( 'YITH_WCBEP_Badge_Management_Compatibility::add_extra_bulk_field', '2.0.0' );
	}

	/**
	 * Save badge meta.
	 *
	 * @param WC_Product $product       The product.
	 * @param array      $matrix_keys   The matrix.
	 * @param array      $single_modify The single modify.
	 * @param bool       $is_variation  Set true if this is a variation.
	 *
	 * @depreacted since 2.0
	 */
	public function save_badge_meta( $product, $matrix_keys, $single_modify, $is_variation ) {
		wc_deprecated_function( 'YITH_WCBEP_Badge_Management_Compatibility::save_badge_meta', '2.0.0' );
	}

}

/**
 * Unique access to instance of YITH_WCBEP_Badge_Management_Compatibility class
 *
 * @return YITH_WCBEP_Badge_Management_Compatibility
 * @since 1.0.11
 */
function yith_wcbep_badge_management_compatibility() {
	return YITH_WCBEP_Badge_Management_Compatibility::get_instance();
}
