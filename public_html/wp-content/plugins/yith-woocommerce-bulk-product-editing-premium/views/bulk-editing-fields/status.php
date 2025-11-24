<?php
/**
 * Status field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$field = array(
	'type'    => 'select',
	'id'      => 'yith-wcbep-bulk-editing-status-' . $id . '-action',
	'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
	'options' => array_merge(
		array(
			'' => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
		),
		yith_wcbep_get_product_statuses()
	),
);

yith_plugin_fw_get_field( $field, true, false );
