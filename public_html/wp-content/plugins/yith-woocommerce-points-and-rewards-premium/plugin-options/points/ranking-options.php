<?php
/**
 * Plugin Options
 *
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;


return array(
	'ranking' => array(
		'ranking_title'               => array(
			'type' => 'title',
			'name' => esc_html__( "Customers' ranking", 'yith-woocommerce-points-and-rewards' ),
			'id'   => 'ywpar_ranking_title',
		),
		'enable_ranking'              => array(
			'name'              => esc_html__( 'Enable customer ranking', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				"Enable the customers' ranking by points",
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_enable_ranking',
			'default'           => 'yes',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_points_upon_sales',
				'data-deps_value' => 'yes',
			),
		),
		'show_ranking'                => array(
			'name'              => esc_html__( 'Show ranking in My Account', 'yith-woocommerce-points-and-rewards' ),
			'desc'              => esc_html__(
				"Enable to show the customers' ranking on their My Account page",
				'yith-woocommerce-points-and-rewards'
			),
			'yith-type'         => 'onoff',
			'type'              => 'yith-field',
			'id'                => 'ywpar_show_ranking',
			'default'           => 'yes',
			'custom_attributes' => array(
				'data-deps'       => 'ywpar_enable_ranking',
				'data-deps_value' => 'yes',
			),
		),

		'ranking_title_end'           => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_ranking_title_end',
		),

		'ranking_title_shortcode'     => array(
			'type' => 'title',
			'name' => esc_html__( 'Ranking shortcodes', 'yith-woocommerce-points-and-rewards' ),
			'desc' => esc_html(
				"Copy and paste these shortcodes into a custom page to show a list of your customers' points. You can also use the Gutenberg block.",
				'yith-woocommerce-points-and-rewards'
			),
			'id'   => 'ywpar_ranking_title_shortcode',
		),

		'ranking_shortcode'           => array(
			'name'      => esc_html__( 'Show ranking in My Account', 'yith-woocommerce-points-and-rewards' ),
			'desc'      => esc_html__(
				"Enable to show the customers' ranking on their My Account page",
				'yith-woocommerce-points-and-rewards'
			),
			'type'      => 'yith-field',
			'yith-type' => 'html',
			'html'      => include_once YITH_YWPAR_VIEWS_PATH . '/tabs/ranking-tab.php',
			'id'        => 'ranking_shortcode',
		),

		'ranking_title_shortcode_end' => array(
			'type' => 'sectionend',
			'id'   => 'ywpar_ranking_title_shortcode_end',
		),

	),
);

