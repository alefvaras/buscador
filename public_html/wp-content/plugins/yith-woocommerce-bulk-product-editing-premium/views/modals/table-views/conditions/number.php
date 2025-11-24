<?php
/**
 * Table views modal - Number condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-number-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-number-condition__operator',
		'options' => array(
			'greater'    => __( 'Higher than', 'yith-woocommerce-bulk-product-editing' ),
			'less'       => __( 'Less than', 'yith-woocommerce-bulk-product-editing' ),
			'equal'      => __( 'Equal to', 'yith-woocommerce-bulk-product-editing' ),
			'greater-eq' => __( 'Higher than or equal to', 'yith-woocommerce-bulk-product-editing' ),
			'less-eq'    => __( 'Less than or equal to', 'yith-woocommerce-bulk-product-editing' ),
		),
		'value'   => '{{data.operator}}',
	);
	yith_plugin_fw_get_field( $select_field, true, false );

	$text_field = array(
		'type'  => 'text',
		'class' => 'yith-wcbep-table-view-number-condition__value',
		'value' => '{{data.value}}',
	);
	yith_plugin_fw_get_field( $text_field, true, false );
	?>
</div>
