<?php
/**
 * Table views modal - Virtual condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-virtual-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-virtual-condition__value',
		'options' => array(
			'virtual'     => __( 'Only virtual products', 'yith-woocommerce-bulk-product-editing' ),
			'not-virtual' => __( 'Only non-virtual products', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
