<?php
/**
 * Taxonomy field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action'            => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-taxonomy-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''       => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'    => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'add'    => __( 'Add', 'yith-woocommerce-bulk-product-editing' ),
			'remove' => __( 'Remove', 'yith-woocommerce-bulk-product-editing' ),
			'empty'  => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'taxonomy-selector' => array(
		'type'     => 'ajax-terms',
		'class'    => 'yith-wcbep-bulk-editing-taxonomy yith-term-search',
		'data'     => array(
			'placeholder' => __( 'Search for taxonomy terms&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => ! empty( $args['taxonomy'] ) ? $args['taxonomy'] : $id,
		),
		'multiple' => true,
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-taxonomy-container yith-wcbep-bulk-editing-first-value-container">
		<?php yith_plugin_fw_get_field( $fields['taxonomy-selector'], true, false ); ?>
</span>
