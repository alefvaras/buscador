<?php
/**
 * Stock status floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'   => 'yith-wcbep-floating-editing-field__stock-status wc-enhanced-select',
	'type'    => 'select',
	'options' => wc_get_product_stock_status_options(),
);

yith_plugin_fw_get_field( $input, true, false );
