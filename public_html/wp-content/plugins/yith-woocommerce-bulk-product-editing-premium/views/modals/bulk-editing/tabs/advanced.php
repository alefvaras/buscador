<?php
/**
 * Bulk Editing - Advanced Tab
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @since   2.0.0
 */

/**
 * APPLY_FILTERS: yith_wcbep_bulk_editing_modal_advanced_tab_fields
 *
 * Ability to filter the advanced tab fields in bulk editing modal.
 *
 * @param array $fields The list of fields.
 *
 * @return array
 */
$fields = apply_filters(
	'yith_wcbep_bulk_editing_modal_advanced_tab_fields',
	array(
		'prod_type'       => __( 'Product type', 'yith-woocommerce-bulk-product-editing' ),
		'featured'        => __( 'Featured', 'yith-woocommerce-bulk-product-editing' ),
		'virtual'         => __( 'Virtual', 'yith-woocommerce-bulk-product-editing' ),
		'downloadable'    => __( 'Downloadable', 'yith-woocommerce-bulk-product-editing' ),
		'download_limit'  => __( 'Download limit', 'yith-woocommerce-bulk-product-editing' ),
		'download_expiry' => __( 'Download expiry', 'yith-woocommerce-bulk-product-editing' ),
		'button_text'     => __( 'Button text', 'yith-woocommerce-bulk-product-editing' ),
		'product_url'     => __( 'Product URL', 'yith-woocommerce-bulk-product-editing' ),
		'up_sells'        => __( 'Upsells', 'yith-woocommerce-bulk-product-editing' ),
		'cross_sells'     => __( 'Cross-sells', 'yith-woocommerce-bulk-product-editing' ),
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
