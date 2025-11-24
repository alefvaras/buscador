<?php
/**
 * Table views modal - Edit view template
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>
<div class="yith-wcbep-edit-table-view-form">
	<div class="yith-wcbep-edit-table-view-name-wrapper">
		<label for="yith-wcbep-edit-table-view-name" class="yith-wcbep-edit-table-view-name"><?php esc_html_e( 'View name', 'yith-woocommerce-bulk-product-editing' ); ?></label>
		<?php
		$name_field = array(
			'type'  => 'text',
			'id'    => 'yith-wcbep-edit-table-view-name',
			'value' => '{{data.viewName}}',
		);
		yith_plugin_fw_get_field( $name_field, true, false );
		?>
	</div>
	<span class="yith-wcbep-edit-table-view-mandatory-name-message"><?php esc_html_e( 'Enter a name to identify this view', 'yith-woocommerce-bulk-product-editing' ); ?></span>
	<div class="yith-wcbep-edit-table-view-form-conditions">
	</div>
	<span class="yith-wcbep-edit-table-view-mandatory-condition-message"><?php esc_html_e( 'Set at least one condition to save the view', 'yith-woocommerce-bulk-product-editing' ); ?></span>
	<span class="yith-wcbep-edit-table-view-add-condition"><?php esc_html_e( '+ Add condition', 'yith-woocommerce-bulk-product-editing' ); ?></span>
	<div class="yith-wcbep-edit-table-view__buttons">
		<button class="yith-wcbep-edit-table-view__button yith-wcbep-edit-table-view-cancel-button yith-plugin-fw__button--secondary"><?php esc_html_e( 'Cancel', 'yith-woocommerce-bulk-product-editing' ); ?></button>
		<button class="yith-wcbep-edit-table-view__button yith-wcbep-edit-table-view-save-button yith-plugin-fw__button--primary"><?php esc_html_e( 'Save view', 'yith-woocommerce-bulk-product-editing' ); ?></button>
	</div>
</div>
