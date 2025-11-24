<?php
/**
 * Product table Tab.
 *
 * @package YITH\BulkProductEditing\Views
 * @since   2.0.0
 * @author  YITH <plugins@yithemes.com>
 *
 * @var string $selected_table_view The selected table view.
 * @var int    $products_per_page   The products per page.
 * @var array  $table_views         The table views list.
 * @var array  $bulk_actions        The bulk actions.
 */

if ( array_key_exists( 'manage_views', $table_views ) ) {
	unset( $table_views['manage_views'] );
}
$table_views['manage_views'] = __( 'Manage views', 'yith-woocommerce-bulk-product-editing' );
$fields                      = array(
	'table_view'        => array(
		'id'               => 'yith-wcbep-table-view',
		'class'            => 'wc-enhanced-select',
		'type'             => 'select',
		'options'          => $table_views,
		'disabled_options' => array( 'manage_views' ),
		'value'            => $selected_table_view,
	),
	'bulk_action'       => array(
		'id'      => 'yith-wcbep-bulk-action',
		'class'   => 'wc-enhanced-select',
		'type'    => 'select',
		'options' => $bulk_actions,
	),
	'products_per_page' => array(
		'id'    => 'yith-wcbep-products-table-products-per-page',
		'type'  => 'number',
		'value' => $products_per_page ?? 10,
		'min'   => 1,
	),
);

$enabled_fields = array_diff( YITH_WCBEP_List_Table::get_enabled_columns(), yith_wcbep_get_always_visible_columns() );

?>

