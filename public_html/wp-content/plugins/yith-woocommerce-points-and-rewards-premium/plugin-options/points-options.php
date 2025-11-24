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
	'points' => array(
		'points-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => apply_filters( 'yith_wpar_points_options_subtabs', array(
				'points-standard'      => array(
					'title'       => esc_html__( 'Points Assignment', 'yith-woocommerce-points-and-rewards' ),
					'description' => esc_html__(
						'Set how to assign and remove points to your customers.',
						'yith-woocommerce-points-and-rewards'
					),
				),
				'points-earning-rules' => array(
					'title' => esc_html__( 'Points Rules', 'yith-woocommerce-points-and-rewards' ),
				),
				'points-extra'         => array(
					'title'       => esc_html__( 'Extra Points', 'yith-woocommerce-points-and-rewards' ),
					'description' => esc_html__(
						'Choose whether or not to award extra points to your customers when specific conditions are met.',
						'yith-woocommerce-points-and-rewards'
					),
				),
				'levels-badges'        => array(
					'title' => esc_html__( 'Levels & Badges', 'yith-woocommerce-points-and-rewards' ),
				),
				'banners'              => array(
					'title' => esc_html__( 'Banners', 'yith-woocommerce-points-and-rewards' ),
				),
				'ranking'              => array(
					'title'       => esc_html__( 'Ranking', 'yith-woocommerce-points-and-rewards' ),
					'description' => esc_html__(
						"Configure the customers' ranking according to the points they have collected",
						'yith-woocommerce-points-and-rewards'
					),
				),
			) ),
		),
	),
);

