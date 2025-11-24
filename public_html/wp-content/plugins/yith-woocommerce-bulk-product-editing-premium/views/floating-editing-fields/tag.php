<?php
/**
 * Tag floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'    => 'yith-wcbep-floating-editing-field__tag yith-term-search',
	'type'     => 'ajax-terms',
	'data'     => array(
		'placeholder' => __( 'Search for a tag&hellip;', 'yith-woocommerce-bulk-product-editing' ),
		'taxonomy'    => 'product_tag',
	),
	'multiple' => true,
);

yith_plugin_fw_get_field( $input, true, false );
