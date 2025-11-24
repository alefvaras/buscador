<?php
/**
 * Table views modal - Text condition template
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-price-condition yith-wcbep-table-view-price-condition__price">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-price-condition__operator',
		'value'   => '{{data.operator}}',
		'options' => array(
			'greater'    => __( 'Higher than', 'yith-woocommerce-bulk-product-editing' ),
			'less'       => __( 'Less than', 'yith-woocommerce-bulk-product-editing' ),
			'equal'      => __( 'Equal to', 'yith-woocommerce-bulk-product-editing' ),
			'greater-eq' => __( 'Higher than or equal to', 'yith-woocommerce-bulk-product-editing' ),
			'less-eq'    => __( 'Less than or equal to', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true, false );

	$price_field = array(
		'type'  => 'text',
		'class' => 'wc_input_price yith-wcbep-table-view-price-condition__value',
		'value' => '{{data.value}}',
	);
	yith_plugin_fw_get_field( $price_field, true, false );
	?>
</div>
