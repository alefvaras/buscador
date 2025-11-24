<?php
/**
 * Table views modal - Downloadable condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-downloadable-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-downloadable-condition__value',
		'options' => array(
			'downloadable'     => __( 'Only downloadable products', 'yith-woocommerce-bulk-product-editing' ),
			'not-downloadable' => __( 'Only non-downloadable products', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
