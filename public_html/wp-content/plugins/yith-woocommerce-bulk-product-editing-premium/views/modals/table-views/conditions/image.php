<?php
/**
 * Table views modal - Image condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-image-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-image-condition__value',
		'options' => array(
			'with-image'    => __( 'Only products with an image', 'yith-woocommerce-bulk-product-editing' ),
			'without-image' => __( 'Only products without an image', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
