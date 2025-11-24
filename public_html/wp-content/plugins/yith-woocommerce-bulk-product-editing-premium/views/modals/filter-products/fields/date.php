<?php
/**
 * Filter products - Date fields
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @var array  $field    The field options.
 * @var string $field_id The field ID.
 *
 * @package YITH\BulkProductEditing\Views\Modals\FilterProducts
 * @since   2.0.0
 */

$fields = array(
	array(
		'id'    => $field['id'],
		'type'  => 'datepicker',
		'class' => 'yith-wcbep-filter-products-' . $field_id . '-value',
		'data'  => array(
			'date-format' => 'yy-mm-dd',
		),
	),
);

foreach ( $fields as $field ) {
	yith_plugin_fw_get_field( $field, true, false );
}
