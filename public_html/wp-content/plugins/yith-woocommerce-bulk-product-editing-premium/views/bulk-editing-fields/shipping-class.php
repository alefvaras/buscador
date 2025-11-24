<?php
/**
 * Shipping class field - Bulk editing
 *
 * @author  YITH <plugins@yithemes.com>
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @var string $id    The field id.
 * @since   2.0.0
 */

$fields = array(
	'action' => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-shipping-class-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array( '' => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ) ) + yith_wcbep_get_wc_shipping_class_options(),
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
