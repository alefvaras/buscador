<?php
/**
 * Gutenberg options
 *
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() && ! empty( $_REQUEST ) && !isset( $_REQUEST['yith-plugin-fw-block-preview'] ) ) { 
	return;
}
$product_id      = 0;
$default_product = wc_get_products( array( 'limit' => 1, 'return' => 'ids' ) );
if ( $default_product ) {
	$product_id = $default_product[0];
}
$colors = ywpar_get_option(
	'single_product_points_message_colors',
	array(
		'text_color'       => '#000000',
		'background_color' => 'rgba(255,255,255,0)',
	)
);

$blocks = array(
	'yith-ywpar-customers-points'       => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Best Users List - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show the list of your best customers.', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'ywpar_customers_points',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-favorite',
		'do_shortcode'                 => 'yes',
		'keywords'                     => array(
			esc_html_x( 'YITH', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points and Rewards Widget', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'style'            => array(
				'type'    => 'radio',
				'label'   => esc_html_x( 'Style of list', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => 'simple',
				'options' => array(
					'simple' => esc_html_x( 'Simple', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
					'boxed'  => esc_html_x( 'Boxed', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				),
			),
			'tabs'             => array(
				'type'    => 'toggle',
				'label'   => esc_html_x( 'Tabs', '[gutenberg]: show or hide the tabs', 'yith-woocommerce-points-and-rewards' ),
				'default' => true,
				'helps'   => array(
					'yes' => esc_html__( 'Yes', 'yith-woocommerce-points-and-rewards' ),
					'no'  => esc_html__( 'No', 'yith-woocommerce-points-and-rewards' ),
				),
			),
			'num_of_customers' => array(
				'type'    => 'number',
				'label'   => esc_html_x( 'Number of customers', '[gutenberg]: number od customers to show', 'yith-woocommerce-points-and-rewards' ),
				'default' => 3,
			),
		),
	),
	'yith-ywpar-points'                 => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Customer Total Points - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show customer credit points.', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'yith_ywpar_points',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-person',
		'do_shortcode'                 => 'yes',
		'keywords'                     => array(
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'label' => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Text before points', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => esc_html_x( 'Your credit is ', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
			),
		),
	),
	'yith-ywpar-points-product-message' => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Points Message on Product Page - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show a single product page message', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'yith_points_product_message',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-product-description',
		'use_frontend_preview'         => true,
		'render_callback'              => function ( $attributes ) {
			$product = '';
			
			if ( defined( 'YITH_PLUGIN_FW_BLOCK_PREVIEW' ) && YITH_PLUGIN_FW_BLOCK_PREVIEW ) {
				if ( empty( $attributes['product_id'] ) ) {
					$products = wc_get_products( array( 'limit' => 1, 'return' => 'ids' ) );
					if ( ! ! $products ) {
						$product = 'product_id="' . $products[0] . '"';
					}
				} else {
					$product = 'product_id="' . $attributes['product_id'] . '"';
				}
			}

			$shortcode = '[yith_points_product_message ' . $product . ' message="' . esc_html( $attributes['message'] ) . '" text_color="' . $attributes['text_color'] . '" background_color="' . $attributes['background_color'] . '"]';
			return is_callable( 'apply_shortcodes' ) ? apply_shortcodes( $shortcode ) : do_shortcode( $shortcode );
		},
		'keywords'                     => array(
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points message', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'product_id'       => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Product id', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => $product_id,
			),
			'message'          => array(
				'type'    => 'textarea',
				'label'   => esc_html_x( 'Message', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => ywpar_get_option( 'single_product_message' ),
			),
			'text_color'       => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Text Color', '[gutenberg]: title of widget', 'yith-woocommerce-points-and-rewards' ),
				'default' => apply_filters( 'ywpar_single_product_message_text_color', $colors['text_color'] ),
			),
			'background_color' => array(
				'type'    => 'colorpicker',
				'label'   => esc_html_x( 'Background Color', '[gutenberg]: title of widget', 'yith-woocommerce-points-and-rewards' ),
				'default' => '#fff',
			),
		),
	),
	'yith-ywpar-threshold-points-message' => array(
		'style'                        => 'yith-ywraq-gutenberg',
		'title'                        => esc_html_x( 'Threshold Points Message - YITH WooCommerce Points and Rewards', '[gutenberg]: block name', 'yith-woocommerce-points-and-rewards' ),
		'description'                  => esc_html_x( 'Show messages about thresholds achieved on Cart & Checkout pages', '[gutenberg]: block description', 'yith-woocommerce-points-and-rewards' ),
		'shortcode_name'               => 'yith_checkout_thresholds_message',
		'elementor_map_from_gutenberg' => true,
		'elementor_icon'               => 'eicon-person',
		'do_shortcode'                 => 'yes',
		'keywords'                     => array(
			esc_html_x( 'Points and Rewards', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
			esc_html_x( 'Points', '[gutenberg]: keywords', 'yith-woocommerce-points-and-rewards' ),
		),
		'attributes'                   => array(
			'label' => array(
				'type'    => 'text',
				'label'   => esc_html_x( 'Text before points', '[gutenberg]: Name of gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
				'default' => esc_html_x( 'Your credit is ', '[gutenberg]: Label for gutenberg attribute', 'yith-woocommerce-points-and-rewards' ),
			),
		),
	),
);
return apply_filters( 'ywraq_gutenberg_blocks', $blocks );
