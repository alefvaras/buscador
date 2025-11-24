<?php
/**
 * Enable Deposit floating editing field.
 *
 * @var string $id The field ID.
 *
 * @package YITH\BulkProductEditing\Compatibility\Deposits
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'   => 'yith-wcbep-floating-editing-field__' . $id . ' wc-enhanced-select',
	'type'    => 'select',
	'options' => yith_wcbep_deposits_compatibility()->get_field_options( $id ),
);

yith_plugin_fw_get_field( $input, true, false );
