<?php
/**
 * Date field - Bulk editing
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @var string $label The field label.
 * @var string $id    The field id.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @since   2.0.0
 */

$fields = array(
	'action' => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-date-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''                 => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'              => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'increase-by-days' => __( 'Increase by days', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-days' => __( 'Decrease by days', 'yith-woocommerce-bulk-product-editing' ),
			'empty'            => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'date'   => array(
		'type' => 'datepicker',
		'id'   => 'yith-wcbep-datepicker-' . $id,
		'data' => array(
			'date-format' => 'yy-mm-dd',
		),
	),
	'days'   => array(
		'type' => 'number',
	),
);

$unit_of_measure = __( 'days', 'yith-woocommerce-bulk-product-editing' );

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-date-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['date'], true, false ); ?>
</span>
<span class="yith-wcbep-bulk-editing-days-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['days'], true, false ); ?>
	<span class="yith-wcbep-bulk-editing-container__unit-of-measure" data-unit-of-measure="<?php echo esc_attr( $unit_of_measure ); ?>"><?php echo esc_html( $unit_of_measure ); ?></span>
</span>
