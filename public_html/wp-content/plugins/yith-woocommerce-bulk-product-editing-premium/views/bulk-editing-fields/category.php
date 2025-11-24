<?php
/**
 * Category field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action'   => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-category-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''       => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'    => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'add'    => __( 'Add', 'yith-woocommerce-bulk-product-editing' ),
			'remove' => __( 'Remove', 'yith-woocommerce-bulk-product-editing' ),
			'empty'  => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'category' => array(
		'type'     => 'ajax-terms',
		'class'    => 'yith-wcbep-bulk-editing-category yith-term-search',
		'data'     => array(
			'placeholder' => __( 'Search for categories&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => 'product_cat',
		),
		'multiple' => true,
	),
);

$name_type = get_option( 'yith-wcbep-name-in-categories-dropdown', 'name' );

if ( 'name' !== $name_type ) {
	$fields['category']['data']['action']    = 'yith_wcbep_search_categories';
	$fields['category']['data']['security']  = wp_create_nonce( 'yith_wcbep_search_categories' );
	$fields['category']['data']['name_type'] = $name_type;
}

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-category-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['category'], true, false ); ?>
</span>
