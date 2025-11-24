<?php
/**
 * Products field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action'                => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-products-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''        => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'     => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'add'     => __( 'Add', 'yith-woocommerce-bulk-product-editing' ),
			'remove'  => __( 'Remove', 'yith-woocommerce-bulk-product-editing' ),
			'replace' => __( 'Replace', 'yith-woocommerce-bulk-product-editing' ),
			'empty'   => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'products'              => array(
		'type'     => 'ajax-products',
		'class'    => 'yith-wcbep-bulk-editing-products yith-post-search',
		'multiple' => true,
	),
	'replace-products-with' => array(
		'type'     => 'ajax-products',
		'class'    => 'yith-wcbep-bulk-editing-replace-products-with yith-post-search',
		'multiple' => true,
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-products-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['products'], true, false ); ?>
</span>
<div class="yith-wcbep-bulk-editing-replace-products-with-container">
	<?php echo esc_html_x( 'with', 'Bulk editing - replacing', 'yith-woocommerce-bulk-product-editing' ); ?>
	<?php yith_plugin_fw_get_field( $fields['replace-products-with'], true, false ); ?>
</div>
