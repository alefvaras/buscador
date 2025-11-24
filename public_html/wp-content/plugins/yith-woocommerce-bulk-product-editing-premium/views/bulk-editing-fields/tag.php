<?php
/**
 * Tag field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action'           => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-tag-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''        => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'     => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'add'     => __( 'Add', 'yith-woocommerce-bulk-product-editing' ),
			'replace' => __( 'Replace', 'yith-woocommerce-bulk-product-editing' ),
			'remove'  => __( 'Remove', 'yith-woocommerce-bulk-product-editing' ),
			'empty'   => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'tag'              => array(
		'type'     => 'ajax-terms',
		'class'    => 'yith-wcbep-bulk-editing-tag yith-term-search',
		'data'     => array(
			'placeholder' => __( 'Search for tags&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => 'product_tag',
			'tags'        => true,
		),
		'multiple' => true,
	),
	'replace-tag-with' => array(
		'type'     => 'ajax-terms',
		'class'    => 'yith-wcbep-bulk-editing-replace-tag-with yith-term-search',
		'data'     => array(
			'placeholder' => __( 'Search for tags&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => 'product_tag',
			'tags'        => true,
		),
		'multiple' => true,
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-tag-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['tag'], true, false ); ?>
</span>
<div class="yith-wcbep-bulk-editing-replace-tag-with-container">
	<?php echo esc_html_x( 'with', 'Bulk editing - replacing', 'yith-woocommerce-bulk-product-editing' ); ?>
	<?php yith_plugin_fw_get_field( $fields['replace-tag-with'], true, false ); ?>
</div>
