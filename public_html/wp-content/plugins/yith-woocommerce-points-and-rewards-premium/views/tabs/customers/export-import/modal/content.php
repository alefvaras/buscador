<?php
/**
 * View to manage the import/export
 *
 * @package YITH\GiftCards\Views
 */

$export_import = array(
	'id'      => 'ywpar-export-import-radio-modal',
	'name'    => 'ywpar-export-import-radio-modal',
	'type'    => 'radio',
	'options' => array(
		'export' => esc_html_x(
			'Export points',
			'[Admin Import/Export modal] Radio input label',
			'yith-woocommerce-points-and-rewards'
		),
		'import' => esc_html_x(
			'Import points',
			'[Admin Import/Export modal] Radio input label',
			'yith-woocommerce-points-and-rewards'
		),
	),
	'default' => 'export',
	'value'   => 'export',
);


wp_enqueue_media();
?>
<script type="text/template" id="ywpar-modal" data-template="ywpar-modal">

	<div class="ywpar-export-import-modal-content yith-plugin-ui">
		<div class="steps-content">
			<!-- Step 1  -->
			<div id="step-1" data-step="1" class="single-step active">
				<?php yith_plugin_fw_get_field( $export_import, true ); ?>

				<footer>
					<button class="yith-plugin-fw__button--primary ywpar-export-import-modal-button move-step"
							data-step-to="2">
						<?php
						echo esc_html_x(
							'Continue',
							'Link to continue to the next step in the export/import modal',
							'yith-woocommerce-points-and-rewards'
						);
						?>
					</button>
					<small class="alert-notice"></small>
				</footer>
			</div>

			<!-- Step 2  -->
			<div id="step-2" data-step="2" class="single-step">
				<?php require_once YITH_YWPAR_VIEWS_PATH . '/tabs/customers/export-import/modal/form.php'; ?>

				<?php require_once YITH_YWPAR_VIEWS_PATH . '/tabs/customers/export-import/modal/buttons.php'; ?>
			</div>

			<!-- Step 3  -->
			<div id="step-3" data-step="3" class="single-step configuration">
				<?php require_once YITH_YWPAR_VIEWS_PATH . '/tabs/customers/export-import/modal/completed.php'; ?>
			</div>

			<?php wp_nonce_field( 'yith-ywpar-import' ); ?>
		</div>
	</div>
</script>
