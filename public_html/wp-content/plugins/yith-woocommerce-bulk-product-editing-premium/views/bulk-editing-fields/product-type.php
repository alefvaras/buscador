<?php
/**
 * Product type field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action' => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-product-type-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array_merge(
			array(
				'no-changes' => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			),
			wc_get_product_types()
		),
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
