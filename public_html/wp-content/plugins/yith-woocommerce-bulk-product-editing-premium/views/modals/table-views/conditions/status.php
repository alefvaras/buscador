<?php
/**
 * Table views modal - Status condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-status-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-status-condition__value',
		'options' => yith_wcbep_get_product_statuses(),
		'value'   => '{{data.value}}',
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
