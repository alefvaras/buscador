<?php
/**
 * Downloadable file template for floating editing field.
 *
 * @package YITH\BulkProductEditing\Views\FloatingEditingFields
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-downloadable-file">
	<span class="yith-wcbep-downloadable-file-drag-handler"></span>
	<div class="yith-wcbep-downloadable-file__name">
		<input type="text" placeholder="<?php echo esc_attr_x( 'File name', 'Downloadable files field placeholder', 'yith-woocommerce-bulk-product-editing' ); ?>" value="{{data.name}}">
	</div>
	<div class="yith-wcbep-downloadable-file__file">
		<input type="text" placeholder="https://" value="{{data.file}}">
	</div>
	<span class="yith-wcbep-downloadable-file-upload yith-icon yith-icon-upload"></span>
	<span class="yith-wcbep-downloadable-file-trash yith-icon yith-icon-trash"></span>
</div>
