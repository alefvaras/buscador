<?php
/**
 * Add product modal content
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing\Views\Modals\AddProduct
 * @since   2.0.0
 */

$fields = apply_filters(
	'yith_wcbep_filter_products_fields',
	array(
		'title'              => array(
			'label' => __( 'Product name', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'text',
		),
		'desc'               => array(
			'label' => __( 'Description', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'text',
		),
		'sku'                => array(
			'label' => __( 'SKU', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'text',
		),
		'regular-price'      => array(
			'label' => __( 'Regular price', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'price',
			'class' => 'wc_input_price',
		),
		'sale-price'         => array(
			'label' => __( 'Sale price', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'price',
			'class' => 'wc_input_price',
		),
		'height'             => array(
			'label'           => __( 'Height', 'yith-woocommerce-bulk-product-editing' ),
			'type'            => 'decimal',
			'unit_of_measure' => $unit_of_measure = get_option( 'woocommerce_dimension_unit' ),
		),
		'width'              => array(
			'label'           => __( 'Width', 'yith-woocommerce-bulk-product-editing' ),
			'type'            => 'decimal',
			'unit_of_measure' => $unit_of_measure = get_option( 'woocommerce_dimension_unit' ),
		),
		'length'             => array(
			'label'           => __( 'Length', 'yith-woocommerce-bulk-product-editing' ),
			'type'            => 'decimal',
			'unit_of_measure' => $unit_of_measure = get_option( 'woocommerce_dimension_unit' ),
		),
		'weight'             => array(
			'label'           => __( 'Weight', 'yith-woocommerce-bulk-product-editing' ),
			'type'            => 'decimal',
			'unit_of_measure' => $unit_of_measure = get_option( 'woocommerce_weight_unit' ),
		),
		'stock-quantity'     => array(
			'label' => __( 'Stock quantity', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'decimal',
		),
		'stock-status'       => array(
			'label'   => __( 'Stock status', 'yith-woocommerce-bulk-product-editing' ),
			'type'    => 'select',
			'options' => wc_get_product_stock_status_options(),
			'data'    => array(
				'placeholder' => __( 'Choose a status', 'yith-woocommerce-bulk-product-editing' ),
			),
		),
		'category'           => array(
			'label' => __( 'Categories', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'terms',
			'data'  => array(
				'placeholder' => __( 'Search for categories&hellip;', 'yith-woocommerce-bulk-product-editing' ),
				'taxonomy'    => 'product_cat',
			),
		),
		'tag'                => array(
			'label' => __( 'Tags', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'terms',
			'data'  => array(
				'placeholder' => __( 'Search for tags&hellip;', 'yith-woocommerce-bulk-product-editing' ),
				'taxonomy'    => 'product_tag',
			),
		),
		'status'             => array(
			'label'   => __( 'Status', 'yith-woocommerce-bulk-product-editing' ),
			'type'    => 'select',
			'options' => yith_wcbep_get_product_statuses(),
			'data'    => array(
				'placeholder' => __( 'Choose a status', 'yith-woocommerce-bulk-product-editing' ),
			),
		),
		'product-visibility' => array(
			'label'   => __( 'Catalog visibility', 'yith-woocommerce-bulk-product-editing' ),
			'type'    => 'select',
			'options' => wc_get_product_visibility_options(),
			'data'    => array(
				'placeholder' => __( 'Choose a visibility', 'yith-woocommerce-bulk-product-editing' ),
			),
		),
		'backorder'          => array(
			'label'   => __( 'Backorder', 'yith-woocommerce-bulk-product-editing' ),
			'type'    => 'select',
			'options' => wc_get_product_backorder_options(),
			'data'    => array(
				'placeholder' => __( 'Choose a backorder status', 'yith-woocommerce-bulk-product-editing' ),
			),
		),
		'shipping-class'     => array(
			'label' => __( 'Shipping class', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'terms',
			'data'  => array(
				'placeholder' => __( 'Search for a shipping class&hellip;', 'yith-woocommerce-bulk-product-editing' ),
				'taxonomy'    => 'product_shipping_class',
			),
		),
		'product-type'       => array(
			'label' => __( 'Product type', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'product-type',
		),
	)
);

?>

<div class="yith-wcbep-modal-content-wrapper yith-wcbep-filter-products-modal-content-wrapper">
	<div class="yith-wcbep-product-filters">
		<?php foreach ( $fields as $field_id => $field ) : ?>
			<?php $field['id'] = 'yith-wcbep-filter-products-' . $field_id . '-field'; ?>
			<div class="yith-wcbep-filter-products-field-wrapper yith-wcbep-filter-products-field-<?php echo esc_attr( $field_id ); ?>-wrapper  yith-wcbep-filter-products-field-<?php echo esc_attr( $field['type'] ); ?>-type" data-filter-type="<?php echo esc_attr( $field['type'] ); ?>" data-filter-id="<?php echo esc_attr( $field_id ); ?>">
				<label for="<?php echo esc_attr( $field['id'] ); ?>" class="yith-wcbep-filter-products-field__label yith-wcbep-filter-products-field-<?php echo esc_attr( $field_id ); ?>__label">
					<?php echo esc_html( $field['label'] ?? '' ); ?>
				</label>
				<div class="yith-wcbep-filter-products-field yith-wcbep-filter-products-field-<?php echo esc_attr( $field_id ); ?>">
					<?php
					$condition_fields = array();
					if ( file_exists( __DIR__ . '/fields/' . $field['type'] . '.php' ) ) {
						yith_wcbep_get_view( 'modals/filter-products/fields/' . $field['type'] . '.php', compact( 'field_id', 'field' ) );
					} else {
						do_action( 'yith_wcbep_filter_products_render_' . $field['type'] . '_fields' );
					}

					?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="yith-wcbep-save-as-table-view-wrapper">
		<div class="yith-wcbep-save-as-table-view">
			<label class="yith-wcbep-save-as-table__label"><?php esc_html_e( 'Save as table view', 'yith-woocommerce-bulk-product-editing' ); ?></label>
			<span class="yith-wcbep-save-as-table__input">
				<?php
				$save_as_table_view_field_options = array(
					'id'   => 'yith-wcbep-save-as-table-view',
					'type' => 'onoff',
				);

				yith_plugin_fw_get_field( $save_as_table_view_field_options, true, false );
				?>
			</span>
		</div>
		<div class="yith-wcbep-new-table-view-name">
			<label class="yith-wcbep-new-table-view-name__label"><?php esc_html_e( 'Table view name', 'yith-woocommerce-bulk-product-editing' ); ?></label>
			<span class="yith-wcbep-new-table-view-name__input">
				<?php
				$save_as_table_view_field_options = array(
					'id'   => 'yith-wcbep-new-table-view-name',
					'type' => 'text',
				);

				yith_plugin_fw_get_field( $save_as_table_view_field_options, true, false );
				?>
				<span class="yith-wcbep-new-table-view-name__invalid-message">
					<?php esc_html_e( 'Enter a name to identify this view', 'yith-woocommerce-bulk-product-editing' ); ?>
				</span>
			</span>
		</div>
	</div>
</div>
