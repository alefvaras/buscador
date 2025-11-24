<?php
/**
 * Price floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'type'  => 'text',
	'class' => 'wc_input_price yith-wcbep-floating-editing-field__price',
	'value' => '{{data.value}}',
);

yith_plugin_fw_get_field( $input, true, false );
