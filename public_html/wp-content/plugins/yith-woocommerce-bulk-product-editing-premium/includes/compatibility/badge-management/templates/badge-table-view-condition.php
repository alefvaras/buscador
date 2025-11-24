<?php
/**
 * Table views modal - Badge condition template
 *
 * @package YITH\BulkProductEditing\Compatibility\BadgeManagement
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-badge-condition">
	<?php
	$compare_field = array(
		'class'   => 'wc-enhanced-select yith-wcbep-table-view-badge-condition__compare',
		'type'    => 'select',
		'options' => array(
			'has'     => __( 'Has badges', 'yith-woocommerce-bulk-product-editing' ),
			'has-not' => __( 'Does not have badges', 'yith-woocommerce-bulk-product-editing' ),
		),
	);
	yith_plugin_fw_get_field( $compare_field, true, false );
	?>
</div>
