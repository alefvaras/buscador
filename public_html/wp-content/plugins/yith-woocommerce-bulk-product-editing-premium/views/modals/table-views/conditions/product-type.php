<?php
/**
 * Table views modal - Product type condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-product-type-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-product-type-condition__value',
		'options' => yith_wcbep_get_wc_product_types(),
		'value'   => '{{data.value}}',
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
	<div class="yith-wcbep-table-view-product-type-include-variation">
		<label>
			<input type="checkbox" class="yith-wcbep-table-view-product-type-include-variation-input">
			<?php esc_html_e( 'Include variations', 'yith-woocommerce-bulk-product-editing' ); ?>
		</label>
	</div>
</div>
