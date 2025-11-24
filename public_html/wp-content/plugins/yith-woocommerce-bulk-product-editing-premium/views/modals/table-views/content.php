<?php
/**
 * Table Views modal content
 *
 * @var array $table_views The table views.
 *
 * @package YITH\BulkProductEditing\Views\Modals\TableViews
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-modal-content-wrapper">
	<span class="yith-wcbep-modal-description"><?php esc_html_e( 'Create custom views and choose which products to show in the bulk editing table.', 'yith-woocommerce-bulk-product-editing' ); ?></span>
	<div class="yith-wcbep-table-views-list">
		<?php
		foreach ( $table_views as $view_key => $view_options ) {
			yith_wcbep_get_view( 'modals/table-views/view.php', compact( 'view_key', 'view_options' ) );
		}
		?>
		<div class="yith-wcbep-table-view-wrapper yith-wcbep-table-view-create">
			<span class="yith-wcbep-table-view-create-label"><?php esc_html_e( '+ Create new view', 'yith-woocommerce-bulk-product-editing' ); ?></span>
		</div>
	</div>
</div>
