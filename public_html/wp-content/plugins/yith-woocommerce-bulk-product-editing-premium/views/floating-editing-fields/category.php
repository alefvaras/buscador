<?php
/**
 * Category floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$name_type = get_option( 'yith-wcbep-name-in-categories-dropdown', 'name' );

$input = array(
	'class'    => 'yith-wcbep-floating-editing-field__category yith-term-search',
	'type'     => 'ajax-terms',
	'data'     => array(
		'placeholder' => __( 'Search for a category&hellip;', 'yith-woocommerce-bulk-product-editing' ),
		'taxonomy'    => 'product_cat',
	),
	'multiple' => true,
);

if ( 'name' !== $name_type ) {
	$input['data']['action']    = 'yith_wcbep_search_categories';
	$input['data']['security']  = wp_create_nonce( 'yith_wcbep_search_categories' );
	$input['data']['name_type'] = $name_type;
}

yith_plugin_fw_get_field( $input, true, false );
