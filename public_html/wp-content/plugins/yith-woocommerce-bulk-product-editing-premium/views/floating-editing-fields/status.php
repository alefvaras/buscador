<?php
/**
 * Status floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'   => 'yith-wcbep-floating-editing-field__status wc-enhanced-select',
	'type'    => 'select',
	'options' => yith_wcbep_get_product_statuses(),
);

yith_plugin_fw_get_field( $input, true, false );
