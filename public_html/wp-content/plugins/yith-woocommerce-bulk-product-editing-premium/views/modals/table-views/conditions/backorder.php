<?php
/**
 * Table views modal - Backorder condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-backorder-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-backorder-condition__value',
		'options' => wc_get_product_backorder_options(),
		'value'   => '{{data.value}}',
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
