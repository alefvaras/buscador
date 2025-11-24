<?php
/**
 * Filter products - Price fields
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @var array  $field    The field options.
 * @var string $field_id The field ID.
 *
 * @package YITH\BulkProductEditing\Views\Modals\FilterProducts
 * @since   2.0.0
 */

$fields = array(
	array(
		'id'      => $field['id'],
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-filter-products-compare yith-wcbep-table-view-' . $field_id . '-compare yith-wcbep-numeric-comparator-select',
		'options' => array(
			'greater'    => __( 'Higher than', 'yith-woocommerce-bulk-product-editing' ),
			'less'       => __( 'Less than', 'yith-woocommerce-bulk-product-editing' ),
			'equal'      => __( 'Equal to', 'yith-woocommerce-bulk-product-editing' ),
			'greater-eq' => __( 'Higher than or equal to', 'yith-woocommerce-bulk-product-editing' ),
			'less-eq'    => __( 'Less than or equal to', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	array(
		'type'  => 'text',
		'class' => 'wc_input_price yith-wcbep-filter-products-value yith-wcbep-filter-products-' . $field_id . '-value',
	),
);

foreach ( $fields as $field ) {
	yith_plugin_fw_get_field( $field, true, false );
}
?>

<span class="yith-wcbep-filter-products-unit-of-measure"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
