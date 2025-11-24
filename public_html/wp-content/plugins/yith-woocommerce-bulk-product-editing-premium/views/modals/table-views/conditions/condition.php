<?php
/**
 * Table views modal - Condition type field template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-edit-table-view-condition">
	<label class="yith-wcbep-edit-table-view-condition-type-label"><?php esc_html_e( 'Set condition', 'yith-woocommerce-bulk-product-editing' ); ?></label>

	<div class="yith-wcbep-edit-table-view-condition-options">
		<div class="yith-wcbep-edit-table-view-condition-type">
			<?php
			$condition_type_field = array(
				'type'    => 'select',
				'class'   => 'yith-wcbep-edit-table-view-condition-type-select wc-enhanced-select',
				'options' => array_merge( array( '' => '' ), yith_wcbep_get_table_views_condition_types() ),
				'data'    => array(
					'placeholder' => __( 'Choose a condition', 'yith-plugin-fw' ),
				),
				'value'   => '{{data.condition}}',
			);
			yith_plugin_fw_get_field( $condition_type_field, true, false );
			?>
			<span class="yith-icon yith-icon-trash yith-wcbep-edit-table-view-remove-condition"></span>
		</div>
		<div class="yith-wcbep-edit-table-view-condition-option"></div>
	</div>
</div>
