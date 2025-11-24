<?php
/**
 * Table views modal - Shipping class condition template
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-shipping-class-condition">
	<?php
	$compare_field = array(
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-shipping-class-condition__compare',
		'type'    => 'select',
		'options' => array(
			'is'     => __( 'Is', 'yith-woocommerce-bulk-product-editing' ),
			'is-not' => __( 'Is not', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $compare_field, true, false );

	$select_field = array(
		'class'    => 'yith-wcbep-table-view-shipping-class-condition__value yith-term-search',
		'type'     => 'ajax-terms',
		'data'     => array(
			'placeholder' => __( 'Search for a shipping-class&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => 'product_shipping_class',
		),
		'multiple' => true,
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
