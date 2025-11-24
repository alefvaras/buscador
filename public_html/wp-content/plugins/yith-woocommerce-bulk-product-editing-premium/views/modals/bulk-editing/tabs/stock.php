<?php
/**
 * Bulk Editing - Stock Tab
 *
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_stock_tab_fields
 *
 * Ability to filter the stock tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_stock_tab_fields',
	array(
		'manage_stock'     => __( 'Manage stock', 'yith-woocommerce-bulk-product-editing' ),
		'stock_status'     => __( 'Stock status', 'yith-woocommerce-bulk-product-editing' ),
		'stock_quantity'   => __( 'Stock quantity', 'yith-woocommerce-bulk-product-editing' ),
		'low_stock_amount' => __( 'Low stock amount', 'yith-woocommerce-bulk-product-editing' ),
		'allow_backorders' => __( 'Allow backorders?', 'yith-woocommerce-bulk-product-editing' ),
	)
);

foreach ( $fields as $field_id => $field_label ) {
	$args = array(
		'id'    => $field_id,
		'label' => $field_label,
		'type'  => YITH_WCBEP_List_Table::get_column_type( $field_id ),
	);
	yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
}
