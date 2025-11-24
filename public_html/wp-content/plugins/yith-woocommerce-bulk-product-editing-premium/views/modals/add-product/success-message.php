<?php
/**
 * Add product modal success message
 *
 * @package YITH\BulkProductEditing\Views\Modals\AddProduct
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-add-product-modal-success-message">
	<div class="yith-wcbep-add-product-modal-success-message-header">
		<?php esc_html_e( 'Product successfully created!', 'yith-woocommerce-bulk-product-editing' ); ?>
	</div>
	<div class="yith-wcbep-add-product-modal-success-message-content">
		<?php echo wp_kses( __( '<b>Note:</b> If you can\'t see this product in the table, check and adjust the parameters of the active table view.', 'yith-woocommerce-bulk-product-editing' ), array( 'b' => array() ) ); ?>
	</div>
</div>