<div class="yith-wcbep-products-table-and-options-wrapper">
	<div class="yith-wcbep-main-buttons">
		<button id="yith-wcbep-enable-fields" class="yith-plugin-fw__button--primary tips" data-tip="<?php esc_html_e( 'Choose which product info to enable for bulk editing', 'yith-woocommerce-bulk-product-editing' ); ?>">
			<?php esc_html_e( 'Enable fields', 'yith-woocommerce-bulk-product-editing' ); ?>
		</button>
		<button id="yith-wcbep-filter-products" class="yith-plugin-fw__button--primary tips" data-tip="<?php esc_html_e( 'Choose which products to show in the table', 'yith-woocommerce-bulk-product-editing' ); ?>">
			<?php esc_html_e( 'Filter products', 'yith-woocommerce-bulk-product-editing' ); ?>
		</button>
	</div>
	<div class="yith-wcbep-products-table-options">
		<div class="yith-wcbep-products-table-options_first-row">
			<div class="yith-wcbep-products-table-view-container">
				<span class="yith-wcbep-products-table-view-label"><?php esc_html_e( 'Table view', 'yith-woocommerce-bulk-product-editing' ); ?></span>
				<?php yith_plugin_fw_get_field( $fields['table_view'], true, false ); ?>
			</div>
			<div class="yith-wcbep-products-table-bulk-actions-container">
				<span class="yith-wcbep-products-table-bulk-actions-label"><?php esc_html_e( 'Bulk action', 'yith-woocommerce-bulk-product-editing' ); ?></span>
				<?php yith_plugin_fw_get_field( $fields['bulk_action'], true, false ); ?>
				<button id="yith-wcbep-products-table-apply-bulk-action" class="yith-wcbep-products-table-apply-bulk-action yith-plugin-fw__button--secondary yith-wcbep-button--disabled">
					<?php esc_html_e( 'Apply', 'yith-woocommerce-bulk-product-editing' ); ?>
					<span class="yith-wcbep-button-disabled-tooltip">
						<?php esc_html_e( 'No product selected: to make a bulk action, select one or more products.', 'yith-woocommerce-bulk-product-editing' ); ?>
					</span>
				</button>
			</div>
			<input type="text" id="yith-wcbep-search-for-a-product" class="yith-wcbep-search-for-a-product" placeholder="<?php esc_attr_e( 'Search for a product...', 'yith-woocommerce-bulk-product-editing' ); ?>">
			<?php if ( current_user_can( 'edit_products' ) ) : ?>
				<button id="yith-wcbep-products-table-add-product" class="yith-wcbep-products-table-add-product yith-plugin-fw__button--primary yith-wcbep-products-table-button__tip" data-tip="<?php esc_attr_e( 'Add new product', 'yith-woocommerce-bulk-product-editing' ); ?>"></button>
			<?php endif; ?>
			<button id="yith-wcbep-products-table-bulk-editing" class="yith-wcbep-products-table-bulk-editing yith-plugin-fw__button--primary yith-wcbep-button--disabled">
				<?php esc_html_e( 'Bulk editing', 'yith-woocommerce-bulk-product-editing' ); ?>
				<span class="yith-wcbep-products-table-bulk-editing-selected-product-count"></span>
				<span class="yith-wcbep-button-disabled-tooltip">
						<?php esc_html_e( 'No product selected: to bulk edit, select one or more products.', 'yith-woocommerce-bulk-product-editing' ); ?>
					</span>
			</button>
		</div>
		<div class="yith-wcbep-products-table-options_second-row">

			<button class="yith-wcbep-products-table-column-visibility yith-plugin-fw__button--secondary <?php echo ! $enabled_fields ? 'yith-wcbep-button--disabled' : ''; ?>">
				<?php esc_html_e( 'Show/Hide columns', 'yith-woocommerce-bulk-product-editing' ); ?>
				<span class="yith-wcbep-button-disabled-tooltip">
						<?php esc_html_e( 'No fields enabled: populate the products table by enabling some fields in step 1.', 'yith-woocommerce-bulk-product-editing' ); ?>
					</span>
			</button>
			<button class="yith-wcbep-products-table-undo yith-plugin-fw__button--secondary yith-icon yith-icon-undo-alt" disabled>
				<span class="yith-wcbep-products-table-button__tip" data-tip="<?php esc_attr_e( 'Undo', 'yith-woocommerce-bulk-product-editing' ); ?>"></span>
			</button>
			<button class="yith-wcbep-products-table-redo yith-plugin-fw__button--secondary yith-icon yith-icon-redo-alt" disabled>
				<span class="yith-wcbep-products-table-button__tip" data-tip="<?php esc_attr_e( 'Redo', 'yith-woocommerce-bulk-product-editing' ); ?>"></span>
			</button>
			<div class="yith-wcbep-products-table-products-per-page-container">
				<label for="yith-wcbep-products-table-products-per-page">
					<?php esc_html_e( 'Products per page', 'yith-woocommerce-bulk-product-editing' ); ?>
				</label>
				<?php yith_plugin_fw_get_field( $fields['products_per_page'], true, false ); ?>
			</div>
		</div>
	</div>
	<div class="yith-wcbep-products-wp-list-wrapper">
		<?php
		$table = new YITH_WCBEP_List_Table();
		$table->prepare_items();
		$class = '';
		if ( ! $table->items ) {
			$class = 'yith-wcbep-empty-state-due-filters';
		} elseif ( ! $enabled_fields ) {
			$class = 'yith-wcbep-empty-state-due-disabled-fields';
		}
		$args = array(
			'limit'  => 1,
			'return' => 'ids',
		);
		?>
		<div id="yith-wcbep-products-wp-list" class="<?php echo esc_attr( $class && ! wc_get_products( $args ) ? 'yith-wcbep-empty-state-zero-products' : $class ); ?>">
			<?php $table->display(); ?>
			<input type="hidden" id="yith-wcbep-products-table-current-page" value="1">
			<input type="hidden" id="yith-wcbep-products-table-total-pages" value="<?php echo absint( $table->get_pagination_arg( 'total_pages' ) ); ?>">
		</div>
	</div>
</div>

<!-- Export Modal  -->
<?php if ( current_user_can( 'edit_products' ) && current_user_can( 'export' ) ) : ?>
	<div class="yith-wcbep-export-form-container">
		<form id="yith-wcbep-export-form" class="woocommerce-exporter">
			<input style="display: none" type="checkbox" id="woocommerce-exporter-meta" checked value="1"/>
			<input type="hidden" id="yith-wcbep-export-form__selected-products" name="yith-wcbep-selected-products" value=""/>
			<progress class="woocommerce-exporter-progress" max="100" value="0"></progress>
		</form>
		<span class="yith-wcbep-export-form-progress">
		<span class="yith-wcbep-export-form-progress-thumb"></span>
	</span>
		<span class="yith-wcbep-export-form-label"><?php esc_html_e( 'CSV file download in progress', 'yith-woocommerce-bulk-product-editing' ); ?></span>
		<span class="yith-wcbep-export-form-description"><?php esc_html_e( 'The process will only need a few seconds.', 'yith-woocommerce-bulk-product-editing' ); ?></span>
	</div>
