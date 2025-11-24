<?php
/**
 * Options
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\BulkProductEditing
 */

defined( 'YITH_WCBEP' ) || exit(); // Exit if accessed directly.

$options = array(
	'settings' => array(
		'general-options'             => array(
			'title' => __( 'General Options', 'yith-woocommerce-bulk-product-editing' ),
			'type'  => 'title',
			'desc'  => '',
		),
		'round-prices'                => array(
			'id'        => 'yith-wcbep-round-prices',
			'name'      => __( 'Round up prices', 'yith-woocommerce-bulk-product-editing' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'yes',
			'desc'      => __( 'If enabled, the prices will be rounded up when bulk editing.', 'yith-woocommerce-bulk-product-editing' ),
		),
		'name-in-categories-dropdown' => array(
			'id'        => 'yith-wcbep-name-in-categories-dropdown',
			'name'      => __( 'Category name in categories dropdown', 'yith-woocommerce-bulk-product-editing' ),
			'type'      => 'yith-field',
			'yith-type' => 'radio',
			'options'   => array(
				'name'                      => esc_html__( 'Category only', 'yith-woocommerce-bulk-product-editing' ) . ' - <code>Man</code>',
				'name-with-slug'            => esc_html__( 'Category with its slug', 'yith-woocommerce-bulk-product-editing' ) . ' - <code>Man (man-shirts)</code>',
				'parent-and-name'           => esc_html__( 'Parent and sub-category', 'yith-woocommerce-bulk-product-editing' ) . ' - <code>Clothing > Shirts > Man</code>',
				'parent-and-name-with-slug' => esc_html__( 'Parent and sub-category with its slug', 'yith-woocommerce-bulk-product-editing' ) . ' - <code>Clothing > Shirts > Man (man-shirts)</code>',
			),
			'default'   => 'name',
			'desc'      => __( 'Choose what to show as the category name in the categories dropdown list.', 'yith-woocommerce-bulk-product-editing' ),
		),
		'hidden-columns-per-user'     => array(
			'id'        => 'yith-wcbep-hidden-columns-per-user',
			'name'      => __( 'Save settings per user', 'yith-woocommerce-bulk-product-editing' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'desc'      => __( 'Enable to save settings (table views, columns visibility, etc.) per user instead of using the global ones.', 'yith-woocommerce-bulk-product-editing' ),
		),
		'use-regex-on-search'         => array(
			'id'        => 'yith-wcbep-use-regex-on-search',
			'name'      => __( 'Use regular expressions', 'yith-woocommerce-bulk-product-editing' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'desc'      => __( 'If enabled, the plugin will let you use regular expressions in the "Replace" option in the bulk product editing table.', 'yith-woocommerce-bulk-product-editing' ),
		),
		'use-light-query'             => array(
			'id'        => 'yith-wcbep-use-light-query',
			'name'      => __( 'Use light query', 'yith-woocommerce-bulk-product-editing' ),
			'type'      => 'yith-field',
			'yith-type' => 'onoff',
			'default'   => 'no',
			'desc'      => __( 'If enabled, the plugin uses a light query to retrieve products, so it improves your website performance. However, by enabling this option you CANNOT use advanced functionalities: for example, filtering variable products by price will not work. Please note: use it only if you have a huge amount of products.', 'yith-woocommerce-bulk-product-editing' ),
		),
		'general-options-end'         => array(
			'type' => 'sectionend',
		),
	),
);

return apply_filters( 'yith_wcbep_panel_settings_options', $options );
