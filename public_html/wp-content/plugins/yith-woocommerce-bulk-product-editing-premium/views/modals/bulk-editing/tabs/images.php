<?php
/**
 * Bulk Editing - Images Tab
 *
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_image_tab_fields
 *
 * Ability to filter the image tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_image_tab_fields',
	array(
		'image'                  => __( 'Image', 'yith-woocommerce-bulk-product-editing' ),
		'upload-image'           => __( 'Upload image', 'yith-woocommerce-bulk-product-editing' ),
		'image_gallery'          => __( 'Image gallery', 'yith-woocommerce-bulk-product-editing' ),
		'upload-multiple-images' => __( 'Upload image', 'yith-woocommerce-bulk-product-editing' ),
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
