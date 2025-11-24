<?php
/**
 * Columns visibility modal content
 *
 * @package YITH\BulkProductEditing\Views\Modals\ColumnsVisibility
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-modal-content-wrapper yith-wcbep-column-visibility-modal-content-wrapper">
	<div class="yith-wcbep-column-visibility-modal-content-wrapper__shadow"></div>
	<div class="yith-wcbep-visibility-columns-actions">
		<div class="yith-wcbep-visibility-columns-select-all-action">
			<input type="checkbox" id="yith-wcbep-column-visibility-select-all" class="yith-wcbep-visibility-columns-select-all-action__input">
			<label class="yith-wcbep-visibility-columns-select-all-action__label" for="yith-wcbep-column-visibility-select-all"><?php esc_html_e( 'Select/Unselect all', 'yith-woocommerce-bulk-product-editing' ); ?></label>
		</div>
		<div class="yith-wcbep-visibility-columns-search-action">
			<input type="text" id="yith-wcbep-visibility-columns-search" class="yith-wcbep-visibility-columns-search__input" placeholder="<?php esc_attr_e( 'Search for a column...', 'yith-woocommerce-bulk-product-editing' ); ?>">
			<span class="yith-wcbep-visibility-columns-search-action__icon"></span>
		</div>
	</div>
	<div class="yith-wcbep-visibility-columns-container">
		<span class="yith-wcbep-visibility-columns--empty-search">
			<?php esc_html_e( 'There are no columns matching your search', 'yith-woocommerce-bulk-product-editing' ); ?>
		</span>
	</div>

</div>
