<?php
/**
 * Filter products - Product Type fields
 *
 * @author  YITH <plugins@yithemes.com>
 *
 * @var array  $field    The field data.
 * @var string $field_id The field ID.
 *
 * @package YITH\BulkProductEditing\Views\Modals\FilterProducts
 * @since   2.0.0
 */

?>
<div class="yith-wcbep-product-type-selector">
	<?php
	yith_plugin_fw_get_field(
		array(
			'id'      => $field['id'],
			'type'    => 'select',
			'class'   => 'wc-enhanced-select yith-wcbep-filter-products-value yith-wcbep-filter-products-' . $field_id,
			'options' => yith_wcbep_get_wc_product_types(),
		),
		true,
		false
	);
	?>
</div>
<div class="yith-wcbep-product-type__include-variations">
	<?php
	yith_plugin_fw_get_field(
		array(
			'id'   => $field['id'] . '-include-variation',
			'type' => 'checkbox',
		),
		true,
		false
	);
	?>
	<label for="<?php echo esc_attr( $field['id'] . '-include-variation' ); ?>" class="yith-wcbep-product-type__include-variations__label"><?php esc_html_e( 'Include variations', 'yith-woocommerce-bulk-product-editing' ); ?></label>
</div>

