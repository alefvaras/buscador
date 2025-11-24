<?php
/**
 * Bulk Editing - Prices Tab
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_prices_tab_fields
 *
 * Ability to filter the prices tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_prices_tab_fields',
	array(
		'regular_price'   => __( 'Regular price', 'yith-woocommerce-bulk-product-editing' ),
		'sale_price'      => __( 'Sale price', 'yith-woocommerce-bulk-product-editing' ),
		'sale_price_from' => __( 'Sale price from', 'yith-woocommerce-bulk-product-editing' ),
		'sale_price_to'   => __( 'Sale price to', 'yith-woocommerce-bulk-product-editing' ),
		'tax_status'      => __( 'Tax status', 'yith-woocommerce-bulk-product-editing' ),
		'tax_class'       => __( 'Tax class', 'yith-woocommerce-bulk-product-editing' ),
	)
);

foreach ( $fields as $field_id => $field_label ) {
	$args = array(
		'id'    => $field_id,
		'label' => $field_label,
		'type'  => 'sale_price' === $field_id ? 'sale-price' : YITH_WCBEP_List_Table::get_column_type( $field_id ),
	);
	yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
}
