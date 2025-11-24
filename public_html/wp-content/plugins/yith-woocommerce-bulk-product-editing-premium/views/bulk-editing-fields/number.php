<?php
/**
 * Number field - Bulk editing
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
		'id'      => 'yith-wcbep-bulk-editing-number-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''         => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'      => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'increase' => __( 'Increase', 'yith-woocommerce-bulk-product-editing' ),
			'decrease' => __( 'Decrease', 'yith-woocommerce-bulk-product-editing' ),
			'empty'    => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'number' => array(
		'type'  => 'number',
		'class' => 'yith-wcbep-bulk-editing-number',
		'min'   => '1',
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-number-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['number'], true, false ); ?>
</span>
