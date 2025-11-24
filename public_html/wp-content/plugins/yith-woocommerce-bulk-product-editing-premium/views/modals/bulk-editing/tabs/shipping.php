<?php
/**
 * Bulk Editing - Shipping Tab
 *
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_shipping_tab_fields
 *
 * Ability to filter the shipping tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_shipping_tab_fields',
	array(
		'shipping_class' => __( 'Shipping class', 'yith-woocommerce-bulk-product-editing' ),
		'weight'         => __( 'Weight', 'yith-woocommerce-bulk-product-editing' ),
		'height'         => __( 'Height', 'yith-woocommerce-bulk-product-editing' ),
		'width'          => __( 'Width', 'yith-woocommerce-bulk-product-editing' ),
		'length'         => __( 'Length', 'yith-woocommerce-bulk-product-editing' ),
	)
);

foreach ( $fields as $field_id => $field_label ) {
	$args = array(
		'id'    => $field_id,
		'label' => $field_label,
		'type'  => in_array( $field_id, array( 'weight', 'height', 'width', 'length' ), true ) ? 'decimal' : YITH_WCBEP_List_Table::get_column_type( $field_id ),
	);
	yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
}
