<?php
/**
 * Filter products - Text fields
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
		'id'      => $field['id'],
		'type'    => 'select',
		'options' => array(
			'contain'     => __( 'Contains', 'yith-woocommerce-bulk-product-editing' ),
			'not-contain' => __( 'Does not contain', 'yith-woocommerce-bulk-product-editing' ),
			'start'       => __( 'Starts with', 'yith-woocommerce-bulk-product-editing' ),
			'end'         => __( 'Ends with', 'yith-woocommerce-bulk-product-editing' ),
			'regex'       => __( 'Regular expression', 'yith-woocommerce-bulk-product-editing' ),
		),
		'class'   => 'wc-enhanced-select yith-wcbep-filter-products-compare yith-wcbep-filter-products-' . $field_id . '-compare',
	),
	array(
		'type'  => 'text',
		'class' => 'yith-wcbep-filter-products-value yith-wcbep-filter-products-' . $field_id . '-value',
	),
);

foreach ( $fields as $field ) {
	yith_plugin_fw_get_field( $field, true, false );
}
