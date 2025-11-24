<?php
/**
 * On-Off field - Bulk editing
 *
 * @author  YITH <plugins@yithemes.com>
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @var string $id    The field id.
 * @since   2.0.0
 */

$field = array(
	'type'    => 'select',
	'id'      => 'yith-wcbep-bulk-editing-onoff-' . $id . '-action',
	'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
	'options' => array(
		''    => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
		'yes' => __( 'Yes', 'yith-woocommerce-bulk-product-editing' ),
		'no'  => __( 'No', 'yith-woocommerce-bulk-product-editing' ),
	),
);

yith_plugin_fw_get_field( $field, true, false );
