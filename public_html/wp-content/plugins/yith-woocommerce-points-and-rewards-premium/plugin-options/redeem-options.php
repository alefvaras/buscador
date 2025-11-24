<?php
/**
 * Redeem Options
 *
 * @since   1.0.0
 * @author  YITH <plugins@yithemes.com>
 * @package YITH WooCommerce Points and Rewards
 */

defined( 'ABSPATH' ) || exit;

return array(
	'redeem' => array(
		'redeem-options' => array(
			'type'     => 'multi_tab',
			'sub-tabs' => array(
				'redeem-standard' => array(
					'title'       => esc_html__( 'Points Redeeming Options', 'yith-woocommerce-points-and-rewards' ),
					'description' => esc_html__( 'Set how to handle the redemption of points collected by customers', 'yith-woocommerce-points-and-rewards' ),
				),
				'rules'           => array(
					'title' => esc_html__( 'Points Redeeming Rules', 'yith-woocommerce-points-and-rewards' ),
				),
			),
		),
	),
);


