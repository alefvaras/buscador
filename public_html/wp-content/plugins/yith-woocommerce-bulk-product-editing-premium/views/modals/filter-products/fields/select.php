<?php
/**
 * Filter products - Select fields
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @var array  $field    The field data.
 * @var string $field_id The field ID.
 *
 * @package YITH\BulkProductEditing\Views\Modals\FilterProducts
 * @since   2.0.0
 */

$options = $field['options'] ?? array();

$fields = array(
	array(
		'id'      => $field['id'],
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-filter-products-value yith-wcbep-filter-products-' . $field_id,
		'options' => ! empty( $field['data']['placeholder'] ) ? array_merge( array( '' => '' ), $options ) : $options,
		'data'    => $field['data'] ?? array(),
	),
);

foreach ( $fields as $field ) {
	yith_plugin_fw_get_field( $field, true, false );
}
