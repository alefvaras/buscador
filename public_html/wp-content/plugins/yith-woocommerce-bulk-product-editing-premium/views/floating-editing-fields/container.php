<?php
/**
 * Floating editing field container.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-floating-editing-field-container">
	{{{data.editingField}}}
	<div class="yith-wcbep-floating-editing-field-footer">
		<button class="yith-plugin-fw__button--primary yith-wcbep-floating-editing-field__apply-button"><?php esc_html_e( 'Apply', 'yith-woocommerce-bulk-product-editing' ); ?></button>
		<button class="yith-wcbep-floating-editing-field__cancel-button"><?php esc_html_e( 'Cancel', 'yith-woocommerce-bulk-product-editing' ); ?></button>
	</div>
</div>
