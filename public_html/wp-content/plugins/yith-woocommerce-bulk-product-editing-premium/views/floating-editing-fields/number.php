<?php
/**
 * Number floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'type'  => 'number',
	'class' => 'yith-wcbep-floating-editing-field__number',
	'value' => '{{data.value}}',
);

yith_plugin_fw_get_field( $input, true, false );
