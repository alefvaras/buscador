<?php
/**
 * Table views modal - Text condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-text-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-text-condition__compare',
		'options' => array(
			'contain'     => __( 'Contains', 'yith-woocommerce-bulk-product-editing' ),
			'not-contain' => __( 'Does not contain', 'yith-woocommerce-bulk-product-editing' ),
			'start'       => __( 'Starts with', 'yith-woocommerce-bulk-product-editing' ),
			'end'         => __( 'Ends with', 'yith-woocommerce-bulk-product-editing' ),
			'regex'       => __( 'Regular expression', 'yith-woocommerce-bulk-product-editing' ),
		),
		'value'   => '{{data.compare}}',
	);
	yith_plugin_fw_get_field( $select_field, true, false );

	$text_field = array(
		'type'  => 'text',
		'value' => '{{data.value}}',
		'class' => 'yith-wcbep-table-view-text-condition__value',
	);
	yith_plugin_fw_get_field( $text_field, true, false );
	?>
</div>
