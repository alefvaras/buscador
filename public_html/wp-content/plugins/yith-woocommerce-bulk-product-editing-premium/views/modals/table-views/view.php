<?php
/**
 * Table view template
 *
 * @var string $view_key          The view key.
 * @var array  $view_options      The view options.
 * @var array  $view_options_json The view options in a JSON string.
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-table-view-container" data-view-properties="<?php echo esc_attr( $view_options_json ?? wp_json_encode( $view_options ) ); ?>" data-view-key="<?php echo esc_attr( $view_key ); ?>">
	<div class="yith-wcbep-table-view-wrapper">
		<span class="yith-wcbep-table-view">
			<label class="yith-wcbep-table-view-input-label">
				<input type="radio" id="yith-wcbep-table-view-<?php echo esc_attr( $view_key ); ?>" class="yith-wcbep-table-view-input" name="yith-wcbep-table-view" value="<?php echo esc_attr( $view_key ); ?>">
				<label for="yith-wcbep-table-view-<?php echo esc_attr( $view_key ); ?>" class="yith-wcbep-table-view-name"><?php echo esc_html( $view_options['name'] ?? '' ); ?></label>
			</label>
			<?php if ( 'table-view-all' !== $view_key ) : ?>
				<span class="yith-wcbep-table-view-actions">
					<button class="yith-wcbep-table-view-action yith-wcbep-table-view-action-edit yith-icon-edit" title="<?php esc_attr_e( 'Edit', 'yith-woocommerce-bulk-product-editing' ); ?>"></button>
					<button class="yith-wcbep-table-view-action yith-wcbep-table-view-action-clone yith-icon-clone" title="<?php esc_attr_e( 'Duplicate', 'yith-woocommerce-bulk-product-editing' ); ?>"></button>
					<button class="yith-wcbep-table-view-action yith-wcbep-table-view-action-delete yith-icon-trash" title="<?php esc_attr_e( 'Delete', 'yith-woocommerce-bulk-product-editing' ); ?>"></button>
				</span>
			<?php endif; ?>
		</span>
	</div>
	<?php if ( 'table-view-all' !== $view_key ) : ?>
		<div class="yith-wcbep-table-view-delete-confirmation">
			<span class="yith-wcbep-table-view-delete-confirmation__message"><?php esc_html_e( 'Delete this view?', 'yith-woocommerce-bulk-product-editing' ); ?></span>
			<button class="yith-wcbep-table-view-delete-confirmation__confirm yith-plugin-fw__button--delete"><?php esc_html_e( 'Yes, delete', 'yith-woocommerce-bulk-product-editing' ); ?></button>
			<button class="yith-wcbep-table-view-delete-confirmation__not-confirm yith-plugin-fw__button--secondary"><?php esc_html_e( 'No', 'yith-woocommerce-bulk-product-editing' ); ?></button>
		</div>
	<?php endif; ?>
</div>
