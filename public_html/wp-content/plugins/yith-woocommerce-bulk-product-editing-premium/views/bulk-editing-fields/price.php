<?php
/**
 * Price field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action' => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-price-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''                       => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'                    => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'increase-by-value'      => __( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-value'      => __( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ),
			'increase-by-percentage' => __( 'Increase by percentage', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-percentage' => __( 'Decrease by percentage', 'yith-woocommerce-bulk-product-editing' ),
			'empty'                  => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'price'  => array(
		'type'  => 'text',
		'class' => 'wc_input_price',
	),
);

$currency = get_woocommerce_currency_symbol();

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-price-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['price'], true, false ); ?>
	<span class="yith-wcbep-bulk-editing-container__unit-of-measure" data-unit-of-measure="<?php echo esc_attr( $currency ); ?>"><?php echo esc_html( $currency ); ?></span>
</span>
