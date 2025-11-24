<?php
/**
 * Bulk Editing - General Tab
 *
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_general_tab_fields
 *
 * Ability to filter the general tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_general_tab_fields',
	array(
		'title'             => __( 'Product name', 'yith-woocommerce-bulk-product-editing' ),
		'slug'              => __( 'Slug', 'yith-woocommerce-bulk-product-editing' ),
		'sku'               => __( 'SKU', 'yith-woocommerce-bulk-product-editing' ),
		'description'       => __( 'Description', 'yith-woocommerce-bulk-product-editing' ),
		'shortdesc'         => __( 'Short description', 'yith-woocommerce-bulk-product-editing' ),
		'purchase_note'     => __( 'Purchase note', 'yith-woocommerce-bulk-product-editing' ),
		'menu_order'        => __( 'Menu order', 'yith-woocommerce-bulk-product-editing' ),
		'sold_individually' => __( 'Sold individually', 'yith-woocommerce-bulk-product-editing' ),
		'enable_reviews'    => __( 'Enable reviews', 'yith-woocommerce-bulk-product-editing' ),
		'status'            => __( 'Status', 'yith-woocommerce-bulk-product-editing' ),
		'visibility'        => __( 'Catalog visibility', 'yith-woocommerce-bulk-product-editing' ),
		'date'              => __( 'Date', 'yith-woocommerce-bulk-product-editing' ),
	)
);

foreach ( $fields as $field_id => $field_label ) {
	$args = array(
		'id'    => $field_id,
		'label' => $field_label,
		'type'  => YITH_WCBEP_List_Table::get_column_type( $field_id ),
	);

	do_action( 'yith_wcbep_bulk_editing_modal_general_tab_' . $field_id . '_field' );
	yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
}
