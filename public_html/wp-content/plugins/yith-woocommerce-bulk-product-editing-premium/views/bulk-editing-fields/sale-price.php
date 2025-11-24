<?php
/**
 * Sale price field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action'     => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-sale-price-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''                                    => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'                                 => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'increase-by-value'                   => __( 'Increase by value', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-value'                   => __( 'Decrease by value', 'yith-woocommerce-bulk-product-editing' ),
			'increase-by-percentage'              => __( 'Increase by percentage', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-percentage'              => __( 'Decrease by percentage', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-value-from-regular'      => __( 'Decrease by value from regular', 'yith-woocommerce-bulk-product-editing' ),
			'decrease-by-percentage-from-regular' => __( 'Decrease by percentage from regular', 'yith-woocommerce-bulk-product-editing' ),
			'empty'                               => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'sale-price' => array(
		'type'  => 'text',
		'class' => 'wc_input_price',
	),
);

$currency = get_woocommerce_currency_symbol();

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-sale-price-container yith-wcbep-bulk-editing-first-value-container">
	<?php yith_plugin_fw_get_field( $fields['sale-price'], true, false ); ?>
	<span class="yith-wcbep-bulk-editing-container__unit-of-measure" data-unit-of-measure="<?php echo esc_attr( $currency ); ?>"><?php echo esc_html( $currency ); ?></span>
</span>
<span class="yith-wcbep-bulk-editing-toggle-sale-price-scheduling ">
	<?php esc_html_e( 'Edit on-sale price scheduling', 'yith-woocommerce-bulk-product-editing' ); ?>
</span>
