<?php
/**
 * Table views modal - On sale condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-on-sale-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-on-sale-condition__value',
		'options' => array(
			'on-sale'     => __( 'Only products on sale', 'yith-woocommerce-bulk-product-editing' ),
			'not-on-sale' => __( 'Only products that are not on sale', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
