<?php
/**
 * Bulk Editing - Taxonomies Tab
 *
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_taxonomies_tab_fields
 *
 * Ability to filter the taxonomies tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_taxonomies_tab_fields',
	array(
		'categories' => __( 'Category', 'yith-woocommerce-bulk-product-editing' ),
		'tags'       => __( 'Tag', 'yith-woocommerce-bulk-product-editing' ),
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

$attribute_taxonomies = wc_get_attribute_taxonomies();

foreach ( $attribute_taxonomies as $attribute_taxonomy ) {
	$args = array(
		'id'           => $attribute_taxonomy->attribute_name,
		'attribute_id' => $attribute_taxonomy->attribute_label,
		'label'        => $attribute_taxonomy->attribute_label ?? $attribute_taxonomy->attribute_name ?? '',
		'type'         => 'attribute',
	);
	yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
}
