<?php
/**
 * Table views modal - Tax class condition template
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-tax-class-condition">
	<?php
	$select_field = array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-tax-class-condition__value',
		'options' => yith_wcbep_get_wc_tax_class_options(),
		'value'   => '{{data.value}}',
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
