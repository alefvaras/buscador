<?php
/**
 * Product type floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'   => 'yith-wcbep-floating-editing-field__product-type wc-enhanced-select',
	'type'    => 'select',
	'options' => wc_get_product_types(),
	'value'   => '{{data.value}}',
);

yith_plugin_fw_get_field( $input, true, false );
