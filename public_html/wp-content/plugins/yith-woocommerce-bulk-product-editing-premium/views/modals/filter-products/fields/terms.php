<?php
/**
 * Filter products - Terms fields
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @var array  $field    The field data.
 * @var string $field_id The field ID.
 *
 * @package YITH\BulkProductEditing\Views\Modals\FilterProducts
 * @since   2.0.0
 */

$fields = array(
	array(
		'id'      => $field['id'],
		'type'    => 'select',
		'class'   => 'wc-enhanced-select yith-wcbep-filter-products-compare yith-wcbep-filter-products-' . $field_id . '-compare',
		'options' => array(
			'is'     => __( 'Is', 'yith-woocommerce-bulk-product-editing' ),
			'is-not' => __( 'Is not', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	array(
		'type'     => 'ajax-terms',
		'class'    => 'yith-wcbep-filter-products-' . $field_id . '-value yith-term-search yith-wcbep-filter-products-value',
		'data'     => $field['data'] ?? array(),
		'multiple' => true,
	),
);

foreach ( $fields as $field ) {
	yith_plugin_fw_get_field( $field, true, false );
}