<?php endif; ?>

<!-- Floating Save Button  -->

<button id="yith-plugin-fw-float-save-button" class="yith-wcbep-products-table-save yith-plugin-fw__button--primary visible yith-wcbep-products-table-save--disabled"><?php esc_html_e( 'Save table', 'yith-woocommerce-bulk-product-editing' ); ?></button>

<!-- JS TEMPLATES -->

<script id="tmpl-yith-wcbep-table-view" type="text/template">
	<?php
	$placeholders = array(
		'view_key'          => '{{data.viewKey}}',
		'view_options'      => array(
			'name' => '{{data.viewName}}',
		),
		'view_options_json' => '{{data.viewOptionsJSON}}',
	);
	yith_wcbep_get_view( 'modals/table-views/view.php', $placeholders );
	?>
</script>

<script id="tmpl-yith-wcbep-edit-table-view" type="text/template">
	<?php yith_wcbep_get_view( 'modals/table-views/edit-view-template.php' ); ?>
</script>

<script id="tmpl-yith-wcbep-floating-edit-field" type="text/template">
	<?php yith_wcbep_get_view( 'floating-editing-fields/container.php' ); ?>
</script>

<script id="tmpl-yith-wcbep-columns-visibility-column" type="text/template">
	<?php
	$placeholders = array(
		'key'  => '{{data.key}}',
		'name' => '{{data.name}}',
	);
	yith_wcbep_get_view( 'modals/columns-visibility/column.php', $placeholders );
	?>
</script>

<script id="tmpl-yith-wcbep-bulk-editing-upload-multiple-images-image" type="text/template">
	<?php yith_wcbep_get_view( 'bulk-editing-fields/upload-multiple-images-image-template.php' ); ?>
</script>

<?php
/**
 * APPLY_FILTERS: yith_wcbep_floating_editing_fields
 *
 * Filters the floating editing fields templates.
 *
 * @param string[] $floating_editing_fields The floating editing fields.
 *
 * @return string[]
 */
$floating_editing_fields = apply_filters(
	'yith_wcbep_floating_editing_fields',
	array(
		'tag',
		'date',
		'text',
		'date',
		'image',
		'onoff',
		'price',
		'number',
		'status',
		'category',
		'products',
		'taxonomy',
		'attribute',
		'tax-class',
		'sale-price',
		'tax-status',
		'visibility',
		'text-editor',
		'product-type',
		'stock-status',
		'regular-price',
		'image-gallery',
		'shipping-class',
		'allow-backorders',
		'downloadable-file',
		'downloadable-files',
		'image-gallery-element',
	)
);

foreach ( $floating_editing_fields as $floating_editing_field ) {
	echo '<script id="tmpl-yith-wcbep-floating-editing-field-' . esc_attr( $floating_editing_field ) . '" type="text/template">';
	if ( file_exists( YITH_WCBEP_VIEWS_PATH . "floating-editing-fields/$floating_editing_field.php" ) ) {
		yith_wcbep_get_view( "floating-editing-fields/$floating_editing_field.php" );
	} else {
		do_action( 'yith_wcbep_render_floating_editing_field', $floating_editing_field );
	}
	echo '</script>';
}

/**
 * APPLY_FILTERS: yith_wcbep_table_views_conditions_templates
 *
 * Filters the table view conditions template to print.
 *
 * @param string[] $condition_templates The condition templates.
 *
 * @return string[]
 */
$condition_templates = apply_filters(
	'yith_wcbep_table_views_conditions_templates',
	array(
		'condition',
		'text',
		'price',
		'number',
		'status',
		'product-visibility',
		'stock-status',
		'backorder',
		'shipping-class',
		'custom-taxonomy',
		'product-type',
		'category',
		'tag',
		'attribute',
		'featured',
		'on-sale',
		'image',
		'virtual',
		'downloadable',
		'tax-status',
		'tax-class',
	)
);

