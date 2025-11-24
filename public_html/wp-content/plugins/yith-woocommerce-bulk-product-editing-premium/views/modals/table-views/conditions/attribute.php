<?php
/**
 * Table views modal - Attribute condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$attributes = wc_get_attribute_taxonomies();
$fields     = array(
	'condition' => array(
		'class'   => 'yith-wcbep-table-view-attribute-condition wc-enhanced-select',
		'type'    => 'select',
		'options' => array(
			'all'     => __( 'All', 'yith-woocommerce-bulk-product-editing' ),
			'has'     => __( 'Has', 'yith-woocommerce-bulk-product-editing' ),
			'has-not' => __( 'Does not have', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'attribute' => array(
		'class'    => 'yith-wcbep-table-view-attribute-condition__value yith-term-search',
		'type'     => 'ajax-terms',
		'multiple' => true,
		'data'     => array(
			'placeholder'          => __( 'Search for attribute terms&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'minimum_input_length' => apply_filters( 'yith_wcbep_term_search_minimum_input_lenght', 3 ),
		),
	),
);
?>

<div class="yith-wcbep-table-view-attributes-condition">
	<?php foreach ( $attributes as $attribute ) : ?>
		<div class="yith-wcbep-table-view-attribute yith-wcbep-table-view-attribute__<?php echo esc_attr( $attribute->attribute_id ); ?>" data-attribute-id="<?php echo esc_attr( $attribute->attribute_id ); ?>">
		<span class="yith-wcbep-table-view-attribute__label">
			<?php echo esc_html( $attribute->attribute_label ?? '' ); ?>
		</span>
			<span class="yith-wcbep-table-view-attribute__field">
				<?php
				$fields['attribute']['data']['taxonomy'] = 'pa_' . $attribute->attribute_name ?? '';
				yith_plugin_fw_get_field( $fields['condition'], true, false );
				yith_plugin_fw_get_field( $fields['attribute'], true, false );
				?>
			</span>
		</div>
	<?php endforeach; ?>
</div>
