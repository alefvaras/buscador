<?php
/**
 * Add product modal content
 *
 * @package YITH\BulkProductEditing\Views\Modals\AddProduct
 * @author  YITH <plugins@yithemes.com>
 * @since   2.0.0
 */

$fields = array(
	'title'             => array(
		'type'  => 'text',
		'label' => __( 'Product name', 'yith-woocommerce-bulk-product-editing' ),
	),
	'image'             => array(
		'id'     => 'yith-wcbep-add-product-image-uploader',
		'type'   => 'custom',
		'action' => 'yith_wcbep_print_product_image_field',
		'label'  => __( 'Product image', 'yith-woocommerce-bulk-product-editing' ),
	),
	'description'       => array(
		'type'  => 'textarea',
		'class' => 'yith-wcbep-add-product__textarea-editor',
		'label' => __( 'Description', 'yith-woocommerce-bulk-product-editing' ),
	),
	'short_description' => array(
		'type'  => 'textarea',
		'class' => 'yith-wcbep-add-product__textarea-editor',
		'label' => __( 'Short description', 'yith-woocommerce-bulk-product-editing' ),
	),
	'regular_price'     => array(
		'type'  => 'text',
		'class' => 'wc_input_price',
		'label' => __( 'Regular price', 'yith-woocommerce-bulk-product-editing' ),
	),
	'sale_price'        => array(
		'type'  => 'text',
		'class' => 'wc_input_price',
		'label' => __( 'Sale price', 'yith-woocommerce-bulk-product-editing' ),
	),
	'schedule_from'     => array(
		'type'  => 'datepicker',
		'class' => 'yith-plugin-fw-datepicker yith-wcbep-add-product__sale-price-from',
		'label' => __( 'From', 'yith-woocommerce-bulk-product-editing' ),
	),
	'schedule_to'       => array(
		'type'  => 'datepicker',
		'class' => 'yith-plugin-fw-datepicker yith-wcbep-add-product__sale-price-to',
		'label' => __( 'To', 'yith-woocommerce-bulk-product-editing' ),
	),
	'categories'        => array(
		'type'     => 'ajax-terms',
		'data'     => array(
			'placeholder' => __( 'Search for a category&hellip;', 'yith-woocommerce-bulk-product-editing' ),
			'taxonomy'    => 'product_cat',
		),
		'multiple' => true,
		'label'    => __( 'Categories', 'yith-woocommerce-bulk-product-editing' ),
	),
	'status'            => array(
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'options' => get_post_statuses(),
		'label'   => __( 'Status', 'yith-woocommerce-bulk-product-editing' ),
		'value'   => 'publish',
	),
);

?>

<div class="yith-wcbep-modal-content-wrapper yith-wcbep-add-product-modal-content-wrapper">
	<?php foreach ( $fields as $field_id => $field ) : ?>
		<?php $field['id'] = 'yith-wcbep-add-product-' . $field_id . '-field'; ?>
		<div class="yith-wcbep-add-product-field-wrapper yith-wcbep-add-product-field-<?php echo esc_attr( $field_id ); ?>-wrapper">
			<span class="yith-wcbep-add-product-field__label yith-wcbep-add-product-field-<?php echo esc_attr( $field_id ); ?>__label">
				<?php echo esc_html( $field['label'] ?? '' ); ?>
			</span>
			<div class="yith-wcbep-add-product-field yith-wcbep-add-product-field-<?php echo esc_attr( $field_id ); ?>">
				<?php yith_plugin_fw_get_field( $field, true, false ); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