foreach ( $condition_templates as $condition_template ) {
	echo '<script id="tmpl-yith-wcbep-edit-table-view-' . esc_attr( $condition_template ) . '" type="text/template">';
	if ( file_exists( YITH_WCBEP_VIEWS_PATH . "modals/table-views/conditions/$condition_template.php" ) ) {
		yith_wcbep_get_view( "modals/table-views/conditions/$condition_template.php" );
	} else {
		do_action( 'yith_wcbep_table_views_conditions_template', $condition_template );
	}
	echo '</script>';

}
?>

<script type="text/template" id="tmpl-yith-wcbep-enable-fields-custom-field">
	<?php
	$key           = '{{data.fieldKey}}';
	$label         = '{{data.fieldLabel}}';
	$classes       = array( 'yith-wcbep-enable-fields__field', '{{data.fieldClasses}}' );
	$data          = array(
		'key'   => '{{data.fieldKey}}',
		'id'    => '{{data.fieldId}}',
		'label' => '{{data.fieldLabel}}',
		'type'  => '{{data.fieldType}}',
		'kind'  => '{{data.fieldKind}}',
	);
	$enabled       = true;
	$print_actions = true;
	$kind          = 'custom_field';
	yith_wcbep_get_view( 'modals/enable-fields/field.php', compact( 'key', 'kind', 'label', 'enabled', 'classes', 'print_actions', 'data' ) );
	?>
</script>

<script type="text/template" id="tmpl-yith-wcbep-enable-fields-taxonomy-field">
	<?php
	$data = array(
		'kind'     => '{{data.fieldKind}}',
		'key'      => '{{data.fieldKey}}',
		'taxonomy' => '{{data.fieldTaxonomy}}',
		'label'    => '{{data.fieldLabel}}',
	);
	$kind = 'taxonomy';
	yith_wcbep_get_view( 'modals/enable-fields/field.php', compact( 'key', 'kind', 'label', 'enabled', 'classes', 'print_actions', 'data' ) );
	?>
</script>

<script type="text/template" id="tmpl-yith-wcbep-enable-fields-edit-field">
	<div class="yith-wcbep-enable-fields__edit-field">
		<table class="yith-wcbep-enable-fields__edit-field-options">
			<?php foreach ( array_diff_key( yith_wcbep_get_enable_fields_add_custom_column_fields(), array_flip( array( 'kind' ) ) ) as $key => $field ) : ?>
				<tr>
					<?php $field['id'] = 'yith-wcbep-edit-field-' . $key; ?>
					<td>
						<label for="<?php echo esc_attr( $field['id'] ?? '' ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
					</td>
					<td>
							<span class="yith-wcbep-edit-field__option-input">
								<?php yith_plugin_fw_get_field( $field, true, false ); ?>
							</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<div class="yith-wcbep-enable-fields__edit-field-buttons">
			<button class="yith-plugin-fw__button--secondary yith-wcbep-enable-fields__edit-field__cancel-button"><?php echo esc_html__( 'Cancel', 'yith-woocommerce-bulk-product-editing' ); ?></button>
			<button class="yith-plugin-fw__button--primary yith-wcbep-enable-fields__edit-field__save-button"><?php echo esc_html__( 'Save', 'yith-woocommerce-bulk-product-editing' ); ?></button>
		</div>
	</div>
</script>

<?php
foreach ( array_keys( yith_wcbep_get_custom_fields_types_options() ) as $custom_field_type ) {
	echo '<script type="text/template" id="tmpl-yith-wcbep-bulk-editing-custom-field-' . esc_attr( $custom_field_type ) . '">';
	$args = array(
		'id'    => '{{data.id}}',
		'label' => '{{data.label}}',
		'type'  => $custom_field_type,
	);
	if ( file_exists( YITH_WCBEP_VIEWS_PATH . 'bulk-editing-fields/' . $custom_field_type . '.php' ) ) {
		yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
	} else {
		do_action( 'yith_wcbep_bulk_editing_custom_field_' . $custom_field_type . '_teamplate' );
	}
	echo '</script>';
}
?>

<script type="text/template" id="tmpl-yith-wcbep-bulk-editing-custom-taxonomy">
	<?php
	$args = array(
		'id'       => '{{data.id}}',
		'taxonomy' => '{{data.taxonomy}}',
		'label'    => '{{data.label}}',
		'type'     => 'taxonomy',
	);
	yith_wcbep_get_view( 'bulk-editing-fields/field.php', $args );
	?>
</script>
