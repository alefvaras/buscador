<?php
/**
 * Taxonomy floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'    => 'yith-wcbep-floating-editing-field__taxonomy yith-term-search',
	'type'     => 'ajax-terms',
	'data'     => array(
		'placeholder' => __( 'Search for a taxonomy&hellip;', 'yith-woocommerce-bulk-product-editing' ),
		'taxonomy'    => '{{data.taxonomyID}}',
	),
	'multiple' => true,
);

yith_plugin_fw_get_field( $input, true, false );
