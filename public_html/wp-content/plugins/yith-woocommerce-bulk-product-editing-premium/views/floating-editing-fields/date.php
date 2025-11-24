<?php
/**
 * Date floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class' => 'yith-wcbep-floating-editing-field__date yith-plugin-fw-datepicker',
	'type'  => 'datepicker',
	'data'  => array(
		'date-format' => 'yy-mm-dd',
	),
	'value' => '{{data.value}}',
);

yith_plugin_fw_get_field( $input, true, false );
