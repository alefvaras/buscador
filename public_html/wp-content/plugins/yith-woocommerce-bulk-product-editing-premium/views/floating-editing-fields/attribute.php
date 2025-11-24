<?php
/**
 * Attribute floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$input = array(
	'class'    => 'yith-wcbep-floating-editing-field__attribute yith-term-search',
	'type'     => 'ajax-terms',
	'data'     => array(
		'placeholder'          => __( 'Search for a term&hellip;', 'yith-woocommerce-bulk-product-editing' ),
		'taxonomy'             => '{{data.taxonomy}}',
		'minimum_input_length' => apply_filters( 'yith_wcbep_term_search_minimum_input_lenght', 3 ),
	),
	'multiple' => true,
);

?>

<div class="yith-wcbep-attribute-options-container">
	<div class="yith-wcbep-attribute-options">
		<div class="yith-wcbep-attribute-options__is-visible">
			<input type="checkbox" id="yith-wcbep-attribute-options__is-visible">
			<label for="yith-wcbep-attribute-options__is-visible"><?php esc_html_e( 'Is visible', 'yith-woocommerce-bulk-product-editing' ); ?></label>
		</div>
		<div class="yith-wcbep-attribute-options__is-variation">
			<input type="checkbox" id="yith-wcbep-attribute-options__is-variation">
			<label for="yith-wcbep-attribute-options__is-variation"><?php esc_html_e( 'Used for variations', 'yith-woocommerce-bulk-product-editing' ); ?></label>
		</div>
	</div>
	<div class="yith-wcbep-attribute-terms">
		<?php yith_plugin_fw_get_field( $input, true, false ); ?>
	</div>
</div>

