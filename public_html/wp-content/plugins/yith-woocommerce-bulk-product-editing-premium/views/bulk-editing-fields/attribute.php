<?php
/**
 * Attribute field - Bulk editing
 *
 * @var string $id    The field id.
 * @var string $label The field label.
 *
 * @package YITH\BulkProductEditing\Views\BulkEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'action'             => array(
		'type'    => 'select',
		'id'      => 'yith-wcbep-bulk-editing-attribute-' . $id . '-action',
		'class'   => 'wc-enhanced-select yith-wcbep-bulk-editing-field-action',
		'options' => array(
			''       => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
			'new'    => __( 'Set new', 'yith-woocommerce-bulk-product-editing' ),
			'add'    => __( 'Add', 'yith-woocommerce-bulk-product-editing' ),
			'remove' => __( 'Remove', 'yith-woocommerce-bulk-product-editing' ),
			'empty'  => __( 'Empty', 'yith-woocommerce-bulk-product-editing' ),
		),
	),
	'attribute-selector' => array(
		'type'     => 'ajax-terms',
		'class'    => 'yith-wcbep-bulk-editing-attribute yith-term-search',
		'data'     => array(
			'placeholder'          => __( 'Search for attribute terms&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'             => 'pa_' . $id,
			'minimum_input_length' => apply_filters( 'yith_wcbep_term_search_minimum_input_lenght', 3 ),
		),
		'multiple' => true,
	),
	'advanced-options'   => array(
		'visible-on-product-page' => array(
			'label'   => _x( 'Visible on product page', 'Bulk editing - attributes advanced options', 'yith-woocommerce-bulk-product-editing' ),
			'type'    => 'select',
			'class'   => 'wc-enhanced-select',
			'options' => array(
				''    => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
				'yes' => __( 'Yes', 'yith-woocommerce-bulk-product-editing' ),
				'no'  => __( 'No', 'yith-woocommerce-bulk-product-editing' ),
			),
		),
		'used-for-variation'      => array(
			'label'   => _x( 'Used for variations', 'Bulk editing - attributes advanced options', 'yith-woocommerce-bulk-product-editing' ),
			'type'    => 'select',
			'class'   => 'wc-enhanced-select',
			'options' => array(
				''    => __( 'No changes', 'yith-woocommerce-bulk-product-editing' ),
				'yes' => __( 'Yes', 'yith-woocommerce-bulk-product-editing' ),
				'no'  => __( 'No', 'yith-woocommerce-bulk-product-editing' ),
			),
		),
		'default-term'            => array(
			'type'  => 'ajax-terms',
			'label' => _x( 'Default', 'Bulk editing - attributes advanced options', 'yith-woocommerce-bulk-product-editing' ),
			'class' => 'yith-term-search',
			'data'  => array(
				'placeholder'          => __( 'Search for attribute terms&hellip;', 'yith-woocommerce-bulk-product-editing' ),
				'taxonomy'             => 'pa_' . $id,
				'minimum_input_length' => apply_filters( 'yith_wcbep_term_search_minimum_input_lenght', 3 ),
			),
		),
	),
);

yith_plugin_fw_get_field( $fields['action'], true, false );
?>
<span class="yith-wcbep-bulk-editing-attribute-container yith-wcbep-bulk-editing-first-value-container">
		<?php yith_plugin_fw_get_field( $fields['attribute-selector'], true, false ); ?>
</span>

<div class="yith-wcbep-bulk-editing-field-advanced-options yith-wcbep-bulk-editing-attribute-advanced-options">
	<?php foreach ( $fields['advanced-options'] as $option_id => $option_field ) : ?>
		<div class="yith-wcbep-bulk-editing-field-advanced-option yith-wcbep-bulk-editing-field-advanced-option-<?php echo esc_attr( $option_id ); ?> ">
			<span class="yith-wcbep-bulk-editing-field-advanced-option__label"><?php echo esc_html( $option_field['label'] ?? '' ); ?></span>
			<span class="yith-wcbep-bulk-editing-field-advanced-option__field">
				<?php
				$option_field['id'] = "yith-wcbep-bulk-editing-attribute-{$id}-{$option_id}";

				$option_field['class'] .= " yith-wcbep-bulk-editing-attribute-{$option_id}";
				yith_plugin_fw_get_field( $option_field, true, false );
				?>
			</span>
		</div>
	<?php endforeach; ?>
</div>
