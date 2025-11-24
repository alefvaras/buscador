<?php
/**
 * Table views modal - Custom Taxonomy class condition template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-custom-taxonomy-condition">
	<?php
	$compare_field = array(
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-custom-taxonomy-condition__compare',
		'type'    => 'select',
		'options' => array(
			'is'     => __( 'Is', 'yith-woocommerce-bulk-product-editing' ),
			'is-not' => __( 'Is not', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $compare_field, true, false );

	$select_field = array(
		'class'    => 'yith-wcbep-table-view-custom-taxonomy-condition__value yith-term-search',
		'type'     => 'ajax-terms',
		'data'     => array(
			'placeholder' => __( 'Search for a term&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => '{{data.taxonomyID}}',
		),
		'multiple' => true,
	);
	yith_plugin_fw_get_field( $select_field, true, false );
	?>
</div>
