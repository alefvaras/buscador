<?php
/**
 * Table views modal - Featured condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-featured-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-featured-condition__value',
		'options' => array(
			'featured'     => __( 'Only featured products', 'yith-woocommerce-bulk-product-editing' ),
			'not-featured' => __( 'Only non-featured products', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
