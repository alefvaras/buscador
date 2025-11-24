<?php
/**
 * Bulk Editing modal content
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\BulkEditing
 * @var string[] $tabs The modal tabs.
 *
 * @since   2.0.0
 */

?>

<div class="yith-wcbep-modal-content-wrapper">
	<span class="yith-wcbep-modal-description">
		<?php esc_html_e( 'With the Bulk Editor you can only edit the enabled product fields. If you can\'t find a field, click on the "Enable fields" button and enable or add it. ', 'yith-woocommerce-bulk-product-editing' ); ?>
	</span>
	<div class="yith-wcbep-bulk-editing-container">
		<ul class="yith-plugin-fw__tabs">
			<?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
				<li class="yith-plugin-fw__tab">
					<a class="yith-plugin-fw__tab__handler" href="#yith-wcbep-bulk-editing-<?php echo esc_attr( $tab_id ); ?>"><?php echo esc_html( $tab_name ); ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( $tabs as $tab_id => $tab_name ) : ?>
			<div class="yith-plugin-fw__tab-panel" id="yith-wcbep-bulk-editing-<?php echo esc_attr( $tab_id ); ?>">
				<?php
				do_action( 'yith_wcbep_bulk_editing_modal_before_tab_content', $tab_id, $tab_name );
				yith_wcbep_get_view( 'modals/bulk-editing/tabs/' . $tab_id . '.php' );
				do_action( 'yith_wcbep_bulk_editing_modal_after_tab_content', $tab_id, $tab_name );
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
