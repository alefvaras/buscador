<?php
/**
 * Tax status floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'   => 'yith-wcbep-floating-editing-field__tax-status wc-enhanced-select',
	'type'    => 'select',
	'options' => yith_wcbep_get_wc_tax_status_options(),
);

yith_plugin_fw_get_field( $input, true, false );
