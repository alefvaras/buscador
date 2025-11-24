<?php
/**
 * Product floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'    => 'yith-wcbep-floating-editing-field__product yith-post-search',
	'type'     => 'ajax-posts',
	'data'     => array(
		'post_type'   => 'product',
		'placeholder' => __( 'Search for a product...', 'yith-woocommerce-bulk-product-editing' ),
		'action'      => 'yith_plugin_fw_json_search_products',
	),
	'multiple' => true,
);

yith_plugin_fw_get_field( $input, true, false );
