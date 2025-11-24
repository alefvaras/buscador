<?php
/**
 * Badges field - Bulk editing
 *
 * @var string $id The field id.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$field_type = YITH_WCBEP_List_Table::get_column_type( $id );

$fields = array(
	'action'             => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-deposit-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''        => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'     => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'replace' => __( 'Replace', 'yith-woocommerce-bulk-product-editing' ),
			'remove'  => __( 'Remove', 'yith-woocommerce-bulk-product-editing' ),
			'empty'   => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'value'              => array(
		'type'    => 'select',
		'options' => yith_wcbep_deposits_compatibility()->get_field_options( $field_type ),
		'class'   => 'yith-wcbep-bulk-editing-' . $field_type . ' wc-enhanced-select',
	),
	'replace-value-with' => array(
		'type'    => 'select',
		'options' => yith_wcbep_deposits_compatibility()->get_field_options( $field_type ),
		'class'   => 'yith-wcbep-bulk-editing-replace-' . $field_type . '-with wc-enhanced-select',
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-<?php echo esc_attr( $field_type ); ?>-container">
	<?php yith_plugin_fw_get_field( $fields['value'], true, false ); ?>
</span>
<div class="yith-wcbep-bulk-editing-replace-<?php echo esc_attr( $field_type ); ?>-with-container">
	<?php echo esc_html_x( 'with', 'Bulk editing - replacing', 'yith-woocommerce-bulk-product-editing' ); ?>
	<?php yith_plugin_fw_get_field( $fields['replace-value-with'], true, false ); ?>
</div>
